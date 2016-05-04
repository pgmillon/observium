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
  $serial = "";
} else {
  if (preg_match('/^Juniper Networks, Inc\. ([a-z]+ )?(?<hw>[\w-][^,]+) SW Version : \((?<version>.+?)\) Build/i', $poll_device['sysDescr'], $matches))
  {
    //Juniper Networks, Inc. E120 Edge Routing Switch SW Version : (9.3.1 patch-0.2 [BuildId 11005]) Build Date : May 29, 2009 03:10 Copyright (c) 1999-2009 Juniper Networks, Inc. All rights reserved.
    //Juniper Networks, Inc. E320 Edge Routing Switch SW Version : (10.0.3 patch-0.3 [BuildId 13115]) Build Date : May 18, 2011 14:17 Copyright (c) 1999-2011 Juniper Networks, Inc. All rights reserved.
    //Juniper Networks, Inc. ERX-700 Edge Routing Switch SW Version : (5.1.0 release-0.0 [BuildId 1425]) Build Date : Nov 5 2003, 15:22:42 Copyright (c) 1999, 2001 Juniper Networks, Inc.
    list($hardware, $features) = explode(' ', $matches['hw'], 2);
    list($version) = explode(' ', $matches['version'], 2);
  } else {
    $hardware = rewrite_junos_hardware($poll_device['sysObjectID']);
  }

  if (empty($version))
  {
    $junose_version   = snmp_get($device, "juniSystemSwVersion.0", "-Ovqs", "Juniper-System-MIB", mib_dirs("junose"));

    list($version) = explode(" ", $junose_version);
    list(,$version) =  explode("(", $version);
    //list($features) = explode("]", $junose_version);
    //list(,$features) =  explode("[", $features);
  }
}

// EOF
