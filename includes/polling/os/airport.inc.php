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

$version = snmp_get($device, "sysConfFirmwareVersion.0", "-Ovq", "AIRPORT-BASESTATION-3-MIB");

// EOF
