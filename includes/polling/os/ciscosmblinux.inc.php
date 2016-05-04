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

if (preg_match('/Linux, Cisco (?:Small Business|Systems, Inc) (?<hardware>[\w\-\.]+)(?: \((?<features>[\w\-\.]+)\))?, Version (?<version>[\d\.\-]+)/', $poll_device['sysDescr'], $matches))
{
  //Linux, Cisco Small Business WAP4410N-A, Version 2.0.6.1
  //Linux, Cisco Small Business RV320, Version 1.1.1.06 Fri Dec 6 11:10:41 CST 2013
  //Linux, Cisco Small Business RV320, Version 1.0.2.03 Fri Mar 1 11:59:16 CST 2013
  //Linux, Cisco Systems, Inc WAP371 (WAP371-E-K9), Version 1.2.0.2
  $hardware = $matches['hardware'];
  //$features = $matches['features'];
  $version  = $matches['version'];
}
else if (preg_match('/Linux (?<linux>\d[\w\.\-]+), Cisco (?:Small Business|Systems, Inc) (?<hardware>[\w\-\.]+)(?: \((?<features>[\w\-\.]+)\))?, Version ([\w\-]+-)?(?<version>\d[\d\.\(\)]+) /', $poll_device['sysDescr'], $matches))
{
  // Linux 2.6.21.5, Cisco Small Business AP541N (AP541N-E-K9), Version AP541N-K9-2.0(4) #1 Sat Sep 8 15:30:35 EDT 2012
  // Linux 2.6.21.5-lvl7-dev, Cisco Small Business WAP321 (WAP321-E-K9), Version 1.0.5.3 Wed Sep 10 13:20:36 EDT 2014
  $hardware = $matches['hardware'];
  $version  = $matches['version'];
} else {
  $hardware = $entPhysical['entPhysicalModelName'];
  $version  = $entPhysical['entPhysicalSoftwareRev'];
}

$serial   = $entPhysical['entPhysicalSerialNum'];

// EOF
