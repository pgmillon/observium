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

echo(" ZYXEL-AS-MIB ");

$oids = array();

$oids = snmpwalk_cache_multi_oid($device, "accessSwitchSysTempCurValue", $oids, "ZYXEL-AS-MIB", mib_dirs('zyxel'));
$oids = snmpwalk_cache_multi_oid($device, "accessSwitchSysTempHighThresh", $oids, "ZYXEL-AS-MIB", mib_dirs('zyxel'));

foreach ($oids as $index => $entry)
{
  $descr  = trim(snmp_get($device, "accessSwitchSysTempDescr.".$index, "-Oqv", "ZYXEL-AS-MIB", mib_dirs('zyxel')),'"');
  $oid    = ".1.3.6.1.4.1.890.1.5.1.1.6.1.2.$index";
  $value  = $entry['accessSwitchSysTempCurValue'];
  $limits = array('limit_high' => $entry['accessSwitchSysTempHighThresh']);
  discover_sensor($valid['sensor'], 'temperature', $device, $oid, $index, 'zyxel-ies', $descr, 1, $value, $limits);
}

// EOF
