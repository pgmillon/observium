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

// nbsCmmcPortName.1.2.3 = XFP at 1.2.3

// nbsCmmcPortTemperature.1.2.3 = 56
// nbsCmmcPortTxPower.1.2.3 = -2569
// nbsCmmcPortRxPower.1.2.3 = -3045
// nbsCmmcPortBiasAmps.1.2.3 = 46863
// nbsCmmcPortSupplyVolts.1.2.3 = 3329

  echo("NBS-CMMC-MIB");

  $oid_list = array('nbsCmmcPortName', 'nbsCmmcPortIfIndex', 'nbsCmmcPortTemperature', 'nbsCmmcPortTxPower', 'nbsCmmcPortRxPower', 'nbsCmmcPortBiasAmps', 'nbsCmmcPortSupplyVolts');
  $oids     = array();

  foreach ($oid_list as $oid)
  {
    $oids = snmpwalk_cache_oid($device, $oid, $oids, "NBS-CMMC-MIB", mib_dirs(array('mrv')));
  }

  foreach ($oids as $index => $port) {

    $name = $port['nbsCmmcPortName'];

    $options = array();

    if (is_numeric($port['nbsCmmcPortIfIndex']))
    {
      $db_port    = get_port_by_index_cache($device['device_id'], $port['nbsCmmcPortIfIndex']);

      if (is_array($db_port))
      {
        $options['measured_class']  = 'port';
        $options['measured_entity'] = $db_port['port_id'];
      }
    }

    if ($port['nbsCmmcPortTemperature'] != -2147483648)
    {
      $sensor_oid = '.1.3.6.1.4.1.629.200.8.1.1.30.' . $index;
      discover_sensor($valid['sensor'], "temperature", $device, $sensor_oid, $index, 'nbsCmmcPortTemperature', $port['nbsCmmcPortName'], 1, $port['nbsCmmcPortTemperature'], $options);
    }

    if ($port['nbsCmmcPortTxPower'] != -2147483648)
    {
      $sensor_oid = '.1.3.6.1.4.1.629.200.8.1.1.31.' . $index;
      discover_sensor($valid['sensor'], "dbm", $device, $sensor_oid, $index, 'nbsCmmcPortTxPower', $port['nbsCmmcPortName'] . ' TX Power', 0.001, $port['nbsCmmcPortTxPower'], $options);
    }

    if ($port['nbsCmmcPortRxPower'] != -2147483648)
    {
      $sensor_oid = '.1.3.6.1.4.1.629.200.8.1.1.32.' . $index;
      discover_sensor($valid['sensor'], "dbm", $device, $sensor_oid, $index, 'nbsCmmcPortRxPower', $port['nbsCmmcPortName'] . ' RX Power', 0.001, $port['nbsCmmcPortRxPower'], $options);
    }

    if ($port['nbsCmmcPortBiasAmps'] != -1)
    {
      $sensor_oid = '.1.3.6.1.4.1.629.200.8.1.1.33.' . $index;
      discover_sensor($valid['sensor'], "current", $device, $sensor_oid, $index, 'nbsCmmcPortBiasAmps', $port['nbsCmmcPortName'] . ' Bias', 0.000001, $port['nbsCmmcPortBiasAmps'], $options);
    }

    if ($port['nbsCmmcPortSupplyVolts'] != -1)
    {
      $sensor_oid = '.1.3.6.1.4.1.629.200.8.1.1.34.' . $index;
      discover_sensor($valid['sensor'], "voltage", $device, $sensor_oid, $index, 'nbsCmmcPortSupplyVolts', $port['nbsCmmcPortName'] . ' Supply', 0.001, $port['nbsCmmcPortSupplyVolts'], $options);
    }

  }

// EOF
