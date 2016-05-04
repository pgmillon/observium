<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package        observium
 * @subpackage     web
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

/**
 * Humanize status indicator.
 *
 * Returns a the $status array with processed information:
 * sensor_state (TRUE: state sensor, FALSE: normal sensor)
 * human_value, sensor_symbol, state_name, state_event, state_class
 *
 * @param array $status
 * @return array $status
 *
 */
// TESTME needs unit testing
function humanize_status(&$status)
{
  global $config;

  // Exit if already humanized
  if ($status['humanized']) { return; }

  switch ($status['status_event'])
  {
    case 'up':
    case 'ok':
      // FIXME -- replace up with ok to follow monitoring convention and not sound so derpy
      $status['state_class'] = 'label label-success';
      $status['row_class']   = 'up';
      break;
    case 'warning':
      $status['state_class'] = 'label label-warning';
      $status['row_class']   = 'warning';
      break;
    case 'alert':
      $status['state_class'] = 'label label-important';
      $status['row_class']   = 'error';
      break;
    case 'ignore':
      $status['state_class'] = 'label';
      $status['row_class']   = 'ignore';
      break;
    default:
      $status['state_class'] = 'label label-info';
      $status['row_class']   = '';
  }

  $device = &$GLOBALS['cache']['devices']['id'][$status['device_id']];
  if ((isset($device['status']) && !$device['status']) || (isset($device['disabled']) && $device['disabled']))
  {
    $status['row_class']     = 'error';
  }

  // Set humanized entry in the array so we can tell later
  $status['humanized'] = TRUE;
}

function generate_status_query($vars)
{
  $sql = "SELECT * FROM `status`";
  $sql .= " LEFT JOIN `status-state` USING(`status_id`)";
  $sql .= " WHERE 1";

  // Build query
  foreach($vars as $var => $value)
  {
    switch ($var)
    {
      case "group":
      case "group_id":
        $values = get_group_entities($value);
        $sql .= generate_query_values($values, 'status.status_id');
        break;
      case "device":
      case "device_id":
        $sql .= generate_query_values($value, 'status.device_id');
        break;
      case "id":
        $sql .= generate_query_values($value, 'status.status_id');
        break;
      case "class":
        $sql .= generate_query_values($value, 'status.entPhysicalClass');
        break;
      case "event":
        $sql .= generate_query_values($value, 'status_event');
        break;
    }
  }
  $sql .= $GLOBALS['cache']['where']['devices_permitted'];

  return $sql;
}


function print_status_table($vars)
{
  $sql = generate_status_query($vars);

  $status_list = array();
  foreach(dbFetchRows($sql) as $status)
  {
    if (isset($GLOBALS['cache']['devices']['id'][$status['device_id']]))
    {
      $status['hostname'] = $GLOBALS['cache']['devices']['id'][$status['device_id']]['hostname'];
      $status_list[] = $status;
    }
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
    case 'device':
      $status_list = array_sort_by($status_list, 'hostname', $sort_order, SORT_STRING);
      break;
    case 'descr':
      $status_list = array_sort_by($status_list, 'status_descr', $sort_order, SORT_STRING);
      break;
    case 'class':
      $status_list = array_sort_by($status_list, 'entPhysicalClass', $sort_order, SORT_STRING);
      break;
    case 'event':
      $status_list = array_sort_by($status_list, 'status_event', $sort_order, SORT_STRING);
      break;
    case 'status':
      $status_list = array_sort_by($status_list, 'status_name', $sort_order, SORT_STRING);
      break;
    case 'last_change':
      $status_list = array_sort_by($status_list, 'status_last_change', $sort_neg, SORT_NUMERIC);
      break;
    default:
      $status_list = array_sort_by($status_list, 'hostname', $sort_order, SORT_STRING, 'status_descr', $sort_order, SORT_STRING);
  }

  $status_count = count($status_list);

  // Pagination
  $pagination_html = pagination($vars, $status_count);
  echo $pagination_html;

  if ($vars['pageno'])
  {
    $status_list = array_chunk($status_list, $vars['pagesize']);
    $status_list = $status_list[$vars['pageno'] - 1];
  }
  // End Pagination

  echo generate_box_open();

  print_status_table_header($vars);

  foreach($status_list as $status)
  {
    print_status_row($status, $vars);
  }

  echo("</tbody></table>");

  echo generate_box_close();

  echo $pagination_html;
}

