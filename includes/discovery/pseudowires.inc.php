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

// FIXME. Migrate later to discover_pseudowire() & check_valid_pseudowire()
if (!$config['enable_pseudowires']) { return; }

  print_cli_data_field("Discovering MIBs", 3);

  $valid['pseudowires'] = array();

  // Pre-cache the existing state of pseudowires for this device from the database
  $pws_cache = array();
  $pws_db_raw = dbFetchRows("SELECT * FROM `pseudowires` WHERE `device_id` = ?", array($device['device_id']));
  foreach ($pws_db_raw as $pw_db)
  {
    $pws_cache['pws_db'][$pw_db['mib']][$pw_db['pwIndex']] = $pw_db;
    $pws_cache['pw_id_db'][$pw_db['mib']][$pw_db['pseudowire_id']] = $pw_db['pseudowire_id'];
  }
  unset($pws_db_raw); unset($pw_db);

  $include_dir = "includes/discovery/pseudowires";
  include($config['install_dir']."/includes/include-dir-mib.inc.php");

  // Cycle the list of pseudowires we cached earlier and make sure we saw them again.
  //echo("PWS_DB: ".count($pws_cache['pws_db'])."\n"); var_dump($pws_cache['pws_db']);
  //echo("PWS: ".count($pws_cache['pws'])."\n"); var_dump($pws_cache['pws']);
  foreach ($pws_cache['pw_id_db'] as $mib => $pw)
  {
    foreach ($pw as $pw_id)
    {
      if (!isset($valid['pseudowires'][$mib][$pw_id]))
      {
        dbDelete('pseudowires', '`pseudowire_id` = ?', array($pw_id));
        $GLOBALS['module_stats'][$module]['deleted']++;
      }
    }
  }

  $GLOBALS['module_stats'][$module]['status'] = count($valid[$module]);
  if (OBS_DEBUG && $GLOBALS['module_stats'][$module]['status']) { print_vars($valid[$module]); }

  // Clean
  unset($pw, $pws_cache, $mib);

// EOF
