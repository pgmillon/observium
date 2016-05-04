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

// Force10 M-Series

$mib = "F10-M-SERIES-CHASSIS-MIB";

echo("$mib ");

$processors_array = snmpwalk_cache_oid($device, "chStackUnitCpuUtil5Min", array(), $mib, mib_dirs('force10'));
$processors_array = snmpwalk_cache_oid($device, "chStackUnitSysType", $processors_array, $mib, mib_dirs('force10'));
if (OBS_DEBUG > 1) { print_vars($processors_array); }

if (is_array($processors_array))
{
  foreach ($processors_array as $index => $entry)
  {
    $descr = "Unit " . strval($index - 1) . " " . $entry['chStackUnitSysType'];
    $oid = "1.3.6.1.4.1.6027.3.19.1.2.8.1.4.".$index;
    $usage = $entry['chStackUnitCpuUtil5Min'];

    discover_processor($valid['processor'], $device, $oid, $index, $mib, $descr, "1", $usage, NULL, NULL);
  }
}

unset ($processors_array);

// EOF
