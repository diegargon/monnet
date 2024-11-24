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
 * @param User $user
 * @param array<int, array<string, mixed>> $items_results
 * @return array<int, array<string, string>>
 */
function format_items(User $user, array $items_results): array
{
    $items = [];
    $theme = $user->getTheme();
    foreach ($items_results as $item) {
        $item_conf = json_decode($item['conf'], true);
        $item_img = '';
        if ($item_conf['image_type'] === 'favicon' && empty($item_conf['image_resource'])) {
            $item_img = $item_conf['url'] . '/favicon.ico';
            $item_img = cached_img($user, $item['id'], $item_img);
        } elseif ($item_conf['image_type'] === 'favicon') {
            $favicon_path = $item_conf['image_resource'];
            $item_img = base_url($item_conf['url']) . '/' . $favicon_path;
            $item_img = cached_img($user, $item['id'], $item_img);
        } elseif ($item_conf['image_type'] === 'url' && !empty($item_conf['image_resource'])) {
            $item_img = $item_conf['image_resource'];
            $item_img = cached_img($user, $item['id'], $item_img);
        } elseif ($item_conf['image_type'] === 'local_img') {
            $item_img = '/local_img/' . $item_conf['image_resource'];
        } else {
            $item_img = 'local_img/www.png';
        }

        $item['img'] = $item_img;
        $items[] = array_merge($item, $item_conf);
    }

    return $items;
}

/**
 *
 * @param array<string, mixed> $cfg
 * @param string $directory
 * @return array<int, array<string, string>>
 */
function getLocalIconsData(array $cfg, string $directory): array
{
    $allowedExtensions = $cfg['allowed_images_ext'];
    $imageData = [];

    if (is_dir($directory)) {
        $dir = new DirectoryIterator($directory);
        foreach ($dir as $fileinfo) {
            if ($fileinfo->isFile()) {
                $extension = strtolower($fileinfo->getExtension());
                if (in_array($extension, $allowedExtensions)) {
                    $imageData[] = [
                        'path' => $fileinfo->getPathname(),
                        'basename' => $fileinfo->getBasename()
                    ];
                }
            }
        }
    }

    return $imageData;
}
