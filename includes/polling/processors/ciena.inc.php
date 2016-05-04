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

$load = snmp_get($device, ".1.3.6.1.4.1.6141.2.60.12.1.7.4.0", "-Ovq");
$proc = (float) $load / 100;

// EOF
