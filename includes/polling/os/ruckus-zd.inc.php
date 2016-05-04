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

/**

RUCKUS-ZD-SYSTEM-MIB::ruckusZDSystemName.0 = <removed>
RUCKUS-ZD-SYSTEM-MIB::ruckusZDSystemIPAddr.0 = 192.168.x.x
RUCKUS-ZD-SYSTEM-MIB::ruckusZDSystemMacAddr.0 = 8c:c:90:xx:xx:xx
RUCKUS-ZD-SYSTEM-MIB::ruckusZDSystemUptime.0 = 46:2:37:16.77
RUCKUS-ZD-SYSTEM-MIB::ruckusZDSystemModel.0 = zd1112
RUCKUS-ZD-SYSTEM-MIB::ruckusZDSystemLicensedAPs.0 = 12
RUCKUS-ZD-SYSTEM-MIB::ruckusZDSystemMaxSta.0 = 1250
RUCKUS-ZD-SYSTEM-MIB::ruckusZDSystemSerialNumber.0 = <removed>
RUCKUS-ZD-SYSTEM-MIB::ruckusZDSystemVersion.0 = 9.8.1.0 build 101
RUCKUS-ZD-SYSTEM-MIB::ruckusZDSystemCountryCode.0 = "US"
RUCKUS-ZD-SYSTEM-MIB::ruckusZDSystemAdminName.0 = ********
RUCKUS-ZD-SYSTEM-MIB::ruckusZDSystemAdminPassword.0 = ********
RUCKUS-ZD-SYSTEM-MIB::ruckusZDSystemStatus.0 = noredundancy
RUCKUS-ZD-SYSTEM-MIB::ruckusZDSystemPeerConnectedStatus.0 = disconnected
RUCKUS-ZD-SYSTEM-MIB::ruckusZDSystemNEId.0 =
RUCKUS-ZD-SYSTEM-MIB::ruckusZDSystemManufacturer.0 = Ruckus Wireless
RUCKUS-ZD-SYSTEM-MIB::ruckusZDSystemSoftwareName.0 = zd3k_9.8.1.0 build 101.img
RUCKUS-ZD-SYSTEM-MIB::ruckusZDSystemMgmtVlanID.0 = 1

 */

$data = snmp_get_multi($device, "ruckusZDSystemModel.0 ruckusZDSystemSerialNumber.0 ruckusZDSystemVersion.0 ", "-OQUs", "RUCKUS-ZD-SYSTEM-MIB", mib_dirs("ruckus"));
$data = $data[0];

$serial       = $data['ruckusZDSystemSerialNumber'];
$hardware     = $data['ruckusZDSystemModel'];
$version      = $data['ruckusZDSystemVersion'];

unset ($data);

// EOF
