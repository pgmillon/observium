<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage poller
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

if ($device['os_group'] == "cisco")
{
  echo("Cisco CEF Switching Path: ");

  $cefs = snmpwalk_cache_threepart_oid($device, "CISCO-CEF-MIB::cefSwitchingStatsEntry", array(), "CISCO-CEF-MIB", mib_dirs(array("cisco")));
  $cef_pfxs = snmpwalk_cache_twopart_oid($device, "CISCO-CEF-MIB::cefFIBSummaryEntry", array(), "CISCO-CEF-MIB", mib_dirs(array("cisco")));

  print_vars($cef_summ);

  $polled = time();

  $cefs_query = dbFetchRows("SELECT * FROM `cef_switching` WHERE `device_id` = ?", array($device['device_id']));
  foreach ($cefs_query as $ceftmp)
  {
    $cef_id = $device['device_id']."-".$ceftmp['entPhysicalIndex']."-".$ceftmp['afi']."-".$ceftmp['cef_index'];
    $cefs_db[$cef_id] = $ceftmp['cef_switching_id'];
  }

  $cef_pfxs_query = dbFetchRows("SELECT * FROM `cef_prefix` WHERE `device_id` = ?", array($device['device_id']));
  foreach ($cef_pfxs_query as $pfx)
  {
    $cef_pfxs_db[$pfx['entPhysicalIndex']][$pfx['afi']] = $pfx['cef_pfx_id'];
  }

  if ($debug) { print_vars($cefs); }

  if (is_array($cefs))
  {
    if (!is_array($entity_array))
    {
      echo("Caching OIDs: ");
      $entity_array = array();
      echo(" entPhysicalDescr");
      $entity_array = snmpwalk_cache_multi_oid($device, "entPhysicalDescr", $entity_array, "ENTITY-MIB");
      echo(" entPhysicalName");
      $entity_array = snmpwalk_cache_multi_oid($device, "entPhysicalName", $entity_array, "ENTITY-MIB");
      echo(" entPhysicalModelName");
      $entity_array = snmpwalk_cache_multi_oid($device, "entPhysicalModelName", $entity_array, "ENTITY-MIB");
    }

    foreach ($cefs as $entity => $afis)
    {
      $entity_name = $entity_array[$entity]['entPhysicalName'] ." - ".$entity_array[$entity]['entPhysicalModelName'];
      echo("\n$entity $entity_name\n");
      foreach ($afis as $afi => $paths)
      {
        echo(" |- $afi ");

        // Do Per-AFI entity summary

        $filename = "cef-pfx-".$entity."-".$afi.".rrd";

        rrdtool_create($device, $filename, " \
          DS:pfx:GAUGE:600:0:10000000 ");

        // FIXME -- memory tables

        if (!isset($cef_pfxs_db[$entity][$afi]))
        {
          dbInsert(array('device_id' => $device['device_id'], 'entPhysicalIndex' => $entity, 'afi' => $afi), 'cef_prefix');
          echo("+");
        }
        unset($cef_pfxs_db[$entity][$afi]);

        $cef_pfx['update']['cef_pfx'] = $cef_pfxs[$entity][$afi]['cefFIBSummaryFwdPrefixes'];
        dbUpdate($cef_pfx['update'], 'cef_prefix', '`device_id` = ? AND `entPhysicalIndex` = ? AND `afi` = ?', array($device['device_id'], $entity, $afi));

        $ret = rrdtool_update($device, "$filename", array($cef_pfxs[$entity][$afi]['cefFIBSummaryFwdPrefixes']));

        print_vars($cef_pfxs[$entity][$afi]);

        // Do Per-path statistics
        foreach ($paths as $path => $cef_stat)
        {
          echo(" | |-".$path.": ".$cef_stat['cefSwitchingPath']);

          $cef_id = $device['device_id']."-".$entity."-".$afi."-".$path;

  #        if (dbFetchCell("SELECT COUNT(*) FROM `cef_switching` WHERE `device_id` = ? AND `entPhysicalIndex` = ? AND `afi` = ? AND `cef_index` = ?", array($device['device_id'], $entity, $afi, $path)) != "1")
          if (!isset($cefs_db[$cef_id]))
          {
            dbInsert(array('device_id' => $device['device_id'], 'entPhysicalIndex' => $entity, 'afi' => $afi, 'cef_index' => $path, 'cef_path' => $cef_stat['cefSwitchingPath']), 'cef_switching');
            echo("+");
          }
          unset($cefs_db[$cef_id]);

          $cef_entry = dbFetchRow("SELECT * FROM `cef_switching` WHERE `device_id` = ? AND `entPhysicalIndex = ? AND `afi` = ? AND `cef_index` = ?", array($device['device_id'], $entity, $afi, $path));

          $filename = "cefswitching-".$entity."-".$afi."-".$path.".rrd";

          rrdtool_create($device, $filename, " \
            DS:drop:DERIVE:600:0:1000000 \
            DS:punt:DERIVE:600:0:1000000 \
            DS:hostpunt:DERIVE:600:0:1000000 ");

          // Copy HC to non-HC if they exist
          if (is_numeric($cef_stat['cefSwitchingHCDrop'])) { $cef_stat['cefSwitchingDrop'] = $cef_stat['cefSwitchingHCDrop']; }
          if (is_numeric($cef_stat['cefSwitchingHCPunt'])) { $cef_stat['cefSwitchingPunt'] = $cef_stat['cefSwitchingHCPunt']; }
          if (is_numeric($cef_stat['cefSwitchingHCPunt2Host'])) { $cef_stat['cefSwitchingPunt2Host'] = $cef_stat['cefSwitchingHCPunt2Host']; }

          // FIXME -- memory tables

          $cef_stat['update']['drop'] = $cef_stat['cefSwitchingDrop'];
          $cef_stat['update']['punt'] = $cef_stat['cefSwitchingPunt'];
          $cef_stat['update']['punt2host'] = $cef_stat['cefSwitchingPunt2Host'];
          $cef_stat['update']['drop_prev'] = $cef_entry['drop'];
          $cef_stat['update']['punt_prev'] = $cef_entry['punt'];
          $cef_stat['update']['punt2host_prev'] = $cef_entry['punt2host'];
          $cef_stat['update']['updated'] = $polled;
          $cef_stat['update']['updated_prev'] = $cef_entry['updated'];

          dbUpdate($cef_stat['update'], 'cef_switching', '`device_id` = ? AND `entPhysicalIndex` = ? AND `afi` = ? AND `cef_index` = ?', array($device['device_id'], $entity, $afi, $path));

          $ret = rrdtool_update($device, "$filename", array($cef_stat['cefSwitchingDrop'], $cef_stat['cefSwitchingPunt'], $cef_stat['cefSwitchingPunt2Host']));

          echo("\n");

        }
      }
    }
  }

  // FIXME - need to delete old ones. FIXME REALLY.

  print_vars($cefs_db);

  foreach ($cefs_db as $cef_switching_id)
  {
    dbDelete("cef_switching", "`cef_switching_id` =  ?", array($cef_switching_id));
    echo("-");
  }

  echo("\n");
} # os_group = cisco

?>
