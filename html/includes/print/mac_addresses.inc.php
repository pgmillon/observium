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
 * Display Interface MACs addresses.
 *
 * Display pages with MAC addresses from device Interfaces.
 *
 * @param array $vars
 * @return none
 *
 */
function print_mac_addresses($vars)
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
          $where .= generate_query_values($value, 'device_id');
          break;
        case 'interface':
          $where .= generate_query_values($value, 'ifDescr', 'LIKE');
          break;
        case 'address':
          $value = str_replace(array(':', ' ', '-', '.', '0x'), '', $value);
          $where .= generate_query_values($value, 'ifPhysAddress', '%LIKE%');
          break;
      }
    }
  }
  $where .= ' AND `ifPhysAddress` IS NOT NULL'; //Exclude empty MACs

  // Show MACs only for permitted ports
  $query_permitted = generate_query_permitted(array('port'));

  $query = 'FROM `ports` ';
  $query .= $where . $query_permitted;
  $query_count = 'SELECT COUNT(*) ' . $query;
  $query =  'SELECT * ' . $query;
  $query .= ' ORDER BY `ifPhysAddress`';
  $query .= " LIMIT $start,$pagesize";

  // Query addresses
  $entries = dbFetchRows($query, $param);
  // Query address count
  if ($pagination) { $count = dbFetchCell($query_count, $param); }

  $list = array('device' => FALSE);
  if (!isset($vars['device']) || empty($vars['device']) || $vars['page'] == 'search') { $list['device'] = TRUE; }

  $string = generate_box_open($vars['header']);
  $string .= '<table class="table  table-striped table-hover table-condensed">' . PHP_EOL;
  if (!$short)
  {
    $string .= '  <thead>' . PHP_EOL;
    $string .= '    <tr>' . PHP_EOL;
    if ($list['device']) { $string .= '      <th>Device</th>' . PHP_EOL; }
    $string .= '      <th>Interface</th>' . PHP_EOL;
    $string .= '      <th>MAC Address</th>' . PHP_EOL;
    $string .= '      <th>Description</th>' . PHP_EOL;
    $string .= '    </tr>' . PHP_EOL;
    $string .= '  </thead>' . PHP_EOL;
  }
  $string .= '  <tbody>' . PHP_EOL;

  foreach ($entries as $entry)
  {
    if (port_permitted($entry['port_id']))
    {
      humanize_port($entry);

      $string .= '  <tr>' . PHP_EOL;
      if ($list['device'])
      {
        $dev = device_by_id_cache($entry['device_id']);
        $string .= '    <td class="entity" style="white-space: nowrap;">' . generate_device_link($dev) . '</td>' . PHP_EOL;
      }
      if ($entry['ifInErrors_delta'] > 0 || $entry['ifOutErrors_delta'] > 0)
      {
        $port_error = generate_port_link($entry, '<span class="label label-important">Errors</span>', 'port_errors');
      }
      $string .= '    <td class="entity">' . generate_port_link($entry, $entry['port_label_short']) . ' ' . $port_error . '</td>' . PHP_EOL;
      $string .= '    <td style="width: 160px;">' . generate_popup_link('mac', $entry['human_mac']) . '</td>' . PHP_EOL;
      $string .= '    <td>' . $entry['ifAlias'] . '</td>' . PHP_EOL;
      $string .= '  </tr>' . PHP_EOL;
    }
  }

  $string .= '  </tbody>' . PHP_EOL;
  $string .= '</table>';
  $string .= generate_box_close();

  // Print pagination header
  if ($pagination) { $string = pagination($vars, $count) . $string . pagination($vars, $count); }

  // Print MAC addresses
  echo $string;
}

// EOF
