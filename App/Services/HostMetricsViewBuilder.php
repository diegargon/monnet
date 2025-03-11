<?php
/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */

namespace App\Services;

use App\Services\TemplateService;

class HostMetricsViewBuilder
{
    private TemplateService $templateService;
    private \AppContext $ctx;

    public function __construct(\AppContext $ctx)
    {
        $this->ctx = $ctx;
        $this->templateService = new TemplateService($ctx);
    }

    public function build(int $hid, string $title, int $type, array $metrics)
    {
        $tdata['graph_name'] = $title;
        $tdata['type'] = $type;
        $tdata['host_id'] = $hid;
        $tdata['data'] = $metrics;

        return $this->templateService->getTpl('chart-time-js', $tdata);
    }
}
