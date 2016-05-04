<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage poller
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

/* TODO:
 * - Implement additional counters
 */
echo(" MSSQL:\n");
foreach ($wmi['mssql']['services'] as $instance)
{
  if ($instance['Name'] !== "MSSQLSERVER")
  {
    $instance['Name'] = substr($instance['Name'], strpos($instance['Name'], "$") + 1);
    $instance['Name'] = preg_replace('/_/', "", $instance['Name']);

    $wmi_class_prefix = "Win32_PerfFormattedData_MSSQL".$instance['Name']."_MSSQL".$instance['Name'];
  }
  else
  {
    $wmi_class_prefix = "Win32_PerfFormattedData_MSSQLSERVER_SQLServer";
  }

  $app_found = FALSE;
  $app_data = array();
  echo("  ".$instance['Name']." - PID: ".$instance['ProcessId']."\n   ");

  $wql = "SELECT * FROM ".$wmi_class_prefix."GeneralStatistics";
  $wmi['mssql'][$instance['Name']]['stats'] = wmi_parse(wmi_query($wql, $override), TRUE);

  if ($wmi['mssql'][$instance['Name']]['stats'])
  {
    $app_found['mssql'] = TRUE;
    echo("Stats; ");

    $rrd_filename = "wmi-app-mssql_".$instance['Name']."-stats.rrd";
    rrdtool_create($device, $rrd_filename,
        "DS:userconnections:GAUGE:600:0:125000000000 "
      );

    $app_data['stats']['UsrConn'] = $wmi['mssql'][$instance['Name']]['stats']['UserConnections'];

    rrdtool_update($device, $rrd_filename, "N:".
      $wmi['mssql'][$instance['Name']]['stats']['UserConnections']
    );
  }

  $wql = "SELECT * FROM ".$wmi_class_prefix."MemoryManager";
  $wmi['mssql'][$instance['Name']]['memory'] = wmi_parse(wmi_query($wql, $override), TRUE);

  if ($wmi['mssql'][$instance['Name']]['memory'])
  {
    $app_found['mssql'] = TRUE;
    echo("Memory; ");

    $wmi['mssql'][$instance['Name']]['memory']['TotalServerMemoryKB'] *= 1024;
    $wmi['mssql'][$instance['Name']]['memory']['TargetServerMemoryKB'] *= 1024;
    $wmi['mssql'][$instance['Name']]['memory']['SQLCacheMemoryKB'] *= 1024;

    $rrd_filename = "wmi-app-mssql_".$instance['Name']."-memory.rrd";

    rrdtool_create($device, $rrd_filename,
        "DS:totalmemory:GAUGE:600:0:125000000000 ".
        "DS:targetmemory:GAUGE:600:0:125000000000 ".
        "DS:cachememory:GAUGE:600:0:125000000000 ".
        "DS:grantsoutstanding:GAUGE:600:0:125000000000 ".
        "DS:grantspending:GAUGE:600:0:125000000000 ",
        "RRA:LAST:0.5:1:2016  RRA:LAST:0.5:6:2976  RRA:LAST:0.5:24:1440  RRA:LAST:0.5:288:1440 " . $GLOBALS['config']['rrd']['rra']
      );

    $app_data['memory']['used'] = $wmi['mssql'][$instance['Name']]['memory']['TotalServerMemoryKB'];
    $app_data['memory']['total'] = $wmi['mssql'][$instance['Name']]['memory']['TargetServerMemoryKB'];
    $app_data['memory']['cache'] = $wmi['mssql'][$instance['Name']]['memory']['SQLCacheMemoryKB'];
    $app_data['memory']['grnto'] = $wmi['mssql'][$instance['Name']]['memory']['MemoryGrantsOutstanding'];
    $app_data['memory']['grntp'] = $wmi['mssql'][$instance['Name']]['memory']['MemoryGrantsPending'];

    rrdtool_update($device, $rrd_filename, "N:".
      $wmi['mssql'][$instance['Name']]['memory']['TotalServerMemoryKB'].":".
      $wmi['mssql'][$instance['Name']]['memory']['TargetServerMemoryKB'].":".
      $wmi['mssql'][$instance['Name']]['memory']['SQLCacheMemoryKB'].":".
      $wmi['mssql'][$instance['Name']]['memory']['MemoryGrantsOutstanding'].":".
      $wmi['mssql'][$instance['Name']]['memory']['MemoryGrantsPending']
    );
  }

  $wql = "SELECT * FROM ".$wmi_class_prefix."BufferManager";
  $wmi['mssql'][$instance['Name']]['buffer'] = wmi_parse(wmi_query($wql, $override), TRUE);

  if ($wmi['mssql'][$instance['Name']]['buffer'])
  {
    $app_found['mssql'] = TRUE;
    echo("Buffer; ");

    $rrd_filename = "wmi-app-mssql_".$instance['Name']."-buffer.rrd";

    rrdtool_create($device, $rrd_filename,
        "DS:pagelifeexpect:GAUGE:600:0:125000000000 ".
        "DS:pagelookupssec:GAUGE:600:0:125000000000 ".
        "DS:pagereadssec:GAUGE:600:0:125000000000 ".
        "DS:pagewritessec:GAUGE:600:0:125000000000 ".
        "DS:freeliststalls:GAUGE:600:0:125000000000 "
      );

    $app_data['buffer']['LifeExp'] = $wmi['mssql'][$instance['Name']]['buffer']['Pagelifeexpectancy'];
    $app_data['buffer']['PgLook'] = $wmi['mssql'][$instance['Name']]['buffer']['PagelookupsPersec'];
    $app_data['buffer']['PgRead'] = $wmi['mssql'][$instance['Name']]['buffer']['PagereadsPersec'];
    $app_data['buffer']['PgWrite'] = $wmi['mssql'][$instance['Name']]['buffer']['PagewritesPersec'];
    $app_data['buffer']['Stalls'] = $wmi['mssql'][$instance['Name']]['buffer']['FreeliststallsPersec'];

    rrdtool_update($device, $rrd_filename, "N:".
      $wmi['mssql'][$instance['Name']]['buffer']['Pagelifeexpectancy'].":".
      $wmi['mssql'][$instance['Name']]['buffer']['PagelookupsPersec'].":".
      $wmi['mssql'][$instance['Name']]['buffer']['PagereadsPersec'].":".
      $wmi['mssql'][$instance['Name']]['buffer']['PagewritesPersec'].":".
      $wmi['mssql'][$instance['Name']]['buffer']['FreeliststallsPersec']
    );
  }

// CPU Usage

  $wql = "SELECT * FROM Win32_PerfRawData_PerfProc_Process WHERE IDProcess=".$instance['ProcessId'];
  $wmi['mssql'][$instance['Name']]['cpu'] = wmi_parse(wmi_query($wql, $override), TRUE);

  // Windows measures CPU usage using the PERF_100NSEC_TIMER_INV counter type, meaning measurements are in 100 nanosecond increments
  // http://msdn.microsoft.com/en-us/library/ms803963.aspx

  $cpu_ntime = sprintf('%u', utime() * 100000000);

  if ($wmi['mssql'][$instance['Name']]['cpu'])
  {
    $app_found['mssql'] = TRUE;
    echo("CPU; ");

    $rrd_filename = "wmi-app-mssql_".$instance['Name']."-cpu.rrd";

    rrdtool_create($device, $rrd_filename,
        "DS:percproctime:COUNTER:600:0:125000000000 ".
        "DS:threads:GAUGE:600:0:125000000000 ".
        "DS:lastpoll:COUNTER:600:0:125000000000 ",
        "RRA:LAST:0.5:1:2016  RRA:LAST:0.5:6:2976  RRA:LAST:0.5:24:1440  RRA:LAST:0.5:288:1440 " . $GLOBALS['config']['rrd']['rra']
      );

    $app_data['cpu']['proc'] = $wmi['mssql'][$instance['Name']]['cpu']['PercentProcessorTime'];
    $app_data['cpu']['time'] = $cpu_ntime;

    rrdtool_update($device, $rrd_filename, "N:".
      $wmi['mssql'][$instance['Name']]['cpu']['PercentProcessorTime'].":".
      $wmi['mssql'][$instance['Name']]['cpu']['ThreadCount'].":".
      $cpu_ntime
    );
  }

  if ($app_found['mssql'] == TRUE)
  {
    $app['type'] = "mssql";
    $app['name'] = "MSSQL";
    $app['instance'] = $instance['Name'];
    wmi_dbAppInsert($device['device_id'], $app); // FIXME discover_app ?
  }

  $sql = "SELECT * FROM `applications` AS A LEFT JOIN `applications-state` AS S ON `A`.`app_id`=`S`.`application_id` WHERE `A`.`device_id` = ? AND `A`.`app_instance` = ? AND `A`.`app_type` = 'mssql'";
  $app_state = dbFetchRow($sql, array($device['device_id'], $instance['Name']));
  $app_data = serialize($app_data);

  if (empty($app_state['app_state']))
  {
    dbInsert(array('application_id' => $app_state['app_id'], 'app_last_polled' => time(), 'app_status' => 1, 'app_state' => $app_data), 'applications-state');
  }
  else
  {
    dbUpdate(array('app_last_polled' => time(), 'app_status' => 1, 'app_state' => $app_data), 'applications-state', "`application_id` = ?", array($app_state['application_id']));
  }

  echo("\n");
}

unset ($wmi['mssql']);

// EOF
