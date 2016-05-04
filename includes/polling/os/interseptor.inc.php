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

// ISPRO-MIB::isIdentManufacturer.0 = STRING: "Jacarta"
// ISPRO-MIB::isIdentModel.0 = STRING: "interSeptor Pro"
// ISPRO-MIB::isIdentAgentSoftwareVersion.0 = STRING: "interSeptor Pro v1.07"
$hardware = snmp_get($device, "isIdentModel.0", "-OQv", "ISPRO-MIB", mib_dirs('jacarta'));
list(,$version) = preg_split('/\ v/', snmp_get($device, "isIdentAgentSoftwareVersion.0", "-OQv", "ISPRO-MIB", mib_dirs('jacarta')));

// EOF
