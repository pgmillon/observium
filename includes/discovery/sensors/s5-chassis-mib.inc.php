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

// This could do with a rewrite.

echo(" S5-CHASSIS-MIB ");

# Get major version number of running firmware
$fw_major_version = Null;
preg_match("/[0-9]\.[0-9]/", $device['version'], $fw_major_version);
$fw_major_version = $fw_major_version[0];

# Temperature info only known to be present in firmware 6.1 or higher
if ($fw_major_version >= 6.1)
{
  $temps = snmp_walk($device, "1.3.6.1.4.1.45.1.6.3.7.1.1.5.5", "-Osqn");
  $scale = 0.5;
  foreach (explode("\n", $temps) as $i => $t)
  {
    $t = explode(" ",$t);
    $oid = $t[0];
    $value = trim($t[1]);
    discover_sensor($valid['sensor'], 'temperature', $device, $oid, zeropad($i+1), 'avaya-ers', "Unit " . ($i+1) . " temperature", $scale, $value);
  }
}

// EOF
