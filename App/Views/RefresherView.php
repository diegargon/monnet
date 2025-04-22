<?php
/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */

use App\Services\TemplateService;

namespace App\Views;

class RefresherView
{
    private $templates;

    public function __construct(TemplateService $templates)
    {
        $this->templates = $templates;
    }

    public function renderHighlightHosts(array $hosts_view, string $title, string $containerId): array
    {
        return [
            'data' => $this->templates->getTpl('hosts-min', [
                'hosts' => $hosts_view,
                'container-id' => $containerId,
                'head-title' => $title,
            ]),
            'cfg' => ['place' => '#host_place'],
        ];
    }

    public function renderOtherHosts(array $hosts_view, string $title, string $containerId): array
    {
        return [
            'data' => $this->templates->getTpl('hosts-min', [
                'hosts' => $hosts_view,
                'container-id' => $containerId,
                'head-title' => $title,
            ]),
            'cfg' => ['place' => '#host_place'],
        ];
    }

    public function renderTermLogs(array $log_lines): array
    {
        return [
            'data' => $this->templates->getTpl('term', ['term_logs' => $log_lines]),
            'cfg' => ['place' => '#center-container'],
        ];
    }

    public function renderJson(array $data): void
    {
        header('Content-Type: application/json; charset=UTF-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
    }
}
