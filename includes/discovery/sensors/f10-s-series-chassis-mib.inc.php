<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage discovery
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

// Force10 S-Series

#F10-S-SERIES-CHASSIS-MIB::chStackUnitTemp.1 = Gauge32: 47
#F10-S-SERIES-CHASSIS-MIB::chStackUnitModelID.1 = STRING: S25-01-GE-24V

echo(" F10-S-SERIES-CHASSIS-MIB ");

$oids = snmpwalk_cache_oid($device, "chStackUnitTemp", array(), "F10-S-SERIES-CHASSIS-MIB", mib_dirs('force10'));
$oids = snmpwalk_cache_oid($device, "chStackUnitSysType", $oids, "F10-S-SERIES-CHASSIS-MIB", mib_dirs('force10'));

foreach ($oids as $index => $entry)
{
  $descr = "Unit " . strval($index - 1) . " " . $entry['chStackUnitSysType'];
  $oid   = ".1.3.6.1.4.1.6027.3.10.1.2.2.1.14.".$index;
  $value = $entry['chStackUnitTemp'];

  discover_sensor($valid['sensor'], 'temperature', $device, $oid, $index, 'ftos-sseries', $descr, 1, $value);
}

// EOF
