<?php

/**
 *  Action Functions
 * 
 *  @author diego////@////envigo.net
 *  @package ProjectBase
 *  @subpackage CORE
 *  @copyright Copyright @ 2016 - 2021 Diego Garcia (diego////@////envigo.net) 
 * 
 *  Class: register_uniq_action("action",  array($class, "method"));
 *  Function: register_uniq_action("action", "function")) * 
 */
!defined('IN_WEB') ? exit : true;

/**
 * @global array $actions
 */
global $actions;
$actions = [];

/**
 * Register a new a event
 * 
 * @global array $actions
 * @param string $event Event name
 * @param string $func  Function to be called
 * @param int $priority
 */
function register_action($event, $func, $priority = 5) {
    global $actions;

    $actions[$event][] = ['function_name' => $func, 'priority' => $priority];
}

/**
 * Add a action if exist replace
 * 
 * @global array $actions
 * @param string $event
 * @param string $func
 * @param int $priority
 */
function register_uniq_action($event, $func, $priority = 5) {
    global $actions;

    //replace
    foreach ($actions as $key => $value) {
        if ($key == $event) {
            $actions[$key][0] = ['function_name' => $func, 'priority' => $priority];
        }
    }
    //add
    $actions[$event][] = ['function_name' => $func, 'priority' => $priority];
}

/**
 * Execute the action
 * 
 * @global array $actions
 * @param string $event
 * @param mixed $params
 * @return boolean
 */
function do_action($event, &$params = null) {
    global $actions;

    if (isset($actions[$event])) {
        usort($actions[$event], function($a, $b) {
            return $a['priority'] - $b['priority'];
        });

        foreach ($actions[$event] as $func) {

            if (is_array($func['function_name'])) {
                if (method_exists($func['function_name'][0], $func['function_name'][1])) {
                    if (isset($return)) {
                        $return .= call_user_func_array($func['function_name'], [&$params]);
                    } else {
                        $return = call_user_func_array($func['function_name'], [&$params]);
                    }
                }
            } else {
                if (function_exists($func['function_name'])) {
                    if (isset($return)) {
                        $return .= call_user_func_array($func['function_name'], [&$params]);
                    } else {
                        $return = call_user_func_array($func['function_name'], [&$params]);
                    }
                }
            }
        }
    }
    if (isset($return)) {
        return $return;
    } else {
        return false;
    }
}

/**
 * Check if event name exists
 * 
 * @global array $actions
 * @param string $this_event
 * @return boolean
 */
function action_isset($this_event) {
    global $actions;

    foreach ($actions as $event => $func) {
        if (($event == $this_event) && function_exists($func[0])) {
            return true;
        }
    }

    return false;
}
