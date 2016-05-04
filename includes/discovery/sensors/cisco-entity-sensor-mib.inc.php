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

echo(" CISCO-ENTITY-SENSOR-MIB ");

$entity_array = snmpwalk_cache_multi_oid($device, 'entSensorValueEntry', $entity_array, 'CISCO-ENTITY-SENSOR-MIB', mib_dirs('cisco'));
if ($GLOBALS['snmp_status'])
{
  if (is_array($GLOBALS['cache']['entity-mib']))
  {
    // If this already received in inventory module, skip walking
    foreach ($GLOBALS['cache']['entity-mib'] as $index => $entry)
    {
      if (isset($entity_array[$index]))
      {
        $entity_array[$index] = array_merge($entity_array[$index], $entry);
      } else {
        $entity_array[$index] = $entry;
      }
    }
    print_debug("ENTITY-MIB already cached");
  } else {
    $oids = array('entPhysicalDescr', 'entPhysicalName', 'entPhysicalClass', 'entPhysicalContainedIn', 'entPhysicalParentRelPos');
    foreach ($oids as $oid)
    {
      $entity_array = snmpwalk_cache_multi_oid($device, $oid, $entity_array, "ENTITY-MIB:CISCO-ENTITY-VENDORTYPE-OID-MIB", mib_dirs('cisco'));
      if (!$GLOBALS['snmp_status']) { break; }
    }
    $entity_array = snmpwalk_cache_twopart_oid($device, "entAliasMappingIdentifier", $entity_array, "ENTITY-MIB:IF-MIB", mib_dirs());
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
      // Invalid description. Lots of these on Nexus
      if ($descr == "") { $ok = FALSE; }

      // Now try to search port bounded with sensor by ENTITY-MIB
      if ($ok && in_array($type, array('temperature', 'voltage', 'current', 'dbm', 'power')))
      {
        $sensor_index = $index; // Initial ifIndex
        do
        {
          //print_debug("DEBUG");
          //print_vars($entity_array[$sensor_index]);
          $sensor_port = $entity_array[$sensor_index];
          if ($sensor_port['entPhysicalClass'] === 'port')
          {
            // Port found, get mapped ifIndex
            if (isset($sensor_port['0']['entAliasMappingIdentifier']) &&
                strpos($sensor_port['0']['entAliasMappingIdentifier'], "fIndex"))
            {
              list(, $ifIndex) = explode(".", $sensor_port['0']['entAliasMappingIdentifier']);

              $port = get_port_by_index_cache($device['device_id'], $ifIndex);
              if (is_array($port))
              {
                // Hola, port really found
                $options['entPhysicalIndex_measured'] = $ifIndex;
                $options['measured_class']  = 'port';
                $options['measured_entity'] = $port['port_id'];
                print_debug("Port is found: ifIndex = $ifIndex, port_id = " . $port['port_id']);
              }
            }

            break; // Exit while
          }
          else if ($sensor_index == $sensor_port['entPhysicalContainedIn'])
          {
            break; // Break if current index same as next to avoid loop
          } else {
            $sensor_index = $sensor_port['entPhysicalContainedIn']; // Next ifIndex
          }
        } while ($sensor_port['entPhysicalClass'] !== 'port' && $sensor_port['entPhysicalContainedIn'] > 0 && $sensor_port['entPhysicalParentRelPos'] >= 0);
      }

      // Set thresholds for numeric sensors
      $limits = array();
      $scale  = NULL;
      if ($c_entitysensor[$entry['entSensorType']] != 'state')
      {
        $scale = si_to_scale($entry['entSensorScale'], $entry['entSensorPrecision']);

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

      if ($ok)
      {
        $options = array_merge($limits, $options);
        discover_sensor($valid['sensor'], $type, $device, $oid, $index, $sensor_type, $descr, $scale, $value, $options);
      }
    }
  }
}

unset($oids, $t_oids, $entity_array, $t_entity_array, $index, $scale, $type, $value, $descr, $ok, $ifIndex, $sensor_port);

// EOF
