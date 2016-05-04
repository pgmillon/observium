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

echo(" ".$mib." ");

$oids = snmpwalk_cache_oid($device, "chStackUnitTemp", array(), $mib, mib_dirs('force10'));
$oids = snmpwalk_cache_oid($device, "chStackUnitSysType", $oids, $mib, mib_dirs('force10'));

foreach ($oids as $index => $entry)
{
  $descr = "Unit " . strval($index - 1) . " " . $entry['chStackUnitSysType'];
  $oid   = ".1.3.6.1.4.1.6027.3.19.1.2.1.1.14.".$index;
  $value = $entry['chStackUnitTemp'];

  discover_sensor($valid['sensor'], 'temperature', $device, $oid, $index, $mib, $descr, 1, $value);
}

// EOF
