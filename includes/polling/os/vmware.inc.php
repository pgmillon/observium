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

// EOF
