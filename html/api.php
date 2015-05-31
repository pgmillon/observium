<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    Simple Observium API
 * @subpackage api
 * @author     Dennis de Houx <dennis@aio.be>
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

include("../includes/defaults.inc.php");
include("../config.php");
include_once("../includes/definitions.inc.php");
include($config['install_dir'] . "/includes/common.inc.php");
include($config['install_dir'] . "/includes/rewrites.inc.php");
include($config['install_dir'] . "/includes/dbFacile.php");
include($config['install_dir'] . "/includes/entities.inc.php");
include($config['html_dir'] . "/includes/functions.inc.php");
include($config['html_dir'] . "/includes/api/functions.inc.php");

$data = array();

ini_set('allow_url_fopen', 0);
ini_set('display_errors', 0);

$cli = FALSE;

$vars = get_vars('GET');
$vars['module']  = (!empty($vars['module']) ? $vars['module'] : "demo");

if ($vars['debug'])
{
  $debug = "1";
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  ini_set('log_errors', 1);
  ini_set('error_reporting', E_ALL);
  $data['debug'] = api_errorcodes("100", "info");
} else {
  $debug = FALSE;
  ini_set('display_errors', 0);
  ini_set('display_startup_errors', 0);
  ini_set('log_errors', 0);
  ini_set('error_reporting', 0);
}

if ($config['api']['enabled'])
{
  $vars['user'] = api_authenticate_user($vars['username'], $vars['password']);
  if ($vars['user']['id'] != 0 || $vars['user']['id'] != "") {
    $data['login'] = api_errorcodes("101", "success");
    if (api_load_module($vars['module'])) {
      $data['data'] = api_module_data();
    } else {
      $data['data'] = api_errorcodes("201");
    }
  } else {
    $data['login'] = api_errorcodes("301");
  }
} else {
  $data['login'] = api_errorcodes("200");
}

echo api_json_data($data);

// EOF
