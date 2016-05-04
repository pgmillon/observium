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

//ADTRAN-AOSUNIT::adAOSDeviceProductName.0 = STRING: Total Access 908e (2nd Gen)
//ADTRAN-AOSUNIT::adAOSDeviceSerialNumber.0 = STRING: CFG034348
//ADTRAN-AOSUNIT::adAOSDeviceVersion.0 = STRING: A2.06.00.E

$version  = snmp_get($device, "adAOSDeviceVersion.0",      "-OQv", "ADTRAN-AOSUNIT");
$hardware = snmp_get($device, "adAOSDeviceProductName.0",  "-OQv", "ADTRAN-AOSUNIT");
$serial   = snmp_get($device, "adAOSDeviceSerialNumber.0", "-OQv", "ADTRAN-AOSUNIT");

// EOF
