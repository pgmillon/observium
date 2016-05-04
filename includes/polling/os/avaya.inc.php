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

if (preg_match('/^(?<hw>[A-Z][\w\-+ ]+) +HW:\w+ +FW:[\w\.]+ +SW:v?(?<version>[\w\.]+)/', $poll_device['sysDescr'], $matches))
{
  //Ethernet Switch 325-24G HW:01 FW:3.6.0.1 SW:v3.6.1.04 BN:04 (c) Nortel Networks
  //Ethernet Routing Switch 2526T HW:03 FW:1.0.0.14 SW:v4.1.1.002 BN:02 (c) Nortel Networks
  //Ethernet Routing Switch 3524GT-PWR+ HW:01 FW:1.0.0.15 SW:v5.2.2.003 BN:03 (c) Avaya Networks
  //Ethernet Switch 3510-24T HW:33 FW:4.0.0.7 SW:v4.0.4.00
  //Business Policy Switch 2000 HW:03 FW:V0.36 SW:v1.0.0.81
  //Business Policy Switch 2000 HW:06 FW:3.0.0.5 SW:v3.0.6.08 ISVN:2
  //Wireless LAN Controller WC8180 HW:07 FW:1.0.2.1 SW:v2.1.1.029 BN:29 (c) Avaya
  //Business Secure Router - Ethernet - BSR222 HW:a7 FW:VBSR222_2.6.0.0.011 SW:VM1.09 BN:22-jan-2010 (c) Nortel Networks
  $hardware = str_replace('- Ethernet - ', '', $matches['hw']);
  $version  = $matches['version'];
}
else if (preg_match('/^(?<hw>[A-Z][\w\-+ ]+) \((?<version>[\w\.]+)\)/', $poll_device['sysDescr'], $matches))
{
  //ERS-8306 (4.2.0.1)
  //MERS-8610co (6.0.1.1OE)
  //Passport-1612G (1.2.2.0)
  $hardware = str_replace(array('MERS', 'ERS', '-'), array('Metro Ethernet Routing Switch', 'Ethernet Routing Switch', ' '), $matches['hw']);
  $version  = $matches['version'];
}

if (strstr($poll_device['sysObjectID'], ".1.3.6.1.4.1.2272."))
{
  if (!$version)
  {
    // Build 4.1.0.0 on Fri Jun 16 21:42:04 PDT 2006
    $version = snmp_get($device, "rcSysVersion.0", "-Oqvn", 'RAPID-CITY', mib_dirs('nortel'));
    list(,$version) = explode(" ", $version);
  }
  $serial  = snmp_get($device, "rcChasSerialNumber.0", "-Oqvn", 'RAPID-CITY', mib_dirs('nortel'));
} else {
  if (!$version)
  {
    $version = snmp_get($device, ".1.3.6.1.4.1.45.1.6.4.2.1.10.0", "-Oqvn");
    $version = str_replace('v', '', $version);
  }
  $serial  = snmp_get($device, ".1.3.6.1.2.1.47.1.1.1.1.11.1", "-Oqvn"); // entPhysicalSerialNum.1
  if ($serial == "")
  {
    $serial  = snmp_get($device, ".1.3.6.1.2.1.47.1.1.1.1.11.2", "-Oqvn"); // entPhysicalSerialNum.2
  }

  // FIXME, remove this retard code
  $stack = snmp_walk($device, "SNMPv2-SMI::enterprises.45.1.6.3.3.1.1.6.8", "-OsqnU");
  $stack_size = count(explode("\n", $stack));
  if ($stack_size > 1)
  {
    $features = "Stack of $stack_size units";
  }
  # Is this a 5500 series or 5600 series stack?
  $features = "";
}

// EOF
