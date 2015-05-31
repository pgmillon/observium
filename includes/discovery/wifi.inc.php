<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage discovery
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

echo("Wifi: ");

// Include all discovery modules

$include_dir = "includes/discovery/wifi";
include("includes/include-dir-mib.inc.php");

echo(PHP_EOL);

//EOF
