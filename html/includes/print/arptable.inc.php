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
 * Display ARP/NDP table addresses.
 *
 * Display pages with ARP/NDP tables addresses from devices.
 *
 * @param array $vars
 * @return none
 *
 */
function print_arptable($vars)
{
  // With pagination? (display page numbers in header)
  $pagination = (isset($vars['pagination']) && $vars['pagination']);
  pagination($vars, 0, TRUE); // Get default pagesize/pageno
  $pageno   = $vars['pageno'];
  $pagesize = $vars['pagesize'];
  $start    = $pagesize * $pageno - $pagesize;

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
        case 'port':
        case 'port_id':
          $where .= ' AND I.`port_id` = ?';
          $param[] = $value;
          break;
        case 'ip_version':
          $where .= ' AND `ip_version` = ?';
          $param[] = $value;
          break;
        case 'address':
          if (isset($vars['searchby']) && $vars['searchby'] == 'ip')
          {
            $where .= ' AND `ip_address` LIKE ?';
            $value = trim($value);
            // FIXME. Need another conversion ("2001:b08:b08" -> "2001:0b08:0b08") -- mike
            if (Net_IPv6::checkIPv6($value)) { $value = Net_IPv6::uncompress($value, true); }
            $param[] = '%'.$value.'%';
          } else {
            $where .= ' AND `mac_address` LIKE ?';
            // FIXME hm? mres in a dbFacile parameter?
            $param[] = '%'.str_replace(array(':', ' ', '-', '.', '0x'),'',mres($value)).'%';
          }
          break;
      }
    }
  }

  // Show ARP tables only for permitted ports
  $query_permitted = generate_query_permitted(array('port'), array('port_table' => 'I'));

  $query = 'FROM `ip_mac` AS M ';
  $query .= 'LEFT JOIN `ports` AS I ON I.`port_id` = M.`port_id` ';
  $query .= $where . $query_permitted;
  $query_count = 'SELECT COUNT(`mac_id`) ' . $query;
  $query =  'SELECT * ' . $query;
  $query .= ' ORDER BY M.`mac_address`';
  $query .= " LIMIT $start,$pagesize";

  // Query ARP/NDP table addresses
  $entries = dbFetchRows($query, $param);
  // Query ARP/NDP table address count
  if ($pagination) { $count = dbFetchCell($query_count, $param); }

  $list = array('device' => FALSE, 'port' => FALSE);
  if (!isset($vars['device']) || empty($vars['device']) || $vars['page'] == 'search') { $list['device'] = TRUE; }
  if (!isset($vars['port']) || empty($vars['port']) || $vars['page'] == 'search') { $list['port'] = TRUE; }

  $string = '<table class="table table-bordered table-striped table-hover table-condensed">' . PHP_EOL;
  if (!$short)
  {
    $string .= '  <thead>' . PHP_EOL;
    $string .= '    <tr>' . PHP_EOL;
    $string .= '      <th>MAC Address</th>' . PHP_EOL;
    $string .= '      <th>IP Address</th>' . PHP_EOL;
    if ($list['device']) { $string .= '      <th>Device</th>' . PHP_EOL; }
    if ($list['port']) { $string .= '      <th>Interface</th>' . PHP_EOL; }
    $string .= '      <th>Remote Device</th>' . PHP_EOL;
    $string .= '      <th>Remote Interface</th>' . PHP_EOL;
    $string .= '    </tr>' . PHP_EOL;
    $string .= '  </thead>' . PHP_EOL;
  }
  $string .= '  <tbody>' . PHP_EOL;

  foreach ($entries as $entry)
  {
    humanize_port ($entry);
    $ip_version = $entry['ip_version'];
    $ip_address = ($ip_version == 6) ? Net_IPv6::compress($entry['ip_address']) : $entry['ip_address'];
    $arp_host = dbFetchRow('SELECT * FROM `ipv'.$ip_version.'_addresses` AS A
                           LEFT JOIN `ports` AS I ON A.`port_id` = I.`port_id`
                           LEFT JOIN `devices` AS D ON D.`device_id` = I.`device_id`
                           WHERE A.`ipv'.$ip_version.'_address` = ?', array($ip_address));
    $arp_name = ($arp_host) ? generate_device_link($arp_host) : '';
    $arp_if = ($arp_host) ? generate_port_link($arp_host) : '';
    if ($arp_host['device_id'] == $entry['device_id']) { $arp_name = 'Self Device'; }
    if ($arp_host['port_id'] == $entry['port_id']) { $arp_if = 'Self Port'; }

    $string .= '  <tr>' . PHP_EOL;
    $string .= '    <td style="width: 160px;">' . format_mac($entry['mac_address']) . '</td>' . PHP_EOL;
    $string .= '    <td style="width: 140px;">' . $ip_address . '</td>' . PHP_EOL;
    if ($list['device'])
    {
      $dev = device_by_id_cache($entry['device_id']);
      $string .= '    <td class="entity" style="white-space: nowrap;">' . generate_device_link($dev) . '</td>' . PHP_EOL;
    }
    if ($list['port'])
    {
      if ($entry['ifInErrors_delta'] > 0 || $entry['ifOutErrors_delta'] > 0)
      {
        $port_error = generate_port_link($entry, '<span class="label label-important">Errors</span>', 'port_errors');
      }
      $string .= '    <td class="entity">' . generate_port_link($entry, short_ifname($entry['label'])) . ' ' . $port_error . '</td>' . PHP_EOL;
    }
    $string .= '    <td class="entity" style="width: 200px;">' . $arp_name . '</td>' . PHP_EOL;
    $string .= '    <td class="entity">' . $arp_if . '</td>' . PHP_EOL;
    $string .= '  </tr>' . PHP_EOL;
  }

  $string .= '  </tbody>' . PHP_EOL;
  $string .= '</table>';

  // Print pagination header
  if ($pagination) { $string = pagination($vars, $count) . $string . pagination($vars, $count); }

  // Print ARP/NDP table
  echo $string;
}

// EOF
