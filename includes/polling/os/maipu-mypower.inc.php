<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage poller
 * @author     Adam Armstrong <adama@observium.org>
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

// sysDescr.0 = STRING: "MyPower S3100-9TP"
// sysDescr.0 = STRING: "MyPower S3100-20TP"
// sysDescr.0 = STRING: "MyPower S3100-28TP"
// sysDescr.0 = STRING: "MyPower S3100-9TC"
// sysDescr.0 = STRING: "Switch"
//if (preg_match('/^(MyPower .*)/i', $poll_device['sysDescr'], $matches))
//{
//  $hardware = $matches[1];
//}

// .1.3.6.1.4.1.5651.1.2.1.1.2.2.0 = STRING: "MyPower S3200-10TP V6.2.3.10"
$somemaipustr = snmp_get($device, ".1.3.6.1.4.1.5651.1.2.1.1.2.2.0", "-OQv", "");
if (preg_match('/^(MyPower [A-Z0-9-]*) (V[0-9\.]*)/i', $somemaipustr, $matches))
{
  $hardware = $matches[1];
  $version = $matches[2];
}

$serial   = snmp_get($device, ".1.3.6.1.4.1.5651.1.2.1.1.2.19.0", "-OQv", "");
//if (preg_match('/^([0-9]*)/i', $poll_device['.1.3.6.1.4.1.5651.1.2.1.1.2.19.0'], $smatches));
//{
//  $serial = trim($smatches[1]);
//}

// EOF
