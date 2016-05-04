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

echo(" ZYXEL-AS-MIB ");

$oids = snmpwalk_cache_multi_oid($device, "accessSwitchSysTempTable", array(), "ZYXEL-AS-MIB", mib_dirs('zyxel'));

foreach ($oids as $index => $entry)
{
  $descr  = trim($entry['accessSwitchSysTempDescr']);
  $oid    = ".1.3.6.1.4.1.890.1.5.1.1.6.1.2.$index";
  $value  = $entry['accessSwitchSysTempCurValue'];
  $limits = array('limit_high' => $entry['accessSwitchSysTempHighThresh']);
  discover_sensor($valid['sensor'], 'temperature', $device, $oid, $index, 'zyxel-ies', $descr, 1, $value, $limits);
}

$oids = snmpwalk_cache_multi_oid($device, "accessSwitchFanRpmTable", array(), "ZYXEL-AS-MIB", mib_dirs('zyxel'));

foreach ($oids as $index => $entry)
{
  $descr  = trim($entry['accessSwitchFanRpmDescr']);
  $oid    = ".1.3.6.1.4.1.890.1.5.1.1.6.1.4.$index";
  $value  = $entry['accessSwitchFanRpmCurValue'];
  $limits = array('limit_low' => $entry['accessSwitchFanRpmLowThresh']);
  discover_sensor($valid['sensor'], 'fanspeed', $device, $oid, $index, 'zyxel-ies', $descr, 1, $value, $limits);
}

$oids = snmpwalk_cache_multi_oid($device, "accessSwitchVoltageTable", array(), "ZYXEL-AS-MIB", mib_dirs('zyxel'));

foreach ($oids as $index => $entry)
{
  $descr  = trim($entry['accessSwitchVoltageDescr']);
  $oid    = ".1.3.6.1.4.1.890.1.5.1.1.6.1.5.$index";
  $value  = $entry['accessSwitchVoltageCurValue'];
  $limits = array('limit_low' => $entry['accessSwitchVoltageLowThresh']);
  discover_sensor($valid['sensor'], 'voltage', $device, $oid, $index, 'zyxel-ies', $descr, 1, $value, $limits);
}

// EOF
