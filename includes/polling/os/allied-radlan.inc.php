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

$hardware = str_replace("ATI", "", $poll_device['sysDescr']);
$version  = snmp_get($device, "rndBrgVersion.0", "-Ovq", "RADLAN-MIB", mib_dirs('radlan'));

// These not features, just boot version
//$features = snmp_get($device, "rndBaseBootVersion.00", "-Ovq", "RADLAN-MIB", mib_dirs('radlan'));

// EOF
