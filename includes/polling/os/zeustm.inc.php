<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage definitions
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

// ZXTM-MIB::version.0 = STRING: "9.1"

$platform = 'ZeusTM';
$version = trim(snmp_get($device, "1.3.6.1.4.1.7146.1.2.1.1.0", "-OQv", 'ZXTM-MIB', mib_dirs('riverbed')),'" ');

// EOF
