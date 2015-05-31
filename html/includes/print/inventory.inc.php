<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage web
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

/**
 * Display Devices Inventory.
 *
 * @param array $vars
 * @return none
 *
 */
function print_inventory($vars)
{
  // On "Inventory" device tab display hierarchical list
  if ($vars['page'] == 'device' && is_numeric($vars['device']) && device_permitted($vars['device']))
  {
    echo('<table class="table table-striped table-bordered table-condensed table-rounded"><tr><td>');
    echo('<div class="btn-group pull-right" style="margin-top:5px; margin-right: 5px;">
      <button class="btn btn-small" onClick="expandTree(\'enttree\');return false;"><i class="icon-plus muted small"></i> Expand</button>
      <button class="btn btn-small" onClick="collapseTree(\'enttree\');return false;"><i class="icon-minus muted small"></i> Collapse</button>
    </div>');

    echo('<div style="clear: left; margin: 5px;"><ul class="mktree" id="enttree" style="margin-left: -10px;">');
    $level = 0;
    $ent['entPhysicalIndex'] = 0;
    print_ent_physical($ent['entPhysicalIndex'], $level, "liOpen");
    echo('</ul></div>');
    echo('</td></tr></table>');
    return TRUE;
  }

  // With pagination? (display page numbers in header)
  $pagination = (isset($vars['pagination']) && $vars['pagination']);
  pagination($vars, 0, TRUE); // Get default pagesize/pageno
  $pageno   = $vars['pageno'];
  $pagesize = $vars['pagesize'];
  $start = $pagesize * $pageno - $pagesize;

  $param = array();
  $where = ' WHERE 1 ';
  foreach ($vars as $var => $value)
  {
    if ($value != '')
    {
      switch ($var)
      {
        case 'device':
        case 'device_id':
          $where .= generate_query_values($value, 'E.device_id');
          break;
        case 'parts':
          $where .= generate_query_values($value, 'E.entPhysicalModelName', 'LIKE');
          break;
        case 'serial':
          $where .= ' AND E.`entPhysicalSerialNum` LIKE ?';
          $param[] = '%'.$value.'%';
          break;
        case 'description':
          $where .= ' AND E.`entPhysicalDescr` LIKE ?';
          $param[] = '%'.$value.'%';
          break;
      }
    }
  }

  // Show inventory only for permitted devices
  $query_permitted = generate_query_permitted(array('device'), array('device_table' => 'D'));

  $query = 'FROM `entPhysical` AS E ';
  $query .= 'LEFT JOIN `devices` AS D ON D.`device_id` = E.`device_id` ';
  $query .= $where . $query_permitted;
  $query_count = 'SELECT COUNT(*) ' . $query;

  $query =  'SELECT * ' . $query;
  $query .= ' ORDER BY D.`hostname`';
  $query .= " LIMIT $start,$pagesize";

  // Query inventories
  $entries = dbFetchRows($query, $param);
  // Query inventory count
  if ($pagination) { $count = dbFetchCell($query_count, $param); }

  $list = array('device' => FALSE);
  if (!isset($vars['device']) || empty($vars['device']) || $vars['page'] == 'inventory') { $list['device'] = TRUE; }

  $string = '<table class="table table-bordered table-striped table-hover table-condensed">' . PHP_EOL;
  if (!$short)
  {
    $string .= '  <thead>' . PHP_EOL;
    $string .= '    <tr>' . PHP_EOL;
    if ($list['device']) { $string .= '      <th>Device</th>' . PHP_EOL; }
    $string .= '      <th>Name</th>' . PHP_EOL;
    $string .= '      <th>Description</th>' . PHP_EOL;
    $string .= '      <th>Part #</th>' . PHP_EOL;
    $string .= '      <th>Serial #</th>' . PHP_EOL;
    $string .= '    </tr>' . PHP_EOL;
    $string .= '  </thead>' . PHP_EOL;
  }
  $string .= '  <tbody>' . PHP_EOL;

  foreach ($entries as $entry)
  {
    $string .= '  <tr>' . PHP_EOL;
    if ($list['device'])
    {
      $string .= '    <td class="entity" style="white-space: nowrap">' . generate_device_link($entry, NULL, array('page' => 'device', 'tab' => 'entphysical')) . '</td>' . PHP_EOL;
    }
    if ($entry['ifIndex'])
    {
      $interface = get_port_by_ifIndex($entry['device_id'], $entry['ifIndex']);
      $entry['entPhysicalName'] = generate_port_link($interface);
    }
    elseif ($entry['entPhysicalClass'] == "sensor")
    {
      $sensor = dbFetchRow("SELECT * FROM `sensors` AS S
                            LEFT JOIN `sensors-state` AS ST ON S.`sensor_id` = ST.`sensor_id`
                            WHERE `device_id` = ? AND (`entPhysicalIndex` = ? OR `sensor_index` = ?)", array($entry['device_id'], $entry['entPhysicalIndex'], $entry['entPhysicalIndex']));
      //$ent_text .= ' ('.$sensor['sensor_value'] .' '. $sensor['sensor_class'].')';
      $entry['entPhysicalName'] = generate_entity_link('sensor', $sensor);
    }
    $string .= '    <td style="width: 160px;">' . $entry['entPhysicalName'] . '</td>' . PHP_EOL;
    $string .= '    <td>' . $entry['entPhysicalDescr'] . '</td>' . PHP_EOL;
    $string .= '    <td>' . $entry['entPhysicalModelName'] . '</td>' . PHP_EOL;
    $string .= '    <td>' . $entry['entPhysicalSerialNum'] . '</td>' . PHP_EOL;
    $string .= '  </tr>' . PHP_EOL;
  }

  $string .= '  </tbody>' . PHP_EOL;
  $string .= '</table>';

  // Print pagination header
  if ($pagination) { $string = pagination($vars, $count) . $string . pagination($vars, $count); }

  // Print Inventories
  echo $string;
}

/**
 * Display device inventory hierarchy.
 *
 * @param string $ent, $level, $class
 * @return none
 *
 */
function print_ent_physical($ent, $level, $class)
{
  global $device;

  $ents = dbFetchRows("SELECT * FROM `entPhysical` WHERE `device_id` = ? AND `entPhysicalContainedIn` = ? ORDER BY `entPhysicalContainedIn`, `entPhysicalIndex`", array($device['device_id'], $ent));
  foreach ($ents as $ent)
  {
    $link = '';
    $text = " <li class='$class'>";

/*
Currently no icons for:

JUNIPER-MIB::jnxFruType.10.1.1.0 = INTEGER: frontPanelModule(8)
JUNIPER-MIB::jnxFruType.12.1.0.0 = INTEGER: controlBoard(5)

For Geist RCX, IPOMan:
outlet
relay
*/

    switch ($ent['entPhysicalClass'])
    {
      case 'chassis':
        $text .= '<i class="oicon-database"></i> ';
        break;
      case 'module':
      case 'portInterfaceCard':
        $text .= '<i class="oicon-drive"></i> ';
        break;
      case 'port':
        $text .= '<i class="oicon-network-ethernet"></i> ';
        break;
      case 'container':
      case 'flexiblePicConcentrator':
        $text .= '<i class="oicon-box-zipper"></i> ';
        break;
      case 'stack':
        $text .= '<i class="oicon-databases"></i> ';
        break;
      case 'fan':
      case 'airflowSensor':
        $text .= '<i class="oicon-weather-wind"></i> ';
        break;
      case 'powerSupply':
      case 'powerEntryModule':
        $text .= '<i class="oicon-plug"></i> ';
        break;
      case 'backplane':
        $text .= '<i class="oicon-zones"></i> ';
        break;
      case 'sensor':
        $text .= '<i class="oicon-asterisk"></i> ';
        $sensor = dbFetchRow("SELECT * FROM `sensors` AS S
                             LEFT JOIN `sensors-state` AS ST ON S.`sensor_id` = ST.`sensor_id`
                             WHERE `device_id` = ? AND (`entPhysicalIndex` = ? OR `sensor_index` = ?)", array($device['device_id'], $ent['entPhysicalIndex'], $ent['entPhysicalIndex']));
        break;
      default:
        $text .= '<i class="oicon-chain"></i> ';
    }

    if ($ent['entPhysicalParentRelPos'] > '-1') { $text .= '<strong>'.$ent['entPhysicalParentRelPos'].'.</strong> '; }

    $ent_text = '';

    if ($ent['ifIndex'])
    {
      $interface = get_port_by_ifIndex($device['device_id'], $ent['ifIndex']);
      $ent['entPhysicalName'] = generate_port_link($interface);
    }

    if ($ent['entPhysicalModelName'] && $ent['entPhysicalName'])
    {
      $ent_text .= "<strong>".$ent['entPhysicalModelName']  . "</strong> (".$ent['entPhysicalName'].")";
    } elseif ($ent['entPhysicalModelName']) {
      $ent_text .= "<strong>".$ent['entPhysicalModelName']  . "</strong>";
    } elseif (is_numeric($ent['entPhysicalName']) && $ent['entPhysicalVendorType']) {
      $ent_text .= "<strong>".$ent['entPhysicalName']." ".$ent['entPhysicalVendorType']."</strong>";
    } elseif ($ent['entPhysicalName']) {
      $ent_text .= "<strong>".$ent['entPhysicalName']."</strong>";
    } elseif ($ent['entPhysicalDescr']) {
      $ent_text .= "<strong>".$ent['entPhysicalDescr']."</strong>";
    }

    $ent_text .= "<br /><div class='small' style='margin-left: 20px;'>" . $ent['entPhysicalDescr'];
    if ($ent['entPhysicalClass'] == "sensor" && $sensor['sensor_value'])
    {
      $ent_text .= ' ('.$sensor['sensor_value'] .' '. $sensor['sensor_class'].')';
      $link = generate_entity_link('sensor', $sensor, $ent_text, NULL, FALSE);
    }

    $text .= ($link) ? $link : $ent_text;

    if ($ent['entPhysicalSerialNum'])
    {
      $text .= ' <span class="text-info">[Serial: '.$ent['entPhysicalSerialNum'].']</span> ';
    }

    $text .= "</div>";
    echo($text);

    $count = dbFetchCell("SELECT COUNT(*) FROM `entPhysical` WHERE `device_id` = ? AND `entPhysicalContainedIn` = ?", array($device['device_id'], $ent['entPhysicalIndex']));
    if ($count)
    {
      echo("<ul>");
      print_ent_physical($ent['entPhysicalIndex'], $level+1, '');
      echo("</ul>");
    }
    echo("</li>");
  }
}

// EOF
