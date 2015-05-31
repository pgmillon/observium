<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage discovery
 * @author     Adam Armstrong <adama@memetic.org>
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

echo("Discovery protocols:");

// Include all discovery modules
$include_dir = "includes/discovery/discovery-protocols";
include("includes/include-dir-mib.inc.php");

// Always discover LLDP-MIB
// Include LLDP-MIB on devices where it's supported, don't just run it for everything.
include("discovery-protocols/lldp-mib.inc.php");

// Always discover CISCO-CDP-MIB (many non-cisco devices support this!)
// Include CDP-MIB on devices where it's supported, don't just run it for everything.
include("discovery-protocols/cisco-cdp-mib.inc.php");

if ($debug && count($valid_link)) { print_vars($valid_link); }

$sql = 'SELECT * FROM `links` AS L, `ports` AS I WHERE L.`local_port_id` = I.`port_id` AND I.`device_id` = ?';
foreach (dbFetchRows($sql, array($device['device_id'])) as $test)
{
  $local_port_id = $test['local_port_id'];
  $remote_hostname = $test['remote_hostname'];
  $remote_port = $test['remote_port'];
  print_debug("$local_port_id -> $remote_hostname -> $remote_port");
  if (!$valid_link[$local_port_id][$remote_hostname][$remote_port])
  {
    echo('-');
    dbDelete('links', '`id` = ?', array($test['id']));
  }
}

unset($valid_link);
echo("\n");

// EOF
