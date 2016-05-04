<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage discovery
 * @author     Adam Armstrong <adama@observium.org>
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

echo(" Virtual machines: ");

// Always run libvirt (module checks for os suitability and enable_libvirt)
include("includes/discovery/virtual-machines/libvirt.inc.php");

// Include all discovery modules by MIB
$include_dir = "includes/discovery/virtual-machines";
include("includes/include-dir-mib.inc.php");

echo(PHP_EOL);

// EOF
