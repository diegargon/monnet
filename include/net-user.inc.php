<?php

/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2024 Diego Garcia (diego/@/envigo.net)
 */
!defined('IN_WEB') ? exit : true;

/**
 *
 * @param string $host_mac
 * @return bool
 */
function sendWOL(string $host_mac): bool
{

    Log::debug("checking mac \"{$host_mac}\"");
    $host_mac = str_replace([':', '-'], '', $host_mac);

    if (strlen($host_mac) % 2 !== 0) {
        Log::error("MAC address must be even \"{$host_mac}\"");
        return false;
    }

    $macAddressBinary = hex2bin($host_mac);
    if ($macAddressBinary === false) :
        Log::error("MAC address is not correct \"{$host_mac}\"");
        return false;
    endif;
    $magicPacket = str_repeat(chr(255), 6) . str_repeat($macAddressBinary, 16);
    $socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
    if ($socket === false) {
        Log::error("Error creating socket " . socket_strerror(socket_last_error()));
        return false;
    }

    socket_set_option($socket, SOL_SOCKET, SO_BROADCAST, 1);
    $result = socket_sendto($socket, $magicPacket, strlen($magicPacket), 0, '255.255.255.255', 9);

    if ($result) {
        Log::debug("Successful sending WOL packet to {$host_mac}");
    } else {
        Log::debug("Failed sending WOL packet to {$host_mac}");
    }
    // Cerrar el socket
    socket_close($socket);

    return $result ? true : false;
}
