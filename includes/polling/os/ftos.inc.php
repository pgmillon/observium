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

// Stats for S-Series

#F10-S-SERIES-CHASSIS-MIB::chStackUnitModelID.1 = STRING: S25-01-GE-24V
#F10-S-SERIES-CHASSIS-MIB::chStackUnitStatus.1 = INTEGER: ok(1)
#F10-S-SERIES-CHASSIS-MIB::chStackUnitDescription.1 = STRING: 24-port E/FE/GE with POE (SB)
#F10-S-SERIES-CHASSIS-MIB::chStackUnitCodeVersion.1 = STRING: 7.8.1.3
#F10-S-SERIES-CHASSIS-MIB::chStackUnitCodeVersionInFlash.1 = STRING:
#F10-S-SERIES-CHASSIS-MIB::chStackUnitSerialNumber.1 = STRING: DL2E9250002
#F10-S-SERIES-CHASSIS-MIB::chStackUnitUpTime.1 = Timeticks: (262804700) 30 days, 10:00:47.00

// Stats for C-Series

#F10-C-SERIES-CHASSIS-MIB::chType.0 = INTEGER: c300(7)
#F10-C-SERIES-CHASSIS-MIB::chChassisMode.0 = INTEGER: cseries1(4)
#F10-C-SERIES-CHASSIS-MIB::chSwVersion.0 = STRING: 8.2.1.2
#F10-C-SERIES-CHASSIS-MIB::chMacAddr.0 = STRING: 0:1:e8:3b:ea:b5
#F10-C-SERIES-CHASSIS-MIB::chSerialNumber.0 = STRING: TY000000491
#F10-C-SERIES-CHASSIS-MIB::chPartNum.0 = STRING: 7520029900
#F10-C-SERIES-CHASSIS-MIB::chProductRev.0 = STRING: 04
#F10-C-SERIES-CHASSIS-MIB::chVendorId.0 = STRING: 04
#F10-C-SERIES-CHASSIS-MIB::chDateCode.0 = STRING: "01182007"
#F10-C-SERIES-CHASSIS-MIB::chCountryCode.0 = STRING: "01"

// Stats for E-Series

#F10-CHASSIS-MIB::chSysSwRuntimeImgVersion.1.1 = STRING: 7.6.1.2
#F10-CHASSIS-MIB::chSysSwRuntimeImgVersion.8.1 = STRING: 7.6.1.2

$hardware = rewrite_ftos_hardware($poll_device['sysObjectID']);

if (strstr($poll_device['sysObjectID'], ".1.3.6.1.4.1.6027.1.3."))
{
  $version = snmp_get($device, "chStackUnitCodeVersion.1", "-Oqvn", "F10-S-SERIES-CHASSIS-MIB", mib_dirs('force10'));
}
elseif (strstr($poll_device['sysObjectID'], ".1.3.6.1.4.1.6027.1.2."))
{
  $version = snmp_get($device, "chSwVersion.0", "-Oqvn", "F10-C-SERIES-CHASSIS-MIB", mib_dirs('force10'));
}
elseif (strstr($poll_device['sysObjectID'], ".1.3.6.1.4.1.6027.1.4."))
{
  $version = snmp_get($device, "chStackUnitCodeVersion.1", "-Oqvn", "F10-M-SERIES-CHASSIS-MIB", mib_dirs('force10'));
  $hardware = snmp_get($device, "chStackUnitModelID.1", "-Oqvn", "F10-M-SERIES-CHASSIS-MIB", mib_dirs('force10'));
}
else
{
  $version = snmp_get($device, "chSysSwRuntimeImgVersion.1.1", "-Oqvn", "F10-CHASSIS-MIB", mib_dirs('force10'));
}

$version = str_replace("\"","", $version);
$features = str_replace("\"","", $features);
$hardware = str_replace("\"","", $hardware);

// EOF
