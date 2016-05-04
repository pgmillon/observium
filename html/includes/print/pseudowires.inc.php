<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage web
 * @copyright  (C) 2006-2015 Adam Armstrong
 *
 */

/**
 * Display pseudowires.
 *
 * Display pages with device pseudowires in some formats.
 * Examples:
 * print_pseudowires() - display all pseudowires from all devices
 * print_pseudowires(array('pagesize' => 99)) - display 99 pseudowires from all device
 * print_pseudowires(array('pagesize' => 10, 'pageno' => 3, 'pagination' => TRUE)) - display 10 pseudowires from page 3 with pagination header
 * print_pseudowires(array('pagesize' => 10, 'device' = 4)) - display 10 pseudowires for device_id 4
 *
 * @param array $vars
 * @return none
 *
 */
function print_pseudowires($vars)
{
  // Get pseudowires array
  $events = get_pseudowires_array($vars);

  if (!$events['count'])
  {
    // There have been no entries returned. Print the warning.
    print_warning('<h4>No pseudowires found!</h4>');
  } else {
    // Entries have been returned. Print the table.
    $list = array('device' => FALSE);
    if ($vars['page'] != 'device') { $list['device'] = TRUE; }
    if (in_array($vars['graph'], array('bits', 'upkts', 'nupkts', 'pktsize', 'percent', 'errors', 'etherlike', 'fdb_count')))
    {
      $graph_types = array($vars['graph']);
    } else {
      $graph_types = array('bits', 'upkts', 'errors');
    }

    $string = '<table class="table table-bordered table-striped table-hover table-condensed-more">' . PHP_EOL;

    $cols = array(
      'id'       => 'PW ID',
      'type'     => array('Type', 'style="width: 5%;"'),
      'device_a' => 'Local Device',
      'port_a'   => 'Local Port',
      'NONE'     => NULL,
      'device_b' => 'Remote Device',
      'port_b'   => 'Remote Port',
    );
    if (!$list['device']) { unset($cols['device_a']); }
    $string .= get_table_header($cols); //, $vars); // Currently sorting is not available

    $string .= '  <tbody>' . PHP_EOL;

    foreach ($events['entries'] as $entry)
    {
      $string .= '  <tr>' . PHP_EOL;
      $string .= '    <td style="font-size: 18px; padding: 4px; width: 5%;">' . $entry['pwID'] . '</td>' . PHP_EOL;
      $string .= '    <td>' . strtoupper($entry['pwPsnType']) . '<br />' . nicecase($entry['pwType']) . '</td>' . PHP_EOL;

      if ($list['device'])
      {
        $local_dev = device_by_id_cache($entry['device_id']);
        $string .= '    <td class="entity">' . generate_device_link($local_dev, short_hostname($local_dev['hostname']), array('tab' => 'pseudowires')) . '</td>' . PHP_EOL;
      }
      $local_if = get_port_by_id_cache($entry['port_id']);
      $string .= '    <td class="entity">' . generate_port_link($local_if) . '<br />' . $local_if['ifAlias'];
      if ($vars['view'] == "minigraphs")
      {
        $string .= '<br />';
        if ($local_if)
        {
          $local_if['width'] = "150";
          $local_if['height'] = "30";
          $local_if['from'] = $GLOBALS['config']['time']['day'];
          $local_if['to'] = $GLOBALS['config']['time']['now'];
          foreach ($graph_types as $graph_type)
          {
            $local_if['graph_type'] = "port_".$graph_type;
            $string .= generate_port_thumbnail($local_if, FALSE);
          }
        }
      }
      $string .= '</td>' . PHP_EOL;

      $string .= '    <td style="width: 3%;"><i class="oicon-arrow_right"></i></td>' . PHP_EOL;

      if (is_numeric($entry['peer_port_id']))
      {
        $peer_if  = get_port_by_id_cache($entry['peer_port_id']);
        $peer_dev = device_by_id_cache($peer_if['device_id']);
        $string .= '    <td class="entity">' . generate_device_link($peer_dev, short_hostname($peer_dev['hostname']), array('tab' => 'pseudowires')) . '</br>' . $entry['peer_addr'] . '</td>' . PHP_EOL;
        $string .= '    <td class="entity">' . generate_port_link($peer_if) . '<br />' . $peer_if['ifAlias'];
        if ($vars['view'] == "minigraphs")
        {
          $string .= '<br />';
          if ($peer_if)
          {
            $peer_if['width'] = "150";
            $peer_if['height'] = "30";
            $peer_if['from'] = $GLOBALS['config']['time']['day'];
            $peer_if['to'] = $GLOBALS['config']['time']['now'];
            foreach ($graph_types as $graph_type)
            {
              $peer_if['graph_type'] = "port_".$graph_type;
              $string .= generate_port_thumbnail($peer_if, FALSE);
            }
          }
        }
        $string .= '</td>' . PHP_EOL;
      } else {
        // Show only peer address (and PTR name)
        $string .= '    <td class="entity">' . $entry['peer_rdns'] . '<br />' . $entry['peer_addr'] . '</td>' . PHP_EOL;
        $string .= '    <td class="entity"><br />' . $entry['pwRemoteIfString'] . '</td>' . PHP_EOL;
      }
    }

    $string .= '  </tbody>' . PHP_EOL;
    $string .= '</table>';

    // Print pagination header
    if ($events['pagination_html']) { $string = $events['pagination_html'] . $string . $events['pagination_html']; }

    // Print
    echo $string;
  }
}

