<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage discovery
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

# We're discovering this MIB
# snmpwalk -v2c -c <community> <hostname> -M mibs/junose/ -m Juniper-UNI-ATM-MIB juniAtmVpStatsEntry

// JunOSe ATM vps
if ($device['os'] == "junose" && ($attribs['enable_ports_junoseatmvp'] || ($config['enable_ports_junoseatmvp'] && !isset($attribs['enable_ports_junoseatmvp']))))
{
  echo("JunOSe ATM vps : ");
  $vp_array = snmpwalk_cache_multi_oid($device, "juniAtmVpStatsInCells", $vp_array, "Juniper-UNI-ATM-MIB" , mib_dirs('junose'));
  $valid_vp = array();
  if (OBS_DEBUG && count($vp_array)) { print_vars($vp_array); }

  if (is_array($vp_array))
  {
    foreach ($vp_array as $index => $entry)
    {
      list($ifIndex,$vp_id)= explode('.', $index);
      $port_id = dbFetchCell("SELECT `port_id` FROM `ports` WHERE `device_id` = ? AND `ifIndex` = ?", array($device['device_id'], $ifIndex));
      if (is_numeric($port_id) && is_numeric($vp_id))
      {
        discover_juniAtmvp($valid_vp, $port_id, $vp_id, NULL);
      }
    } // End Foreach
  } // End if array

  unset ($vp_array);

  // Remove ATM vps which weren't redetected here

  // Fix Me - preferred method is to track existance by removing from an array.

  if (OBS_DEBUG && count($valid_vp)) { print_vars ($valid_vp); }

  foreach (dbFetchRows("SELECT * FROM `ports` AS P, `juniAtmVp` AS J WHERE P.`device_id`  = ? AND J.port_id = P.port_id", array($device['device_id'])) as $test)
  {
    $port_id = $test['port_id'];
    $vp_id = $test['vp_id'];
    if (OBS_DEBUG > 1) { echo($port_id . " -> " . $vp_id . "\n"); }
    if (!$valid_vp[$port_id][$vp_id])
    {
      echo("-");
      dbDelete('juniAtmvp', '`juniAtmVp` = ?', array($test['juniAtmvp']));
    }

    unset($port_id); unset($vp_id);
  }

  unset($valid_vp);
  echo(PHP_EOL);
}

// EOF
