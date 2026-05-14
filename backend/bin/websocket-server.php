<?php

use App\Kernel;
use Symfony\Component\Dotenv\Dotenv;

require __DIR__.'/../vendor/autoload.php';

(new Dotenv())->bootEnv(__DIR__.'/../.env');

$kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$kernel->boot();

$host = '0.0.0.0';
$port = 8081;
$cmdDir = dirname(__DIR__) . '/var/commands';

if (!is_dir($cmdDir)) mkdir($cmdDir, 0777, true);

$context = stream_context_create([
    'ssl' => [
        'local_cert' => __DIR__.'/../certs/server.crt',
        'local_pk' => __DIR__.'/../certs/server.key',
        'cafile' => __DIR__.'/../certs/ca.crt',
        'verify_peer' => true,
        'allow_self_signed' => true,
    ]
]);

$server = stream_socket_server("ssl://$host:$port", $errno, $errstr, STREAM_SERVER_BIND | STREAM_SERVER_LISTEN, $context);
if (!$server) die("Error: $errstr ($errno)\n");

echo "MVP Secure Server started on $port\n";

$sockets = [(int)$server => $server];
$handshaked = [];
$agentMap = [];

while (true) {
    $read = array_values($sockets);
    $write = $except = null;

    if (stream_select($read, $write, $except, 0, 100000) > 0) {
        foreach ($read as $s) {
            $id = (int)$s;
            if ($s === $server) {
                $conn = stream_socket_accept($server);
                if ($conn) $sockets[(int)$conn] = $conn;
            } else {
                if (!isset($handshaked[$id])) {
                    $header = fread($s, 2048);
                    if ($header && preg_match("/Sec-WebSocket-Key: (.*)\r\n/", $header, $matches)) {
                        $key = base64_encode(pack('H*', sha1($matches[1] . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
                        $upgrade = "HTTP/1.1 101 Switching Protocols\r\nUpgrade: websocket\r\nConnection: Upgrade\r\nSec-WebSocket-Accept: $key\r\n\r\n";
                        fwrite($s, $upgrade);
                        $handshaked[$id] = true;
                    }
                } else {
                    $data = fread($s, 8192);
                    if (!$data || feof($s)) {
                        unset($sockets[$id], $handshaked[$id]);
                        foreach($agentMap as $aId => $sid) if($sid === $id) unset($agentMap[$aId]);
                        fclose($s);
                        continue;
                    }
                    $payload = decode_ws_frame($data);
                    if ($payload) {
                        $msg = json_decode($payload, true);
                        if (isset($msg['agent_id'])) $agentMap[$msg['agent_id']] = $id;
                        echo "Received from agent: $payload\n";
                    }
                }
            }
        }
    }

    foreach (glob("$cmdDir/*.json") as $file) {
        $c = json_decode(file_get_contents($file), true);
        $targetId = $c['agent_id'] ?? null;
        if ($targetId && isset($agentMap[$targetId])) {
            $socket = $sockets[$agentMap[$targetId]];
            fwrite($socket, encode_ws_frame(json_encode($c)));
            echo "Command pushed to $targetId\n";
        }
        unlink($file);
    }

    usleep(10000); // Prevent high CPU
}

function decode_ws_frame($data) {
    $len = ord($data[1]) & 127;
    $offset = 2;
    if ($len === 126) {
        $len = unpack('n', substr($data, 2, 2))[1];
        $offset = 4;
    } elseif ($len === 127) {
        $len = unpack('J', substr($data, 2, 8))[1];
        $offset = 10;
    }

    $masks = substr($data, $offset, 4);
    $payload = substr($data, $offset + 4);
    $res = '';
    for ($i = 0; $i < strlen($payload); $i++) {
        $res .= $payload[$i] ^ $masks[$i % 4];
    }
    return $res;
}

function encode_ws_frame($p) {
    $l = strlen($p);
    if ($l <= 125) $h = pack('CC', 129, $l);
    elseif ($l < 65536) $h = pack('CCn', 129, 126, $l);
    else $h = pack('CCNN', 129, 127, 0, $l);
    return $h . $p;
}
