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

if (strpos($poll_device['sysDescr'], "olive"))
{
  $hardware = "Olive";
}
else if (preg_match('/^Juniper Networks, Inc\. ([a-z]+ )?(?<hw>[\w-][^,]+), kernel JUNOS (?<version>[^, ]+)/i', $poll_device['sysDescr'], $matches))
{
  //Juniper Networks, Inc. qfx5100-48s-6q Ethernet Switch, kernel JUNOS 13.2X51-D38, Build date: 2015-06-12 02:19:10 UTC Copyright (c) 1996-2015 Juniper Networks, Inc.
  //Juniper Networks, Inc. ex2200-48t-4g Ethernet Switch, kernel JUNOS 12.3R3.4, Build date: 2013-06-14 02:21:01 UTC Copyright (c) 1996-2013 Juniper Networks, Inc.
  //Juniper Networks, Inc. acx4000 internet router, kernel JUNOS 12.3X52-D10.4, Build date: 2013-08-19 23:31:40 UTC Copyright (c) 1996-2013 Juniper Networks, Inc.
  //Juniper Networks, Inc. ex4200-48t internet router, kernel JUNOS 11.3R2.4 #0: 2011-09-29 07:21:04 UTC builder@dagmath.juniper.net:/volume/build/junos/11.3/release/11.3R2.4/obj-powerpc/bsd/kernels/JUNIPER-EX/kernel Build date: 2011-09-29 06:44:19 UTC C
  //Juniper Networks, Inc. DELL J-EX4200-24T internet router, kernel JUNOS 12.1R2.9 #0: 2012-05-31 09:24:31 UTC builder@greteth:/volume/build/junos/12.1/release/12.1R2.9/obj-powerpc/junos/bsd/kernels/JUNIPER-EX/kernel Build date: 2012-05-31 11:29:38 UTC

  list($hardware, $features) = explode(' ', $matches['hw'], 2);
  $hardware = strtoupper($hardware);
  $features = ucwords($features);
  $version = $matches['version'];
}

if (empty($hardware))
{
  $hw = snmp_get($device, "JUNIPER-MIB::jnxBoxDescr.0", "-Ovqsn", "JUNIPER-MIB");
  if (preg_match('/^([a-z]+ )?(?<hw>[\w\ -]+)/i', $hw, $matches))
  {
    //Juniper SRX100H2 Internet Router
    list($hardware, $features) = explode(' ', $matches['hw'], 2);
    //$hardware = strtoupper($hardware);
    $features = ucwords($features);
  } else {
    $hardware = $hw;
  }
#  $hardware = rewrite_junos_hardware($hardware);
}

if (empty($version))
{
  $jun_ver = snmp_get($device, "hrSWInstalledName.2", "-Oqv", "HOST-RESOURCES-MIB");
  if (preg_match('/^[^\[]+\[(?<version>[^]]+)\]/', $jun_ver, $matches))
  {
    //JUNOS Software Release [12.1X46-D30.2]
    $version = $matches['version'];
  }
}

if ($hardware != 'Olive')
{
  $serial   = snmp_get($device, "JUNIPER-MIB::jnxBoxSerialNo.0", "-OQv", "JUNIPER-MIB");
}

// EOF
