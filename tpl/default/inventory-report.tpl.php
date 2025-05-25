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
    // Crear un mapa de VLANs por ID de red
    $vlan_map = [];
    foreach ($tdata['networks'] as $net) {
        $vlan_map[$net['id']] = [
            'vlan' => $net['vlan'] ?? '',
            'name' => $net['name'] ?? ''
        ];
    }
    foreach ($tdata['hosts'] as $host) {
        // Construir display_name
        if (!empty($host['title'])) {
            $host['display_name'] = $host['title'];
        } elseif (!empty($host['hostname'])) {
            $host['display_name'] = explode('.', $host['hostname'])[0];
        } else {
            $host['display_name'] = $host['ip'];
        }

        // Agregar campo vlan (name(vlanid)) si corresponde
        if (isset($host['network']) && isset($vlan_map[$host['network']])) {
            $host['vlan'] = $vlan_map[$host['network']]['name'] . ' (' . $vlan_map[$host['network']]['vlan'] . ')';
        } else {
            $host['vlan'] = '';
        }

        // Formatear fecha de last_seen
        if (!empty($host['last_seen'])) {
            try {
                $dt = new DateTime($host['last_seen']);
                $host['last_seen_fmt'] = $dt->format('Y-m-d');
            } catch (\Exception $e) {
                $host['last_seen_fmt'] = '';
            }
        } else {
            $host['last_seen_fmt'] = '';
        }

        if (isset($host['network']) && $host['network'] !== '' && $host['network'] !== null) {
            $hosts_by_network_id[$host['network']][] = $host;
        } else {
            $hosts_no_network[] = $host;
        }
    }
    ?>

    <?php foreach ($tdata['networks'] as $net): ?>
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
                    <th>Online</th>
                    <th>Rol</th>
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
                        <td><?= $host['online'] ? 'Sí' : 'No' ?></td>
                        <td><?= $host['rol_name'] ?? '' ?></td>
                        <td><?= $host['category'] ?? '' ?></td>
                        <td><?= $host['last_seen_fmt'] ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="10" style="text-align:center;">Sin hosts en esta red</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
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
                    <td><?= $host['mac'] ?? '' ?></td>
                    <td><?= $host['online'] ? 'Sí' : 'No' ?></td>
                    <td><?= $host['rol_name'] ?? '' ?></td>
                    <td><?= $host['category'] ?? '' ?></td>
                    <td><?= $host['last_seen_fmt'] ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
