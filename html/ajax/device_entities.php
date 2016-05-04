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

if ($_SESSION['userlevel'] >= '5')
{

  switch ($_GET['entity_type'])
  {

    case "sensor":
      foreach (dbFetch("SELECT * FROM `sensors` WHERE device_id = ?", array($_GET['device_id'])) as $sensor)
      {
        if(is_entity_permitted($sensor, 'sensor'))
        {
          $string = addslashes($sensor['sensor_descr']);
          echo("obj.options[obj.options.length] = new Option('".$string."','".$sensor['sensor_id']."');\n");
        }
      }
      break;

    case "port":
      foreach (dbFetch("SELECT * FROM `ports` WHERE `device_id` = ? AND `deleted` = '0'", array($_GET['device_id'])) as $port)
      {
        $string = addslashes($port['port_label_short']." - ".$port['ifAlias']);
        echo("obj.options[obj.options.length] = new Option('".$string."','".$port['port_id']."');\n");
      }
      break;
  }

}

?>
