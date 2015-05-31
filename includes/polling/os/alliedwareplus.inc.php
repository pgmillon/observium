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

$version = snmp_get($device, "currSoftVersion.0", "-OsvQU", "AT-SETUP-MIB");

// EOF
