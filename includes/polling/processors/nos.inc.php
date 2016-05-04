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

// FIXME swCpuUsage.0, SW-MIB
$proc = trim(snmp_get($device, "1.3.6.1.4.1.1588.2.1.1.1.26.1.0", "-Ovq"),'"');

// EOF
