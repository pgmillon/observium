<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage graphing
 * @author     Adam Armstrong <adama@observium.org>
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

#ob_start(); // FIXME why no more?

include_once("../includes/sql-config.inc.php");

$start = utime(); // Needs common.php

include($config['html_dir'] . "/includes/functions.inc.php");

if (isset($config['allow_unauth_graphs']) && $config['allow_unauth_graphs'])
{
  $auth = TRUE; // hardcode auth for all with config function
  print_debug('Authentication bypassed by $config[\'allow_unauth_graphs\'].');
}
elseif (isset($config['allow_unauth_graphs_cidr']) && count($config['allow_unauth_graphs_cidr']))
{
  if (match_network($_SERVER['REMOTE_ADDR'], $config['allow_unauth_graphs_cidr']))
  {
    $auth = TRUE; // hardcode authenticated for matched subnet
    print_debug("Authentication by matched CIDR.");
  }
  //foreach ($config['allow_unauth_graphs_cidr'] as $range)
  //{
  //  list($net, $mask) = explode('/', trim($range));
  //  if (Net_IPv4::validateIP($net))
  //  {
  //    // IPv4
  //    $mask = ($mask != NULL) ? $mask : '32';
  //    $range = $net.'/'.$mask;
  //    if ($mask >= 0 && $mask <= 32 && Net_IPv4::ipInNetwork($_SERVER['REMOTE_ADDR'], $range))
  //    {
  //      $auth = TRUE; // hardcode authenticated for matched subnet
  //      print_debug("Authentication by CIDR matched IPv4 $range.");
  //      break;
  //    }
  //  }
  //  elseif (Net_IPv6::checkIPv6($net))
  //  {
  //    // IPv6
  //    $mask = ($mask != NULL) ? $mask : '128';
  //    $range = $net.'/'.$mask;
  //    if ($mask >= 0 && $mask <= 128 && Net_IPv6::isInNetmask($_SERVER['REMOTE_ADDR'], $range))
  //    {
  //      $auth = TRUE; // hardcode authenticated for matched subnet
  //      print_debug("Authentication by CIDR matched IPv6 $range");
  //      break;
  //    }
  //  }
  //}
}

if (!$auth)
{
  // Normal auth
  include($config['html_dir'] . "/includes/authenticate.inc.php");
}

// Push $_GET into $vars to be compatible with web interface naming
$vars = get_vars('GET');

include($config['html_dir'] . "/includes/graphs/graph.inc.php");

$runtime = utime() - $start;

print_debug("Runtime ".$runtime." secs");

// EOF
