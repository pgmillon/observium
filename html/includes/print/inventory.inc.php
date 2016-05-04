<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage web
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
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
    // DHTML expandable tree
    $GLOBALS['cache_html']['js'][]  = 'js/mktree.js';
    $GLOBALS['cache_html']['css'][] = 'css/mktree.css';

    echo('<table class="table table-striped  table-condensed "><tr><td>');
    echo('<div class="btn-group pull-right" style="margin-top:5px; margin-right: 5px;">
      <button class="btn btn-small" onClick="expandTree(\'enttree\');return false;"><i class="icon-plus muted small"></i> Expand</button>
      <button class="btn btn-small" onClick="collapseTree(\'enttree\');return false;"><i class="icon-minus muted small"></i> Collapse</button>
    </div>');

    echo('<div style="clear: left; margin: 5px;"><ul class="mktree" id="enttree" style="margin-left: -10px;">');
    print_ent_physical(0, 0, "liOpen");
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
          $where .= generate_query_values($value, 'device_id');
          break;
        case 'os':
          $where .= generate_query_values($value, 'os');
          break;
        case 'parts':
          $where .= generate_query_values($value, 'entPhysicalModelName', 'LIKE');
          break;
        case 'serial':
          $where .= generate_query_values($value, 'entPhysicalSerialNum', '%LIKE%');
          break;
        case 'description':
          $where .= generate_query_values($value, 'entPhysicalDescr', '%LIKE%');
          break;
      }
    }
  }

  // Show inventory only for permitted devices
  //$query_permitted = generate_query_permitted(array('device'), array('device_table' => 'D'));

  $query = 'FROM `entPhysical`';
  $query .= ' LEFT JOIN `devices` USING(`device_id`) ';
  $query .= $where . $GLOBALS['cache']['where']['devices_permitted'];
  $query_count = 'SELECT COUNT(*) ' . $query;

  $query =  'SELECT * ' . $query;
  $query .= ' ORDER BY `hostname`';
  $query .= " LIMIT $start,$pagesize";

  // Query inventories
  $entries = dbFetchRows($query, $param);
  // Query inventory count
  if ($pagination) { $count = dbFetchCell($query_count, $param); }

  $list = array('device' => FALSE);
  if (!isset($vars['device']) || empty($vars['device']) || $vars['page'] == 'inventory') { $list['device'] = TRUE; }

  $string = generate_box_open($vars['header']);
  $string .= '<table class="'.OBS_CLASS_TABLE_STRIPED.'">' . PHP_EOL;
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
  $string .= generate_box_close();

  // Print pagination header
  if ($pagination) { $string = pagination($vars, $count) . $string . pagination($vars, $count); }

  $entries_allowed = array('entPhysical_id', 'device_id', 'entPhysicalIndex', 'entPhysicalDescr',
                           'entPhysicalClass','entPhysicalName','entPhysicalHardwareRev','entPhysicalFirmwareRev',
                           'entPhysicalSoftwareRev','entPhysicalAlias','entPhysicalAssetID','entPhysicalIsFRU',
                           'entPhysicalModelName','entPhysicalVendorType','entPhysicalSerialNum','entPhysicalContainedIn',
                           'entPhysicalParentRelPos','entPhysicalMfgName');


  foreach($entries as $entry)
  {
    $entries_cleaned[$entry['entPhysical_id']] = array_intersect_key($entry, array_flip($entries_allowed));
  }

  // Print Inventories
  switch($vars['format'])
  {
    case "csv":

      echo(implode($entry, ", "));
      echo("\n");

      break;
    default:
      echo $string;
      break;
  }
}

/**
 * Display device inventory hierarchy.
 *
 * @param string $ent, $level, $class
 * @return none
 *
 */
function print_ent_physical($entPhysicalContainedIn, $level, $class)
{
  global $device;

  $ents = dbFetchRows("SELECT * FROM `entPhysical` WHERE `device_id` = ? AND `entPhysicalContainedIn` = ? ORDER BY `entPhysicalContainedIn`, `entPhysicalIndex`", array($device['device_id'], $entPhysicalContainedIn));
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
      case 'board':
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

    //entPhysicalVendorType

    if ($ent['entPhysicalModelName'] && $ent['entPhysicalName'])
    {
      $ent_text .= "<strong>".$ent['entPhysicalModelName']  . "</strong> (".$ent['entPhysicalName'].")";
    } elseif ($ent['entPhysicalModelName'] && $ent['entPhysicalVendorType']) {
      $ent_text .= "<strong>".$ent['entPhysicalModelName']  . "</strong> (".$ent['entPhysicalVendorType'].")";
    } elseif ($ent['entPhysicalModelName'] ) {
      $ent_text .= "<strong>".$ent['entPhysicalModelName']  . "</strong>";
    } elseif ($ent['entPhysicalName'] && $ent['entPhysicalVendorType']) {
      $ent_text .= "<strong>".$ent['entPhysicalName']."</strong> (".$ent['entPhysicalVendorType'].")";
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
