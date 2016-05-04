<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage search
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

/// SEARCH IP ADDRESSES

list($addr, $mask) = explode('/', $queryString);

if (preg_match('/^(?:(?<both>\d+)|(?<ipv6>[\d\:abcdef]+)|(?<ipv4>[\d\.]+))$/i', $addr, $matches))
{
  $query_ipv4  = 'SELECT `port_id`, `ipv4_address` AS `ip_address`, `ipv4_prefixlen` AS `ip_prefixlen` FROM `ipv4_addresses`';
  $query_ipv4 .= ' WHERE `ipv4_address` LIKE ?';
  $query_ipv6  = 'SELECT `port_id`, `ipv6_compressed` AS `ip_address`, `ipv6_prefixlen` AS `ip_prefixlen` FROM `ipv6_addresses`';
  $query_ipv6 .= ' WHERE (`ipv6_address` LIKE ? OR `ipv6_compressed` LIKE ?)';
  $query_end   = $query_permitted_port . " ORDER BY `ip_address` LIMIT $query_limit";
  $query_param = "%$addr%";
  if (isset($matches['ipv4']))
  {
    // IPv4 only
    $results = dbFetchRows($query_ipv4 . $query_end, array($query_param));
  }
  else if (isset($matches['ipv6']))
  {
    // IPv6 only
    $results = dbFetchRows($query_ipv6 . $query_end, array($query_param, $query_param));
  } else {
    // Both
    $results_ipv4 = dbFetchRows($query_ipv4 . $query_end, array($query_param));
    $results_ipv6 = dbFetchRows($query_ipv6 . $query_end, array($query_param, $query_param));
    if ((count($results_ipv4) + count($results_ipv6)) > $query_limit)
    {
      // Ya.. it's not simple
      $count_ipv4 = $query_limit - min(count($results_ipv6), intval($query_limit/2));
      $results_ipv4 = array_slice($results_ipv4, 0, $count_ipv4);
      $results_ipv6 = array_slice($results_ipv6, 0, $query_limit - $count_ipv4);
    }
    $results = array_merge($results_ipv4, $results_ipv6);
  }
  
} else {
  $results = array();
}

if (count($results))
{
  foreach ($results as $result)
  {
    $port = get_port_by_id_cache($result['port_id']);
    $device = device_by_id_cache($port['device_id']);

    $descr = $device['hostname'].' | '.rewrite_ifname($port['port_label']);

    $name = $result['ip_address'].'/'.$result['ip_prefixlen'];
    if (strlen($name) > 35) { $name = substr($name, 0, 35) . "..."; }

    /// FIXME: once we have alerting, colour this to the sensor's status
    $tab_colour = '#194B7F'; // FIXME: This colour pulled from functions.inc.php humanize_device, maybe set it centrally in definitions?

    $ip_search_results[] = array(
      'url'    => generate_port_url($port),
      'name'   => $name,
      'colour' => $tab_colour,
      'icon'   => '<i class="oicon-magnifier-zoom-actual"></i>',
      'data'   => array(
        '',
        escape_html($descr)),
    );

  }

  // FIXME after array-ization, we're missing "on x ports"; is this important? need to amend the "framework" a little, then.
  // Counter data came from: foreach ($results as $result) {$addr_ports[$result['port_id']][] = $result; }
  // echo('<li class="nav-header">IPs found: '.count($results).' (on '.count($addr_ports).' ports)</li>');

  $search_results['ip-addresses'] = array('descr' => 'IPs found', 'results' => $ip_search_results);
}

// EOF
