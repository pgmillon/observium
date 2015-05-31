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

echo(" CISCO-ENTITY-SENSOR-MIB ");

$oids = array('entPhysicalDescr', 'entPhysicalName', 'entPhysicalClass', 'entSensorValueEntry');
$entity_array = array();
foreach ($oids as $oid)
{
  $entity_array = snmpwalk_cache_multi_oid($device, $oid, $entity_array, 'CISCO-ENTITY-SENSOR-MIB', mib_dirs('cisco'));
}

$t_oids = array('entSensorThresholdSeverity', 'entSensorThresholdRelation', 'entSensorThresholdValue');
$t_entity_array = array();
foreach ($t_oids as $oid)
{
  $t_entity_array = snmpwalk_cache_twopart_oid($device, $oid, $t_entity_array, 'CISCO-ENTITY-SENSOR-MIB', mib_dirs('cisco'));
}

// http://tools.cisco.com/Support/SNMP/do/BrowseOID.do?local=en&translate=Translate&typeName=SensorDataType
/* sensor measurement data types. valid values are:
    other(1): a measure other than those listed below
    unknown(2): unknown measurement, or arbitrary, relative numbers
    voltsAC(3): electric potential
    voltsDC(4): electric potential
    amperes(5): electric current
    watts(6): power
    hertz(7): frequency
    celsius(8): temperature
    percentRH(9): percent relative humidity
    rpm(10): shaft revolutions per minute
    cmm(11),: cubic meters per minute (airflow)
    truthvalue(12): value takes { true(1), false(2) }
    specialEnum(13): value takes user defined enumerated values
    dBm(14): dB relative to 1mW of power
*/

$c_entitysensor = array(
  'voltsAC'     => 'voltage',
  'voltsDC'     => 'voltage',
  'amperes'     => 'current',
  'watts'       => 'power',
  'hertz'       => 'frequency',
  'celsius'     => 'temperature',
  'percentRH'   => 'humidity',
  'rpm'         => 'fanspeed',
  'cmm'         => 'airflow',
  'truthvalue'  => 'state',
  //'specialEnum' => 'count', // Skip counter sensors
  'dBm'         => 'dbm'
);

foreach ($entity_array as $index => $entry)
{
  if (is_numeric($index) && isset($c_entitysensor[$entry['entSensorType']]) &&
      is_numeric($entry['entSensorValue']) && $entry['entSensorStatus'] == 'ok')
  {
    $ok = TRUE;
    $options = array('entPhysicalIndex' => $index);

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

    // Set description based on measured entity if it exists
    if (is_numeric($entry['entSensorMeasuredEntity']) && $entry['entSensorMeasuredEntity'])
    {
      $measured_descr = rewrite_entity_name($entity_array[$entry['entSensorMeasuredEntity']]['entPhysicalDescr']);
      if (!$measured_descr)
      {
        $measured_descr = rewrite_entity_name($entity_array[$entry['entSensorMeasuredEntity']]['entPhysicalName']);
      }
      if ($measured_descr) { $descr = $measured_descr . " - " . $descr; }
    }

    $oid = ".1.3.6.1.4.1.9.9.91.1.1.1.1.4.$index";
    $value = $entry['entSensorValue'];
    if ($c_entitysensor[$entry['entSensorType']] != 'state')
    {
      // Normal sensors
      $type = $c_entitysensor[$entry['entSensorType']];
      $sensor_type = 'cisco-entity-sensor';
    } else {
      // State sensors
      $type = 'state';
      // 1:other, 2:unknown, 3:chassis, 4:backplane, 5:container, 6:powerSupply,
      // 7:fan, 8:sensor, 9:module, 10:port, 11:stack, 12:cpu
      $options['entPhysicalClass'] = $entry['entPhysicalClass'];
      $sensor_type = 'cisco-entity-state';
    }

    // Returning blatantly broken value. IGNORE.
    if ($value == "-32768" || $value == "-127") { $ok = FALSE; }

    // Set thresholds to null
    $limits = array();
    $scale  = NULL;
    if ($c_entitysensor[$entry['entSensorType']] != 'state')
    {
      $scale = si_to_scale($entry['entSensorScale'], $entry['entSensorPrecision']);
      $value = $value * $scale;

      // Check thresholds for this entry
      foreach ($t_entity_array[$index] as $t_index => $t_entry)
      {
        if ($t_entry['entSensorThresholdValue'] == "-32768") { continue; }

        if ($t_entry['entSensorThresholdSeverity'] == "major" && $t_entry['entSensorThresholdRelation'] == "greaterOrEqual")
        {
          $limits['limit_high']      = $t_entry['entSensorThresholdValue'] * $scale;
        }
        if ($t_entry['entSensorThresholdSeverity'] == "major" && $t_entry['entSensorThresholdRelation'] == "lessOrEqual")
        {
          $limits['limit_low']       = $t_entry['entSensorThresholdValue'] * $scale;
        }
        if ($t_entry['entSensorThresholdSeverity'] == "minor" && $t_entry['entSensorThresholdRelation'] == "greaterOrEqual")
        {
          $limits['limit_high_warn'] = $t_entry['entSensorThresholdValue'] * $scale;
        }
        if ($t_entry['entSensorThresholdSeverity'] == "minor" && $t_entry['entSensorThresholdRelation'] == "lessOrEqual")
        {
          $limits['limit_low_warn']  = $t_entry['entSensorThresholdValue'] * $scale;
        }
      }
      if ((float_cmp($limits['limit_high'],      $limits['limit_low'])       === 0) &&
          (float_cmp($limits['limit_high_warn'], $limits['limit_low_warn'])  === 0) &&
          (float_cmp($limits['limit_high'],      $limits['limit_high_warn']) === 0))
      {
        // Some Cisco sensors have all limits as same value (f.u. cisco), than leave only one limit
        unset ($limits['limit_high_warn'], $limits['limit_low_warn'], $limits['limit_low']);
      }
      // End Threshold code
    }

    if ($descr == "") { $ok = FALSE; } // Invalid description. Lots of these on Nexus

    if ($ok)
    {
      $options = array_merge($limits, $options);
      discover_sensor($valid['sensor'], $type, $device, $oid, $index, $sensor_type, $descr, $scale, $value, $options);
    }
  }
}

unset($oids, $t_oids, $entity_array, $t_entity_array, $index, $scale, $type, $value, $descr, $ok);

// EOF
