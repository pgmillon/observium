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

$version = trim(snmp_get($device, "1.3.6.1.4.1.674.10893.2.102.3.1.1.9.1", "-OQv", "", ""),'"');

// EOF
