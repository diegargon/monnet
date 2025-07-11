<?php
/**
 * Inventory report template.
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */
/**
 * @var array $tdata
 *   - hosts: array
 *   - networks: array
 */
?>
<style>
@media print {
    body *,
    .inventory-report-container * {
        color: black !important;
    }
    body * {
        visibility: hidden !important;
        zoom: 98% !important;
    }
    .main_align_container {
        visibility: hidden !important;
    }
    .inventory-report-container, .inventory-report-container * {
        visibility: visible !important;

    }

    .inventory-table {
        width: 100% !important;
    }
    .inventory-report-container {
        position: static !important;
        background: white !important;
        overflow: visible !important;
    }
    button[onclick="window.print()"] {
        display: none !important;
    }
    tr:nth-child(2n) {
        background-color: #bfbfbf !important;
    }
    #stdbox-container {
        display: block !important;
        position: static !important;
        top: auto !important;
        overflow: visible !important;
        resize: none !important;
        background: white !important;
        z-index: auto !important;
        overflow: unset !important;
        position: unset !important;
        top: 0 !important;
        left: 0 !important;
        max-width: 100% !important;
    }
    #stdbox-content {
        max-height: fit-content !important;
        margin: 0px !important;
    }
    .term_container,
    #term_container,
    .left-container,
    .bookmarks-container,
    .header,
    .form_container {
        display: none !important;
    }
    #right-container {
        flex-grow: 0;
        overflow: visible !important;
        margin: 0 !important;
        width: 100% !important;
    }
    .main_container {
        overflow: visible !important;
    }
    .main {
        margin: 0 !important;
    }
    .stdbox-bar {
        display: none !important;
    }
}
</style>
<div class="inventory-report-container">
    <!-- Botón Imprimir -->
    <button onclick="window.print()" style="float:right; margin-bottom:10px;">🖨️ Imprimir</button>

    <h2>Redes</h2>
    <table class="inventory-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Red</th>
                <th>VLAN</th>
                <th>Pool</th>
                <th>Scan</th>
                <th>Online Only</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($tdata['networks'] as $net): ?>
            <tr>
                <td><?= $net['id'] ?></td>
                <td><?= $net['name'] ?></td>
                <td><?= $net['network'] ?></td>
                <td><?= $net['vlan'] ?></td>
                <td><?= $net['pool'] ?></td>
                <td><?= $net['scan'] ?></td>
                <td><?= $net['only_online'] ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <h2>Inventario de Hosts por Red</h2>
    <?php
    // Agrupar hosts por network (ID de red)
    $hosts_by_network_id = [];
    $hosts_no_network = [];
    foreach ($tdata['hosts'] as $host) {
        if (isset($host['network']) && $host['network'] !== '' && $host['network'] !== null) {
            $hosts_by_network_id[$host['network']][] = $host;
        } else {
            $hosts_no_network[] = $host;
        }
    }
    ?>

    <?php foreach ($tdata['networks'] as $net): ?>
        <div class="network-group network-group-<?= $net['id'] ?>">
            <h3><?= $net['name'] ?> (<?= $net['network'] ?>)</h3>
            <table class="inventory-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>IP</th>
                        <th>Hostname</th>
                        <th>VLAN</th>
                        <th>MAC</th>
                        <th>Vendor</th>
                        <th>Online</th>
                        <th>Rol</th>
                        <th>Linked</th>
                        <th>Categoria</th>
                        <th>Última conexión</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $hosts = isset($hosts_by_network_id[$net['id']]) ? $hosts_by_network_id[$net['id']] : [];
                ?>
                <?php if (!empty($hosts)): ?>
                    <?php foreach ($hosts as $host): ?>
                        <tr>
                            <td><?= $host['id'] ?></td>
                            <td><?= $host['display_name'] ?></td>
                            <td><?= $host['ip'] ?></td>
                            <td><?= $host['hostname'] ?? '' ?></td>
                            <td><?= $host['vlan'] ?></td>
                            <td><?= $host['mac'] ?? '' ?></td>
                            <td title="<?= $host['misc']['mac_vendor'] ?>">
                                <?= $host['vendor_short'] ?? '' ?>
                            </td>
                            <td><?= $host['online'] ? 'Sí' : 'No' ?></td>
                            <td><?= $host['rol_name'] ?? '' ?></td>
                            <td><?= $host['linked_name'] ?? '' ?></td>
                            <td><?= $host['category'] ?? '' ?></td>
                            <td><?= $host['last_seen_fmt'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="11" style="text-align:center;">Sin hosts en esta red</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    <?php endforeach; ?>

    <?php if (!empty($hosts_no_network)): ?>
        <h3>Sin Red</h3>
        <table class="inventory-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>IP</th>
                    <th>Hostname</th>
                    <th>VLAN</th>
                    <th>MAC</th>
                    <th>Online</th>
                    <th>Rol</th>
                    <th>Linked</th>
                    <th>Categoria</th>
                    <th>Última conexión</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($hosts_no_network as $host): ?>
                <tr>
                    <td><?= $host['id'] ?></td>
                    <td><?= $host['display_name'] ?></td>
                    <td><?= $host['ip'] ?></td>
                    <td><?= $host['hostname'] ?? '' ?></td>
                    <td><?= $host['vlan'] ?></td>
                    <td><?= $host['mac'] ?></td>
                    <td><?= $host['online'] ? 'Sí' : 'No' ?></td>
                    <td><?= $host['rol_name'] ?? '' ?></td>
                    <td><?= $host['linked_name'] ?? '' ?></td>
                    <td><?= $host['category'] ?? '' ?></td>
                    <td><?= $host['last_seen_fmt'] ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
