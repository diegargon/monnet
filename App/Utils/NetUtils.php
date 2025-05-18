<?php
namespace App\Utils;

use App\Services\DateTimeService;
use App\Services\Filter;

class NetUtils
{
    /**
     *
     * @param User $user
     * @param int $id
     * @param string $img_url
     * @param int $renew
     * @return string|bool
     */
    public static function cachedImg(User $user, int $id, string $img_url, int $renew = 0): string|bool
    {
        $http_options = [];

        $cache_path = 'cache';
        $http_options['timeout'] = 5; //seconds
        $http_options['max_redirects'] = 2;
        //$http_options['request_fulluri'] = true;
        $http_options['ssl']['verify_peer'] = false;
        $http_options['ssl']['verify_peer_name'] = false;
        $http_options['header'] = "User-agent: Mozilla/5.0 (X11; Fedora;" .
            "Linux x86_64; rv:52.0) Gecko/20100101 Firefox/52.0";

        if (empty($img_url) || is_dir($img_url) || empty($id)) {
            return false;
        }

        if (!Filter::varImgUrl($img_url)) {
            throw new \InvalidArgumentException($img_url . ' invalid image url');
        }

        if (!is_writeable($cache_path)) {
            throw new \RuntimeException($cache_path . ' is not writable');
        }

        $file_name = basename($img_url);

        $cache_img_path = $cache_path . '/' . $id . '_' . $file_name;

        if (file_exists($cache_img_path) && $renew === 0) {
            return $cache_img_path;
        } else {
            $img_item_check = $user->getPref($img_url);
            if ($img_item_check) {
                $img_item_check = new \DateTime($img_item_check);
                $img_item_check->modify('+48 hours');

                if ($img_item_check > new \DateTime(DateTimeService::dateNow())) :
                    return $img_url;
                endif;
            }

            $ctx = stream_context_create(['http' => $http_options]);
            $img_file = @file_get_contents($img_url, false, $ctx);
            if ($img_file !== false) {
                if (file_put_contents($cache_img_path, $img_file) !== false) :
                    return $cache_img_path;
                endif;
            } else {
                $user->setPref($img_url, DateTimeService::dateNow());
                $error = error_get_last();
                throw new \RuntimeException('Error getting image error msg ' . ($error['message'] ?? 'unknown'));
            }
        }

        return $img_url;
    }

    /**
     *
     * @param string $url
     * @return string|false
     */
    public static function baseUrl(string $url): string|false
    {
        $parsed_url = parse_url($url);

        if ($parsed_url === false) {
            throw new \InvalidArgumentException('Cant parse url: ' . $url);
        }

        if (isset($parsed_url['fragment'])) {
            unset($parsed_url['fragment']);
        }

        if (empty($parsed_url['scheme']) || empty($parsed_url['host'])) :
            throw new \InvalidArgumentException('Cant parse url: ' . $url);
        endif;

        $base_url = $parsed_url['scheme'] . '://' . $parsed_url['host'];

        if (isset($parsed_url['port'])) {
            $base_url .= ':' . $parsed_url['port'];
        }

        return $base_url;
    }

    /**
     * Send Wake-on-LAN magic packet.
     *
     * @param string $host_mac
     * @return bool
     * @throws \InvalidArgumentException|\RuntimeException
     */
    public static function sendWOL(string $host_mac): bool
    {
        $host_mac = str_replace([':', '-'], '', $host_mac);

        if (strlen($host_mac) % 2 !== 0) {
            throw new \InvalidArgumentException("MAC address must be even: \"{$host_mac}\"");
        }

        $macAddressBinary = hex2bin($host_mac);
        if ($macAddressBinary === false) {
            throw new \InvalidArgumentException("MAC address is not correct: \"{$host_mac}\"");
        }
        $magicPacket = str_repeat(chr(255), 6) . str_repeat($macAddressBinary, 16);
        $socket = socket_create(AF_INET, SOCK_DGRAM, SOL_UDP);
        if ($socket === false) {
            throw new \RuntimeException("Error creating socket: " . socket_strerror(socket_last_error()));
        }

        socket_set_option($socket, SOL_SOCKET, SO_BROADCAST, 1);
        $result = socket_sendto($socket, $magicPacket, strlen($magicPacket), 0, '255.255.255.255', 9);

        // Cerrar el socket
        socket_close($socket);

        if ($result) {
            return true;
        } else {
            throw new \RuntimeException("Failed sending WOL packet to {$host_mac}");
        }
    }

}
