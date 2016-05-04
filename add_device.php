#!/usr/bin/env php
<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package        observium
 * @subpackage     cli
 * @author         Adam Armstrong <adama@observium.org>
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

chdir(dirname($argv[0]));
$scriptname = basename($argv[0]);

$options = getopt("dhpt");
if (isset($options['d'])) { array_shift($argv); }

include("includes/sql-config.inc.php");
include("includes/discovery/functions.inc.php");

print_message("%g" . OBSERVIUM_PRODUCT . " " . OBSERVIUM_VERSION . "\n%WAdd Device(s)%n\n", 'color');

if (OBS_DEBUG) { print_versions(); }

if (isset($options['h'])) { print_help($scriptname); exit; }

$snmp_options = array();
// Just test, do not add device
if (isset($options['t']))
{
  $snmp_options['test'] = TRUE;
  array_shift($argv);
}
// Add skip pingable checks if argument -p passed
if (isset($options['p']))
{
  $snmp_options['ping_skip'] = 1;
  array_shift($argv);
}

$added = 0;

if (!empty($argv[1]))
{
  $add_array = array();
  if (is_file($argv[1]))
  {
    // Parse file into array with devices to add
    foreach (new SplFileObject($argv[1]) as $line)
    {
      $d = preg_split('/\s/', $line, -1, PREG_SPLIT_NO_EMPTY);
      if (empty($d) || substr(reset($d), 0, 1) == '#') continue;
      $add_array[] = $d;
    }
  } else {
    $add_array[0] = $argv;
    array_shift($add_array[0]);
  }

  // Save base SNMP v3 credentials and v2c/v1 community
  $snmp_config_v3        = $config['snmp']['v3'];
  $snmp_config_community = $config['snmp']['community'];

  foreach ($add_array as $add)
  {
    $hostname = strtolower($add[0]);
    $snmp_community = $add[1];
    $snmp_version = strtolower($add[2]);

    $snmp_port = 161;
    $snmp_transport = 'udp';

    if ($snmp_version == "v3")
    {
      $config['snmp']['v3'] = $snmp_config_v3; // Restore base SNMP v3 credentials
      $snmp_v3_seclevel = $snmp_community;

      // These values are the same as in defaults.inc.php
      $snmp_v3_auth = array(
        'authlevel'  => "noAuthNoPriv",
        'authname'   => "observium",
        'authpass'   => "",
        'authalgo'   => "MD5",
        'cryptopass' => "",
        'cryptoalgo' => "AES"
      );

      if ($snmp_v3_seclevel == "nanp" || $snmp_v3_seclevel == "any" || $snmp_v3_seclevel == "noAuthNoPriv")
      {
        $snmp_v3_auth['authlevel'] = "noAuthNoPriv";
        $snmp_v3_args = array_slice($add, 3);

        while ($arg = array_shift($snmp_v3_args))
        {
          // parse all remaining args
          if (is_numeric($arg))
          {
            $snmp_port = $arg;
          }
          else if (preg_match('/^(' . implode("|", $config['snmp']['transports']) . ')$/', $arg))
          {
            $snmp_transport = $arg;
          } else {
            // FIXME: should add a sanity check of chars allowed in user
            $user = $arg;
          }
        }

        if ($snmp_v3_seclevel != "any")
        {
          array_push($config['snmp']['v3'], $snmp_v3_auth);
        }
      }
      else if ($snmp_v3_seclevel == "anp" || $snmp_v3_seclevel == "authNoPriv")
      {

        $snmp_v3_auth['authlevel'] = "authNoPriv";
        $snmp_v3_args = array_slice($argv, 4);
        $snmp_v3_auth['authname'] = array_shift($snmp_v3_args);
        $snmp_v3_auth['authpass'] = array_shift($snmp_v3_args);

        while ($arg = array_shift($snmp_v3_args))
        {
          // parse all remaining args
          if (is_numeric($arg))
          {
            $snmp_port = $arg;
          }
          else if (preg_match('/^(' . implode("|", $config['snmp']['transports']) . ')$/i', $arg))
          {
            $snmp_transport = $arg;
          }
          else if (preg_match('/^(sha|md5)$/i', $arg))
          {
            $snmp_v3_auth['authalgo'] = $arg;
          }
        }

        array_push($config['snmp']['v3'], $snmp_v3_auth);
      }
      else if ($snmp_v3_seclevel == "ap" || $snmp_v3_seclevel == "authPriv")
      {
        $snmp_v3_auth['authlevel'] = "authPriv";
        $snmp_v3_args = array_slice($argv, 4);
        $snmp_v3_auth['authname'] = array_shift($snmp_v3_args);
        $snmp_v3_auth['authpass'] = array_shift($snmp_v3_args);
        $snmp_v3_auth['cryptopass'] = array_shift($snmp_v3_args);

        while ($arg = array_shift($snmp_v3_args))
        {
          // parse all remaining args
          if (is_numeric($arg))
          {
            $snmp_port = $arg;
          }
          elseif (preg_match('/^(' . implode("|", $config['snmp']['transports']) . ')$/i', $arg))
          {
            $snmp_transport = $arg;
          }
          elseif (preg_match('/^(sha|md5)$/i', $arg))
          {
            $snmp_v3_auth['authalgo'] = $arg;
          }
          elseif (preg_match('/^(aes|des)$/i', $arg))
          {
            $snmp_v3_auth['cryptoalgo'] = $arg;
          }
        }

        array_push($config['snmp']['v3'], $snmp_v3_auth);
      }
    } else {
      // v1 or v2c
      $snmp_v2_args = array_slice($argv, 2);

      while ($arg = array_shift($snmp_v2_args))
      {
        // parse all remaining args
        if (is_numeric($arg))
        {
          $snmp_port = $arg;
        }
        elseif (preg_match('/(' . implode("|", $config['snmp']['transports']) . ')/i', $arg))
        {
          $snmp_transport = $arg;
        }
        elseif (preg_match('/^(v1|v2c)$/i', $arg))
        {
          $snmp_version = $arg;
        }
      }

      $config['snmp']['community'] = ($snmp_community ? array($snmp_community) : $snmp_config_community);
    }

    print_message("Try to add $hostname:");
    if (in_array($snmp_version, array('v1', 'v2c', 'v3')))
    {
      // If snmp version passed in arguments, then use the exact version
      $device_id = add_device($hostname, $snmp_version, $snmp_port, $snmp_transport, $snmp_options);
    } else {
      // If snmp version unknown ckeck all possible snmp versions and auth options
      $device_id = add_device($hostname,          NULL, $snmp_port, $snmp_transport, $snmp_options);
    }

    if ($device_id)
    {
      if (!isset($options['t']))
      {
        $device = device_by_id_cache($device_id);
        print_success("Added device " . $device['hostname'] . " (" . $device_id . ").");
      } // Else this is device testing, success message already written by add_device()
      $added++;
    }
  }
}

