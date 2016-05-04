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

$mib = 'HUAWEI-ENERGYMNGT-MIB';
echo(" $mib ");

//HUAWEI-ENERGYMNGT-MIB::hwBoardIndex.4294902016 = -65280
//HUAWEI-ENERGYMNGT-MIB::hwBoardType.4294902016 = MPU
//HUAWEI-ENERGYMNGT-MIB::hwBoardName.4294902016 = LS61S24N
//HUAWEI-ENERGYMNGT-MIB::hwBoardCurrentPower.4294902016 = 103812
//HUAWEI-ENERGYMNGT-MIB::hwBoardRatedPower.4294902016 = 165000
//HUAWEI-ENERGYMNGT-MIB::hwBoardThresholdOfPower.4294902016 = 500000

$huawei['power']  = snmpwalk_cache_oid($device, 'HwBoardPowerMngtEntry',  array(), $mib, mib_dirs('huawei'));

$scale = 0.001;
foreach ($huawei['power'] as $index => $entry)
{
  $oid   = '.1.3.6.1.4.1.2011.6.157.2.1.1.4.'.$index;
  $value = $entry['hwBoardCurrentPower'];
  //$descr = $entry['hwBoardName'];
  if ($entry['hwBoardCurrentPower'] > 0)
  {
    $options = array('limit_high' => $entry['hwBoardThresholdOfPower'] * $scale);
    discover_sensor($valid['sensor'], 'power', $device, $oid, $index, 'huawei', 'Device Power Consumption', $scale, $value, $options);
  }
}

// EOF
