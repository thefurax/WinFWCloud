<?php

use App\Kernel;
use Symfony\Component\Dotenv\Dotenv;

require __DIR__.'/../vendor/autoload.php';

(new Dotenv())->bootEnv(__DIR__.'/../.env');

$kernel = new Kernel($_SERVER['APP_ENV'], (bool) $_SERVER['APP_DEBUG']);
$kernel->boot();

$host = '0.0.0.0';
$port = 8081;

$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
socket_set_option($socket, SOL_SOCKET, SO_REUSEADDR, 1);
socket_bind($socket, $host, $port);
socket_listen($socket);
socket_set_nonblock($socket);

echo "MVP WebSocket Server (Select-based) started on $host:$port\n";

$clients = [$socket];
$agentConnections = [];

while (true) {
    $read = $clients;
    $write = $except = null;

    if (socket_select($read, $write, $except, 1) > 0) {
        foreach ($read as $s) {
            if ($s === $socket) {
                $newSocket = socket_accept($socket);
                $clients[] = $newSocket;
                $header = socket_read($newSocket, 1024);
                perform_handshake($newSocket, $header);
                echo "New connection established\n";
            } else {
                $data = socket_read($s, 2048);
                if ($data === false || $data === '') {
                    $key = array_search($s, $clients);
                    unset($clients[$key]);
                    socket_close($s);
                    echo "Connection closed\n";
                } else {
                    $payload = unmask($data);
                    echo "Received from agent: $payload\n";
                    // En production, on mapperait ici l'agent_id à la socket $s
                }
            }
        }
    }
}

function perform_handshake($client, $header) {
    if (preg_match("/Sec-WebSocket-Key: (.*)\r\n/", $header, $matches)) {
        $key = base64_encode(pack('H*', sha1($matches[1] . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11')));
        $upgrade = "HTTP/1.1 101 Switching Protocols\r\nUpgrade: websocket\r\nConnection: Upgrade\r\nSec-WebSocket-Accept: $key\r\n\r\n";
        socket_write($client, $upgrade, strlen($upgrade));
    }
}

function unmask($text) {
    $length = ord($text[1]) & 127;
    if ($length == 126) { $masks = substr($text, 4, 4); $data = substr($text, 8); }
    elseif ($length == 127) { $masks = substr($text, 10, 4); $data = substr($text, 14); }
    else { $masks = substr($text, 2, 4); $data = substr($text, 6); }
    $text = "";
    for ($i = 0; $i < strlen($data); ++$i) { $text .= $data[$i] ^ $masks[$i % 4]; }
    return $text;
}
