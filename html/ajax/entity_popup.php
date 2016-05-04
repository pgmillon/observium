<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage ajax
 * @author     Adam Armstrong <adama@memetic.org>
 * @copyright  (C) 2006-2015 Adam Armstrong
 *
 */

/* DEBUG enabled in definitions
if (isset($_GET['debug']) && $_GET['debug'])
{
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  ini_set('log_errors', 0);
  ini_set('allow_url_fopen', 0);
  ini_set('error_reporting', E_ALL);
}
*/

include_once("../../includes/defaults.inc.php");
include_once("../../config.php");
include_once("../../includes/definitions.inc.php");

include($config['install_dir'] . "/includes/common.inc.php");
include($config['install_dir'] . "/includes/dbFacile.php");
include($config['install_dir'] . "/includes/rewrites.inc.php");
include($config['install_dir'] . "/includes/entities.inc.php");
include($config['html_dir'] . "/includes/functions.inc.php");
include($config['html_dir'] . "/includes/authenticate.inc.php");

if (!$_SESSION['authenticated']) { print_error('Session expired, please log in again!'); exit; }

$vars = get_vars();

switch($vars['entity_type'])
{
  case "port":
    if (is_numeric($vars['entity_id']) && (port_permitted($vars['entity_id'])))
    {
      $port   = get_port_by_id($vars['entity_id']);
      echo generate_port_popup($port);
    } else {
      print_warning("You are not permitted to view this port.");
    }
    exit;
    break;

  case "device":
    if (is_numeric($vars['entity_id']) && device_permitted($vars['entity_id']))
    {
      $device = device_by_id_cache($vars['entity_id']);
      echo generate_device_popup($device, $vars, $start, $end);
    } else {
      print_warning("You are not permitted to view this device.");
    }
    exit;
    break;

  case "netscaler_svc":
  case "netscaler_vsvr":
  case "bgp_peer":
  case "storage":
  case "sensor":
  case "status";
  case "mempool":
  case "processor":
  case "sla":
    if (is_numeric($vars['entity_id']) && (is_entity_permitted($vars['entity_id'], $vars['entity_type'])))
    {
      $entity = get_entity_by_id_cache($vars['entity_type'], $vars['entity_id']);
      echo generate_entity_popup($entity, $vars['entity_type']);
    } else {
      print_warning("You are not permitted to view this entity.");
    }
    exit;
    break;

  case "mac":
    if (Net_MAC::check($vars['entity_id']))
    {
      // Other way by using Pear::Net_MAC, see here: http://pear.php.net/manual/en/package.networking.net-mac.importvendors.php
      $url = 'http://api.macvendors.com/' . urlencode($vars['entity_id']);
      $response = get_http_request($url);
      if ($response)
      {
        echo  'MAC vendor: ' . $response;
      } else {
        echo  'Not Found';
      }
    } else {
      echo  'Not correct MAC address';
    }
    exit;
    break;

  default:
    print_error("Unknown entity type.");
    exit;
    break;
}

// EOF
