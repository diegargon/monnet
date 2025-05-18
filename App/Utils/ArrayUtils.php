<?php

namespace App\Utils;

class ArrayUtils
{
    /**
     *
     * @param array<array<string, string>> $ary
     * @param string $sortKey
     * @param string $order
     * @return void
     */
    public static function order(array &$ary, string $sortKey, string $order = 'asc'): void
    {

        usort($ary, function ($a, $b) use ($sortKey, $order) {
            if (!isset($a[$sortKey]) || !isset($b[$sortKey])) {
                return false;
            }

            $itemA = $a[$sortKey];
            $itemB = $b[$sortKey];

            if ($order === 'desc') {
                return ($itemA < $itemB) ? 1 : -1;
            } else {
                return ($itemA < $itemB) ? -1 : 1;
            }
        });
    }

    /**
     *
     * @param array<array<string, string>> $ary
     * @return void
     */
    public static function orderByDate(array &$ary): void
    {
        usort($ary, function ($a, $b) {
            $itemA = strtotime($a['date']);
            $itemB = strtotime($b['date']);

            return ($itemA < $itemB) ? 1 : -1;
        });
    }

    /**
     *
     * @param array<array<string, string>> $ary
     * @return void
     */
    public static function orderByName(array &$ary): void
    {
        $elementZero = array_shift($ary);

        usort($ary, function ($a, $b) {
            $itemA = $a['name'];
            $itemB = $b['name'];

            return ($itemA < $itemB) ? -1 : 1;
        });
        array_unshift($ary, $elementZero);
    }

    /**
     * @param array<int|string, mixed> $ary
     *
     * @return string
     */
    public static function array2String(array $ary): string
    {
        $result = [];
        foreach ($ary as $subarray) {
            if (is_array($subarray)) {
                $result[] = self::array2String($subarray) . '::';
            } else {
                $result[] = $subarray;
            }
        }
        return implode(', ', $result);
    }

    /**
     * Renders a nested array as an HTML unordered list with collapsible functionality.
     *
     * @param array<string,mixed> $array The input array (can be nested).
     * @param bool $omitEmpty Whether to omit keys with null/empty values (default: true).
     * @return string The generated HTML string with collapsible arrays.
     */
    public static function array2Html(array $array, bool $omitEmpty = true): string
    {
        static $idCounter = 0; // To ensure unique IDs for toggle buttons and sections
        $html = '<ul>';

        foreach ($array as $key => $value) {
            // Skip empty values if $omitEmpty is true
            if (
                $omitEmpty && (is_null($value) || $value === '' ||
                (is_array($value) && empty(array_filter($value, fn($v) => $v !== '' && $v !== null))))
            ) {
                continue;
            }

            $id = 'section_' . $idCounter++; // Unique ID for collapsible sections

            if (is_array($value)) {
                $html .= '<li>';
                $html .= "<button onclick=\"toggleSection('$id')\">[+] $key</button>";
                $html .= "<div id=\"$id\" class=\"hidden-section\">";
                $html .= self::array2Html($value, $omitEmpty); // Recursively render nested arrays
                $html .= '</div>';
                $html .= '</li>';
            } elseif (is_string($value) && strpos($value, "\n") !== false) {
                // Handle multiline strings (e.g., stdout content)
                $lines = explode("\n", $value);
                $html .= '<li>';
                $html .= "<button onclick=\"toggleSection('$id')\">[+] $key</button>";
                $html .= "<div id=\"$id\" class=\"hidden-section\"><ul>";
                foreach ($lines as $line) {
                    $html .= "<li><pre>" . htmlspecialchars($line) . "</pre></li>";
                }
                $html .= '</ul></div></li>';
            } else {
                $html .= '<li><pre>';
                $html .= "<strong>$key:</strong> " . htmlspecialchars($value);
                $html .= '</pre></li>';
            }
        }

        $html .= '</ul>';
        return $html;
    }

    /**
     * Used in ansible report template to extract data msg recursiively
     *
     * @param array<string,string> $data
     * @return array<string, string>
     */
    public static function extractMessages(array $data): array
    {
        $messages = [];

        foreach ($data as $key => $value) {
            if ($key === 'msg') {
                if (is_array($value)) {
                    $flattened = [];
                    array_walk_recursive($value, function ($item) use (&$flattened) {
                        $flattened[] = $item;
                    });
                    $messages[] = "\n\t" . implode("\n", $flattened);
                } else {
                    $messages[] = "\n\t" . $value;
                }
            } elseif (is_array($value)) {
                $messages = array_merge($messages, self::extractMessages($value));
            }
        }

        return $messages;
    }

}