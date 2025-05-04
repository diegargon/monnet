<?php

/**
 *
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */

namespace App\Services;

class DateTimeService
{
    /**
     * @var string
     */
    private string $timezone = 'UTC';

    public function __construct(string $timezone = 'UTC')
    {
        $this->timezone = $timezone;
    }

    /**
     * Validate timezone
     *
     * @param string $timezone
     * @return bool
     */
    public function validTimezone(string $timezone): bool
    {
        if (empty($timezone)) {
            return false;
        }
        $valid_time_zones = timezone_identifiers_list();

        return in_array($timezone, $valid_time_zones);
    }

    /**
     *
     * @param string $timezone
     * @return string|false
     */
    public function dateNow(string $timezone = 'UTC'): string|false
    {
        if (!$this->validTimezone($timezone)) {
            return false;
        }
        $date_timezone = new \DatetimeZone($timezone);
        $date_now = new \DateTime('now', $date_timezone);

        return $date_now->format('Y-m-d H:i:s');
    }

    /**
     * Covert UTC to Timezone
     *
     * @param string $utc_date
     * @param string $timezone
     * @param string $time_format
     * @return string
     */
    public function utcToTz(string $utc_date, string $timezone, string $time_format = 'Y-m-d H:i:s'): string
    {
        $date = new \DateTime($utc_date, new \DateTimeZone('UTC'));
        $date->setTimezone(new \DateTimeZone($timezone));

        return $date->format($time_format);
    }

    /**
     *
     * @param string $timezone
     * @param string $time_format
     * @return string|false
     */
    public function formatDateNow(string $timezone = 'UTC', string $time_format = 'Y-m-d H:i:s'): string|false
    {
        if (!$this->validTimezone($timezone)) {
            return false;
        }
        $date_timezone = new \DatetimeZone($timezone);
        $date_now = new \DateTime('now', $date_timezone);

        return $date_now->format($time_format);
    }

    /**
     * Reformat string date
     *
     * @param string $date
     * @param string $time_format
     * @return string|false
     */
    public function formatDateString(string $date, string $time_format = 'Y-m-d H:i:s'): string|false
    {
        $timestamp = strtotime($date);

        return ($timestamp) ? date($time_format, $timestamp) : false;
    }

    /**
     *
     * @return string
     */
    public function datetimeMachine(): string
    {
        $now = new \DateTime();
        return $now->format('Y-m-d H:i:s');
    }

    /**
     * Convierte un timestamp UNIX a una fecha formateada en la zona horaria deseada.
     *
     * @param int $timestamp El timestamp UNIX a convertir.
     * @param string $timezone La zona horaria destino (ej: 'Europe/Madrid').
     * @param string $time_format El formato de salida (por defecto: 'Y-m-d H:i:s').
     * @return string Fecha formateada en la zona horaria especificada.
     */

    public function formatTimestamp(int $timestamp, string $timezone, string $time_format = 'Y-m-d H:i:s'): string
    {
        try {
            $date = new \DateTime('@' . $timestamp); // '@' indica un timestamp UNIX
            $date->setTimezone(new \DateTimeZone($timezone)); // Ajustar la zona horaria

            return $date->format($time_format);
        } catch (\Exception $e) {
            return 'Error: ' . $e->getMessage();
        }
    }
}
