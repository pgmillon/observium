<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage discovery
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

// FIXME should do the deletion etc in a common file perhaps? like for the sensors

/*
 * Try to discover any Virtual Machines.
 */

if (($device['os'] == "vmware") || ($device['os'] == "linux"))
{
  /*
   * Variable to hold the discovered Virtual Machines.
   */

  $vmw_vmlist = array();

  /*
   * CONSOLE: Start the VMware discovery process.
   */

  echo("VMware VM: ");

  /*
   * Fetch the list is Virtual Machines.
   *
   *  VMWARE-VMINFO-MIB::vmwVmVMID.224 = INTEGER: 224
   *  VMWARE-VMINFO-MIB::vmwVmVMID.416 = INTEGER: 416
   *  ...
   */

  $oids = snmp_walk($device, "VMWARE-VMINFO-MIB::vmwVmVMID", "-Osqnv", "+VMWARE-ROOT-MIB:VMWARE-VMINFO-MIB", mib_dirs('vmware'));

  /*
   * Newer VMware versions don't populate VMWARE-VMINFO-MIB::vmwVmVMID
   * anymore.  It has been deprecated and officially
   * VMWARE-VMINFO-MIB::vmwVmUUID should be used as the identifier.  Lets
   * use that to get the old indexes back.
   */
  if ($oids == "")
  {
    $indexes = array();

    $uuids = snmp_walk($device, "VMWARE-VMINFO-MIB::vmwVmUUID", "-Osqn", "+VMWARE-ROOT-MIB:VMWARE-VMINFO-MIB", mib_dirs('vmware'));

    if ($uuids != "")
    {
      $uuids = explode("\n", $uuids);

      foreach ($uuids as $uuid)
      {
        list($oid,) = explode(" ", $uuid, 2);
        $index = array_pop(explode(".", $oid));
        $indexes[] = $index;
      }

      $oids = implode("\n", $indexes);
    }
  }

  if ($oids != "")
  {
    $oids = explode("\n", $oids);

    foreach ($oids as $oid)
    {
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

      $vmwVmDisplayName = snmp_get($device, "VMWARE-VMINFO-MIB::vmwVmDisplayName.". $oid, "-Osqnv", "+VMWARE-ROOT-MIB:VMWARE-VMINFO-MIB", mib_dirs('vmware'));
      $vmwVmGuestOS     = snmp_get($device, "VMWARE-VMINFO-MIB::vmwVmGuestOS."    . $oid, "-Osqnv", "+VMWARE-ROOT-MIB:VMWARE-VMINFO-MIB", mib_dirs('vmware'));
      $vmwVmMemSize     = snmp_get($device, "VMWARE-VMINFO-MIB::vmwVmMemSize."    . $oid, "-Osqnv", "+VMWARE-ROOT-MIB:VMWARE-VMINFO-MIB", mib_dirs('vmware'));
      $vmwVmState       = snmp_get($device, "VMWARE-VMINFO-MIB::vmwVmState."      . $oid, "-Osqnv", "+VMWARE-ROOT-MIB:VMWARE-VMINFO-MIB", mib_dirs('vmware'));
      $vmwVmCpus        = snmp_get($device, "VMWARE-VMINFO-MIB::vmwVmCpus."       . $oid, "-Osqnv", "+VMWARE-ROOT-MIB:VMWARE-VMINFO-MIB", mib_dirs('vmware'));

      /*
       * VMware does not return an INTEGER but a STRING of the vmwVmMemSize. This bug
       * might be resolved by VMware in the future making this code obsolete.
       */

      if (preg_match("/^([0-9]+) .*$/", $vmwVmMemSize, $matches))
      {
        $vmwVmMemSize = $matches[1];
      }

      /*
       * Check whether the Virtual Machine is already known for this host.
       */

      if (dbFetchCell("SELECT COUNT(`id`) FROM `vminfo` WHERE `device_id` = ? AND `vmwVmVMID` = ? AND `vm_type` = 'vmware'", array($device['device_id'], $oid)) == '0')
      {
        $vmware_insert = array('device_id' => $device['device_id'], 'vm_type' => 'vmware', 'vmwVmVMID' => $oid, 'vmwVmDisplayName' => $vmwVmDisplayName, 'vmwVmGuestOS' => $vmwVmGuestOS, 'vmwVmMemSize' => $vmwVmMemSize, 'vmwVmCpus' => $vmwVmCpus, 'vmwVmState' => $vmwVmState);
        $inserted = dbInsert($vmware_insert, 'vminfo');
        echo('+');
        log_event("Virtual Machine added: $vmwVmDisplayName ($vmwVmMemSize MB)", $device, 'vm', $inserted);
      } else {
        echo('.');
      }
      // FIXME update code!

      /*
       * Save the discovered Virtual Machine.
       */

      $vmw_vmlist[] = $oid;
    }
  }

  /*
   * Get a list of all the known Virtual Machines for this host.
   */

  foreach (dbFetchRows("SELECT id, vmwVmVMID, vmwVmDisplayName FROM `vminfo` WHERE `device_id` = ? AND `vm_type` = 'vmware'", array($device['device_id'])) as $db_vm)
  {
    /*
     * Delete the Virtual Machines that are removed from the host.
     */

    if (!in_array($db_vm['vmwVmVMID'], $vmw_vmlist))
    {
      dbDelete('vminfo', '`id` = ?', array($db_vm['id']));
      echo('-');
      log_event("Virtual Machine removed: " . $db_vm['vmwVmDisplayName'], $device, 'vm', $db_vm['id']);
    }
  }

  /*
   * Finished discovering VMware information.
   */

  echo(PHP_EOL);
}

// EOF
