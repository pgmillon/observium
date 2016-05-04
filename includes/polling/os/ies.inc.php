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

$version = trim(snmp_get($device, "accessSwitchFWVersion.0", "-OQv", "ZYXEL-AS-MIB", mib_dirs('zyxel')),'"');

preg_match("/IES-(\d)*/",$poll_device['sysDescr'], $matches);
$hardware = $matches[0];

// EOF
