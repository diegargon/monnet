<?php
/**
 *
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */

class EventType
{
    use ConstantsUtils;

    public const HIGH_IOWAIT = 1;
    public const HIGH_MEMORY_USAGE = 2;
    public const HIGH_DISK_USAGE = 3;
    public const HIGH_CPU_USAGE = 4;
    public const STARTING = 5;
    public const AGENT_SHUTDOWN = 6;
    public const SYSTEM_SHUTDOWN = 7;
    public const PORT_UP = 8;
    public const PORT_DOWN = 9;
    public const PORT_NEW = 10;
    public const SEND_STATS = 11;
    public const SERVICE_NAME_CHANGE = 12;
    public const HOST_INFO_CHANGE = 13;
    public const HOST_BECOME_ON = 14;
    public const HOST_BECOME_OFF = 15;
    public const NEW_HOST_DISCOVERY = 16;
    public const CERT_ERROR = 17;
    public const PORT_NEW_LOCAL = 18;
    public const PORT_UP_LOCAL = 19;
    public const PORT_DOWN_LOCAL = 20;
}
