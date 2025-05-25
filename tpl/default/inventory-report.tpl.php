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
<div class="inventory-report-container">
    <h2>Inventario de Hosts</h2>
    <table class="inventory-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>IP</th>
                <th>MAC</th>
                <th>Red</th>
                <th>Online</th>
                <th>Categoria</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($tdata['hosts'] as $host): ?>
            <tr>
                <td><?= $host['id'] ?></td>
                <td><?= $host['hostname'] ?? $host['title'] ?? $host['ip'] ?></td>
                <td><?= $host['ip'] ?></td>
                <td><?= $host['mac'] ?? '' ?></td>
                <td><?= $host['network'] ?></td>
                <td><?= $host['online'] ? 'Sí' : 'No' ?></td>
                <td><?= $host['category'] ?? '' ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <h2>Redes</h2>
    <table class="inventory-table" style="min-width:600px;">
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
</div>
