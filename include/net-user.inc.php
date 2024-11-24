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
        Log::err("MAC address must be even \"{$host_mac}\"");
        return false;
    }

    $macAddressBinary = hex2bin($host_mac);
    $magicPacket = str_repeat(chr(255), 6) . str_repeat($macAddressBinary, 16);
    $socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
    if ($socket === false) {
        Log::err("Error creating socket " . socket_strerror(socket_last_error()));
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

/**
 *
 * @param string $input
 * @return array<int, array<string, int|float|string>>
 */
function validatePortsInput(string $input): array
{
    // Split the input by commas
    $values = explode(',', $input);
    $valid_values = [];

    foreach ($values as $value) {
        // Split the value by /
        $parts = explode('/', $value);

        // Check if there are three parts
        if (count($parts) == 3) {
            // Check if the first part is a number between 1 and 65535
            if (is_numeric($parts[0]) && $parts[0] >= 1 && $parts[0] <= 65535) {
                // Check if the second part is either 'tcp' or 'udp'
                if ($parts[1] === "tcp" || $parts[1] === "udp") {
                    $port_type = $parts[1] === "tcp" ? 1 : 2;
                    $name = $parts[2]; // Get the port name
                    // Add the valid port information to the array
                    $valid_values[] = [
                        'n' => $parts[0],
                        'name' => $name,
                        'port_type' => $port_type,
                        'user' => 1,
                        'online' => 0,
                        'latency' => 0.0
                    ];
                }
            }
        }
    }

    return $valid_values; // Return the array of valid values
}
