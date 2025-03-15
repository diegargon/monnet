<?php
/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */

Namespace App\Controllers;

use App\Models\EncryptModel;
use App\Services\EncryptionService;


class EncryptController
{
    private \AppContext $ctx;
    private EncryptionService $encryptService;
    private EncryptModel $encryptModel;

    public function __construct(\AppContext $ctx)
    {
        $this->ctx = $ctx;

        $this->encryptModel = new EncryptModel($this->ctx);
        $this->encryptService = new EncryptionService($this->ctx);
    }

    /**
     *
     * @param string $raw_data
     * @return array<string, string>
     */
    public function encrypt(string $raw_data): array
    {
        try {
            $encryptedData = $this->encryptService->encrypt($raw_data);

            $this->dataModel->saveEncryptedData($encryptedData);

            return ['status' => 'Success'];
        } catch (Exception $e) {
            return ['status' => 'Fail' , 'error_msg' => $e->getMessage()];
        }
    }
}
