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

#RADWARE-MIB::rndBrgVersion.0 = STRING: "6.09.01"

$version = trim(snmp_get($device, "rndBrgVersion.0", "-OQv", 'RADWARE-MIB', mib_dirs('radware')),'"');

// EOF
