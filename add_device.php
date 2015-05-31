#!/usr/bin/env php
<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage cli
 * @author     Adam Armstrong <adama@memetic.org>
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

chdir(dirname($argv[0]));

include("includes/defaults.inc.php");
include("config.php");
include("includes/definitions.inc.php");
include("includes/functions.inc.php");
include("includes/discovery/functions.inc.php");

$scriptname = basename($argv[0]);

print_message("%g".OBSERVIUM_PRODUCT." ".OBSERVIUM_VERSION."\n%WAdd Device(s)%n\n", 'color');

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
    $host      = strtolower($add[0]);
    $community = $add[1];
    $snmpver   = strtolower($add[2]);

    $port = 161;
    $transport = 'udp';

    if ($snmpver == "v3")
    {
      $config['snmp']['v3'] = $snmp_config_v3; // Restore base SNMP v3 credentials
      $seclevel = $community;

      // These values are the same as in defaults.inc.php
      $v3 = array(
        'authlevel'  => "noAuthNoPriv",
        'authname'   => "observium",
        'authpass'   => "",
        'authalgo'   => "MD5",
        'cryptopass' => "",
        'cryptoalgo' => "AES"
      );

      if ($seclevel == "nanp" || $seclevel == "any" || $seclevel == "noAuthNoPriv")
      {
        $v3['authlevel'] = "noAuthNoPriv";
        $v3args = array_slice($add, 3);

        while ($arg = array_shift($v3args))
        {
          // parse all remaining args
          if (is_numeric($arg))
          {
            $port = $arg;
          }
          elseif (preg_match ('/^(' . implode("|",$config['snmp']['transports']) . ')$/', $arg))
          {
            $transport = $arg;
          }
          else
          {
            // should add a sanity check of chars allowed in user
            $user = $arg;
          }
        }

        if ($seclevel != "any") { array_push($config['snmp']['v3'], $v3); }
      }
      elseif ($seclevel == "anp" || $seclevel == "authNoPriv")
      {

        $v3['authlevel'] = "authNoPriv";
        $v3args = array_slice($argv, 4);
        $v3['authname'] = array_shift($v3args);
        $v3['authpass'] = array_shift($v3args);

        while ($arg = array_shift($v3args))
        {
          // parse all remaining args
          if (is_numeric($arg))
          {
            $port = $arg;
          }
          elseif (preg_match ('/^(' . implode("|",$config['snmp']['transports']) . ')$/i', $arg))
          {
            $transport = $arg;
          }
          elseif (preg_match ('/^(sha|md5)$/i', $arg))
          {
            $v3['authalgo'] = $arg;
          }
        }

        array_push($config['snmp']['v3'], $v3);
      }
      elseif ($seclevel == "ap" or $seclevel == "authPriv")
      {
        $v3['authlevel'] = "authPriv";
        $v3args = array_slice($argv, 4);
        $v3['authname'] = array_shift($v3args);
        $v3['authpass'] = array_shift($v3args);
        $v3['cryptopass'] = array_shift($v3args);

        while ($arg = array_shift($v3args))
        {
          // parse all remaining args
          if (is_numeric($arg))
          {
            $port = $arg;
          }
          elseif (preg_match ('/^(' . implode("|",$config['snmp']['transports']) . ')$/i', $arg))
          {
            $transport = $arg;
          }
          elseif (preg_match ('/^(sha|md5)$/i', $arg))
          {
            $v3['authalgo'] = $arg;
          }
          elseif (preg_match ('/^(aes|des)$/i', $arg))
          {
            $v3['cryptoalgo'] = $arg;
          }
        }

        array_push($config['snmp']['v3'], $v3);
      }
    } else {
      // v1 or v2c
      $v2args = array_slice($argv, 2);

      while ($arg = array_shift($v2args))
      {
        // parse all remaining args
        if (is_numeric($arg))
        {
          $port = $arg;
        }
        elseif (preg_match ('/(' . implode("|",$config['snmp']['transports']) . ')/i', $arg))
        {
          $transport = $arg;
        }
        elseif (preg_match ('/^(v1|v2c)$/i', $arg))
        {
          $snmpver = $arg;
        }
      }

      $config['snmp']['community'] = ($community ? array($community) : $snmp_config_community);
    }

    print_message("Try to add $host:");
    if ($snmpver)
    {
      $device_id = add_device($host, $snmpver, $port, $transport);
    } else {
      $device_id = add_device($host, NULL, $port, $transport);
    }

    if ($device_id)
    {
      $device = device_by_id_cache($device_id);
      print_success("Added device ".$device['hostname']." (".$device_id.").");
      $added++;
    }
  }
}

$count  = count($add_array);
$failed = $count - $added;
if ($added)
{
  print_message("\nDevices added: $added.");
  if ($failed) { print_message("Devices skipped: $failed."); }
} else {
  if ($count)  { print_message("Devices skipped: $failed."); }
  print_message("%n
USAGE:
$scriptname <hostname> [community] [v1|v2c] [port] [" . implode("|",$config['snmp']['transports']) . "]
$scriptname <hostname> [any|nanp|anp|ap] [v3] [user] [password] [enckey] [md5|sha] [aes|des] [port] [" . implode("|",$config['snmp']['transports']) . "]
$scriptname <filename>

EXAMPLE:
%WSNMPv1/2c%n:                    $scriptname <%Whostname%n> [community] [v1|v2c] [port] [" . implode("|",$config['snmp']['transports']) . "]
%WSNMPv3%n   :         Defaults : $scriptname <%Whostname%n> any v3 [user] [port] [" . implode("|",$config['snmp']['transports']) . "]
           No Auth, No Priv : $scriptname <%Whostname%n> nanp v3 [user] [port] [" . implode("|",$config['snmp']['transports']) . "]
              Auth, No Priv : $scriptname <%Whostname%n> anp v3 <user> <password> [md5|sha] [port] [" . implode("|",$config['snmp']['transports']) . "]
              Auth,    Priv : $scriptname <%Whostname%n> ap v3 <user> <password> <enckey> [md5|sha] [aes|des] [port] [" . implode("|",$config['snmp']['transports']) . "]
%WFILE%n     :                    $scriptname <%Wfilename%n>

ADD FROM FILE:
 To add multiple devices, create a file in which each line contains one device with or without options.
 Format for device options, the same as specified in USAGE.", 'color', FALSE);
}

// EOF
