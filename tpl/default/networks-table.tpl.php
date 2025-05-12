<?php
/**
 *
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */
/**
 * In frontend->getTpl()
 * @var array<string> $lng
 * @var array<mixed> $tdata
 */

?>
<div class="report-table-container">
    <table>
        <thead>
            <tr>
                <?php
                foreach (
                    [
                        'id', 'ip', 'cidr', 'name', 'pool', 'only_online', 'vlan', 'scan', 'weight', 'clean', 'disable', 'occ'
                    ] as $header
                ) : ?>
                    <th><?= $header ?></th>
                    <?php
                endforeach;
                ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tdata['networks'] as $row) : ?>
                <tr data-id="<?= $row['id'] ?>">
                    <td>
                        <?= $row['id'] ?>
                    </td>
                    <td>
                        <input
                            type="text"
                            size="13"
                            maxlength="13"
                            name="network_<?= $row['id']?>" required value="<?= $row['ip'] ?>"
                        >
                    </td>
                    <td>
                        <input
                            type="text"
                            size="2"
                            maxlength="2"
                            name="networkCIDR_<?= $row['id']?>" required value="<?= $row['cidr'] ?>"
                        >
                    </td>
                    <td>
                        <input
                            type="text"
                            size="20"
                            maxlength="32"
                            name="networkName_<?= $row['id']?>" required value="<?= $row['name'] ?>"
                        >
                    </td>
                    <td>
                        <?php $checked = $row['pool'] ? 'checked' : ''; ?>
                        <input type="hidden" name="networkPool_<?= $row['id']?>" value="0" />
                        <input type="checkbox" name="networkPool_<?= $row['id']?>" value="1" <?= $checked ?> />
                    </td>
                    <td>
                        <?php $checked = !empty($row['only_online']) ? 'checked' : ''; ?>
                        <input type="hidden" name="networkOnlyOnline_<?= $row['id']?>" value="0" />
                        <input type="checkbox" name="networkOnlyOnline_<?= $row['id']?>" value="1" <?= $checked ?> />
                    </td>
                    <td>
                        <input
                            type="text"
                            size="3"
                            maxlength="5"
                            name="networkVLAN_<?= $row['id']?>" required value="<?= $row['vlan'] ?>"
                        >
                    </td>
                    <td>
                        <input type="hidden" name="networkScan_<?= $row['id']?>" value="0" />
                        <?php
                        if (
                            (strpos($row['ip'], '0') !== 0) &&
                            (strpos($row['ip'], '255.') !== 0)
                        ) :
                            $checked = $row['scan'] ? 'checked' : '';
                            ?>
                        <input type="checkbox" name="networkScan_<?= $row['id']?>" value="1" <?= $checked ?> />
                            <?php
                        endif;
                        ?>
                    </td>
                    <td>
                        <input
                            type="text"
                            size="3"
                            maxlength="5"
                            name="networkWeight_<?= $row['id']?>" required value="<?= $row['weight'] ?>"
                        >
                    </td>
                    <td>
                        <?php $checked = $row['clean'] ? 'checked' : ''; ?>
                        <input type="hidden" name="networkClean_<?= $row['id']?>" value="0" />
                        <input type="checkbox" name="networkClean_<?= $row['id']?>" value="1" <?= $checked ?> />
                    </td>
                    <td>
                        <?php $checked = $row['disable'] ? 'checked' : ''; ?>
                        <input type="hidden" name="networkDisable_<?= $row['id']?>" value="0" />
                        <input type="checkbox" name="networkDisable_<?= $row['id']?>" value="1" <?= $checked ?> />
                    </td>
                    <td>
                        <div class="occupancy">
                            <?= $row['occupancy'] ?>%
                        </div>
                    </td>
                    <td>
                        <button class="updateNetwork" data-id="<?= $row['id']?>"><?= $lng['L_UPDATE'] ?></button>
                        <button
                            onclick="submitCommand('mgmtNetworks',{id: <?= $row['id'] ?>, action: 'remove'})"
                            >
                            <?= $lng['L_DELETE'] ?>
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
