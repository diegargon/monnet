<?php
/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */

namespace App\Services;

class EncryptionService
{
    private \Config $ncfg;
    private $publicKey;
    private $cipherType;

    public function __construct(\AppContext $ctx, string $cipherType = 'RSA')
    {
        $this->ncfg = $ctx->get('Config');

        $public_cert_path = $this->ncfg->get('public_cert_path');
        if (empty($public_cert_path) || !file_exists($public_cert_path)) {
            return false;
        }
        $this->publicKey = file_get_contents($public_cert_path);

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
            throw new Exception("Error al cifrar los datos con RSA.");
        } else {
            throw new Exception("Algoritmo de cifrado no soportado.");
        }
    }

}
