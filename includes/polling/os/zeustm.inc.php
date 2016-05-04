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

// ZXTM-MIB::version.0 = STRING: "9.1"

$platform = 'ZeusTM';
$version = trim(snmp_get($device, "1.3.6.1.4.1.7146.1.2.1.1.0", "-OQv", 'ZXTM-MIB', mib_dirs('riverbed')),'" ');

// EOF
