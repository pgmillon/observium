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

echo("GEIST-MIB-V3 ");

$productTitle = snmp_get($device, 'productTitle.0', '-OQv', 'GEIST-MIB-V3', mib_dirs('geist'));

if ($productTitle)
{
  // Insert chassis as index 1, everything hangs off of this.
  $system_index = 1;
  $inventory[$system_index] = array(
    'entPhysicalDescr'        => $productTitle,
    'entPhysicalClass'        => 'chassis',
    'entPhysicalName'         => 'Chassis',
    'entPhysicalIsFRU'        => 'true',
    'entPhysicalContainedIn'  => 0,
    'entPhysicalParentRelPos' => -1,
    'entPhysicalMfgName'      => 'Geist',
  );

  discover_inventory($valid['inventory'], $device, $system_index, $inventory[$system_index], 'geist-mib-v3');

  $relPos = 1;

  // Note: sensors without example SNMP output have not been tested.
  $geist_sensors = array(
    // GEIST-MIB-V3::climateSerial.1 = STRING: 28F123456700000D
    // GEIST-MIB-V3::climateName.1 = STRING: RSMINI163
    // GEIST-MIB-V3::climateAvail.1 = Gauge32: 1
    array('descr' => 'Climate Monitor', 'prefix' => 'climate', 'oid' => 2, 'class' => 'sensor'),
    // GEIST-MIB-V3::powMonSerial.1 = STRING: 3B0000007BF12345
    // GEIST-MIB-V3::powMonName.1 = STRING: Outlet
    // GEIST-MIB-V3::powMonAvail.1 = Gauge32: 1
    array('descr' => 'Power Monitor', 'prefix' => 'powMon', 'oid' => 3, 'class' => 'powerSupply', 'avail' => 1),
    array('descr' => 'Temperature Sensor', 'prefix' => 'tempSensor', 'oid' => 4, 'class' => 'sensor'),
    // GEIST-MIB-V3::airFlowSensorSerial.1 = STRING: 2000000012345678
    // GEIST-MIB-V3::airFlowSensorName.1 = STRING: AF/HTD Sensor
    // GEIST-MIB-V3::airFlowSensorAvail.1 = Gauge32: 1
    array('descr' => 'AF/HTD Sensor', 'prefix' => 'airFlowSensor', 'oid' => 5, 'class' => 'airflowSensor'),
    array('descr' => 'DELTA 3 Channel Controller', 'prefix' => 'ctrl3ChDELTA', 'oid' => 6, 'class' => 'sensor'),
    // GEIST-MIB-V3::doorSensorSerial.1 = STRING: 0E00000123456789
    // GEIST-MIB-V3::doorSensorName.1 = STRING: Door Sensor
    // GEIST-MIB-V3::doorSensorAvail.1 = Gauge32: 1
    array('descr' => 'Door Sensor', 'prefix' => 'doorSensor', 'oid' => 7, 'class' => 'sensor'),
    array('descr' => 'Water Sensor', 'prefix' => 'waterSensor', 'oid' => 8, 'class' => 'sensor'),
    array('descr' => 'Current Sensor', 'prefix' => 'currentSensor', 'oid' => 9, 'class' => 'sensor'),
    array('descr' => 'Millivolt Sensor', 'prefix' => 'millivoltSensor', 'oid' => 10, 'class' => 'sensor'),
    array('descr' => '3 Channel Power Sensor', 'prefix' => 'power3ChSensor', 'oid' => 11, 'class' => 'sensor'),
    array('descr' => 'Outlet', 'prefix' => 'outlet', 'oid' => 12, 'class' => 'outlet'),
    array('descr' => 'Fan Controller Monitor', 'prefix' => 'vsfc', 'oid' => 13, 'class' => 'sensor'),
    array('descr' => '3 Channel Power Monitor', 'prefix' => 'ctrl3Ch', 'oid' => 14, 'class' => 'sensor'),
    array('descr' => 'Amperage Controller', 'prefix' => 'analogSensor', 'oid' => 15, 'class' => 'powerSupply'),
    // GEIST-MIB-V3::ctrlOutletName.1 = STRING: Outlet 1
    array('descr' => 'Controlled outlet', 'prefix' => 'ctrlOutlet', 'oid' => 16, 'class' => 'outlet', 'avail' => 1),
    array('descr' => 'Dew Point Sensor', 'prefix' => 'dewpointSensor', 'oid' => 17, 'class' => 'sensor'),
    // GEIST-MIB-V3::digitalSensorSerial.1 = STRING: 8C00000493782754
    // GEIST-MIB-V3::digitalSensorName.1 = STRING: CCAT
    // GEIST-MIB-V3::digitalSensorAvail.1 = Gauge32: 1
    array('descr' => 'Digital Sensor', 'prefix' => 'digitalSensor', 'oid' => 18, 'class' => 'sensor'),
    array('descr' => 'DSTS Controller Sensor', 'prefix' => 'dstsSensor', 'oid' => 19, 'class' => 'sensor'),
    array('descr' => 'City Power Sensor', 'prefix' => 'cpmSensor', 'oid' => 20, 'class' => 'sensor'),
    // GEIST-MIB-V3::smokeAlarmSerial.1 = STRING: D900000498765432
    // GEIST-MIB-V3::smokeAlarmName.1 = STRING: Smoke Alarm
    // GEIST-MIB-V3::smokeAlarmAvail.1 = Gauge32: 1
    array('descr' => 'Smoke Alarm Sensor', 'prefix' => 'smokeAlarm', 'oid' => 21, 'class' => 'sensor'),
    array('descr' => '-48VDC Sensor', 'prefix' => 'neg48VdcSensor', 'oid' => 22, 'class' => 'sensor'),
    array('descr' => '+30VDC Sensor', 'prefix' => 'pos30VdcSensor', 'oid' => 23, 'class' => 'sensor'),
    array('descr' => 'Analog Sensor', 'prefix' => 'analogSensor', 'oid' => 24, 'class' => 'sensor'),
    // GEIST-MIB-V3::ctrl3ChIECSerial.1 = STRING: 0000777654567777
    // GEIST-MIB-V3::ctrl3ChIECName.1 = STRING: my-geist-pdu0
    // GEIST-MIB-V3::ctrl3ChIECAvail.1 = Gauge32: 1
    array('descr' => '3 Channel IEC Power Monitor', 'prefix' => 'ctrl3ChIEC', 'oid' => 25, 'class' => 'powerSupply'),
    // GEIST-MIB-V3::climateRelaySerial.1 = STRING: 2878924802000000
    // GEIST-MIB-V3::climateRelayName.1 = STRING: GRSO
    // GEIST-MIB-V3::climateRelayAvail.1 = Gauge32: 1
    array('descr' => 'Climate Relay Monitor', 'prefix' => 'climateRelay', 'oid' => 26, 'class' => 'sensor'),
    // GEIST-MIB-V3::ctrlRelayName.1 = STRING: Relay-1
    array('descr' => 'Controlled Relay', 'prefix' => 'ctrlRelay', 'oid' => 27, 'class' => 'relay', 'avail' => 1),
    array('descr' => 'Airspeed Switch Sensor', 'prefix' => 'airSpeedSwitchSensor', 'oid' => 28, 'class' => 'sensor'),
    // GEIST-MIB-V3::powerDMSerial.1 = STRING: E200000076221234
    // GEIST-MIB-V3::powerDMName.1 = STRING: DM16 PDU
    // GEIST-MIB-V3::powerDMAvail.1 = Gauge32: 1
    array('descr' => 'DM16/48 Current Sensor', 'prefix' => 'powerDM', 'oid' => 29, 'class' => 'sensor'),
    array('descr' => 'I/O Expander', 'prefix' => 'ioExpander', 'oid' => 30, 'class' => 'sensor'),
    array('descr' => 'T3HD Sensor', 'prefix' => 't3hdSensor', 'oid' => 31, 'class' => 'sensor'),
    array('descr' => 'THD Sensor', 'prefix' => 'thdSensor', 'oid' => 32, 'class' => 'sensor'),
    array('descr' => '+60VDC Sensor', 'prefix' => 'pos60VdcSensor', 'oid' => 33, 'class' => 'sensor'),
    array('descr' => '3Phase Outlet Control', 'prefix' => 'ctrl2CirTot', 'oid' => 34, 'class' => 'outlet'),
    array('descr' => 'SC10 Sensor', 'prefix' => 'sc10Sensor', 'oid' => 35, 'class' => 'sensor'),
  );

  foreach ($geist_sensors as $sensor)
  {
    $cache['geist'][$sensor['prefix'].'Table'] = snmpwalk_cache_multi_oid($device, $sensor['prefix'].'Table', array(), "GEIST-MIB-V3", mib_dirs('geist'));

    foreach ($cache['geist'][$sensor['prefix'].'Table'] as $index => $entry)
    {
      // Index can only be int in the database, so we create our own from, this sensor is at 21239.2.$oid.
      $system_index = $sensor['oid'] * 256 + $index;

      if ($sensor['avail'] || $entry[$sensor['prefix'].'Avail'])
      {
        $inventory[$system_index] = array(
          'entPhysicalDescr'        => $sensor['descr'],
          'entPhysicalClass'        => $sensor['class'],
          'entPhysicalName'         => $entry[$sensor['prefix'].'Name'],
          'entPhysicalSerialNum'    => $entry[$sensor['prefix'].'Serial'],
          'entPhysicalIsFRU'        => 'true',
          'entPhysicalContainedIn'  => 1,
          'entPhysicalParentRelPos' => $relPos,
          'entPhysicalMfgName'      => 'Geist',
        );

        discover_inventory($valid['inventory'], $device, $system_index, $inventory[$system_index], 'geist-mib-v3');

        $relPos++;
      }
    }
  }
}

unset($geist_sensors);

// EOF
