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

$hardware = snmp_get($device,"1.3.6.1.4.1.4547.2.3.1.3.0","-OQv");
$serial = snmp_get($device,"1.3.6.1.4.1.4547.2.3.1.9.0","-OQv");
list($version) = explode(" ",snmp_get($device,"1.3.6.1.4.1.4547.2.3.1.4.0","-OQv"));

// EOF
