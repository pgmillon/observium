<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    Simple Observium API
 * @subpackage Inventory module
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
  if ($config['api']['module']['inventory']) {
    if (api_device_permitted($vars['device'])) {
      $res = api_inventory_db($vars);
    } else {
      $res = api_errorcodes("310");
    }
  } else {
    $res = api_errorcodes("212");
  }
  return $res;
}


/**
 * Grab the mysql data
 *
 * @return array
 * @param  vars
 *
*/
function api_inventory_db($vars) {
  global $config;
  $res     = array();
  foreach(dbFetchRows("SELECT * FROM `hrDevice` WHERE `device_id` = ? ORDER BY `hrDeviceIndex`", array($vars['device'])) as $hrdevice) {
    if ($config['api']['module']['encryption']) {
      $tmp   = array();
      foreach($hrdevice as $item=>$value) {
        $tmp[$item] = api_encrypt_data($value, $config['api']['encryption']['key']);
      }
      $res[] = $tmp;
      unset($tmp);
    } else {
      $res[] = $hrdevice;
    }
  }
  return $res;
}

?>
