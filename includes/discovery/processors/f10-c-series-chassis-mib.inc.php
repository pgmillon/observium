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

// Force10 C-Series

#F10-C-SERIES-CHASSIS-MIB::chRpmCpuUtil5Min.1 = Gauge32: 47

echo("F10-C-SERIES-CHASSIS-MIB ");

$processors_array = snmpwalk_cache_oid($device, "chRpmCpuUtil5Min", array(), "F10-C-SERIES-CHASSIS-MIB", mib_dirs('force10'));
if (OBS_DEBUG > 1) { print_vars($processors_array); }

if (is_array($processors_array))
{
  foreach ($processors_array as $index => $entry)
  {
    $descr = ($index == 1) ? "CP" : "RP" . strval($index - 1);
    $oid = ".1.3.6.1.4.1.6027.3.8.1.3.7.1.5.".$index;
    $usage = $entry['chRpmCpuUtil5Min'];

    discover_processor($valid['processor'], $device, $oid, $index, "ftos-cseries", $descr, "1", $usage, NULL, NULL);
  }
}

unset ($processors_array);

// EOF
