<?php
/**
 *
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */

namespace App\Services;

use App\Core\AppContext;
use App\Services\TemplateService;

class HostMetricsViewBuilder
{
    private TemplateService $templateService;
    private AppContext $ctx;

    public function __construct(AppContext $ctx)
    {
        $this->ctx = $ctx;
        $this->templateService = new TemplateService($ctx);
    }

    /**
     * Build the metrics graph template
     *
     * @param int $hid Host ID
     * @param string $title Graph title
     * @param int $type Graph type (1: ping, 2: loadavg, 3: iowait, 4: memory)
     * @param array $metrics Metrics data
     * @return string Rendered template
     */
    public function build(int $hid, string $title, int $type, array $metrics)
    {
        $tdata = [
            'graph_name' => $title,
            'type' => $type,
            'host_id' => $hid,
            'data' => $metrics,
        ];

        return $this->templateService->getTpl('chart-time-js', $tdata);
    }
}
