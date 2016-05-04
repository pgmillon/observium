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

// SNIA-SML-MIB::product-Name.0 = STRING: "IBM System Storage TS3500 Tape Library"
// SNIA-SML-MIB::product-IdentifyingNumber.0 = STRING: "7823156"
// SNIA-SML-MIB::product-Vendor.0 = STRING: "International Business Machines"
// SNIA-SML-MIB::product-Version.0 = STRING: "8870"
// SNIA-SML-MIB::product-ElementName.0 = STRING: "IBM System Storage TS3500 Tape Library 7823156"
// SNIA-SML-MIB::chassis-Manufacturer.0 = STRING: "International Business Machines"
// SNIA-SML-MIB::chassis-Model.0 = STRING: "3584"
// SNIA-SML-MIB::chassis-SerialNumber.0 = STRING: "7823156"
// SNIA-SML-MIB::chassis-LockPresent.0 = INTEGER: true(1)
// SNIA-SML-MIB::chassis-SecurityBreach.0 = INTEGER: 0
// SNIA-SML-MIB::chassis-IsLocked.0 = INTEGER: true(1)
// SNIA-SML-MIB::chassis-Tag.0 = STRING: "International Business Machines 3584 7823156"
// SNIA-SML-MIB::chassis-ElementName.0 = STRING: "IBM System Storage TS3500 Tape Library        "

$data = snmp_get_multi($device, 'chassis-Model.0 chassis-SerialNumber.0 product-Version.0', "-OQUs", "SNIA-SML-MIB");

if (is_array($data[0]))
{
  $hardware = $data[0]['chassis-Model'];
  $version  = $data[0]['product-Version'];
  $serial   = $data[0]['chassis-SerialNumber'];
}

unset($data);

// EOF