/**
 * Params:
 *
 * pagination, pageno, pagesize
 * device, port
 */
function get_pseudowires_array($vars)
{
  $array = array();

  // With pagination? (display page numbers in header)
  $array['pagination'] = (isset($vars['pagination']) && $vars['pagination']);
  pagination($vars, 0, TRUE); // Get default pagesize/pageno
  $array['pageno']   = $vars['pageno'];
  $array['pagesize'] = $vars['pagesize'];
  $start    = $array['pagesize'] * $array['pageno'] - $array['pagesize'];
  $pagesize = $array['pagesize'];

  // Begin query generate
  $param = array();
  $where = ' WHERE 1 ';
  foreach ($vars as $var => $value)
  {
    if ($value != '')
    {
      switch ($var)
      {
        case 'device':
        case 'device_a':
          $where .= generate_query_values($value, 'device_id');
          break;
        case 'port':
        case 'port_a':
          $where .= generate_query_values($value, 'port_id');
          break;
        //case 'type':
        //  $where .= generate_query_values($value, 'type');
        //  break;
        //case 'message':
        //  $where .= generate_query_values($value, 'message', '%LIKE%');
        //  break;
      }
    }
  }

  // Show pseudowires only for permitted devices and ports
  $query_permitted = generate_query_permitted(array('device', 'port'));

  $query = 'FROM `pseudowires` ';
  $query .= $where . $query_permitted;
  $query_count = 'SELECT COUNT(*) '.$query;
  //$query_updated = 'SELECT MAX(`timestamp`) '.$query;

  $query = 'SELECT * '.$query;
  //$query .= ' ORDER BY `event_id` DESC ';
  $query .= " LIMIT $start,$pagesize";

  // Query pseudowires
  foreach (dbFetchRows($query, $param) as $entry)
  {
    if ($entry['peer_addr'])
    {
      $peer_addr = $entry['peer_addr'];
    }
    else if ($entry['pwMplsPeerLdpID'])
    {
      $peer_addr = preg_replace('/:\d+$/', '', $pw['pwMplsPeerLdpID']);
    }
    $peer_addr_type = get_ip_version($peer_addr);
    if ($peer_addr_type)
    {
      if ($peer_addr_type == 6)
      {
        $peer_addr = Net_IPv6::uncompress($peer_addr, TRUE);
      }
      $peer_addr_type          = 'ipv'.$peer_addr_type;
      $entry['peer_addr']      = $peer_addr;
      $entry['peer_addr_type'] = $peer_addr_type;
    } else {
      continue; // Peer address unknown
    }
    if (!is_array($cache_pseudowires['ips'][$peer_addr]))
    {
      $cache_pseudowires['ips'][$peer_addr]['port_id'] = dbFetchCell('SELECT `port_id` FROM `'.$peer_addr_type.'_addresses` WHERE `'.$peer_addr_type.'_address` = ? '.generate_query_values($GLOBALS['cache']['ports']['pseudowires'], 'port_id').' LIMIT 1;', array($peer_addr));
      if (!is_numeric($cache_pseudowires['ips'][$peer_addr]['port_id']))
      {
        $cache_pseudowires['ips'][$peer_addr]['port_id'] = dbFetchCell('SELECT `port_id` FROM `'.$peer_addr_type.'_addresses` WHERE `'.$peer_addr_type.'_address` = ? '.$GLOBALS['cache']['where']['ports_permitted'].' LIMIT 1;', array($peer_addr));
        if (is_numeric($cache_pseudowires['ips'][$peer_addr]['port_id']))
        {
          // If we found port on remote device, than both devices in DB and will try to fix real port
          $peer_port_tmp = get_port_by_id_cache($cache_pseudowires['ips'][$peer_addr]['port_id']);
          $peer_port_fix = dbFetchCell('SELECT `port_id` FROM `pseudowires` WHERE `device_id` = ? AND `pwID` = ? LIMIT 1;', array($peer_port_tmp['device_id'], $entry['pwID']));
          if (is_numeric($peer_port_fix))
          {
            $cache_pseudowires['ips'][$peer_addr]['port_id'] = $peer_port_fix;
          }
        }
      }
      //$cache_pseudowires['ips'][$peer_addr]['host'] = $entry['reverse_dns'];
    }
    $entry['peer_port_id']   = $cache_pseudowires['ips'][$peer_addr]['port_id'];
    //$entry['peer_port']      = get_port_by_id_cache($entry['peer_port_id']);
    //$entry['peer_device_id'] = $entry['peer_port']['device_id'];
    //$entry['peer_device']    = device_by_id_cache($entry['peer_device_id']);

    $array['entries'][] = $entry;
  }

  // Query pseudowires count
  if ($array['pagination'])
  {
    $array['count'] = dbFetchCell($query_count, $param);
    $array['pagination_html'] = pagination($vars, $array['count']);
  } else {
    $array['count'] = count($array['entries']);
  }

  // Query for last timestamp
  //$array['updated'] = dbFetchCell($query_updated, $param);

  return $array;
}

// EOF
