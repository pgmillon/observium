<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage discovery
 * @copyright  (C) 2006-2015 Adam Armstrong
 *
 */

echo(" F5-BIGIP-SYSTEM-MIB ");

/*
sysCpuNumber.0 = 0
sysChassisFanNumber.0 = 4
sysChassisFanIndex.1 = 1
sysChassisFanIndex.2 = 2
sysChassisFanIndex.3 = 3
sysChassisFanIndex.4 = 4
sysChassisFanStatus.1 = good
sysChassisFanStatus.2 = good
sysChassisFanStatus.3 = good
sysChassisFanStatus.4 = good
sysChassisFanSpeed.1 = 12000
sysChassisFanSpeed.2 = 12000
sysChassisFanSpeed.3 = 11619
sysChassisFanSpeed.4 = 12200
sysChassisPowerSupplyNumber.0 = 2
sysChassisPowerSupplyIndex.1 = 1
sysChassisPowerSupplyIndex.2 = 2
sysChassisPowerSupplyStatus.1 = good
sysChassisPowerSupplyStatus.2 = bad
sysChassisTempNumber.0 = 5
sysChassisTempIndex.1 = 1
sysChassisTempIndex.2 = 2
sysChassisTempIndex.3 = 3
sysChassisTempIndex.4 = 4
sysChassisTempIndex.5 = 5
sysChassisTempTemperature.1 = 29
sysChassisTempTemperature.2 = 27
sysChassisTempTemperature.3 = 29
sysChassisTempTemperature.4 = 18
sysChassisTempTemperature.5 = 28
sysBladeTempNumber.0 = 0
sysBladeVoltageNumber.0 = 0
sysGeneralHwName.0 = C113
sysGeneralHwNumber.0 = deprecated
sysGeneralChassisSerialNum.0 = f5-xwhu-ptwt
sysPlatformInfoName.0 = C113
sysPlatformInfoMarketingName.0 = BIG-IP 4200
sysCpuSensorNumber.0 = 1
sysCpuSensorIndex.0.1 = 1
sysCpuSensorTemperature.0.1 = 43
sysCpuSensorFanSpeed.0.1 = 12000
sysCpuSensorName.0.1 = cpu1
sysCpuSensorSlot.0.1 = 0
 */

if (strpos($device['hardware'], 'BIG-IP Virtual Edition') === FALSE) // FIXME. Not sure.. why?
{
  $oids = snmpwalk_cache_multi_oid($device, "sysPlatform", array(), "F5-BIGIP-SYSTEM-MIB", mib_dirs('f5'));
}

$sysPlatform_oid = ".1.3.6.1.4.1.3375.2.1.3";
foreach ($oids as $index => $entry)
{
  $scale = 1; // Default scale
  foreach ($entry as $oid_name => $value)
  {
    switch ($oid_name)
    {
      case 'sysChassisFanStatus':
        $physical = "fan";
        $class    = "state";
        $descr    = "Chassis Fan $index";
        $oid      = "$sysPlatform_oid.2.1.2.1.2.$index";
        break;
      case 'sysChassisFanSpeed':
        $class    = "fanspeed";
        $descr    = "Chassis Fan $index";
        $oid      = "$sysPlatform_oid.2.1.2.1.3.$index";
        break;
      case 'sysChassisPowerSupplyStatus':
        $physical = "power";
        $class    = "state";
        $descr    = "Chassis Power Supply $index";
        $oid      = "$sysPlatform_oid.2.2.2.1.2.$index";
        break;
      case 'sysChassisTempTemperature':
        $class    = "temperature";
        $descr    = "Chassis Temperature $index";
        $oid      = "$sysPlatform_oid.2.3.2.1.2.$index";
        break;
      case 'sysCpuSensorTemperature':
        $class    = "temperature";
        list($slot, $cpu) = explode('.', $index);
        $descr    = "Slot $slot CPU $cpu";
        $oid      = "$sysPlatform_oid.6.2.1.2.$index";
        break;
      case 'sysCpuSensorFanSpeed':
        $class    = "fanspeed";
        list($slot, $cpu) = explode('.', $index);
        $descr    = "Slot $slot CPU $cpu";
        $oid      = "$sysPlatform_oid.6.2.1.3.$index";
        break;
      default:
        continue 2; // Skip all other
    }
  
    if ($class == 'state')
    {
      discover_sensor($valid['sensor'], $class, $device, $oid, "$oid_name.$index", 'f5-bigip-state',  $descr, NULL, $value, array('entPhysicalClass' => $physical));
    }
    else if (is_numeric($value))
    {
      discover_sensor($valid['sensor'], $class, $device, $oid, "$oid_name.$index", 'f5-bigip-system', $descr, $scale, $value);
    }
  }
}

unset($oids, $oid_name, $entry, $oid, $index, $class, $sysPlatform_oid);

// EOF
