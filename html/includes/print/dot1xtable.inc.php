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
 * Display dot1x sessions
 *
 * @param array $vars
 * @return none
 *
 */
function print_dot1xtable($vars)
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
        case 'address':
          if (isset($vars['searchby']) && $vars['searchby'] == 'ip')
          {
            $value = trim($value);
            $where .= generate_query_values($value, 'ipv4_addr', '%LIKE%');
          } else if (isset($vars['searchby']) && $vars['searchby'] == 'mac') {
            $value = str_replace(array(':', ' ', '-', '.', '0x'), '', $value);
            $where .= generate_query_values($value, 'M.mac_addr', '%LIKE%');
          } else {
            $value = trim($value);
            $where .= generate_query_values($value, 'username', '%LIKE%');
          }
          break;
      }
    }
  }

  // Check permissions
  $query_permitted = generate_query_permitted(array('device'), array('device_table' => 'M'));

  $query = 'FROM `wifi_sessions` AS M ';
  $query .= 'LEFT JOIN `wifi_radios` AS I ON I.`wifi_radio_id` = M.`radio_id` ';
  $query .= $where . $query_permitted;
  $query_count = 'SELECT COUNT(`wifi_session_id`) ' . $query;
  $query =  'SELECT *, M.`mac_addr` AS `session_mac` ' . $query;
  $query .= ' ORDER BY M.`timestamp` DESC';
  $query .= " LIMIT $start,$pagesize";

  // Query wireless  sessions table
  $entries = dbFetchRows($query, $param);
  // Query wireless  sessions table count
  if ($pagination) { $count = dbFetchCell($query_count, $param); }

  $aps_db = dbFetchRows("SELECT `wifi_accesspoint_id`, `name`, `ap_number`  FROM `wifi_accesspoints`");

  foreach ($aps_db as $ap_db)
  {
    $aps_sorted_db[$ap_db['wifi_accesspoint_id']] = $ap_db;
  }

  $list = array('device' => FALSE, 'port' => FALSE); // A radio is like a port
  if (!isset($vars['device']) || empty($vars['device']) || $vars['page'] == 'search') { $list['device'] = TRUE; }
  if (!isset($vars['port']) || empty($vars['port']) || $vars['page'] == 'search') { $list['port'] = TRUE; }

  $string = '<table class="table  table-striped table-hover table-condensed">' . PHP_EOL;
  if (!$short)
  {
    $string .= '  <thead>' . PHP_EOL;
    $string .= '    <tr>' . PHP_EOL;
    $string .= '      <th>MAC Address</th>' . PHP_EOL;
    $string .= '      <th>IP Address</th>' . PHP_EOL;
    $string .= '      <th>Username</th>' . PHP_EOL;
    $string .= '      <th>SSID/VLAN</th>' . PHP_EOL;
    $string .= '      <th>Last Seen</th>' . PHP_EOL;
    if ($list['device']) { $string .= '      <th>Device</th>' . PHP_EOL; }
    if ($list['port']) { $string .= '      <th>Interface/AP</th>' . PHP_EOL; }
    $string .= '    </tr>' . PHP_EOL;
    $string .= '  </thead>' . PHP_EOL;
  }
  $string .= '  <tbody>' . PHP_EOL;

  foreach ($entries as $entry)
  {
    $ap_id = $entry['accesspoint_id'];
    $interface = $aps_sorted_db[$ap_id]['name'];
    $string .= '  <tr>' . PHP_EOL;
    $string .= '    <td style="width: 140px;">' . generate_popup_link('mac', format_mac($entry['session_mac'])) . '</td>' . PHP_EOL;
    $string .= '    <td style="width: 140px;">' . generate_popup_link('ip', $entry['ipv4_addr']) . '</td>' . PHP_EOL;
    $string .= '    <td style="white-space: nowrap;">' . $entry['username'] . '</td>' . PHP_EOL;
    $string .= '    <td style="width: 140px;">' . $entry['ssid'] . '</td>' . PHP_EOL;
    $string .= '    <td style="white-space: nowrap;">' . $entry['timestamp'] . '</td>' . PHP_EOL;
    if ($list['device'])
    {
      $dev = device_by_id_cache($entry['device_id']);
      $string .= '    <td class="entity" style="white-space: nowrap;">' . generate_device_link($dev) . '</td>' . PHP_EOL;
    }
    if ($list['port'])
    {
      $string .= '    <td class="entity"><a href="' . generate_url(array('page' => 'device', 'device' => $entry['device_id'], 'tab' => 'wifi', 'view' => 'accesspoint', 'accesspoint' => $ap_id)).'">' . $interface . '</a></td>' . PHP_EOL;
    }
    $string .= '  </tr>' . PHP_EOL;
  }

  $string .= '  </tbody>' . PHP_EOL;
  $string .= '</table>';

  // Print pagination header
  if ($pagination) { $string = pagination($vars, $count) . $string . pagination($vars, $count); }

  // Print wireless sessions
  echo $string;
}

// EOF
