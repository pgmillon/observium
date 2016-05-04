<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage poller
 * @copyright  (C) 2006-2013 Adam Armstrong, 2013-2016 Observium Limited
 *
 */

/*
COLUBRIS-SYSTEM-MIB::systemProductName.0 = STRING: MSM410
COLUBRIS-SYSTEM-MIB::systemFirmwareRevision.0 = STRING: 5.3.6.0-01-8252
COLUBRIS-SYSTEM-MIB::systemBootRevision.0 = STRING: Boot 11.28 (Dec 17 2009 - 18:58:53)
COLUBRIS-SYSTEM-MIB::systemHardwareRevision.0 = STRING: 50-00-1036-02.
COLUBRIS-SYSTEM-MIB::systemSerialNumber.0 = STRING: SG038xxxx
COLUBRIS-SYSTEM-MIB::systemConfigurationVersion.0 = STRING: not configured
COLUBRIS-SYSTEM-MIB::systemUpTime.0 = Counter32: 1448531 seconds
COLUBRIS-SYSTEM-MIB::systemProductFlavor.0 = STRING: DEFAULT
COLUBRIS-SYSTEM-MIB::systemDeviceIdentification.0 = STRING: 0:24:a8:xx:xx:xx
COLUBRIS-SYSTEM-MIB::systemFirmwareBuildDate.0 = STRING: "2010/06/23"
*/


$version  = trim(snmp_get($device, ".1.3.6.1.4.1.8744.5.6.1.1.2.0", "-Ovq"),'"');
$hardware = trim(snmp_get($device, ".1.3.6.1.4.1.8744.5.6.1.1.1.0", "-Ovq"),'"');
$serial   = trim(snmp_get($device, ".1.3.6.1.4.1.8744.5.6.1.1.5.0", "-Ovq"),'"');
$features = trim(snmp_get($device, ".1.3.6.1.4.1.8744.5.6.1.1.3.0", "-Ovq"),'"');

// EOF

