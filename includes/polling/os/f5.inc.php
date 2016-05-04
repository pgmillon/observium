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

#F5-BIGIP-SYSTEM-MIB::sysPlatformInfoMarketingName.0 = STRING: BIG-IP 4000
#F5-BIGIP-SYSTEM-MIB::sysProductName.0 = STRING: BIG-IP
#F5-BIGIP-SYSTEM-MIB::sysProductVersion.0 = STRING: 11.4.1
#F5-BIGIP-SYSTEM-MIB::sysProductBuild.0 = STRING: 637.0
#F5-BIGIP-SYSTEM-MIB::sysProductEdition.0 = STRING: Hotfix HF3
#F5-BIGIP-SYSTEM-MIB::sysGeneralChassisSerialNum.0 = STRING: f5-rpht-dohz
#F5-BIGIP-SYSTEM-MIB::sysModuleAllocationProvisionLevel."am" = INTEGER: none(1)
#F5-BIGIP-SYSTEM-MIB::sysModuleAllocationProvisionLevel."lc" = INTEGER: none(1)
#F5-BIGIP-SYSTEM-MIB::sysModuleAllocationProvisionLevel."afm" = INTEGER: none(1)
#F5-BIGIP-SYSTEM-MIB::sysModuleAllocationProvisionLevel."apm" = INTEGER: none(1)
#F5-BIGIP-SYSTEM-MIB::sysModuleAllocationProvisionLevel."asm" = INTEGER: none(1)
#F5-BIGIP-SYSTEM-MIB::sysModuleAllocationProvisionLevel."avr" = INTEGER: none(1)
#F5-BIGIP-SYSTEM-MIB::sysModuleAllocationProvisionLevel."gtm" = INTEGER: none(1)
#F5-BIGIP-SYSTEM-MIB::sysModuleAllocationProvisionLevel."ltm" = INTEGER: nominal(3)
#F5-BIGIP-SYSTEM-MIB::sysModuleAllocationProvisionLevel."psm" = INTEGER: none(1)

$hardware = snmp_get($device, "sysPlatformInfoMarketingName.0", "-OQv", 'F5-BIGIP-SYSTEM-MIB', mib_dirs('f5'));

$version = snmp_get($device, "sysProductVersion.0", "-OQv", 'F5-BIGIP-SYSTEM-MIB', mib_dirs('f5'));
$version .= " Build " . snmp_get($device, "sysProductBuild.0", "-OQv", 'F5-BIGIP-SYSTEM-MIB', mib_dirs('f5'));
$version .= " " . snmp_get($device, "sysProductEdition.0", "-OQv", 'F5-BIGIP-SYSTEM-MIB', mib_dirs('f5'));

$serial .= snmp_get($device, "sysGeneralChassisSerialNum.0", "-OQv", 'F5-BIGIP-SYSTEM-MIB', mib_dirs('f5'));

$data = snmpwalk_cache_oid($device, 'sysModuleAllocationProvisionLevel', array(), 'F5-BIGIP-SYSTEM-MIB', mib_dirs('f5'));
$all_features = array("am", "lc", "afm", "apm", "asm", "avr", "gtm", "ltm", "psm");
foreach ($all_features as $feature)
{
  if (isset($data[$feature]))
  {
    $enabled = $data[$feature]['sysModuleAllocationProvisionLevel'];
    if ($enabled != "" && $enabled != "none")
    {
      $features .= " " . $feature;
    }
  }
}
$features = trim($features);

// EOF
