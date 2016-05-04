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

$hardware = rewrite_definition_hardware($device, $poll_device['sysObjectID']);
if (strstr($hardware, 'E7'))
{
  /**
    E7-Calix-MIB::e7CardRowStatus.1.1 = INTEGER: active(1)
    E7-Calix-MIB::e7CardAdminStatus.1.1 = INTEGER: enabled(1)
    E7-Calix-MIB::e7CardProvType.1.1 = INTEGER: gpon4(1017)
    E7-Calix-MIB::e7CardActualType.1.1 = INTEGER: gpon4(1017)
    E7-Calix-MIB::e7CardSoftwareVersion.1.1 = STRING: "2.2.80.2"
    E7-Calix-MIB::e7CardSerialNumber.1.1 = STRING: 211306600765
    E7-Calix-MIB::e7CardCurrentPowerLevel.1.1 = INTEGER: notSet(0)
    E7-Calix-MIB::e7CardCleiCode.1.1 = STRING: "BVL3AW8FTA"
    E7-Calix-MIB::e7CardPartNumber.1.1 = STRING: "100-01773"
    E7-Calix-MIB::e7CardStartMacRange.1.1 = STRING: "00:02:5d:c3:8b:8b"
    E7-Calix-MIB::e7CardEndMacRange.1.1 = STRING: "00:02:5d:c3:8b:9c"
    E7-Calix-MIB::e7CardHardwareRevision.1.1 = STRING: "notyet"
    E7-Calix-MIB::e7CardTableEnd.0 = INTEGER: 0
    E7-Calix-MIB::e7SystemId.0 = STRING: "PHIPAALLOXT#1265"
    E7-Calix-MIB::e7SystemLocation.0 = STRING: "Philadelphia, PA"
    E7-Calix-MIB::e7SystemAutoUpgrade.0 = INTEGER: yes(1)
    E7-Calix-MIB::e7SystemTelnetServer.0 = INTEGER: yes(1)
    E7-Calix-MIB::e7SystemUnsecuredWeb.0 = INTEGER: no(0)
    E7-Calix-MIB::e7SystemPasswordExpiry.0 = INTEGER: 30
    E7-Calix-MIB::e7SystemDnsPrimary.0 = IpAddress: 192.168.1.2
    E7-Calix-MIB::e7SystemDnsSecondary.0 = IpAddress: 192.168.2.2
    E7-Calix-MIB::e7SystemTimezone.0 = STRING: "US/Pacific"
    E7-Calix-MIB::e7SystemChassisSerialNumber.0 = Wrong Type (should be OCTET STRING): Counter64: 71308303059
    E7-Calix-MIB::e7SystemChassisMacAddress.0 = STRING: 0:2:35:9e:46:af
    E7-Calix-MIB::e7SystemTime.0 = STRING:  04:00:23
    E7-Calix-MIB::e7SystemDate.0 = STRING: 2013-12-07
   */
  $serial  = snmp_get($device, '.1.3.6.1.4.1.6321.1.2.2.2.1.7.10.0', '-Oqvn');     // e7SystemChassisSerialNumber.0
  $version = snmp_get($device, '.1.3.6.1.4.1.6321.1.2.2.2.1.6.1.1.7.1.1', '-Oqv'); // e7CardSoftwareVersion.1.1

  // Here definition override for ifDescr, because Calix switch ifDescr <> ifName since fw 2.2
  unset($config['os'][$device['os']]['ifname'], $version_parts);
  $version_parts = explode('.', $version);
  if ($version_parts[0] > 2 || ($version_parts[0] == 2 && $version_parts[1] > 1))
  {
    $config['os'][$device['os']]['ifname'] = 1;
  }
  ///FIXME: $features
}
else if (strstr($hardware, 'E5'))
{
  ///FIXME: $version, $features, $serial
}
else if (strstr($hardware, 'C7'))
{
  ///FIXME: $version, $features, $serial
}

// EOF
