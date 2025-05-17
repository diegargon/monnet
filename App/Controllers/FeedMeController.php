<?php
/**
 *
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */
/*
    {
        cmd: ping
        token: string
        version: 0.1
        data: []
    }
    {
        cmd: pong
        token: string
        version: 0.1
        response_msg: true
        refresh: 5 // Inform when is the next update
        data: []
    }

    Notifications
        {
            "id": id,
            "cmd": "notification",
            "token": token,
            "data":  data,
            "meta": meta
            "name": "notification name" // Mandatory
            "version": to remplace (field is in meta)
        }
            data {
                "msg": "Custom msg" //Optional
                ... other custom fields ...
            }

        "high_iowait", "iowait"
        "high_memory_usage", "memory_usage": meminfo_data
        "high_disk_usage", stats
        "high_cpu_usage", "cpu_usage": loadavg_data["usage"]
        "starting", "msg": "Custom msg"
        "shutdown", "msg": "Custom msg"
        "system_shutdown", "msg": "Custom msg"
*/


namespace App\Controllers;

use App\Services\FeedMeService;
use App\Services\LogSystemService;

class FeedMeController {
    private \AppContext $ctx;
    private FeedMeService $feedMeService;
    private LogSystemService $logSystemService;

    public function __construct(\AppContext $ctx)
    {
        $this->ctx = $ctx;
        $this->feedMeService = new FeedMeService($ctx);
        $this->logSystemService = new LogSystemService($ctx);
    }

    public function __destruct()
    {
        unset($this->ctx);
        unset($this->feedMeService);
        unset($this->logSystemService);
    }
    /**
     *
     * @return void
     */
    public function handleRequest(): void
    {
        $request_content = file_get_contents('php://input');

        if ($request_content === false) {
            $this->responseError('Error: file_get_contents');
        }

        $request = json_decode($request_content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->responseError('Invalid JSON received: ' . json_last_error_msg());
        }
        unset($request_content);

        $validation = $this->validateRequest($request);
        if (!empty($validation['error'])) {
            $this->responseError($request['error']);
        }

        $response = $this->feedMeService->processRequest($request);

        if (!empty($response['error'])) {
            $this->responseError($response['error']);
        } elseif ($response['success']) {
            $this->responseSuccess($response['response_data']);
        } elseif (empty($response)) {
            exit;
        }

        $this->responseError('Unknown error on handleRequest');
    }

    /**
     *
     * @param array<string, string|int> $request
     * @return array<string, string|int>
     */
    private function validateRequest(array $request): array
    {

        if (!isset($request['cmd'])) {
            return ['error' => 'Missing command on request'];
        }
        if (!is_numeric($request['id'])) {
            return ['error' => 'Missing id on request'];
        }
        if (empty($request['token'])) {
            return ['error' => 'Token missing'];
        }
        if (!is_array($request['data'])) {
            return [
                'error' =>  'Invalid data field recevive: not an array. ID: ' . $request['id']];
        }
        if (empty($request['version'])) {
            return ['error' => 'Missing version field'];
        }

        return ['success' => true];
    }

    /**
     *
     * @param string $msg
     * @return never
     */
    private function responseError(string $msg): never
    {
        $this->logSystemService->error($msg);

        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode([
            'error' => $msg
        ]);
        exit;
    }

    /**
     *
     * @param array<string, mixed> $response
     * @return never
     */
    private function responseSuccess(array $response): never
    {
        http_response_code(200);
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }
}
