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

// Uses UPS-MIB
include("includes/polling/os/ups-mib.inc.php");

// Version returned as "n/a" above, override with version form sysDescr
preg_match('/CS121 v(.*)/', $poll_device['sysDescr'], $matches);

$version = trim($matches[1]);

// EOF
