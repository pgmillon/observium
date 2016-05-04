<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage poller
 * @copyright  (C) 2006-2015 Adam Armstrong
 *
 */

// Generate some statistics to send along with the version request.
//
// These stats are used to allow us to prioritise development resources
// to target features and devices that are used the most.

// Overall Ports/Devices statistics
$stats['ports']         = dbFetchCell("SELECT COUNT(*) FROM ports");
$stats['devices']       = dbFetchCell("SELECT COUNT(*) FROM devices");
$stats['edition']       = OBSERVIUM_EDITION;

// Per-feature statistics
$stats['sensors']       = dbFetchCell("SELECT COUNT(*) FROM `sensors`");
$stats['services']      = dbFetchCell("SELECT COUNT(*) FROM `services`");
$stats['applications']  = dbFetchCell("SELECT COUNT(*) FROM `applications`");
$stats['bgp']           = dbFetchCell("SELECT COUNT(*) FROM `bgpPeers`");
$stats['ospf']          = dbFetchCell("SELECT COUNT(*) FROM `ospf_ports`");
$stats['eigrp']         = dbFetchCell("SELECT COUNT(*) FROM `eigrp_ports`");
$stats['ipsec_tunnels'] = dbFetchCell("SELECT COUNT(*) FROM `ipsec_tunnels`");
$stats['munin_plugins'] = dbFetchCell("SELECT COUNT(*) FROM `munin_plugins`");
$stats['pseudowires']   = dbFetchCell("SELECT COUNT(*) FROM `pseudowires`");
$stats['vrfs']          = dbFetchCell("SELECT COUNT(*) FROM `vrfs`");
$stats['vminfo']        = dbFetchCell("SELECT COUNT(*) FROM `vminfo`");
$stats['users']         = dbFetchCell("SELECT COUNT(*) FROM `users`");
$stats['bills']         = dbFetchCell("SELECT COUNT(*) FROM `bills`");
$stats['alerts']        = dbFetchCell("SELECT COUNT(*) FROM `alert_table`");
$stats['alert_tests']   = dbFetchCell("SELECT COUNT(*) FROM `alert_tests`");
$stats['groups']        = dbFetchCell("SELECT COUNT(*) FROM `groups`");
$stats['group_members'] = dbFetchCell("SELECT COUNT(*) FROM `group_table`");

$stats['poller_time']   = dbFetchCell("SELECT SUM(`last_polled_timetaken`) FROM devices");
$stats['php_version']   = phpversion();

$os_text                = external_exec("DISTROFORMAT=export " . $config['install_dir'] . "/scripts/distro");

foreach (explode("\n", $os_text) as $part)
{
  list($a, $b) = explode("=", $part);
  $stats['os'][$a] = $b;
}

// sysObjectID for Generic devices
foreach (dbFetchRows("SELECT `sysObjectID`, COUNT(*) AS `count` FROM `devices` WHERE `os` = 'generic' GROUP BY `sysObjectID`") as $data)
{
  $stats['generics'][$data['sysObjectID']] = $data['count'];
}

// Per-OS counts
foreach (dbFetchRows("SELECT COUNT(*) AS `count`, `os` FROM `devices` GROUP BY `os`") as $data)
{
  $stats['devicetypes'][$data['os']] = $data['count'];
}

// Per-type counts
foreach (dbFetchRows("SELECT COUNT(*) AS `count`, `type` FROM `devices` GROUP BY `type`") as $data)
{
  $stats['types'][$data['type']] = $data['count'];
}

// Per-apptype counts
foreach (dbFetchRows("SELECT COUNT(*) AS `count`, `app_type` FROM `applications` GROUP BY `app_type`") as $data)
{
  $stats['app_types'][$data['app_type']] = $data['count'];
}

// Serialize and base64 encode stats array for transportation
$stat_serial = serialize($stats);
$stat_base64 = base64_encode($stat_serial);

$url = "http://update.observium.org/latest.php?i=".$stats['ports']."&d=".$stats['devices']."&v=".OBSERVIUM_VERSION."&stats=".$stat_base64;
$data = rtrim(get_http_request($url));

if ($data)
{
  list($omnipotence, $year, $month, $revision) = explode(".", $data);
  list($cur, $tag) = explode("-", OBSERVIUM_VERSION); /// FIXME. $tag is OBSERVIUM_TRAIN?
  list($cur_omnipotence, $cur_year, $cur_month, $cur_revision) = explode(".", $cur);

  $version_string = $omnipotence.'.'.$year.'.'.$month.'.'.$revision;

  file_put_contents($config['rrd_dir'].'/version.txt', $version_string);

  if ($argv[1] == "--cron" || isset($options['q']))
  {
    $fd = fopen($config['log_file'],'a');
    fputs($fd,$string . "\n");
    fclose($fd);

  } else {
    if ($cur != $data)
    {
      echo("Current Revision : $cur_revision\n");

      if ($revision > $cur_revision || TRUE)
      {
        echo("New Revision   : $revision\n");
      }
    }
  }
}

// EOF
