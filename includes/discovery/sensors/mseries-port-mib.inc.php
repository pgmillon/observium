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

/*
MSERIES-PORT-MIB::smartPortName.1 = STRING: Line Tx
MSERIES-PORT-MIB::smartPortName.2 = STRING: Line Rx
MSERIES-PORT-MIB::smartPortName.11 = STRING: 921 Tx
MSERIES-PORT-MIB::smartPortName.12 = STRING: 921 Rx

MSERIES-PORT-MIB::smartPortAlias.1 = STRING:
MSERIES-PORT-MIB::smartPortAlias.2 = STRING:
MSERIES-PORT-MIB::smartPortAlias.11 = STRING:
MSERIES-PORT-MIB::smartPortAlias.12 = STRING:

MSERIES-PORT-MIB::smartPortPower.1 = INTEGER: 121
MSERIES-PORT-MIB::smartPortPower.2 = INTEGER: -39
MSERIES-PORT-MIB::smartPortPower.11 = INTEGER: -400
MSERIES-PORT-MIB::smartPortPower.12 = INTEGER: -400

MSERIES-PORT-MIB::smartPortStatus.1 = INTEGER: up(3)
MSERIES-PORT-MIB::smartPortStatus.2 = INTEGER: up(3)
MSERIES-PORT-MIB::smartPortStatus.11 = INTEGER: idle(1)
MSERIES-PORT-MIB::smartPortStatus.12 = INTEGER: idle(1)

MSERIES-PORT-MIB::smartPortHighPowerAlarmThreshold.1 = INTEGER: 0
MSERIES-PORT-MIB::smartPortHighPowerAlarmThreshold.2 = INTEGER: 0
MSERIES-PORT-MIB::smartPortHighPowerAlarmThreshold.11 = INTEGER: -80
MSERIES-PORT-MIB::smartPortHighPowerAlarmThreshold.12 = INTEGER: 30

MSERIES-PORT-MIB::smartPortLowPowerAlarmThreshold.1 = INTEGER: 0
MSERIES-PORT-MIB::smartPortLowPowerAlarmThreshold.2 = INTEGER: 0
MSERIES-PORT-MIB::smartPortLowPowerAlarmThreshold.11 = INTEGER: -120
MSERIES-PORT-MIB::smartPortLowPowerAlarmThreshold.12 = INTEGER: -50

*/

echo(' MSERIES-PORT-MIB ');

$oids = array();
$todo = array ('smartPortName', 'smartPortPower', 'smartPortHighPowerAlarmThreshold', 'smartPortLowPowerAlarmThreshold', 'smartPortStatus');
foreach ($todo as $table)
{
  $oids = snmpwalk_cache_oid($device, $table, $oids, 'MSERIES-PORT-MIB', mib_dirs('smartoptics'));
}

$pwr_pfx    = ".1.3.6.1.4.1.30826.1.3.1.2.1.1.5";
$status_pfx = ".1.3.6.1.4.1.30826.1.3.1.2.1.1.6";
foreach ($oids as $index => $entry)
{
  $pwr_current = $entry['smartPortPower'];
  $options = array();
  $options['limit_high'] = $entry['smartPortHighPowerAlarmThreshold'] / 10;
  $options['limit_low'] = $entry['smartPortLowPowerAlarmThreshold'] / 10;
  # Idle ports indicate -40dBm signal power and always fail the threshold check
  # because the lowest threshold is -25dBm. To stop such ports making to the
  # list of failed health checks override the thresholds of an idle port to
  # a range that never fails.
  if ($entry['smartPortStatus'] == 'idle')
  {
    $options['limit_high'] = 50;
    $options['limit_low'] = -50;
  }

  $status_current = $entry['smartPortStatus'];

  discover_sensor($valid['sensor'], 'dbm',   $device, "$pwr_pfx.$index",    $index, 'mseries-port-power', $entry['smartPortName'], 0.1, $pwr_current, $options);
  discover_sensor($valid['sensor'], 'state', $device, "$status_pfx.$index", $index, 'mseries-port-status-state', $entry['smartPortName'], NULL, $status_current);
}

// EOF
