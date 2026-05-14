<?php

namespace App\Service;

class PKIService
{
    private string $certsDir;

    public function __construct(string $projectDir)
    {
        $this->certsDir = $projectDir . '/certs';
    }

    /**
     * Sign a CSR and return the certificate content
     */
    public function signCSR(string $csrContent, string $agentId): string
    {
        $caCert = 'file://' . $this->certsDir . '/ca.crt';
        $caKey = 'file://' . $this->certsDir . '/ca.key';

        if (!file_exists($this->certsDir . '/ca.crt')) {
            throw new \Exception("Internal CA not found. Run scripts/generate-certs.sh first.");
        }

        $x509 = openssl_csr_sign($csrContent, $caCert, $caKey, 365, ['digest_alg' => 'sha256']);
        if (!$x509) {
            throw new \Exception("Failed to sign CSR: " . openssl_error_string());
        }

        openssl_x509_export($x509, $certOut);

        return $certOut;
    }

    public function getCACert(): string
    {
        return file_get_contents($this->certsDir . '/ca.crt');
    }
}
