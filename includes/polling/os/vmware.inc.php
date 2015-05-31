<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage poller
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

/*
 * Fetch the VMware product version.
 *
 *  VMWARE-SYSTEM-MIB::vmwProdName.0 = STRING: VMware ESXi
 *  VMWARE-SYSTEM-MIB::vmwProdVersion.0 = STRING: 4.1.0
 *  VMWARE-SYSTEM-MIB::vmwProdBuild.0 = STRING: 348481
 *
 *  version:   ESXi 4.1.0
 *  features:  build-348481
 */

$data   = snmp_get_multi($device, "VMWARE-SYSTEM-MIB::vmwProdName.0 VMWARE-SYSTEM-MIB::vmwProdVersion.0 VMWARE-SYSTEM-MIB::vmwProdBuild.0", "-OQUs", "+VMWARE-ROOT-MIB:VMWARE-SYSTEM-MIB", mib_dirs("vmware"));
$version  = preg_replace("/^VMware /", "", $data[0]["vmwProdName"]) . " " . $data[0]["vmwProdVersion"];
$features = "build-" . $data[0]["vmwProdBuild"];

if (is_array($entPhysical))
{
  $hw = $entPhysical['entPhysicalDescr'];
  if (!empty($entPhysical['entPhysicalSerialNum']))
  {
    $serial = $entPhysical['entPhysicalSerialNum'];
  }
}

$hardware = rewrite_unix_hardware($poll_device['sysDescr'], $hw);

// FIXME Below doesn't seem to belong here, but in a vminfo vmware poller module ...

/*
 * CONSOLE: Start the VMware discovery process.
 */

echo("VMware VM: ");

/*
 * Get a list of all the known Virtual Machines for this host.
 */

$db_info_list = dbFetchRows("SELECT * FROM vminfo WHERE device_id = ?", array($device["device_id"]));

$vm_data = snmpwalk_cache_oid($device, "vmwVmTable", array(), "VMWARE-VMINFO-MIB", mib_dirs(array("vmware")));

foreach ($db_info_list as $db_info)
{
  /*
   * Fetch the Virtual Machine information.
   *
   *  VMWARE-VMINFO-MIB::vmwVmDisplayName.224 = STRING: My First VM
   *  VMWARE-VMINFO-MIB::vmwVmGuestOS.224 = STRING: windows7Server64Guest
   *  VMWARE-VMINFO-MIB::vmwVmMemSize.224 = INTEGER: 8192 megabytes
   *  VMWARE-VMINFO-MIB::vmwVmState.224 = STRING: poweredOn
   *  VMWARE-VMINFO-MIB::vmwVmVMID.224 = INTEGER: 224
   *  VMWARE-VMINFO-MIB::vmwVmCpus.224 = INTEGER: 2
   */

  $vm_info = $vm_data[$db_info["vmwVmVMID"]];

#  $vm_info["vmwVmDisplayName"] = snmp_get($device, "VMWARE-VMINFO-MIB::vmwVmDisplayName." . $db_info["vmwVmVMID"], "-Osqnv", "+VMWARE-ROOT-MIB:VMWARE-VMINFO-MIB","+" . $config['install_dir'] . "/mibs/vmware:" . $config['install_dir'] . "/mibs");
#  $vm_info["vmwVmGuestOS"] = snmp_get($device, "VMWARE-VMINFO-MIB::vmwVmGuestOS."   . $db_info["vmwVmVMID"], "-Osqnv", "+VMWARE-ROOT-MIB:VMWARE-VMINFO-MIB","+" . $config['install_dir'] . "/mibs/vmware:" . $config['install_dir'] . "/mibs");
#  $vm_info["vmwVmMemSize"] = snmp_get($device, "VMWARE-VMINFO-MIB::vmwVmMemSize."   . $db_info["vmwVmVMID"], "-Osqnv", "+VMWARE-ROOT-MIB:VMWARE-VMINFO-MIB","+" . $config['install_dir'] . "/mibs/vmware:" . $config['install_dir'] . "/mibs");
#  $vm_info["vmwVmState"] = snmp_get($device, "VMWARE-VMINFO-MIB::vmwVmState."     . $db_info["vmwVmVMID"], "-Osqnv", "+VMWARE-ROOT-MIB:VMWARE-VMINFO-MIB","+" . $config['install_dir'] . "/mibs/vmware:" . $config['install_dir'] . "/mibs");
#  $vm_info["vmwVmCpus"] = snmp_get($device, "VMWARE-VMINFO-MIB::vmwVmCpus."    . $db_info["vmwVmVMID"], "-Osqnv", "+VMWARE-ROOT-MIB:VMWARE-VMINFO-MIB","+" . $config['install_dir'] . "/mibs/vmware:" . $config['install_dir'] . "/mibs");

  /*
   * VMware does not return an INTEGER but a STRING of the vmwVmMemSize. This bug
   * might be resolved by VMware in the future making this code absolete.
   */

  if (preg_match("/^([0-9]+) .*$/", $vm_info["vmwVmMemSize"], $matches))
  {
    $vm_info["vmwVmMemSize"] = $matches[1];
  }

  /*
   * If VMware Tools is not running then don't overwrite the GuesOS with the error
   * message, but just leave it as it currently is.
   */
  if (stristr($vm_info["vmwVmGuestOS"], 'tools not running') !== FALSE)
  {
    $vm_info["vmwVmGuestOS"] = $db_info["vmwVmGuestOS"];
  }

  /*
   * Process all the VMware Virtual Machine properties.
   */

  foreach ($vm_info as $property => $value)
  {
    /*
     * Check the property for any modifications.
     */

    if ($vm_info[$property] != $db_info[$property])
    {

      echo($vm_info[$property] ."!=". $db_info[$property].PHP_EOL);

      // FIXME - this should loop building a query and then run the query after the loop (bad geert!)
      dbUpdate(array($property => $vm_info[$property]), 'vminfo', '`id` = ?', array($db_info["id"]));
      log_event($db_info["vmwVmDisplayName"] . " (" . preg_replace("/^vmwVm/", "", $property) . ") -> " . $vm_info[$property], $device, 'vm');
    }
  }
}

/*
 * Finished discovering VMware information.
 */

echo(PHP_EOL);

// EOF
