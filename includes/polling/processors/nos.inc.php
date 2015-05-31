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

$proc = trim(snmp_get($device, "1.3.6.1.4.1.1588.2.1.1.1.26.1.0", "-Ovq"),'"');

?>
