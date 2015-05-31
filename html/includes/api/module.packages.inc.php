<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    Simple Observium API
 * @subpackage Packages module
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
  if ($config['api']['module']['packages']) {
    if (api_device_permitted($vars['device'])) {
      $res = api_packages_db($vars);
    } else {
      $res = api_errorcodes("310");
    }
  } else {
    $res = api_errorcodes("213");
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
function api_packages_db($vars) {
  global $config;
  $res     = array();
  foreach(dbFetchRows("SELECT * FROM `packages` WHERE `device_id` = ? GROUP BY `name`", array($vars['device'])) as $packages) {
    if ($config['api']['module']['encryption']) {
      $tmp   = array();
      foreach($packages as $item=>$value) {
        $tmp[$item] = api_encrypt_data($value, $config['api']['encryption']['key']);
      }
      $res[] = $tmp;
      unset($tmp);
    } else {
      $res[] = $packages;
    }
  }
  return $res;
}

?>
