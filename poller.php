#!/usr/bin/env php
<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage poller
 * @author     Adam Armstrong <adama@observium.org>
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

chdir(dirname($argv[0]));

// Get options before definitions!
$options = getopt("h:i:m:n:dqrMV");

include("includes/sql-config.inc.php");
include("includes/polling/functions.inc.php");

$scriptname = basename($argv[0]);

$cli = TRUE;

$poller_start = utime();

if (isset($options['V']))
{
  print_message(OBSERVIUM_PRODUCT." ".OBSERVIUM_VERSION);
  if (is_array($options['V'])) { print_versions(); }
  exit;
}
else if (isset($options['M']))
{
  print_message(OBSERVIUM_PRODUCT." ".OBSERVIUM_VERSION);

  print_message('Enabled poller modules:');
  $m_disabled = array();
  foreach ($config['poller_modules'] as $module => $ok)
  {
    if ($ok) { print_message('  '.$module); }
    else { $m_disabled[] = $module; }
  }
  if (count($m_disabled))
  {
    print_message('Disabled poller modules:');
    print_message('  '.implode("\n  ", $m_disabled));
  }
  exit;
}

if (!isset($options['q']))
{

print_cli_banner();

$latest['version']  = get_obs_attrib('latest_ver');
$latest['revision'] = get_obs_attrib('latest_rev');
$latest['date']     = get_obs_attrib('latest_rev_date');

if ($latest['revision'] > OBSERVIUM_REV)
{
  print_message("%GThere is a newer revision of Observium available!%n", 'color');
  print_message("%GVersion %r" . $latest['version']."%G (" . format_unixtime(datetime_to_unixtime($latest['date']), 'jS F Y').") is %r". ($latest['revision']-OBSERVIUM_REV) ."%G revisions ahead.%n\n", 'color');
}

//  print_message("%g".OBSERVIUM_PRODUCT." ".OBSERVIUM_VERSION."\n%WPoller%n\n", 'color');
  if (OBS_DEBUG) { print_versions(); }
}

if ($options['h'] == "odd")      { $options['n'] = "1"; $options['i'] = "2"; }
elseif ($options['h'] == "even") { $options['n'] = "0"; $options['i'] = "2"; }
elseif ($options['h'] == "all")  { $where = " "; $doing = "all"; }
elseif ($options['h'])
{
  $params = array();
  if (is_numeric($options['h']))
  {
    $where = "AND `device_id` = ?";
    $doing = $options['h'];
    $params[] = $options['h'];
  }
  else
  {
    $where = "AND `hostname` LIKE ?";
    $doing = $options['h'];
    $params[] = str_replace('*','%', $options['h']);
  }
}

if (isset($options['i']) && $options['i'] && isset($options['n']))
{
  $where = true; // FIXME

  $query = 'SELECT `device_id` FROM (SELECT @rownum :=0) r,
              (
                SELECT @rownum := @rownum +1 AS rownum, `device_id`
                FROM `devices`
                WHERE `disabled` = 0
                ORDER BY `device_id` ASC
              ) temp
            WHERE MOD(temp.rownum, '.$options['i'].') = ?;';
  $doing = $options['n'] ."/".$options['i'];
  $params[] = $options['n'];
}

