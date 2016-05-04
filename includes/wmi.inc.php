<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage wmi
 * @author     Adam Armstrong <adama@observium.org>
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

// Execute wmic using provided config variables and WQL then return output string
// DOCME needs phpdoc block
// TESTME needs unit testing
function wmi_query($wql, $override = NULL, $namespace = NULL)
{
  if (!isset($namespace))
  {
    $namespace = $GLOBALS['config']['wmi']['namespace'];
  }

  if (isset($override) && is_array(($override)))
  {
    $hostname = $override['hostname'];
    $domain   = $override['domain'];
    $username = $override['username'];
    $password = $override['password'];
  }
  else
  {
    $hostname = $GLOBALS['device']['hostname'];
    $domain   = $GLOBALS['config']['wmi']['domain'];
    $username = $GLOBALS['config']['wmi']['user'];
    $password = $GLOBALS['config']['wmi']['pass'];
  }

  $options = "--user='" . $username . "' ";
  if (empty($password)) { $options .= "--no-pass "; } else { $options .= "--password='". $password . "' "; }
  if (!empty($domain)) { $options .= "--workgroup='". $domain . "' "; }
  if (empty($GLOBALS['config']['wmi']['delimiter'])) { $options .= "--delimiter=## "; } else { $options .= "--delimiter=" . $GLOBALS['config']['wmi']['delimiter'] ." "; }
  if (empty($namespace)) { $options .= "--namespace='root\CIMV2' "; } else { $options .= "--namespace='" . $namespace ."' "; }
  if (OBS_DEBUG) { $options .= "-d2 "; }
  $options .= "//" . $hostname;

  $cmd = $GLOBALS['config']['wmic'] . " " . $options . " " . "\"".$wql."\"";

  return external_exec($cmd);
}

// Import WMI string to array, remove any empty lines, find "CLASS:" in string, parse the following lines into array
// $ret_single == TRUE will output a single dimension array only if there is one "row" of results
// $ret_val == <WMI Property> will output the value of a single property. Only works when $ret_single == TRUE
// Will quit if "ERROR:" is found (usually means the WMI class does not exist)
// DOCME needs phpdoc block
// TESTME needs unit testing
function wmi_parse($wmi_string, $ret_single = FALSE, $ret_val = NULL)
{

  $wmi_lines = array_filter(explode(PHP_EOL, $wmi_string), 'strlen');
  $wmi_class = NULL;
  $wmi_error = NULL;
  $wmi_properties = array();
  $wmi_results = array();

  foreach ($wmi_lines as $line)
  {
    if (preg_match('/ERROR:/', $line))
    {
      $wmi_error = substr($line, strpos($line, 'ERROR:') + strlen("ERROR: "));
      if (OBS_DEBUG)
      {
        // If the error is something other than "Retrieve result data." please report it
        switch($wmi_error) {
          case "Retrieve result data.":
            echo("WMI Error: Cannot connect to host or Class\n");
            break;
          case "Login to remote object.":
            echo("WMI Error: Invalid security credentials or insufficient WMI security permissions\n");
            break;
          default:
            echo("WMI Error: Please report");
            break;
        }
      }
      return NULL;
    }
    if (empty($wmi_class))
    {
      if (preg_match('/^CLASS:/', $line))
      {
        $wmi_class = substr($line, strlen("CLASS: "));
      }
    }
    else if (empty($wmi_properties))
    {
      $wmi_properties = explode($GLOBALS['config']['wmi']['delimiter'], $line);
    } else {
      $wmi_results[] = array_combine($wmi_properties, explode($GLOBALS['config']['wmi']['delimiter'], str_replace('(null)', '', $line)));
    }
  }
  if (count($wmi_results) == 1)
  {
    if ($ret_single)
    {
      if ($ret_val)
      {
        return $wmi_results[0][$ret_val];
      } else {
        return $wmi_results[0];
      }
    }
  }

  return $wmi_results;
}

// DOCME needs phpdoc block
// TESTME needs unit testing
function wmi_dbAppInsert($device_id, $app)
{
  $dbCheck = dbFetchRow("SELECT * FROM `applications` WHERE `device_id` = ? AND `app_type` = ? AND `app_instance` = ?", array($device_id, $app['type'], $app['instance']));

  if (empty($dbCheck))
  {
    echo("Found new application '".strtoupper($app['type'])."'");
    if (isset($app['instance']))
    {
      echo(" Instance '".$app['instance']."'");
    }
    echo("\n");

    dbInsert(array('device_id' => $device_id, 'app_type' => $app['type'], 'app_instance' => $app['instance'], 'app_name' => $app['name']), 'applications');
  }
  else if (empty($dbCheck['app_name']) && isset($app['name']))
  {
    dbUpdate(array('app_name' => $app['name']), 'applications', "`app_id` = ?", array($dbCheck['app_id']));
  }
}

// EOF
