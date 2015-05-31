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

$version  = snmp_get($device, "rndBrgVersion.0", "-Ovq", "RADLAN-MIB", mib_dirs('radlan'));
$hardware = str_replace("ATI", "", $poll_device['sysDescr']);

$features = snmp_get($device, "rndBaseBootVersion.00", "-Ovq", "RADLAN-MIB", mib_dirs('radlan'));

$version  = trim($version,'"');
$features = trim($features,'"');
$hardware = trim($hardware,'"');

// EOF