function print_status_table_header($vars)
{
  if ($vars['view'] == "graphs" || isset($vars['id']))
  {
    $stripe_class = "table-striped-two";
  } else {
    $stripe_class = "table-striped";
  }

  echo('<table class="table ' . $stripe_class . ' table-condensed ">' . PHP_EOL);
  $cols = array(
                     array(NULL, 'class="state-marker"'),
    'device'      => array('Device', 'style="width: 250px;"'),
    'descr'       => array('Description'),
    'class'       => array('Physical&nbsp;Class', 'style="width: 100px;"'),
                     array('History', 'style="width: 90px;"'),
    'last_change' => array('Last&nbsp;changed', 'style="width: 80px;"'),
    'event'       => array('Event', 'style="width: 60px; text-align: right;"'),
    'status'      => array('Status', 'style="width: 80px; text-align: right;"'),
  );

  if ($vars['page'] == "device")
  {
    unset($cols['device']);
  }

  echo(get_table_header($cols, $vars));
  echo('<tbody>' . PHP_EOL);
}

function print_status_row($status, $vars)
{
  echo generate_status_row($status, $vars);
}

function generate_status_row($status, $vars)
{
  global $config;

  $table_cols = 7;

  humanize_status($status);

  $alert = ($status['state_event'] == 'alert' ? 'oicon-exclamation-red' : '');

  // FIXME - make this "four graphs in popup" a function/include and "small graph" a function.
  // FIXME - DUPLICATED IN device/overview/status

  $graph_array = array();
  $graph_array['to'] = $config['time']['now'];
  $graph_array['id'] = $status['status_id'];
  $graph_array['type'] = "status_graph";
  $graph_array['legend'] = "no";
  $graph_array['width'] = 80;
  $graph_array['height'] = 20;
  $graph_array['bg'] = 'ffffff00'; # the 00 at the end makes the area transparent.
  $graph_array['from'] = $config['time']['day'];

  $status_misc = '<span class="label">' . $status['entPhysicalClass'] . '</span>';

  $row .= '<tr class="' . $status['row_class'] . '">
        <td class="state-marker"></td>';

  if ($vars['page'] != "device" && $vars['popup'] != TRUE)
  {
    $row .= '<td class="entity">' . generate_device_link($status) . '</td>';
    $table_cols++;
  }

  if ($status['status_event'] && $status['status_name'])
  {
    $mini_graph = generate_graph_tag($graph_array);
  } else {
    // Do not show "Draw Error" minigraph
    $mini_graph = '';
  }

  $row .= '<td class="entity">' . generate_entity_link('status', $status) . '</td>';
  if ($vars['tab'] != "overview")
  {
    $row .= '<td><span class="label">' . $status['entPhysicalClass'] . '</span></td>';
    $table_cols++;
  }
  $row .= '<td style="width: 90px; text-align: right;">' . generate_entity_link('status', $status, $mini_graph, NULL, FALSE) . '</td>';
  if ($vars['tab'] != "overview")
  {
    $row .= '<td style="white-space: nowrap">' . generate_tooltip_link(NULL, formatUptime(($config['time']['now'] - $status['status_last_change']), 'short-2') . ' ago', format_unixtime($status['status_last_change'])) . '</td>
        <td style="text-align: right;"><strong><span class="' . $status['state_class'] . '">' . $status['status_event'] . '</span></strong></td>';
    $table_cols++;
    $table_cols++;
  }
  $row .= '<td style="width: 80px; text-align: right;"><strong><span class="' . $status['state_class'] . '">' . $status['status_name'] . '</span></strong></td>
        </tr>' . PHP_EOL;

  if ($vars['view'] == "graphs")
  {
    $vars['graph'] = "status";
  }

  if ($vars['graph'] || $vars['id'] == $status['status_id'])
  {
    // If id set in vars, display only specific graphs
    $row .= '<tr class="' . $status['row_class'] . '">
      <td class="state-marker"></td>
      <td colspan="' . $table_cols . '">';

    unset($graph_array['height'], $graph_array['width'], $graph_array['legend']);
    $graph_array['to'] = $config['time']['now'];
    $graph_array['id'] = $status['status_id'];
    $graph_array['type'] = "status_graph";

    $row .= generate_graph_row($graph_array, TRUE);

    $row .= '</td></tr>';
  } # endif graphs

  return $row;

}

// EOF
