<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage discovery
 * @copyright  (C) 2006-2015 Adam Armstrong
 *
 */

/// FIXME. Make this module generic, not only cisco-like

if ($config['enable_sla'] && is_device_mib($device, 'CISCO-RTTMON-MIB'))
{
  echo("SLAs : ");

  $valid['slas'] = array();

  $sla_table = snmpwalk_cache_multi_oid($device, "rttMonCtrl", array(), 'CISCO-RTTMON-MIB', mib_dirs('cisco'));
  if (OBS_DEBUG > 1) { print_vars($sla_table); }

  // Get existing SLAs
  $sla_db  = array();
  //$sla_ids = array();
  foreach (dbFetchRows("SELECT * FROM `slas` WHERE `device_id` = ?", array($device['device_id'])) as $entry)
  {
    $sla_db[$entry['sla_index']] = $entry;
    //$sla_ids[$entry['sla_id']] = $entry['sla_id'];
  }

  foreach ($sla_table as $sla_index => $entry)
  {
    if (!isset($entry['rttMonCtrlAdminStatus'])) { continue; } // Skip additional multiindex entries from table

    $data = array(
      'device_id'  => $device['device_id'],
      'sla_index'  => $sla_index,
      'sla_owner'  => $entry['rttMonCtrlAdminOwner'],
      'sla_tag'    => $entry['rttMonCtrlAdminTag'],
      'rtt_type'   => $entry['rttMonCtrlAdminRttType'],
      'sla_status' => $entry['rttMonCtrlAdminStatus'], // Possible: active, notInService, notReady, createAndGo, createAndWait, destroy
      'deleted'    => 0,
    );

    // Some fallbacks for when the tag is empty
    if (!$data['sla_tag'])
    {
      switch ($data['rtt_type'])
      {
        case 'http':
        case 'ftp':
          $data['sla_tag'] = $entry['rttMonEchoAdminURL'];
          break;
        case 'dns':
          $data['sla_tag'] = $entry['rttMonEchoAdminTargetAddressString'];
          break;
        case 'echo':
        case 'jitter':
        case 'icmpjitter':
          $data['sla_tag'] = hex2ip($entry['rttMonEchoAdminTargetAddress']);
          break;
      }
    }

    if (!isset($sla_db[$sla_index]))
    {
      // Not exist, add
      $sla_id = dbInsert($data, 'slas');
      $GLOBALS['module_stats'][$module]['added']++; //echo "+";
    } else {
      $sla_id = $sla_db[$sla_index]['sla_id'];

      $update_db = array();
      foreach ($data as $key => $value)
      {
        if ($sla_db[$sla_index][$key] != $value) { $update_db[$key] = $value; }
      }
      if (count($update_db))
      {
        dbUpdate($update_db, 'slas', "`sla_id` = ?", array($sla_id));
        if (OBS_DEBUG > 1) { print_vars($update_db); }
        if (isset($update_db['deleted']))
        {
          // This is re-added sla
          $GLOBALS['module_stats'][$module]['added']++; //echo "+";
        } else {
          $GLOBALS['module_stats'][$module]['updated']++; //echo "U";
        }
      } else {
        $GLOBALS['module_stats'][$module]['unchanged']++; //echo ".";
      }
    }
    $valid['slas'][$sla_id] = $sla_id;
  }

  // Mark all remaining SLAs as deleted
  foreach ($sla_db as $entry)
  {
    if (isset($valid['slas'][$entry['sla_id']]) || $entry['deleted'] == 1)
    {
      // SLA exist or already deleted
      continue;
    } else {
      if (!$entry['rttMonCtrlAdminStatus'])
      {
        dbDelete('slas', "`sla_id` = ?", array($entry['sla_id']));
      } else {
        dbUpdate(array('deleted' => 1), 'slas', "`sla_id` = ?", array($entry));
      }
      $GLOBALS['module_stats'][$module]['deleted']++; //echo "-";
    }
  }

  $GLOBALS['module_stats'][$module]['status'] = count($valid['slas']);
  if (OBS_DEBUG && $GLOBALS['module_stats'][$module]['status']) { print_vars($valid['slas']); }

  // Clean
  unset($update_db, $sla_db, $sla_table, $sla_ids, $data, $entry);
} # enable_sla && cisco

// EOF
