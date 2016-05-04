<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage discovery
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

print_cli_data_field("Discovering MIBs", 3);

$valid['inventory'] = array();

$include_dir = "includes/discovery/inventory";
include($config['install_dir']."/includes/include-dir-mib.inc.php");

check_valid_inventory($device, $valid['inventory']);

$GLOBALS['module_stats'][$module]['status'] = count($valid[$module]);
if (OBS_DEBUG && $GLOBALS['module_stats'][$module]['status']) { print_vars($valid[$module]); }

// EOF
