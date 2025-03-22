<?php

/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */

namespace App\Services;

use App\Services\DateTimeService;
use App\Services\HostMetricsViewBuilder;
use App\Models\HostMetricsModel;

class HostMetricsService
{
    private \AppContext $ctx;
    private HostMetricsViewBuilder $hostMetricsViewBuilder;
    private DateTimeService $dateTimeService;
    private \App\Models\HostMetricsModel $hostMetricsModel;

    public function __construct(\AppContext $ctx)
    {
        $this->ctx = $ctx;
        $this->dateTimeService = new DateTimeService();
        $this->hostMetricsViewBuilder = new HostMetricsViewBuilder($ctx);
        $this->hostMetricsModel = new HostMetricsModel($ctx);
    }

    /**
     *
     * @param int $hid
     * @return string
     */
    public function getMetricsGraph(int $hid): string
    {
        $lng = $this->ctx->get('lng');
        $metrics_tpl = '';
        $metrics_types = [1, 2, 3];

        foreach ($metrics_types as $metrics_type) {
            /* 1 ping 2 loadavg 3 iowait */
            $metrics = $this->getMetricsByType($hid, $metrics_type);
            if ($metrics) {
                switch ($metrics_type) {
                    case 1:
                        $title = $lng['L_LATENCY'];
                        break;
                    case 2:
                        $title = 'LoadAVG';
                        break;
                    case 3:
                        $title = 'IOWait';
                        break;
                }
                $metrics_tpl .= $this->hostMetricsViewBuilder->build($hid, $title, $metrics_type, $metrics);
            }
        }
        return $metrics_tpl;
    }

    /**
     *
     * @param int $hid
     * @param int $metrics_type
     * @return array<string, string|int>
     */
    public function getMetricsByType(int $hid, int $metrics_type): array
    {
        $metrics = $this->hostMetricsModel->getDbMetrics($hid, $metrics_type);
        /*
        if (!empty($metrics)) {
            $metrics = $this->fMetricsDate($metrics);
        }
        */

        return $metrics;
    }

    /**
     *
     * @param array<string, string> $metrics
     * @return array<string, string>
     */
    public function fMetricsDate(array $metrics): array
    {
        $cfg = $this->ctx->get('cfg');

        foreach ($metrics as &$metric) {
            $new_date = $this->dateTimeService->utcToTz($metric['date'], $cfg['timezone']);
            $metric['date'] = $new_date;
        }

        return $metrics;
    }
}
