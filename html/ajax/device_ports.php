<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage ajax
 * @author     Adam Armstrong <adama@observium.org>
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

$config['install_dir'] = "../..";

include_once("../../includes/sql-config.inc.php");

include($config['html_dir'] . "/includes/functions.inc.php");
include($config['html_dir'] . "/includes/authenticate.inc.php");

if (!$_SESSION['authenticated']) { echo("unauthenticated"); exit; }

if (is_numeric($_GET['device_id']) && device_permitted($_GET['device_id']))
{
  foreach (dbFetchRows("SELECT `port_id`,`port_label_short`,`ifAlias`,`ifDescr`,`ifName` FROM `ports` WHERE `device_id` = ? AND deleted = 0 ORDER BY ifIndex", array($_GET['device_id'])) as $interface)
  {
    $descr = array();
    if (empty($interface['port_label_short']))
    {
      $device = device_by_id_cache($interface['port_id']);
      process_port_label($interface, $device);
    }
    $descr[] = $interface['port_label_short'];

    if ($interface['ifAlias'])
    {
      // second part
      $descr[] = $interface['ifAlias'];
    }
    $string = addslashes(implode(" - ", $descr));
    echo("obj.options[obj.options.length] = new Option('".$string."','".$interface['port_id']."');\n");
  }
}

// EOF
