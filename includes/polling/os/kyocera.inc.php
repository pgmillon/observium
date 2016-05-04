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

# Some useful OIDs at http://www.kyoceramita.be/en/index/kyoware_solutions/system_management/kyocount_3_01.-contextmargin-65897-files-62084-File.cpsdownload.tmp/Models.xml

# KYOCERA-Private-MIB::kcprtGeneralModelName.1 = STRING: "FS-1028MFP"
$hardware = trim(snmp_get($device, "kcprtGeneralModelName.1", "-OQv", "KYOCERA-Private-MIB", mib_dirs('kyocera')),'" ');

# SNMPv2-SMI::enterprises.1347.43.5.1.1.28.1 = STRING: "QUV9600664"
$serial = trim(snmp_get($device, "1.3.6.1.4.1.1347.43.5.1.1.28.1", "-OQv", "KYOCERA-Private-MIB", mib_dirs('kyocera')),'" ');

# KYOCERA-Private-MIB::kcprtFirmwareVersion.1.1 = STRING: "2H9_2F00.002.002"
$version = trim(snmp_get($device, "kcprtFirmwareVersion.1.1", "-OQv", "KYOCERA-Private-MIB", mib_dirs('kyocera')),'" ');

// EOF
