<?php

/* Observium Network Management and Monitoring System
 *
 * @package    observium
 * @subpackage poller
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

global $graphs;

$count = dbFetchCell('SELECT COUNT(*) FROM `status` WHERE `device_id` = ? AND `status_deleted` = ?;', array($device['device_id'], '0'));

print_cli_data("Status Count", $count);

if ($count > 0)
{

  // Cache data for use by polling modules
  foreach (dbFetchRows("SELECT DISTINCT `status_type` FROM `status` WHERE `device_id` = ? AND `poller_type` = 'snmp' AND `status_deleted` = '0';", array($device['device_id'])) as $s_type)
  {
    if (is_array($config['sensor']['cache_oids'][$s_type['sensor_type']]))
    {
      echo('Caching: '.$s_type['sensor_type'].' ');
      // FIXME : This needs to be a function.
      foreach ($config['sensor']['cache_oids'][$s_type['sensor_type']] as $oid_to_cache)
      {
        if (!$oids_cached[$oid_to_cache])
        {
          echo($oid_to_cache . ' ');
          $oids_cached[$oid_to_cache] = TRUE;
          $oid_cache = snmpwalk_numericoids($device, $oid_to_cache, $oid_cache);
          $oids_cached[$oid_to_cache] = TRUE;
        }
      }
      //echo(PHP_EOL);
    }
  }

  global $table_rows;

  $table_rows = array();

  poll_status($device);

  $headers = array('%WDescr%n', '%WType%n', '%WIndex%n', '%WOrigin%n', '%WValue%n', '%WStatus%n', '%WLast Changed%n');
  print_cli_table($table_rows, $headers);

}

// EOF
