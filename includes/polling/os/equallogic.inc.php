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

// We are interrested in equallogic group members (devices), not in the group
// find group member id.

// eqlMemberName.1.443914937 = hostname-1
// eqlMemberName.1.1664046123 = hostname-2
$eqlgrpmembers = snmpwalk_cache_multi_oid($device, 'eqlMemberName', array(), 'EQLMEMBER-MIB', mib_dirs('equallogic'));

foreach ($eqlgrpmembers as $index => $entry)
{
  // Find member id and name in results
  if (!empty($entry['eqlMemberName']) && strtolower($entry['eqlMemberName']) == $poll_device['sysName'])
  {
    list(,$eqlgrpmemid) = explode('.', $index);
    break;
  }
}

if (!isset($eqlgrpmemid))
{
  // Fall-back to old method.
  $eqlgrpmemid = snmp_get($device, "eqliscsiLocalMemberId.0", "-OQv", "EQLVOLUME-MIB", mib_dirs("equallogic"));
}

if (is_numeric($eqlgrpmemid) && $eqlgrpmemid != $attribs['eqlgrpmemid'])
{
  // Store member id when detected
  set_dev_attrib($device, "eqlgrpmemid", $eqlgrpmemid);
  $attribs['eqlgrpmemid'] = $eqlgrpmemid;
  print_debug("\neqlgrpmemid: $eqlgrpmemid");
}

// EQLMEMBER-MIB::eqlMemberProductFamily.1.$eqlgrpmemid = STRING: PS6500
// EQLMEMBER-MIB::eqlMemberControllerMajorVersion.1.$eqlgrpmemid = Gauge32: 6
// EQLMEMBER-MIB::eqlMemberControllerMinorVersion.1.$eqlgrpmemid = Gauge32: 0
// EQLMEMBER-MIB::eqlMemberControllerMaintenanceVersion.1.$eqlgrpmemid = Gauge32: 2
// EQLMEMBER-MIB::eqlMemberSerialNumber.1.$eqlgrpmemid = STRING: XXXNNNNNNNXNNNN
// EQLMEMBER-MIB::eqlMemberServiceTag.1.$eqlgrpmemid = STRING: XXXXXXX

$hardware = "Dell EqualLogic ".trim(snmp_get($device, "eqlMemberProductFamily.1.".$eqlgrpmemid, "-OQv", "EQLMEMBER-MIB", mib_dirs("equallogic")),'" ');

$serial = trim(snmp_get($device, "eqlMemberSerialNumber.1.".$eqlgrpmemid, "-OQv", "EQLMEMBER-MIB", mib_dirs("equallogic")),'" ');
$serial .= ' ['.trim(snmp_get($device, "eqlMemberServiceTag.1.".$eqlgrpmemid, "-OQv", "EQLMEMBER-MIB", mib_dirs("equallogic")),'" ').']';

$eqlmajor = snmp_get($device, "eqlMemberControllerMajorVersion.1.".$eqlgrpmemid, "-OQv", "EQLMEMBER-MIB", mib_dirs("equallogic"));
$eqlminor = snmp_get($device, "eqlMemberControllerMinorVersion.1.".$eqlgrpmemid, "-OQv", "EQLMEMBER-MIB", mib_dirs("equallogic"));
$eqlmaint = snmp_get($device, "eqlMemberControllerMaintenanceVersion.1.".$eqlgrpmemid, "-OQv", "EQLMEMBER-MIB", mib_dirs("equallogic"));
$version = sprintf("V%d.%d.%d",$eqlmajor, $eqlminor, $eqlmaint);

unset($eqlgrpmemid, $eqlgrpmembers, $eqlgrpmem, $eqlmajor, $eqlminor, $eqlmaint, $index);

// EOF
