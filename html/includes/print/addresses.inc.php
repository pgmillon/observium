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
 * Display IPv4/IPv6 addresses.
 *
 * Display pages with IP addresses from device Interfaces.
 *
 * @param array $vars
 * @return none
 *
 */
function print_addresses($vars)
{
  // With pagination? (display page numbers in header)
  $pagination = (isset($vars['pagination']) && $vars['pagination']);
  pagination($vars, 0, TRUE); // Get default pagesize/pageno
  $pageno   = $vars['pageno'];
  $pagesize = $vars['pagesize'];
  $start    = $pagesize * $pageno - $pagesize;

  switch($vars['search'])
  {
    case '6':
    case 'ipv6':
    case 'v6':
      $address_type = 'ipv6';
      break;
    default:
      $address_type = 'ipv4';
  }

  $ip_array = array();
  $param = array();
  $where = ' WHERE 1 ';
  $param_netscaler = array();
  $where_netscaler = " WHERE `vsvr_ip` != '0.0.0.0' AND `vsvr_ip` != '' ";
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
        case 'interface':
          $where .= generate_query_values($value, 'I.ifDescr', 'LIKE%');
          break;
        case 'network':
          $where .= generate_query_values($value, 'N.ip_network', 'LIKE%');
          break;
        case 'address':
          list($addr, $mask) = explode('/', $value);
          if (is_numeric(stripos($addr, ':abcdef'))) { $address_type = 'ipv6'; }
          switch ($address_type)
          {
            case 'ipv6':
              $ip_valid = Net_IPv6::checkIPv6($addr);
              break;
            case 'ipv4':
              $ip_valid = Net_IPv4::validateIP($addr);
              break;
          }
          if ($ip_valid)
          {
            // If address valid -> seek occurrence in network
            if (!$mask) { $mask = ($address_type === 'ipv4') ? '32' : '128'; }
            $where_netscaler .= generate_query_values($addr, 'N.vsvr_ip');
          } else {
            // If address not valid -> seek LIKE
            $where .= generate_query_values($addr, 'A.ip_address', '%LIKE%');
            $where_netscaler .= generate_query_values($addr, 'N.vsvr_ip', '%LIKE%');
          }
          break;
      }
    }
  }

  $query_device_permitted = generate_query_permitted(array('device'), array('device_table' => 'D'));
  $query_port_permitted   = generate_query_permitted(array('port'),   array('port_table' => 'I'));

  // Also search netscaler Vserver IPs
  $query_netscaler = 'FROM `netscaler_vservers` AS N ';
  $query_netscaler .= 'LEFT JOIN `devices` AS D ON N.`device_id` = D.`device_id` ';
  $query_netscaler .= $where_netscaler . $query_device_permitted;
  //$query_netscaler_count = 'SELECT COUNT(`vsvr_id`) ' . $query_netscaler;
  $query_netscaler =  'SELECT * ' . $query_netscaler;
  $query_netscaler .= ' ORDER BY N.`vsvr_ip`';
  // Override by address type
  if ($address_type == 'ipv6')
  {
    $query_netscaler = str_replace(array('vsvr_ip', '0.0.0.0'), array('vsvr_ipv6', '0:0:0:0:0:0:0:0'), $query_netscaler);
    //$query_netscaler_count = str_replace(array('vsvr_ip', '0.0.0.0'), array('vsvr_ipv6', '0:0:0:0:0:0:0:0'), $query_netscaler_count);
  }

  $entries = dbFetchRows($query_netscaler, $param_netscaler);
  // Rewrite netscaler addresses
  foreach ($entries as $entry)
  {
    $ip_address = ($address_type == 'ipv4') ? $entry['vsvr_ip'] : $entry['vsvr_'.$address_type];
    $ip_network = ($address_type == 'ipv4') ? $entry['vsvr_ip'].'/32' : $entry['vsvr_'.$address_type].'/128';

    $ip_array[] = array('type'        => 'netscaler_vsvr',
                        'device_id'   => $entry['device_id'],
                        'hostname'    => $entry['hostname'],
                        'vsvr_id'     => $entry['vsvr_id'],
                        'vsvr_label'  => $entry['vsvr_label'],
                        'ifAlias'     => 'Netscaler: '.$entry['vsvr_type'].'/'.$entry['vsvr_entitytype'],
                        $address_type.'_address' => $ip_address,
                        $address_type.'_network' => $ip_network
                        );
  }
  //print_message($query_netscaler_count);

  $query = 'FROM `ip_addresses` AS A ';
  $query .= 'LEFT JOIN `ports`   AS I ON I.`port_id`   = A.`port_id` ';
  $query .= 'LEFT JOIN `devices` AS D ON I.`device_id` = D.`device_id` ';
  $query .= 'LEFT JOIN `ip_networks` AS N ON N.`ip_network_id` = A.`ip_network_id` ';
  $query .= $where . $query_port_permitted;
  //$query_count = 'SELECT COUNT(`ip_address_id`) ' . $query;
  $query =  'SELECT * ' . $query;
  $query .= ' ORDER BY A.`ip_address`';
  if ($ip_valid)
  {
    $pagination = FALSE;
  }

  // Override by address type
  $query = str_replace(array('ip_address', 'ip_network'), array($address_type.'_address', $address_type.'_network'), $query);
  //$query_count = str_replace(array('ip_address', 'ip_network'), array($address_type.'_address', $address_type.'_network'), $query_count);

  // Query addresses
  $entries = dbFetchRows($query, $param);
  $ip_array = array_merge($ip_array, $entries);
  $ip_array = array_sort($ip_array, $address_type.'_address');

  // Query address count
  //if ($pagination) { $count = dbFetchCell($query_count, $param); }
  if ($pagination)
  {
    $count = count($ip_array);
    $ip_array = array_slice($ip_array, $start, $pagesize);
  }

  $list = array('device' => FALSE);
  if (!isset($vars['device']) || empty($vars['device']) || $vars['page'] == 'search') { $list['device'] = TRUE; }

  $string = generate_box_open($vars['header']);
  $string .= '<table class="'.OBS_CLASS_TABLE_STRIPED.'">' . PHP_EOL;
  if (!$short)
  {
    $string .= '  <thead>' . PHP_EOL;
    $string .= '    <tr>' . PHP_EOL;
    if ($list['device']) { $string .= '      <th>Device</th>' . PHP_EOL; }
    $string .= '      <th>Interface</th>' . PHP_EOL;
    $string .= '      <th>Address</th>' . PHP_EOL;
    $string .= '      <th>Description</th>' . PHP_EOL;
    $string .= '    </tr>' . PHP_EOL;
    $string .= '  </thead>' . PHP_EOL;
  }
  $string .= '  <tbody>' . PHP_EOL;

  foreach ($ip_array as $entry)
  {
    $address_show = TRUE;
    if ($ip_valid)
    {
      // If address not in specified network, don't show entry.
      if ($address_type === 'ipv4')
      {
        $address_show = Net_IPv4::ipInNetwork($entry[$address_type.'_address'], $addr . '/' . $mask);
      } else {
        $address_show = Net_IPv6::isInNetmask($entry[$address_type.'_address'], $addr, $mask);
      }
    }

    if ($address_show)
    {
      list($prefix, $length) = explode('/', $entry[$address_type.'_network']);

      if (port_permitted($entry['port_id']) || $entry['type'] == 'netscaler_vsvr')
      {
        if ($entry['type'] == 'netscaler_vsvr')
        {
          $entity_link = generate_entity_link($entry['type'], $entry);
        } else {
          humanize_port ($entry);
          if ($entry['ifInErrors_delta'] > 0 || $entry['ifOutErrors_delta'] > 0)
          {
            $port_error = generate_port_link($entry, '<span class="label label-important">Errors</span>', 'port_errors');
          }
          $entity_link = generate_port_link($entry, $entry['port_label_short']) . ' ' . $port_error;
        }
        $device_link = generate_device_link($entry);
        $string .= '  <tr>' . PHP_EOL;
        if ($list['device'])
        {
          $string .= '    <td class="entity" style="white-space: nowrap">' . $device_link . '</td>' . PHP_EOL;
        }
        $string .= '    <td class="entity">' . $entity_link . '</td>' . PHP_EOL;
        if ($address_type === 'ipv6') { $entry[$address_type.'_address'] = Net_IPv6::compress($entry[$address_type.'_address']); }
        $string .= '    <td>' . generate_popup_link('ip', $entry[$address_type.'_address'] . '/' . $length) . '</td>' . PHP_EOL;
        $string .= '    <td>' . $entry['ifAlias'] . '</td>' . PHP_EOL;
        $string .= '  </tr>' . PHP_EOL;
      }
    }
  }

  $string .= '  </tbody>' . PHP_EOL;
  $string .= '</table>';
  $string .= generate_box_close();

  // Print pagination header
  if ($pagination) { $string = pagination($vars, $count) . $string . pagination($vars, $count); }

  // Print addresses
  echo $string;
}

// EOF
