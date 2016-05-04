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

#RADWARE-MIB::rndBrgVersion.0 = STRING: "6.09.01"

$version = trim(snmp_get($device, "rndBrgVersion.0", "-OQv", 'RADWARE-MIB', mib_dirs('radware')),'"');

// EOF
