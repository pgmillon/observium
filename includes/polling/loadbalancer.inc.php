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

echo("Load balancer: ");

$include_dir = "includes/polling/loadbalancer";
include("includes/include-dir-mib.inc.php");

echo(PHP_EOL);

// EOF
