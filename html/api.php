<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    Simple Observium API
 * @subpackage api
 * @author     Dennis de Houx <dennis@aio.be>
 * @copyright  (C) 2006-2015 Adam Armstrong
 *
 */

include_once("../includes/defaults.inc.php");
include_once("../config.php");
include_once("../includes/definitions.inc.php");
include($config['install_dir'] . "/includes/common.inc.php");
include($config['install_dir'] . "/includes/rewrites.inc.php");
include($config['install_dir'] . "/includes/dbFacile.php");
include($config['install_dir'] . "/includes/entities.inc.php");
include($config['html_dir'] . "/includes/functions.inc.php");
include($config['html_dir'] . "/includes/api/functions.inc.php");

$data = array();

ini_set('allow_url_fopen', 0);

$cli = FALSE;

$vars = get_vars('GET');
$vars['module']  = (!empty($vars['module']) ? $vars['module'] : "demo");

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
