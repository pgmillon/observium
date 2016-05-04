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

echo(" SMARTNODE-MIB ");

// temperature
$sensor_array = snmpwalk_cache_multi_oid($device, 'temperature', array(), 'SMARTNODE-MIB', mib_dirs('patton'));

foreach ($sensor_array as $index => $entry)
{
  if ($entry['tempProbeDescr'] != '' && is_numeric($entry['currentDegreesCelsius']))
  {
    $descr = rewrite_entity_name($entry['tempProbeDescr']);

    $oid     = ".1.3.6.1.4.1.1768.100.70.30.2.1.2.".$index;
    $type    = 'temperature';
    $value   = $entry['currentDegreesCelsius'];

    discover_sensor($valid['sensor'], $type, $device, $oid, $index, 'smartnode-temp', $descr, 1, $value);
  }
}

/// FIXME. Disabled, since this functionality should be implemented in graphs module.
//  http://jira.observium.org/browse/OBSERVIUM-1066
/**
// calls
$sensor_array = snmpwalk_cache_multi_oid($device, 'gateway', array(), 'SMARTNODE-MIB', mib_dirs('patton'));

foreach ($sensor_array as $index => $entry)
{
  if ($entry['gwDescr'] != '')
  {
    $descr = rewrite_entity_name($entry['gwDescr']);

    $oidOng     = ".1.3.6.1.4.1.1768.100.70.40.2.1.3.".$index;
    $oidCon     = ".1.3.6.1.4.1.1768.100.70.40.2.1.2.".$index;
    $type    = 'state';
    $valueOng= $entry['gwCurrentOngoingCalls'];
    $valueCon= $entry['gwCurrentConnectedCalls'];

    discover_sensor($valid['sensor'], $type, $device, $oidOng, $index, 'smartnode-gw-ongoing', $descr . " Ongoing Calls", 1, $valueOng);
    discover_sensor($valid['sensor'], $type, $device, $oidCon, $index, 'smartnode-gw-connected', $descr . " Connected Calls", 1, $valueCon);
  }
}
*/

unset($sensor_array, $index, $type, $value, $descr);

// EOF
