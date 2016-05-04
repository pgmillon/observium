<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage webinterface
 * @author     Adam Armstrong <adama@observium.org>
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

include_once("../includes/sql-config.inc.php");

include($config['html_dir'] . "/includes/functions.inc.php");
include($config['html_dir'] . "/includes/authenticate.inc.php");

if (is_numeric($_GET['id']) && ($config['allow_unauth_graphs'] || port_permitted($_GET['id'])))
{
  $port   = get_port_by_id($_GET['id']);
  $device = device_by_id_cache($port['device_id']);
  //$title  = generate_device_link($device);
  //$title .= " :: Port  ".generate_port_link($port);
  $auth   = TRUE;

  $time = time();
  $HC   = ($port['port_64bit'] ? 'HC' : '');

  $data = snmp_get_multi($device, "if${HC}InOctets.".$port['ifIndex']." if${HC}OutOctets.".$port['ifIndex'], "-OQUs", "IF-MIB", mib_dirs());
  printf("%lf|%s|%s\n", $time, $data[$port['ifIndex']]["if${HC}InOctets"], $data[$port['ifIndex']]["if${HC}OutOctets"]);
} else {
  echo("unauthenticated");
  exit;
}

// EOF