if (!$where)
{
  print_message("%n
USAGE:
$scriptname [-drqV] [-i instances] [-n number] [-m module] [-h device]

EXAMPLE:
-h <device id> | <device hostname wildcard>  Poll single device
-h odd                                       Poll odd numbered devices  (same as -i 2 -n 0)
-h even                                      Poll even numbered devices (same as -i 2 -n 1)
-h all                                       Poll all devices
-h new                                       Poll all devices that have not had a discovery run before

-i <instances> -n <number>                   Poll as instance <number> of <instances>
                                             Instances start at 0. 0-3 for -n 4

OPTIONS:
 -h                                          Device hostname, id or key odd/even/all/new.
 -i                                          Poll instance.
 -n                                          Poll number.
 -q                                          Quiet output.
 -M                                          Show globally enabled/disabled modules and exit.
 -V                                          Show version and exit.

DEBUGGING OPTIONS:
 -r                                          Do not create or update RRDs
 -d                                          Enable debugging output.
 -dd                                         More verbose debugging output.
 -m                                          Specify module(s) (separated by commas) to be run.

%rInvalid arguments!%n", 'color', FALSE);
  exit;
}

if (isset($options['r']))
{
  $config['norrd'] = TRUE;
}

$cache['maint'] = cache_alert_maintenance();

rrdtool_pipe_open($rrd_process, $rrd_pipes);

print_cli_heading("%WStarting polling run at ".date("Y-m-d H:i:s"), 0);
$polled_devices = 0;
if (!isset($query))
{
  $query = "SELECT `device_id` FROM `devices` WHERE `disabled` = 0 $where ORDER BY `device_id` ASC";
}

foreach (dbFetch($query, $params) as $device)
{
  $device = dbFetchRow("SELECT * FROM `devices` WHERE `device_id` = ?", array($device['device_id']));
  poll_device($device, $options);
  $polled_devices++;
}

$poller_end = utime(); $poller_run = $poller_end - $poller_start; $poller_time = substr($poller_run, 0, 5);

if ($polled_devices)
{
  dbInsert(array('type' => 'poll', 'doing' => $doing, 'start' => $poller_start, 'duration' => $poller_time, 'devices' => $polled_devices ), 'perf_times');
  if (is_numeric($doing)) { $doing = $device['hostname']; } // Single device ID convert to hostname for log
} else {
  print_warning("WARNING: 0 devices polled. Did you specify a device that does not exist?");
}

$string = $argv[0] . ": $doing - $polled_devices devices polled in $poller_time secs";
print_debug($string);

print_cli_heading("%WCompleted polling run at ".date("Y-m-d H:i:s"), 0);


if (!isset($options['q']))
{
  if ($config['snmp']['hide_auth'])
  {
    print_debug("NOTE, \$config['snmp']['hide_auth'] is set to TRUE, snmp community and snmp v3 auth hidden from debug output.");
  }

  print_cli_data('Devices Polled', $polled_devices, 0);

  print_cli_data('Poller Time', $poller_time ." secs", 0);

  print_cli_data('Memory usage', formatStorage(memory_get_usage(TRUE), 2, 4).' (peak: '.formatStorage(memory_get_peak_usage(TRUE), 2, 4).')', 0);

  print_cli_data('MySQL Usage', 'Cell['.($db_stats['fetchcell']+0).'/'.round($db_stats['fetchcell_sec']+0,3).'s]'.
                       ' Row['.($db_stats['fetchrow']+0). '/'.round($db_stats['fetchrow_sec']+0,3).'s]'.
                      ' Rows['.($db_stats['fetchrows']+0).'/'.round($db_stats['fetchrows_sec']+0,3).'s]'.
                    ' Column['.($db_stats['fetchcol']+0). '/'.round($db_stats['fetchcol_sec']+0,3).'s]'.
                    ' Update['.($db_stats['update']+0).'/'.round($db_stats['update_sec']+0,3).'s]'.
                    ' Insert['.($db_stats['insert']+0). '/'.round($db_stats['insert_sec']+0,3).'s]'.
                    ' Delete['.($db_stats['delete']+0). '/'.round($db_stats['delete_sec']+0,3).'s]', 0);

  foreach($GLOBALS['rrdtool'] AS $cmd => $data)
  {
    $rrd_times[] = $cmd."[".$data['count']."/".round($data['time'],3)."s]";
  }

  print_cli_data('RRDTool Usage', implode(" ", $rrd_times), 0);
}

logfile($string);
rrdtool_pipe_close($rrd_process, $rrd_pipes);
unset($config); // Remove this for testing

#print_vars(get_defined_vars());

echo("\n");

// EOF
