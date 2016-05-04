<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage discovery
 * @author     Adam Armstrong <adama@observium.org>
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

echo(" Neighbours discovery: ");

$valid['neighbours'] = array();

// Include all discovery modules
$include_dir = "includes/discovery/neighbours";
include("includes/include-dir-mib.inc.php");

if (OBS_DEBUG > 1 && count($GLOBALS['cache']['discovery-protocols']))
{
  var_dump($GLOBALS['cache']['discovery-protocols']);
}

$table_rows = array();
$neighbours_db = dbFetchRows('SELECT * FROM `neighbours` LEFT JOIN `ports` USING(`port_id`) WHERE `device_id` = ?', array($device['device_id']));
foreach ($neighbours_db as $neighbour)
{
  $local_port_id   = $neighbour['port_id'];
  $remote_hostname = $neighbour['remote_hostname'];
  $remote_port     = $neighbour['remote_port'];
  print_debug("$local_port_id -> $remote_hostname -> $remote_port");
  if (!$valid['neighbours'][$local_port_id][$remote_hostname][$remote_port])
  {
    dbDelete('neighbours', '`neighbour_id` = ?', array($neighbour['neighbour_id']));
    $GLOBALS['module_stats'][$module]['deleted']++;
  } else {
    $port = get_port_by_id_cache($local_port_id);
    if (is_numeric($neighbour['remote_port_id']))
    {
      $remote_port_array = get_port_by_id_cache($neighbour['remote_port_id']);
      $remote_port = $remote_port_array['port_label'];
    }
    $table_rows[] = array(nicecase($neighbour['protocol']), $port['port_label'], $remote_hostname, $remote_port, truncate($neighbour['remote_platform'], 20), truncate($neighbour['remote_version'], 40));
  }
}

echo(PHP_EOL);
$table_headers = array('%WProtocol%n', '%WifName%n', '%WRemote: hostname%n', '%Wport%n', '%Wplatform%n', '%Wversion%n');
print_cli_table($table_rows, $table_headers);

$GLOBALS['module_stats'][$module]['status'] = count($valid[$module]);
if (OBS_DEBUG && $GLOBALS['module_stats'][$module]['status']) { print_vars($valid[$module]); }

unset($valid['neighbours']);
echo(PHP_EOL);

// EOF
