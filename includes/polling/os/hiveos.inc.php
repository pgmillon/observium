<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage definitions
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

// SNMPv2-MIB::sysDescr.0 = STRING: AP230, HiveOS 6.6r1 release build2287

preg_match('/(?P<hardware>.+),\ HiveOS\ (?P<version>.+)\ release (?P<features>.+)/', $poll_device['sysDescr'], $matches);
$version  = $matches['version'];
$features = $matches['features'];
$hardware = $matches['hardware'];

// EOF
