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

echo(" ENTITY-SENSOR-MIB ");

$entity_array = snmpwalk_cache_multi_oid($device, 'entPhySensorValue', $entity_array, 'ENTITY-MIB:ENTITY-SENSOR-MIB', mib_dirs());
if ($GLOBALS['snmp_status'])
{
  $oids = array('entPhySensorType', 'entPhySensorScale', 'entPhySensorPrecision', 'entPhySensorOperStatus');
  foreach ($oids as $oid)
  {
    $entity_array = snmpwalk_cache_multi_oid($device, $oid, $entity_array, 'ENTITY-MIB:ENTITY-SENSOR-MIB', mib_dirs());
  }

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
    if (is_device_mib($device, 'ARISTA-ENTITY-SENSOR-MIB'))
    {
      $oids[] = 'entPhysicalAlias';
    }
    foreach ($oids as $oid)
    {
      $entity_array = snmpwalk_cache_multi_oid($device, $oid, $entity_array, "ENTITY-MIB:CISCO-ENTITY-VENDORTYPE-OID-MIB", mib_dirs('cisco'));
      if (!$GLOBALS['snmp_status']) { break; }
    }
    $entity_array = snmpwalk_cache_twopart_oid($device, "entAliasMappingIdentifier", $entity_array, "ENTITY-MIB:IF-MIB", mib_dirs());
  }

  if (is_device_mib($device, 'ARISTA-ENTITY-SENSOR-MIB'))
  {
    $oids_arista = array('aristaEntSensorThresholdLowWarning', 'aristaEntSensorThresholdLowCritical',
                         'aristaEntSensorThresholdHighWarning', 'aristaEntSensorThresholdHighCritical');
    foreach ($oids_arista as $oid)
    {
      $entity_array = snmpwalk_cache_multi_oid($device, $oid, $entity_array, "ARISTA-ENTITY-SENSOR-MIB", mib_dirs('arista'));
      if (!$GLOBALS['snmp_status']) { break; }
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
      $options = array('entPhysicalIndex' => $index);

      $oid   = ".1.3.6.1.2.1.99.1.1.1.4.$index";
      $type  = $entitysensor[$entry['entPhySensorType']];

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
      else if (!$entry['entPhysicalDescr'] && $entry['entPhysicalName'])
      {
        $descr = rewrite_entity_name($entry['entPhysicalName']);
      }
      else if (!$entry['entPhysicalDescr'] && !$entry['entPhysicalName'])
      {
        // This is also trick for some retard devices like NetMan Plus
        $descr = nicecase($type);
      }

      if ($device['os'] == 'asa' && $entry['entPhySensorScale'] == 'yocto' && $entry['entPhySensorPrecision'] == '0')
      {
        // Hardcoded fix for Cisco ASA 9.1.5 (can be other) bug when all scales equals yocto (OBSERVIUM-1110)
        $scale = 1;
      }
      else if (isset($entry['entPhySensorScale']))
      {
        $scale = si_to_scale($entry['entPhySensorScale'], $entry['entPhySensorPrecision']);
      } else {
        // Some devices not report scales, like NetMan Plus. But this is really HACK
        // Heh, I not know why only ups.. I'm not sure that this for all ups.. just I see this only on NetMan Plus.
        $scale = ($device['os_group'] == 'ups' && $type == 'temperature') ? 0.1 : 1;
      }
      $value = $entry['entPhySensorValue'];

      if ($type == "temperature")
      {
        if ($value * $scale > 200 || $value == 0) { $ok = FALSE; }
      }
      if ($value == "-127") { $ok = FALSE; }

      // Now try to search port bounded with sensor by ENTITY-MIB
      if ($ok && in_array($type, array('temperature', 'voltage', 'current', 'dbm', 'power')))
      {
        $sensor_index = $index; // Initial ifIndex
        do
        {
          $sensor_port = $entity_array[$sensor_index];
          //print_vars($sensor_port);
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
          else if ($device['os'] == 'arista_eos' && $sensor_port['entPhysicalClass'] == 'container' && strlen($sensor_port['entPhysicalAlias']))
          {
            // Arista not have entAliasMappingIdentifier, but used entPhysicalAlias as ifDescr
            $port_id = get_port_id_by_ifDescr($device['device_id'], $sensor_port['entPhysicalAlias']);
            if (is_numeric($port_id))
            {
              // Hola, port really found
              $port    = get_port_by_id($port_id);
              $ifIndex = $port['ifIndex'];
              $options['entPhysicalIndex_measured'] = $ifIndex;
              $options['measured_class']  = 'port';
              $options['measured_entity'] = $port_id;
              print_debug("Port is found: ifIndex = $ifIndex, port_id = " . $port_id);
              break; // Exit while
            }
            $sensor_index = $sensor_port['entPhysicalContainedIn']; // Next ifIndex
          }
          else if ($sensor_index == $sensor_port['entPhysicalContainedIn'])
          {
            break; // Break if current index same as next to avoid loop
          } else {
            $sensor_index = $sensor_port['entPhysicalContainedIn']; // Next ifIndex
          }
        } while ($sensor_port['entPhysicalClass'] !== 'port' && $sensor_port['entPhysicalContainedIn'] > 0 && ($sensor_port['entPhysicalParentRelPos'] > 0 || $device['os'] == 'arista_eos'));
      }

      // Set thresholds for numeric sensors
      $limits = array();
      if (isset($entry['aristaEntSensorThresholdHighCritical']))
      {
        foreach (array('limit_high' => 'aristaEntSensorThresholdHighCritical', 'limit_low' => 'aristaEntSensorThresholdLowCritical',
                       'limit_low_warn' => 'aristaEntSensorThresholdLowWarning', 'limit_high_warn' => 'aristaEntSensorThresholdHighWarning') as $limit => $limit_oid)
        {
          if (abs($entry[$limit_oid]) != 1000000000)
          {
            $limits[$limit] = $entry[$limit_oid] * $scale;
          } else {
            // The MIB can return -1000000000 or +1000000000, if there should be no threshold there.
            $limits['limit_auto'] = FALSE;
          }
        }
      }

      // Check to make sure we've not already seen this sensor via cisco's entity sensor mib
      if ($ok && !isset($valid['sensor'][$type]['cisco-entity-sensor'][$index]))
      {
        $options = array_merge($limits, $options);
        discover_sensor($valid['sensor'], $type, $device, $oid, $index, 'entity-sensor', $descr, $scale, $value, $options);
      }
    }
  }
}

unset($oids, $oids_arista, $entity_array, $index, $scale, $type, $value, $descr, $ok, $ifIndex, $sensor_port);

// EOF
