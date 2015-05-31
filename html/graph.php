<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage graphing
 * @author     Adam Armstrong <adama@memetic.org>
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

#ob_start(); // FIXME why no more?

if (isset($_GET['debug']))
{
  $debug = TRUE;
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 0);
  ini_set('log_errors', 0);
  ini_set('error_reporting', E_ALL ^ E_NOTICE);
}
else
{
  $debug = FALSE;
  ini_set('display_errors', 0);
  ini_set('display_startup_errors', 0);
  ini_set('log_errors', 0);
  ini_set('error_reporting', 0);
}

include_once("../includes/defaults.inc.php");
include_once("../config.php");
include_once("../includes/definitions.inc.php");
include($config['install_dir'] . "/includes/common.inc.php");

$start = utime(); // Needs common.php

include($config['install_dir'] . "/includes/dbFacile.php");
include($config['install_dir'] . "/includes/rewrites.inc.php");
include($config['install_dir'] . "/includes/rrdtool.inc.php");
include($config['install_dir'] . "/includes/entities.inc.php");
include($config['html_dir'] . "/includes/functions.inc.php");

if (isset($config['allow_unauth_graphs']) && $config['allow_unauth_graphs'])
{
  $auth = TRUE; // hardcode auth for all with config function
  print_debug('Authentication bypassed by $config[\'allow_unauth_graphs\'].');
}
elseif (isset($config['allow_unauth_graphs_cidr']) && count($config['allow_unauth_graphs_cidr']))
{
  foreach ($config['allow_unauth_graphs_cidr'] as $range)
  {
    list($net, $mask) = explode('/', trim($range));
    if (Net_IPv4::validateIP($net))
    {
      // IPv4
      $mask = ($mask != NULL) ? $mask : '32';
      $range = $net.'/'.$mask;
      if ($mask >= 0 && $mask <= 32 && Net_IPv4::ipInNetwork($_SERVER['REMOTE_ADDR'], $range))
      {
        $auth = TRUE; // hardcode authenticated for matched subnet
        print_debug("Authentication by CIDR matched IPv4 $range.");
        break;
      }
    }
    elseif (Net_IPv6::checkIPv6($net))
    {
      // IPv6
      $mask = ($mask != NULL) ? $mask : '128';
      $range = $net.'/'.$mask;
      if ($mask >= 0 && $mask <= 128 && Net_IPv6::isInNetmask($_SERVER['REMOTE_ADDR'], $range))
      {
        $auth = TRUE; // hardcode authenticated for matched subnet
        print_debug("Authentication by CIDR matched IPv6 $range");
        break;
      }
    }
  }
}

if (!$auth)
{
  // Normal auth
  include($config['html_dir'] . "/includes/authenticate.inc.php");
}

// Push $_GET into $vars to be compatible with web interface naming
$vars = get_vars('GET');

include($config['html_dir'] . "/includes/graphs/graph.inc.php");

$end = utime(); $run = $end - $start;

if($debug) { echo("<br />Runtime ".$run." secs"); }

// EOF
