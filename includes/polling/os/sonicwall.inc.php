<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage poller
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

//SNWL-COMMON-MIB::snwlSysModel.0 = STRING: TZ 210
//SNWL-COMMON-MIB::snwlSysSerialNumber.0 = STRING: 0017C54525DC
//SNWL-COMMON-MIB::snwlSysFirmwareVersion.0 = STRING: SonicOS Enhanced 5.8.1.9-58o
//SNWL-COMMON-MIB::snwlSysROMVersion.0 = STRING: 5.0.2.11

$data = snmp_get_multi($device, 'snwlSysModel.0 snwlSysSerialNumber.0 snwlSysFirmwareVersion.0 snwlSysROMVersion.0', '-OQUs', 'SNWL-COMMON-MIB');
$hardware   = $data[0]['snwlSysModel'];
$serial     = $data[0]['snwlSysSerialNumber'];
$fwversion  = str_ireplace(array('SonicOS ', 'Enhanced '), '', $data[0]['snwlSysFirmwareVersion']);
$romversion = $data[0]['snwlSysROMVersion'];
$version = "$fwversion (ROM $romversion)";

unset($data, $fwversion, $romversion);

// EOF