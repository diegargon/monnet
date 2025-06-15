<?php
/**
 *
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */

namespace App\Services;

use App\Core\AppContext;
use App\Core\Config;

class EncryptionService
{
    /**
     * @var Config
     */
    private Config $ncfg;

    /**
     * @var string
     */
    private string $publicKey;

    /**
     * @var string
     */
    private string $cipherType;

    public function __construct(AppContext $ctx, string $cipherType = 'RSA')
    {
        $this->ncfg = $ctx->get(Config::class);

        $public_cert_base64 = $this->ncfg->get('public_key');

        if (empty($public_cert_base64)) {
            throw new \RuntimeException("Missing required configuration: 'public_key'");
        }

        $public_cert = base64_decode($public_cert_base64, true);
        if ($public_cert === false) {
            throw new \InvalidArgumentException("Invalid base64 encoding for 'public_key'");
        }

        $this->publicKey = $public_cert;
        $this->cipherType = $cipherType;
    }

    /**
     * Cifra los datos con la clave pública o privada.
     *
     * @param string $data Los datos a cifrar
     * @return string Los datos cifrados en base64
     */
    public function encrypt(string $data): string
    {
        if ($this->cipherType === 'RSA') {
            $encryptedData = null;
            if ($this->publicKey && openssl_public_encrypt($data, $encryptedData, $this->publicKey)) {
                return base64_encode($encryptedData);
            }
            throw new \Exception("Error al cifrar los datos con RSA.");
        } else {
            throw new \Exception("Algoritmo de cifrado no soportado.");
        }
    }
}
