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

class HostViewBuilder
{
    private TemplateService $templateService;
    private \AppContext $ctx;

    private $reportKeysToShow = ['id', 'display_name', 'ip', 'mac', 'online'];

    public function __construct(\AppContext $ctx)
    {
        $this->ctx = $ctx;
        $this->templateService = new TemplateService($ctx);
    }


    /**
     *
     * @param int $hid
     * @param array<string, string|int> $hostDetails
     * @return string the template
     */
    public function hostDetails(int $hid, array $hostDetails): string
    {
        $user = $this->ctx->get('User');
        $hostDetails = $this->buildStats($hostDetails);

        $tdata['theme'] = $user->getTheme();
        $tdata['host_details'] = $hostDetails;
        $tdata['host_details']['host_logs'] =  $this->templateService->getTpl(
            'term',
            [
                'term_logs' => '',
                'host_id' => $hid
            ]
        );

        return $this->templateService->getTpl('host-details', $tdata);
    }

    /**
     *
     * @param array<string, string|int> $tdata
     * @param array<string, string|int> $show_keys
     * @return string
     */
    public function buildHostReport(array $tdata, array $show_keys = []): string
    {

        $keysToShow = $this->reportKeysToShow;
        $keysToShow = array_merge($keysToShow, $show_keys);
        $tdata['keysToShow'] = $keysToShow;

        return  $this->templateService->getTpl('hosts-report', $tdata);
    }

    /**
     *
     * @param array<string, string|int> $hostDetails
     * @return array<string, string|int>
     */
    public function buildStats(array $hostDetails): array
    {
        if (!empty($hostDetails['mem_info']) && is_array($hostDetails['mem_info'])) :
            $hostDetails['mem_info'] = $this->templateService->getTpl(
                'progressbar',
                [
                    'progress_bar_data' => [$hostDetails['mem_info']]
                ]
            );
        endif;

        if (!empty($hostDetails['load_avg']) && is_array($hostDetails['load_avg'])) :
            $hostDetails['load_avg'] = $this->templateService->getTpl(
                'gauge',
                [
                    'gauge_graphs' => $hostDetails['load_avg']
                ]
            );
        endif;

        if (isset($hostDetails['iowait']) && is_numeric($hostDetails['iowait'])) :
            $hostDetails['iowait_stats'] = $this->templateService->getTpl(
                'gauge',
                [
                    'gauge_graphs' => [
                        ['legend' => 'IO Delay', 'min' => 0, 'max' => 100, 'value' => $hostDetails['iowait']]
                    ]
                ]
            );
        endif;

        if (!empty($hostDetails['disks_info']) && is_array($hostDetails['disks_info'])) :
            $hostDetails['disks_info'] = $this->templateService->getTpl(
                'progressbar',
                [
                    'progress_bar_data' => $hostDetails['disks_info']
                ]
            );
        endif;

        return $hostDetails;
    }
}
