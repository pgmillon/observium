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

$version = snmp_get($device, "sysConfFirmwareVersion.0", "-Ovq", "AIRPORT-BASESTATION-3-MIB");

// EOF
