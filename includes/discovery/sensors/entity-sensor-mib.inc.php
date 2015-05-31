<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage discovery
 * @author     Adam Armstrong <adama@memetic.org>
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

if (!is_device_mib($device, 'JUNIPER-DOM-MIB', FALSE))
{
  echo(" ENTITY-SENSOR-MIB ");

  $oids = array('entPhysicalDescr', 'entPhysicalName', 'entPhySensorType', 'entPhySensorScale', 'entPhySensorPrecision', 'entPhySensorOperStatus', 'entPhySensorValue');
  $entity_array = array();
  foreach ($oids as $oid)
  {
    $entity_array = snmpwalk_cache_multi_oid($device, $oid, $entity_array, 'ENTITY-MIB:ENTITY-SENSOR-MIB', mib_dirs());
  }

  if (is_device_mib($device, 'ARISTA-ENTITY-SENSOR-MIB'))
  {
    $oids_arista = array('aristaEntSensorThresholdLowWarning', 'aristaEntSensorThresholdLowCritical',
                         'aristaEntSensorThresholdHighWarning', 'aristaEntSensorThresholdHighCritical');
    foreach ($oids_arista as $oid)
    {
      $entity_array = snmpwalk_cache_multi_oid($device, $oid, $entity_array, "ARISTA-ENTITY-SENSOR-MIB", mib_dirs('arista'));
    }
  }

  $entitysensor = array(
    'voltsDC'   => 'voltage',
    'voltsAC'   => 'voltage',
    'amperes'   => 'current',
    'watts'     => 'power',
    'hertz'     => 'frequency',
    'percentRH' => 'humidity',
    'rpm'       => 'fanspeed',
    'celsius'   => 'temperature',
    'dBm'       => 'dbm'
  );

  foreach ($entity_array as $index => $entry)
  {
    if ($entitysensor[$entry['entPhySensorType']] && is_numeric($entry['entPhySensorValue']) && is_numeric($index) &&
        $entry['entPhySensorOperStatus'] != 'unavailable' && $entry['entPhySensorOperStatus'] != 'nonoperational')
    {
      $ok      = TRUE;

      $descr = rewrite_entity_name($entry['entPhysicalDescr']);
      if ($entry['entPhysicalDescr'] && $entry['entPhysicalName'])
      {
        // Check if entPhysicalDescr equals entPhysicalName,
        // Also compare like this: 'TenGigabitEthernet2/1 Bias Current' and 'Te2/1 Bias Current'
        if (strpos($entry['entPhysicalDescr'], substr($entry['entPhysicalName'], 2)) === FALSE)
        {
          $descr = rewrite_entity_name($entry['entPhysicalDescr']) . " - " . rewrite_entity_name($entry['entPhysicalName']);
        }
      }
      else if (!$entry['entPhysicalDescr'])
      {
        $descr = rewrite_entity_name($entry['entPhysicalName']);
      }

      $oid   = ".1.3.6.1.2.1.99.1.1.1.4.$index";
      $type  = $entitysensor[$entry['entPhySensorType']];
      $scale = si_to_scale($entry['entPhySensorScale'], $entry['entPhySensorPrecision']);
      $value = $entry['entPhySensorValue'] * $scale;

      if ($type == "temperature")
      {
        if ($value > 200) { $ok = FALSE; }
      }
      if ($value == "-127") { $ok = FALSE; }

      // Set thresholds to null

      $limits = array();
      if (isset($entry['aristaEntSensorThresholdLowCritical']))
      {
        $limits = array(
          'limit_high'      => $entry['aristaEntSensorThresholdHighCritical'] * $scale,
          'limit_low'       => $entry['aristaEntSensorThresholdLowCritical']  * $scale,
          'limit_low_warn'  => $entry['aristaEntSensorThresholdLowWarning']   * $scale,
          'limit_high_warn' => $entry['aristaEntSensorThresholdHighWarning']  * $scale
        );
        # FIXME: The MIB can return -1000000000 or +1000000000, if there
        # should be no threshold there.  We don't use NULL in that case
        # because observium then calculates its own threshold using
        # sensor_limit_high() or sensor_limit_low(), but instead it should
        # have a flag value for "the device has no limit for this sensor".
      }

      if ($ok && !isset($valid['sensor'][$type]['cisco-entity-sensor'][$index]))
      // Check to make sure we've not already seen this sensor via cisco's entity sensor mib
      {
        discover_sensor($valid['sensor'], $type, $device, $oid, $index, 'entity-sensor', $descr, $scale, $value, $limits);
      }
    }
  }
}

unset($oids, $oids_arista, $entity_array, $index, $scale, $type, $value, $descr, $ok);

// EOF
