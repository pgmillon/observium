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

// Try to discover any Virtual Machines.

if (($device['os'] == "vmware") || ($device['os'] == "linux"))
{
  // Variable to hold the discovered Virtual Machines.
  $vmw_vmlist = array();

  echo("VMWARE-VMINFO-MIB ");

  /*
   * Fetch the Virtual Machine information.
   *
   *  VMWARE-VMINFO-MIB::vmwVmDisplayName.224 = STRING: My First VM
   *  VMWARE-VMINFO-MIB::vmwVmDisplayName.416 = STRING: My Second VM
   *  VMWARE-VMINFO-MIB::vmwVmGuestOS.224 = STRING: windows7Server64Guest
   *  VMWARE-VMINFO-MIB::vmwVmGuestOS.416 = STRING: winLonghornGuest
   *  VMWARE-VMINFO-MIB::vmwVmMemSize.224 = INTEGER: 8192 megabytes
   *  VMWARE-VMINFO-MIB::vmwVmMemSize.416 = INTEGER: 8192 megabytes
   *  VMWARE-VMINFO-MIB::vmwVmState.224 = STRING: poweredOn
   *  VMWARE-VMINFO-MIB::vmwVmState.416 = STRING: poweredOn
   *  VMWARE-VMINFO-MIB::vmwVmVMID.224 = INTEGER: 224
   *  VMWARE-VMINFO-MIB::vmwVmVMID.416 = INTEGER: 416
   *  VMWARE-VMINFO-MIB::vmwVmCpus.224 = INTEGER: 2
   *  VMWARE-VMINFO-MIB::vmwVmCpus.416 = INTEGER: 2
   */

  $oids = snmpwalk_cache_multi_oid($device, "vmwVmTable", array(), "VMWARE-VMINFO-MIB", mib_dirs('vmware'));

  foreach ($oids as $index => $entry)
  {
    // Call VM discovery
    discover_virtual_machine($valid, $device, array('id' => $entry['vmwVmUUID'], 'name' => $entry['vmwVmDisplayName'], 'cpucount' => $entry['vmwVmCpus'],
      'memory' => $entry['vmwVmMemSize'] * 1024 * 1024, 'status' => $entry['vmwVmState'], 'os' => $entry['vmwVmGuestOS'],'type' => 'vmware', 'source' => 'vmware-snmp'));
  }

  // Clean up removed VMs (our type - vmware-snmp - only, so we don't clean up other modules' VMs)
  check_valid_virtual_machines($device, $valid, 'vmware-snmp');
  echo(PHP_EOL);
}

// EOF
