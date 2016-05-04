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
 * Display FDB table.
 *
 * @param array $vars
 * @return none
 *
 */
function print_fdbtable($vars)
{
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
          $where .= generate_query_values($value, 'I.device_id');
          break;
        case 'port':
        case 'port_id':
          $where .= generate_query_values($value, 'I.port_id');
          break;
        case 'interface':
        case 'port_name':
          $where .= generate_query_values($value, 'I.ifDescr', 'LIKE%');
          break;
        case 'vlan_id':
          $where .= generate_query_values($value, 'F.vlan_id');
          break;
        case 'vlan_name':
          $where .= generate_query_values($value, 'V.vlan_name');
          break;
        case 'address':
          $where .= generate_query_values(str_replace(array(':', ' ', '-', '.', '0x'),'', $value), 'F.mac_address', '%LIKE%');
          break;
      }
    }
  }

  if(isset($vars['sort']))
  {
    switch($vars['sort'])
    {
      case "vlan_id":
        $sort = " ORDER BY `V`.`vlan_vlan`";
        break;

      case "vlan_name":
        $sort = " ORDER BY `V`.`vlan_name`";
        break;

      case "port":
        $sort = " ORDER BY `I`.`port_label`";
        break;

      case "mac":
      default:
        $sort = " ORDER BY `mac_address`";

    }
  }

  // Show FDB tables only for permitted ports
  $query_permitted = generate_query_permitted(array('port'), array('port_table' => 'I'));

  $query = 'FROM `vlans_fdb` AS F ';
  $query .= 'LEFT JOIN `vlans` as V ON V.`vlan_vlan` = F.`vlan_id` AND V.`device_id` = F.`device_id` ';
  $query .= 'LEFT JOIN `ports` AS I ON I.`port_id` = F.`port_id` ';
  $query .= $where . $query_permitted;
  $query_count = 'SELECT COUNT(*) ' . $query;
  $query =  'SELECT * ' . $query;
  $query .= $sort;
  $query .= " LIMIT $start,$pagesize";

  // Query addresses
  $entries = dbFetchRows($query, $param);
  // Query address count
  if ($pagination) { $count = dbFetchCell($query_count, $param); }

  $list = array('device' => FALSE, 'port' => FALSE);
  if (!isset($vars['device']) || empty($vars['device']) || $vars['page'] == 'search') { $list['device'] = TRUE; }
  if (!isset($vars['port'])   || empty($vars['port'])   || $vars['page'] == 'search') { $list['port'] = TRUE; }

  $string = generate_box_open();

  $string .= '<table class="table  table-striped table-hover table-condensed">' . PHP_EOL;

  $cols = array(
    'device'         => 'Device',
    'mac'            => array('MAC Address', 'style="width: 160px;"'),
    'status'         => array('Status', 'style="width: 100px;"'),
    'port'           => 'Port',
    'vlan_id'        => 'VLAN ID',
    'vlan_name'      => 'VLAN NAME',
  );

  if (!$list['device'])  { unset($cols['device']); }
  if (!$list['port'])    { unset($cols['port']); }

  if (!$short)
  {
    $string .= get_table_header($cols, $vars); // Currently sorting is not available
  }

  foreach ($entries as $entry)
  {
    humanize_port($entry);

    $string .= '  <tr>' . PHP_EOL;
    if ($list['device'])
    {
      $dev = device_by_id_cache($entry['device_id']);
      $string .= '    <td class="entity" style="white-space: nowrap;">' . generate_device_link($dev) . '</td>' . PHP_EOL;
    }
    $string .= '    <td>' . generate_popup_link('mac', format_mac($entry['mac_address'])) . '</td>' . PHP_EOL;
    $string .= '    <td>' . $entry['fdb_status'] . '</td>' . PHP_EOL;
    if ($list['port']) { $string .= '    <td class="entity">' . generate_port_link($entry, $entry['port_label_short']) . ' ' . $port_error . '</td>' . PHP_EOL; }
    $string .= '    <td>Vlan' . $entry['vlan_vlan'] . '</td>' . PHP_EOL;
    $string .= '    <td>' . $entry['vlan_name'] . '</td>' . PHP_EOL;
    $string .= '  </tr>' . PHP_EOL;
  }

  $string .= '  </tbody>' . PHP_EOL;
  $string .= '</table>';

  $string .= generate_box_close();

  // Print pagination header
  if ($pagination) { $string = pagination($vars, $count) . $string . pagination($vars, $count); }

  // Print FDB table
  echo $string;
}

// EOF
