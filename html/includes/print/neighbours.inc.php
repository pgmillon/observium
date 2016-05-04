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
 * Display neighbours.
 *
 * Display pages with device neighbours in some formats.
 * Examples:
 * print_neighbours() - display all neighbours from all devices
 * print_neighbours(array('pagesize' => 99)) - display 99 neighbours from all device
 * print_neighbours(array('pagesize' => 10, 'pageno' => 3, 'pagination' => TRUE)) - display 10 neighbours from page 3 with pagination header
 * print_neighbours(array('pagesize' => 10, 'device' = 4)) - display 10 neighbours for device_id 4
 *
 * @param array $vars
 * @return none
 *
 */
function print_neighbours($vars)
{
  // Get neighbours array
  $neighbours = get_neighbours_array($vars);

  if (!$neighbours['count'])
  {
    // There have been no entries returned. Print the warning.
    print_warning('<h4>No neighbours found!</h4>');
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

    $string = generate_box_open($vars['header']);

    $string .= '<table class="table  table-striped table-hover table-condensed">' . PHP_EOL;

    $cols = array(
                     array(NULL, 'class="state-marker"'),
      'device_a' => 'Local Device',
      'port_a'   => 'Local Port',
      'NONE'     => NULL,
      'device_b' => 'Remote Device',
      'port_b'   => 'Remote Port',
      'protocol' => 'Protocol',
    );
    if (!$list['device']) { unset($cols[0], $cols['device_a']); }
    $string .= get_table_header($cols, $vars);

    $string .= '  <tbody>' . PHP_EOL;

    foreach ($neighbours['entries'] as $entry)
    {
      $string .= '  <tr class="' . $entry['row_class'] . '">' . PHP_EOL;

      if ($list['device'])
      {
        $string .=  '   <td class="state-marker"></td>';
        $string .= '    <td class="entity">' . generate_device_link($entry, NULL, array('tab' => 'ports', 'view' => 'neighbours')) . '</td>' . PHP_EOL;
      }
      $string .= '    <td><span class="entity">'.generate_port_link($entry) . '</span><br />' . $entry['ifAlias'] . '</td>' . PHP_EOL;
      $string .= '    <td><i class="icon-resize-horizontal text-success"></i></td>' . PHP_EOL;
      if (is_numeric($entry['remote_port_id']) && $entry['remote_port_id'])
      {
        $remote_port   = get_port_by_id_cache($entry['remote_port_id']);
        $remote_device = device_by_id_cache($remote_port['device_id']);
        $string .= '    <td><span class="entity">' . generate_device_link($remote_device) . '</span><br />' . $remote_device['hardware'] . '</td>' . PHP_EOL;
        $string .= '    <td><span class="entity">' . generate_port_link($remote_port) . '</span><br />' . $remote_port['ifAlias'] . '</td>' . PHP_EOL;
      } else {
        $string .= '    <td><span class="entity">' . $entry['remote_hostname'] . '</span><br />' . $entry['remote_platform'] . '</td>' . PHP_EOL;
        $string .= '    <td><span class="entity">' . $entry['remote_port'] . '</span></td>' . PHP_EOL;
      }
      $string .= '    <td>' . strtoupper($entry['protocol']) . '</td>' . PHP_EOL;
      $string .= '  </tr>' . PHP_EOL;
    }

    $string .= '  </tbody>' . PHP_EOL;
    $string .= '</table>';

    $string .= generate_box_close();

    // Print pagination header
    if ($neighbours['pagination_html']) { $string = $neighbours['pagination_html'] . $string . $neighbours['pagination_html']; }

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
function get_neighbours_array(&$vars)
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
  $where = ' WHERE `active` = 1 ';
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
        case 'device_b':
          $where .= generate_query_values($value, 'remote_hostname');
          break;
        case 'port_b':
          $where .= generate_query_values($value, 'remote_port');
          break;
        case 'protocol':
          $where .= generate_query_values($value, 'protocol');
          break;
        case 'platform':
          $where .= generate_query_values($value, 'remote_platform');
          break;
        case 'version':
          $where .= generate_query_values($value, 'remote_version');
          break;
        case 'remote_port_id':
          if ($value != 0)
          {
            $where .= ' AND `remote_port_id` != 0';
          } else {
            $where .= generate_query_values($value, 'remote_port_id');
          }
          break;        
      }
    }
  }

  // Show neighbours only for permitted devices and ports
  $query_permitted = $GLOBALS['cache']['where']['ports_permitted'];

  $query = 'FROM `neighbours` LEFT JOIN `ports` USING(`port_id`) ';
  $query .= $where . $query_permitted;
  //$query_count = 'SELECT COUNT(*) '.$query;

  $query = 'SELECT * '.$query;
  //$query .= ' ORDER BY `event_id` DESC ';
  //$query .= " LIMIT $start,$pagesize";

  // Query neighbours
  $array['entries'] = dbFetchRows($query, $param);
  foreach ($array['entries'] as &$entry)
  {
    $device = &$GLOBALS['cache']['devices']['id'][$entry['device_id']];
    if ((isset($device['status']) && !$device['status']))
    {
      $entry['row_class']     = 'error';
    }
    else if (isset($device['disabled']) && $device['disabled'])
    {
      $entry['row_class']     = 'ignore';
    }
    $entry['hostname']  = $device['hostname'];
    //$entry['row_class'] = $device['row_class'];
  }

  // Sorting
  // FIXME. Sorting can be as function, but in must before print_table_header and after get table from db
  switch ($vars['sort_order'])
  {
    case 'desc':
      $sort_order = SORT_DESC;
      $sort_neg   = SORT_ASC;
      break;
    case 'reset':
      unset($vars['sort'], $vars['sort_order']);
      // no break here
    default:
      $sort_order = SORT_ASC;
      $sort_neg   = SORT_DESC;
  }
  switch ($vars['sort'])
  {
    case 'device_a':
      $array['entries'] = array_sort_by($array['entries'], 'hostname', $sort_order, SORT_STRING);
      break;
    case 'port_a':
      $array['entries'] = array_sort_by($array['entries'], 'port_label', $sort_order, SORT_STRING);
      break;
    case 'device_b':
      $array['entries'] = array_sort_by($array['entries'], 'remote_hostname', $sort_order, SORT_STRING);
      break;
    case 'port_b':
      $array['entries'] = array_sort_by($array['entries'], 'remote_port',  $sort_order, SORT_STRING);
      break;
    case 'protocol':
      $array['entries'] = array_sort_by($array['entries'], 'protocol', $sort_order, SORT_STRING);
      break;
    default:
      // Not sorted
  }

  // Query neighbours count
  $array['count'] = count($array['entries']);
  if ($array['pagination'])
  {
    $array['pagination_html'] = pagination($vars, $array['count']);
    $array['entries'] = array_chunk($array['entries'], $vars['pagesize']);
    $array['entries'] = $array['entries'][$vars['pageno'] - 1];
  }

  // Query for last timestamp
  //$array['updated'] = dbFetchCell($query_updated, $param);

  return $array;
}

// EOF
