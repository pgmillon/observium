<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage discovery
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

// STEELHEAD-MIB::model.0 = STRING: "Virtual (V150M)"
// STEELHEAD-MIB::serialNumber.0 = STRING: "VC1RT000EC304"
// STEELHEAD-MIB::systemVersion.0 = STRING: "rbt_sh 8.6.1 #60 2014-08-20 00:41:02 x86_64 uuid:4d69c1d7-2709-4325-8328-00f4e67d6807"
// STEELHEAD-MIB::systemClock.0 = STRING: 2014-11-6,20:56:50.0,+0:0
// STEELHEAD-MIB::health.0 = STRING: "Critical"
// STEELHEAD-MIB::serviceStatus.0 = STRING: "stopped"

// STEELHEAD-MIB::model.0 = STRING: "1050 (1050L)"
// STEELHEAD-MIB::serialNumber.0 = STRING: "%{SERIAL}%"
// STEELHEAD-MIB::systemVersion.0 = STRING: "rbt_sh 6.5.4a #147_24 2012-03-05 16:48:17 x86_64 root@palermo0:svn://svn/mgmt/branches/canary_147_fix_branch"
// STEELHEAD-MIB::systemVersion.0 = STRING: "rbt_sh 7.0.5d #529_80 2013-03-28 17:31:15 x86_64 root@poznan:svn://svn/mgmt/branches/malta_529_fix_branch"
// STEELHEAD-MIB::systemVersion.0 = STRING: "rbt_sh 7.0.5d #529_80 2013-03-28 17:31:15 x86_64 root@poznan:svn://svn/mgmt/branches/malta_529_fix_branch"
// RBT-MIB::products.51.1.3.0 = STRING: "rbt_ex 2.0.0 #149_21 2012-11-12 16:57:32 x86_64 root@spade:svn://svn/build/branches/lanai-ex_149_fix_branch"
// STEELHEAD-MIB::crlFeatureName.1 = STRING: "SSL_CAs"
// STEELHEAD-MIB::crlFeatureName.2 = STRING: "SSL_Peering_CAs"

// RBT-MIB::products.51.1.1.0 = STRING: "EX560 (EX560H)"
// RBT-MIB::products.51.1.2.0 = STRING: "%{SERIAL}%"
// RBT-MIB::products.51.1.3.0 = STRING: "rbt_ex 2.0.0 #149_21 2012-11-12 16:57:32 x86_64 root@spade:svn://svn/build/branches/lanai-ex_149_fix_branch"
// RBT-MIB::products.51.2.11.1.1.2.1 = STRING: "SSL_CAs"
// RBT-MIB::products.51.2.11.1.1.2.2 = STRING: "SSL_Peering_CAs"

// CX models, most common
$serial = trim(snmp_get($device, ".1.3.6.1.4.1.17163.1.1.1.2.0", "-OQv", "STEELHEAD-MIB", mib_dirs('riverbed')),'" ');
$model_oid = '.1.3.6.1.4.1.17163.1.1.1.1.0';
$version_oid = '.1.3.6.1.4.1.17163.1.1.1.3.0';

if ($serial == '')
{
  $serial = trim(snmp_get($device, ".1.3.6.1.4.1.17163.1.51.1.2.0", "-OQv", "STEELHEAD-MIB", mib_dirs('riverbed')),'" ');
  $model_oid = '.1.3.6.1.4.1.17163.1.51.1.1.0';
  $version_oid = '.1.3.6.1.4.1.17163.1.51.1.3.0';
}

if ($serial != '')
{
  if (preg_match('/^(rbt[a-zA-Z_\-]+) ([a-zA-Z\.\-_0-9]+) (.*)/', trim(snmp_get($device, $version_oid, "-OQv", "STEELHEAD-MIB", mib_dirs('riverbed')),'" '), $regexp_result))
  {
    $version = $regexp_result[2];
  }

  $hw = trim(snmp_get($device, $model_oid, "-OQv", "STEELHEAD-MIB", mib_dirs('riverbed')),'" ');
  if (preg_match('/([a-zA-Z0-9\.\-_]+) \(([a-zA-Z0-9\.\-_]+)\)/', $hw, $regexp_result))
  {
    $hardware = $regexp_result[2];
  }
}

// TODO: $features = '';

unset($hw, $model_oid, $version_oid, $regexp_result);

// EOF
