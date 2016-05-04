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

if ($config['enable_sla'])
{
  print_cli_data_field("Discovering MIBs", 3);

  $valid['slas'] = array();

  $include_dir = "includes/discovery/slas";
  include($config['install_dir']."/includes/include-dir-mib.inc.php");

  if (OBS_DEBUG > 1) { print_vars($sla_table); }

  // Get existing SLAs
  $sla_db  = array();
  foreach (dbFetchRows("SELECT * FROM `slas` WHERE `device_id` = ?", array($device['device_id'])) as $entry)
  {
    if (!isset($entry['sla_mib'])) { $entry['sla_mib'] = 'CISCO-RTTMON-MIB'; } // FIXME, remove in r7000

    $index = $entry['sla_index'];
    $mib_lower = strtolower($entry['sla_mib']);
    if ($mib_lower != 'cisco-rttmon-mib')
    {
      // Use 'owner.index' as index, because all except Cisco use this!
      $index = $entry['sla_owner'] . '.' . $index;
    }

    $sla_db[$mib_lower][$index] = $entry;
  }
  if (OBS_DEBUG > 1) { print_vars($sla_db); }

  foreach ($sla_table as $mib => $oids)
  {
    $mib_lower = strtolower($mib);
    foreach ($oids as $index => $entry)
    {

      if (!isset($sla_db[$mib_lower][$index]))
      {
        // Not exist, add
        $sla_id = dbInsert($entry, 'slas');
        $GLOBALS['module_stats'][$module]['added']++; //echo "+";
      } else {
        $sla_id = $sla_db[$mib_lower][$index]['sla_id'];

        $update_db = array();
        foreach ($entry as $key => $value)
        {
          if ($sla_db[$mib_lower][$index][$key] != $value) { $update_db[$key] = $value; }
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
      $valid['slas'][$mib_lower][$sla_id] = $sla_id;
    }
  }

  // Mark all remaining SLAs as deleted
  foreach ($sla_db as $mib_lower => $data)
  {
    foreach ($data as $entry)
    {
      if (isset($valid['slas'][$mib_lower][$entry['sla_id']]) || $entry['deleted'] == 1)
      {
        // SLA exist or already deleted
        continue;
      } else {
        if (!$entry['rttMonCtrlAdminStatus'])
        {
          dbDelete('slas', "`sla_id` = ?", array($entry['sla_id']));
          dbDelete('slas-state', "`sla_id` = ?", array($entry['sla_id']));
        } else {
          dbUpdate(array('deleted' => 1), 'slas', "`sla_id` = ?", array($entry));
        }
        $GLOBALS['module_stats'][$module]['deleted']++; //echo "-";
      }
    }
  }

  $GLOBALS['module_stats'][$module]['status'] = count($valid['slas']);
  if (OBS_DEBUG && $GLOBALS['module_stats'][$module]['status'])
  {
    print_vars($valid['slas']);
  }

  // Clean
  unset($update_db, $sla_db, $sla_table, $sla_ids, $data, $entry, $oids, $mib);
} # enable_sla

// EOF
