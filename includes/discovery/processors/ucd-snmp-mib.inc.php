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

$count = @dbFetchCell("SELECT COUNT(*) FROM processors WHERE device_id = ? AND processor_type != 'ucd-old'", array($device['device_id']));

if ($device['os_group'] == "unix" && $count == "0")
{
  echo("UCD-SNMP-MIB ");

  $system = snmp_get($device, "ssCpuSystem.0", "-OvQ", "UCD-SNMP-MIB", mib_dirs());
  $user   = snmp_get($device, "ssCpuUser.0"  , "-OvQ", "UCD-SNMP-MIB", mib_dirs());
  $idle   = snmp_get($device, "ssCpuIdle.0"  , "-OvQ", "UCD-SNMP-MIB", mib_dirs());

  if (is_numeric($system))
  {
    $percent = $system + $user + $idle;
    discover_processor($valid['processor'], $device, 0, 0, "ucd-old", "CPU", 1, $system+$user, NULL, NULL);
  }
}

// EOF
