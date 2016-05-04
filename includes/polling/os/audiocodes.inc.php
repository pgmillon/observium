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

if (strpos($poll_device['sysDescr'], 'Product:') !== FALSE)
{
  # sysDescr.0 = STRING: Product: GW 2 FXS;SW Version: 5.40A.032.004
  # sysDescr.0 = STRING: Product: MG 600;SW Version: 5.40A.022.003
  # sysDescr.0 = STRING: Product: MS 2K;SW Version: 5.60A.038.001
  # sysDescr.0 = STRING: Product: MP-114 FXS;SW Version: 5.00A.034.001
  # sysDescr.0 = STRING: Product: TrunkPack 260_UN;SW Version: 4.80.000
  # sysDescr.0 = STRING: Product: Mediant 1000;SW Version: 5.00A.046.004
  # sysDescr.0 = STRING: Product: MEDIANT8000 ; SW Version: 5.4.39

  list($hardware, $version) = explode(';', $poll_device['sysDescr']);
  list(,$hardware) = explode(':', $hardware, 2); $hardware = trim($hardware);
  list(,$version)  = explode(':', $version, 2);  $version  = trim($version);
} else {
  # AC-SYSTEM-MIB::acSysIdName.0 = STRING: MP-118 FXS
  # AC-SYSTEM-MIB::acSysVersionSoftware.0 = STRING: 4.80A.014.006
  # AC-SYSTEM-MIB::acSysIdSerialNumber.0 = Wrong Type (should be Gauge32 or Unsigned32): INTEGER: 2182014

  $data = snmp_get_multi($device, 'acSysIdName.0 acSysVersionSoftware.0', '-OQUs', 'AC-SYSTEM-MIB', mib_dirs('audiocodes'));
  $hardware = $data[0]['acSysIdName'];
  $version  = $data[0]['acSysVersionSoftware'];
}

$data = snmp_get_multi($device, 'acSysIdSerialNumber.0', '-OQUs', 'AC-SYSTEM-MIB', mib_dirs('audiocodes'));
$serial = $data[0]['acSysIdSerialNumber'];

// EOF
