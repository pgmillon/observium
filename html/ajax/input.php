<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage ajax
 * @author     Adam Armstrong <adama@observium.org>
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

$config['install_dir'] = "../..";

include_once("../../includes/sql-config.inc.php");

include($config['html_dir'] . "/includes/functions.inc.php");
include($config['html_dir'] . "/includes/authenticate.inc.php");

if (!$_SESSION['authenticated']) { echo('<li class="nav-header">Session expired, please log in again!</li>'); exit; }

$vars = get_vars();

if (strlen($vars['field']) && $vars['cache'] != 'no' && isset($_SESSION['cache']['options_' . $vars['field']]))
{
  // Return cached data (if not set in vars cache = 'no')
  header("Content-type: application/json; charset=utf-8");
  echo json_encode(array('options' => $_SESSION['cache']['options_' . $vars['field']]));
} else {
  $query  = '';
  $params = array();
  switch ($vars['field'])
  {
    case 'ipv4_network':
    case 'ipv6_network':
      list($ip_version)  = explode('_', $vars['field']);
      $query_permitted   = generate_query_permitted('ports');
      $network_permitted = dbFetchColumn('SELECT DISTINCT(`' . $ip_version . '_network_id`) FROM `' . $ip_version . '_addresses` WHERE 1' . $query_permitted);
      $query = 'SELECT `' . $ip_version . '_network` FROM `' . $ip_version . '_networks` WHERE 1 ' . generate_query_values($network_permitted, $ip_version . '_network_id') . ' ORDER BY `' . $ip_version . '_network`;';
      break;
    case 'ifspeed':
      $query_permitted   = generate_query_permitted('ports');
      $query = 'SELECT `ifSpeed`, COUNT(ifSpeed) as `count` FROM `ports` WHERE `ifSpeed` > 0 '. $query_permitted .' GROUP BY ifSpeed ORDER BY `count` DESC';
      $call_function = 'formatRates';
      $call_params   = array(4, 4);
      break;
    default:
      json_output('error', 'Search type unknown');
  }

  if (strlen($query))
  {
    $options = dbFetchColumn($query, $params);
    if (count($options))
    {
      if (isset($call_function))
      {
        $call_options = array();
        foreach ($options as $option)
        {
          $call_options[] = call_user_func_array($call_function, array_merge(array($option), $call_params));
        }
        $options = $call_options;
      }
      if ($vars['cache'] != 'no')
      {
        $_SESSION['cache']['options_' . $vars['field']] = $options; // Cache query data in session for speedup
      }

      header("Content-type: application/json; charset=utf-8");
      echo json_encode(array('options' => $options));
    } else {
      json_output('error', 'Data fields are empty');
    }
  }
}

// EOF
