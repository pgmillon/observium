<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    Simple Observium API
 * @subpackage Billing module
 * @author     Dennis de Houx <dennis@aio.be>
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */


/**
 * Show the module data
 *
 * @return array
 *
*/
function api_module_data() {
  global $config, $vars;
  if ($config['api']['module']['billing']) {
    switch ($vars['type']) {
      case "list":
        $res = api_list_db($vars);
        break;
      case "traffic":
        $res = api_traffic_db($vars);
        break;
      case "history":
        $res = api_history_db($vars);
        break;
      default:
        $res = api_errorcodes("403");
    }
  } else {
    $res = api_errorcodes("211");
  }
  return $res;
}


/**
 * Check user permission to access the bill
 *
 * @return boolean
 * @param  bill_id
 *
*/
function api_bill_permitted($bill_id) {
  global $vars;
  $res     = false;
  if ($vars['user']['level'] >= 10) {
    $res   = true;
  } else {
    api_show_debug("Checking permission for bill", $bill_id);
    $row = dbFetchRow("SELECT * FROM `entity_permissions` WHERE `entity_type` = 'bill' AND `user_id` = ? AND `entity_id`= ? LIMIT 1", array($vars['user']['id'], $bill_id));
    if (is_array($row)) {
      $res  = true;
    }
  }
  api_show_debug("Returned bill permitted", $res);
  return $res;
}


/**
 * Grab the mysql list data
 *
 * @return array
 * @param  vars
 *
*/
function api_list_db($vars) {
  global $config;
  $res     = array();
  foreach(dbFetchRows("SELECT * FROM `bills` ORDER BY `bill_name`") as $bill) {
    if (api_bill_permitted($bill['bill_id'])) {
      if ($config['api']['module']['encryption']) {
        $tmp   = array();
        foreach($bill as $item=>$value) {
          $tmp[$item] = api_encrypt_data($value, $config['api']['encryption']['key']);
        }
        $res[] = $tmp;
        unset($tmp);
      } else {
        $res[] = $bill;
      }
    }
  }
  return $res;
}


/**
 * Grab the mysql history data
 *
 * @return array
 * @param  vars
 *
*/
function api_history_db($vars) {
  global $config;
  $res     = array();
  if (api_bill_permitted($vars['bill'])) {
    foreach(dbFetchRows("SELECT * FROM `bill_history` WHERE `bill_id`= ? ORDER BY `bill_datefrom`", array($vars['bill'])) as $history) {
      if ($config['api']['module']['encryption']) {
        $tmp   = array();
        foreach($history as $item=>$value) {
          $tmp[$item] = api_encrypt_data($value, $config['api']['encryption']['key']);
        }
        $res[] = $tmp;
        unset($tmp);
      } else {
        $res[] = $history;
      }
    }
  } else {
    $res = api_errorcodes("310");
  }
  return $res;
}


/**
 * Grab the mysql traffic data
 *
 * @return array
 * @param  vars
 *
*/
function api_traffic_db($vars) {
  global $config;
  $res     = array();
  if (api_bill_permitted($vars['bill'])) {
    $start  = (is_numeric($vars['from']) ? $vars['from'] : strtotime("-1 month"));
    $end    = (is_numeric($vars['to']) ? $vars['to'] : strtotime("now"));
    $group = (isset($vars['group']) ? $vars['group'] : "day");
    //$group = (($group == "5min" or $group == "hour" or $group == "day" or $group == "week" or $group == "month") ? $group : "day");
    $sql["vars"]    = array($vars['bill'], $start, $end);
    if ($vars['group'] == "hour") {
      $sql["query"] = "SELECT DISTINCT UNIX_TIMESTAMP(timestamp) as timestamp, SUM(delta) as traf_total, SUM(in_delta) as traf_in, SUM(out_delta) as traf_out FROM `bill_data` WHERE `bill_id`= ? AND `timestamp` >= FROM_UNIXTIME(?) AND `timestamp` <= FROM_UNIXTIME(?) GROUP BY HOUR(timestamp) ORDER BY timestamp ASC";
    } elseif ($vars['group'] == "day") {
      $sql["query"] = "SELECT DISTINCT UNIX_TIMESTAMP(timestamp) as timestamp, SUM(delta) as traf_total, SUM(in_delta) as traf_in, SUM(out_delta) as traf_out FROM `bill_data` WHERE `bill_id`= ? AND `timestamp` >= FROM_UNIXTIME(?) AND `timestamp` <= FROM_UNIXTIME(?) GROUP BY DAY(timestamp) ORDER BY timestamp ASC";
    } elseif ($vars['group'] == "week") {
      $sql["query"] = "SELECT DISTINCT UNIX_TIMESTAMP(timestamp) as timestamp, SUM(delta) as traf_total, SUM(in_delta) as traf_in, SUM(out_delta) as traf_out FROM `bill_data` WHERE `bill_id`= ? AND `timestamp` >= FROM_UNIXTIME(?) AND `timestamp` <= FROM_UNIXTIME(?) GROUP BY WEEK(timestamp) ORDER BY timestamp ASC";
    } elseif ($vars['group'] == "month") {
      $sql["query"] = "SELECT DISTINCT UNIX_TIMESTAMP(timestamp) as timestamp, SUM(delta) as traf_total, SUM(in_delta) as traf_in, SUM(out_delta) as traf_out FROM `bill_data` WHERE `bill_id`= ? AND `timestamp` >= FROM_UNIXTIME(?) AND `timestamp` <= FROM_UNIXTIME(?) GROUP BY MONTH(timestamp) ORDER BY timestamp ASC";
    } else {
      $sql["query"] = "SELECT DISTINCT UNIX_TIMESTAMP(timestamp) as timestamp, delta as traf_total, in_delta as traf_in, out_delta as traf_out FROM `bill_data` WHERE `bill_id`= ? AND `timestamp` >= FROM_UNIXTIME(?) AND `timestamp` <= FROM_UNIXTIME(?) ORDER BY timestamp ASC";
    }
    foreach(dbFetchRows($sql["query"], $sql["vars"]) as $traffic) {
      if ($config['api']['module']['encryption']) {
        $tmp   = array();
        foreach($traffic as $item=>$value) {
          $tmp[$item] = api_encrypt_data($value, $config['api']['encryption']['key']);
        }
        $res[] = $tmp;
        unset($tmp);
      } else {
        $res[] = $traffic;
      }
    }
  } else {
    $res = api_errorcodes("310");
  }
  return $res;
}

?>