$count = count($add_array);
$failed = $count - $added;
if ($added)
{
  print_message("\nDevices success: $added.");
  if ($failed)
  {
    print_message("Devices failed: $failed.");
  }
} else {
  if ($count)
  {
    print_message("Devices failed: $failed.");
  }
  print_help($scriptname);
}

function print_help($scriptname)
{
  global $config;

  print_message("%n
USAGE:
$scriptname <hostname> [community] [v1|v2c] [port] [" . implode("|", $config['snmp']['transports']) . "]
$scriptname <hostname> [any|nanp|anp|ap] [v3] [user] [password] [enckey] [md5|sha] [aes|des] [port] [" . implode("|", $config['snmp']['transports']) . "]
$scriptname <filename>

EXAMPLE:
%WSNMPv1/2c%n:                    $scriptname <%Whostname%n> [community] [v1|v2c] [port] [" . implode("|", $config['snmp']['transports']) . "]
%WSNMPv3%n   :         Defaults : $scriptname <%Whostname%n> any v3 [user] [port] [" . implode("|", $config['snmp']['transports']) . "]
           No Auth, No Priv : $scriptname <%Whostname%n> nanp v3 [user] [port] [" . implode("|", $config['snmp']['transports']) . "]
              Auth, No Priv : $scriptname <%Whostname%n> anp v3 <user> <password> [md5|sha] [port] [" . implode("|", $config['snmp']['transports']) . "]
              Auth,    Priv : $scriptname <%Whostname%n> ap v3 <user> <password> <enckey> [md5|sha] [aes|des] [port] [" . implode("|", $config['snmp']['transports']) . "]
%WFILE%n     :                    $scriptname <%Wfilename%n>

ADD FROM FILE:
 To add multiple devices, create a file in which each line contains one device with or without options.
 Format for device options, the same as specified in USAGE.

OPTIONS:
 -p                                          Skip icmp echo checks, device added only by SNMP checks

DEBUGGING OPTIONS:
 -d                                          Enable debugging output.
 -dd                                         More verbose debugging output.
 -t                                          Do not add device(s), only test auth options.", 'color', FALSE);
}

// EOF
