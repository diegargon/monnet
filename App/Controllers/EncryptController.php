<?php
/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 *
 * RSA 2048 - 245 bytes max -> 60 chars max -> base64 -> varchar(341)
 * RSA 4096 - 501 bytes max -> 60 chars     -> base64 -> varchar(681)
 *
 * Limit to allow both: 60 chars -> varchar(700)
 */

namespace App\Controllers;

use App\Services\EncryptionService;

class EncryptController
{
    private \AppContext $ctx;
    private EncryptionService $encryptService;
    private int $max_chars = 60;

    public function __construct(\AppContext $ctx)
    {
        $this->ctx = $ctx;

        $this->encryptService = new EncryptionService($this->ctx);
    }

    /**
     *
     * @param string $raw_data
     * @return array<string, string>
     */

    public function encrypt(string $raw_data): array
    {
        if (mb_strlen($raw_data, 'UTF-8') > $this->max_chars) {
            return [
                'status' => 'Fail',
                'error_msg' => 'Input data exceeds the maximum allowed length of ' . $this->max_chars . ' characters.'
            ];
        }

        try {
            $byte_length = strlen(utf8_decode($raw_data)); // Número de bytes en la cadena UTF-8

            if ($byte_length > 245) {
                return [
                    'status' => 'Fail',
                    'error_msg' => 'Input data exceeds the maximum byte length for RSA encryption (245 bytes).'
                ];
            }
            $encryptedData = $this->encryptService->encrypt($raw_data);

            return ['status' => 'Success', 'response_msg' => $encryptedData];
        } catch (\Exception $e) {
            return ['status' => 'Fail' , 'error_msg' => $e->getMessage()];
        }
    }
}
