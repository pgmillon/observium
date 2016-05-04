<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage webinterface
 * @author     Adam Armstrong <adama@memetic.org>
 * @copyright  (C) 2006-2015 Adam Armstrong
 *
 */

include_once("../includes/defaults.inc.php");
include_once("../config.php");
include_once("../includes/definitions.inc.php");

include($config['install_dir'] . "/includes/common.inc.php");
include($config['install_dir'] . "/includes/dbFacile.php");
include($config['install_dir'] . "/includes/rewrites.inc.php");
include($config['install_dir'] . "/includes/entities.inc.php");
include($config['install_dir'] . "/includes/snmp.inc.php");
include($config['html_dir'] . "/includes/functions.inc.php");
include($config['html_dir'] . "/includes/authenticate.inc.php");

if (is_numeric($_GET['id']) && ($config['allow_unauth_graphs'] || port_permitted($_GET['id'])))
{
  $port   = get_port_by_id($_GET['id']);
  $device = device_by_id_cache($port['device_id']);
  $title  = generate_device_link($device);
  $title .= " :: Port  ".generate_port_link($port);
  $auth   = TRUE;
}

$time = time();
$HC   = ($port['port_64bit'] ? 'HC' : '');

$data = snmp_get_multi($device, "if${HC}InOctets.".$port['ifIndex']." if${HC}OutOctets.".$port['ifIndex'], "-OQUs", "IF-MIB", mib_dirs());
printf("%lf|%s|%s\n", time(), $data[$port['ifIndex']]["if${HC}InOctets"], $data[$port['ifIndex']]["if${HC}OutOctets"]);

// EOF
