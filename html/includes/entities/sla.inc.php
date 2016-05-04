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

function generate_sla_query($vars)
{
  $sql = 'SELECT * FROM `slas` ';
  $sql .= ' LEFT JOIN `slas-state` USING (`sla_id`)';
  $sql .= ' WHERE `deleted` = 0';

  // Build query
  foreach($vars as $var => $value)
  {
    switch ($var)
    {
      case "group":
      case "group_id":
        $values = get_group_entities($value);
        $sql .= generate_query_values($values, 'slas.sla_id');
        break;
      case "device":
      case "device_id":
        $sql .= generate_query_values($value, 'slas.device_id');
        break;
      case "id":
        $sql .= generate_query_values($value, 'slas.sla_id');
        break;
      case "owner":
        $sql .= generate_query_values($value, 'slas.sla_owner');
        break;
      case "rtt_type":
        $sql .= generate_query_values($value, 'slas.rtt_type');
        break;
      case "event":
        $sql .= generate_query_values($value, 'rtt_event');
        break;
    }
  }
  $sql .= $GLOBALS['cache']['where']['devices_permitted'];

  return $sql;
}

function print_sla_table_header($vars)
{
  if ($vars['view'] == "graphs" || isset($vars['graph']) || isset($vars['id']))
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
    'owner'       => array('Owner', 'style="width: 180px;"'),
    'type'        => array('Type', 'style="width: 100px;"'),
                     array('History', 'style="width: 100px;"'),
    'last_change' => array('Last&nbsp;changed', 'style="width: 80px;"'),
    'event'       => array('Event', 'style="width: 60px; text-align: right;"'),
    'sense'       => array('Sense', 'style="width: 100px; text-align: right;"'),
    'rtt'         => array('RTT', 'style="width: 60px;"'),
  );

  if ($vars['page'] == "device" || $vars['popup'] == TRUE )
  {
    unset($cols['device']);
  }

  echo(get_table_header($cols, $vars));
  echo('<tbody>' . PHP_EOL);

}

function print_sla_table($vars)
{
  $sql = generate_sla_query($vars);

  $slas = array();
  foreach(dbFetchRows($sql) as $sla)
  {
    if (isset($GLOBALS['cache']['devices']['id'][$sla['device_id']]))
    {
      $sla['hostname'] = $GLOBALS['cache']['devices']['id'][$sla['device_id']]['hostname'];
      $slas[] = $sla;
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
      $slas = array_sort_by($slas, 'hostname', $sort_order, SORT_STRING);
      break;
    case 'descr':
      $slas = array_sort_by($slas, 'sla_index', $sort_order, SORT_STRING, 'sla_tag', $sort_order, SORT_STRING);
      break;
    case 'owner':
      $slas = array_sort_by($slas, 'sla_owner', $sort_order, SORT_STRING);
      break;
    case 'type':
      $slas = array_sort_by($slas, 'rtt_type',  $sort_order, SORT_STRING);
      break;
    case 'event':
      $slas = array_sort_by($slas, 'rtt_event', $sort_order, SORT_STRING);
      break;
    case 'sense':
      $slas = array_sort_by($slas, 'rtt_sense', $sort_order, SORT_STRING);
      break;
    case 'last_change':
      $slas = array_sort_by($slas, 'rtt_last_change', $sort_neg, SORT_NUMERIC);
      break;
    case 'rtt':
      $slas = array_sort_by($slas, 'rtt_value', $sort_order, SORT_NUMERIC);
      break;
    default:
      // Not sorted
  }
  $slas_count = count($slas);

  // Pagination
  $pagination_html = pagination($vars, $slas_count);
  echo $pagination_html;

  if ($vars['pageno'])
  {
    $slas = array_chunk($slas, $vars['pagesize']);
    $slas = $slas[$vars['pageno'] - 1];
  }
  // End Pagination

  echo generate_box_open();

  print_sla_table_header($vars);

  foreach($slas as $sla)
  {
    print_sla_row($sla, $vars);
  }

  echo '</tbody></table>';

  echo generate_box_close();

  echo $pagination_html;
}

function humanize_sla(&$sla)
{
  if (isset($sla['humanized'])) { return; }

  if ($sla['sla_status'] != 'active' || $sla['rtt_event'] == 'ignore')
  {
    $sla['row_class'] = 'ignore';
  }
  else if ($sla['rtt_event'] == 'alert')
  {
    $sla['row_class'] = 'error';
  }
  else if ($sla['rtt_event'] != 'ok')
  {
    $sla['row_class'] = 'warning';
  }

  $device = &$GLOBALS['cache']['devices']['id'][$sla['device_id']];
  if (isset($device['status']) && !$device['status'])
  {
    $sla['row_class']     = 'error';
  }
  else if (isset($device['disabled']) && $device['disabled'])
  {
    $sla['row_class']     = 'ignore';
  }

  if (!empty($sla['sla_graph']))
  {
    $sla['graph_type'] = "sla_" . $sla['sla_graph'];
  } else {
    if (stripos($sla['rtt_type'], 'jitter') !== FALSE)
    {
      $sla['graph_type'] = "sla_jitter";
    } else {
      $sla['graph_type'] = "sla_echo";
    }
  }

  if (isset($GLOBALS['config']['sla_type_labels'][$sla['rtt_type']]))
  {
    $sla['rtt_label'] = $GLOBALS['config']['sla_type_labels'][$sla['rtt_type']];
  } else {
    $sla['rtt_label'] = nicecase($sla['rtt_type']);
  }

  if (is_numeric($sla['rtt_value']))
  {
    if ($sla['rtt_value'] > 950)
    {
      $sla['human_value'] = round($sla['rtt_value'] / 1000, 2);
      $sla['human_unit']  = 's';
    } else {
      $sla['human_value'] = $sla['rtt_value'];
      $sla['human_unit']  = 'ms';
    }
    $sla['human_value'] = str_replace('.00', '', $sla['human_value']);
  }

  /*
  // FIXME, add table columns in discovery time 'rtt_high', 'rtt_warning'
  if ($sla['rtt_value'] > 200)
  {
    $sla['sla_class'] = 'label label-error';
  }
  else if ($sla['rtt_value'] > 80)
  {
    $sla['sla_class'] = 'label label-warning';
  } else {
    $sla['sla_class'] = 'label';
  }
  */

  if ($sla['rtt_event'] == 'ok')
  {
    $sla['sla_class'] = 'label';
    $sla['rtt_class'] = 'label label-success';
  }
  else if ($sla['rtt_event'] == 'alert')
  {
    $sla['sla_class'] = 'label label-error';
    $sla['rtt_class'] = 'label label-error';
  }
  else if ($sla['rtt_event'] == 'ignore')
  {
    $sla['sla_class'] = 'label';
    $sla['rtt_class'] = 'label';
  } else {
    $sla['sla_class'] = 'label label-warning';
    $sla['rtt_class'] = 'label label-warning';
  }

  $sla['humanized'] = TRUE;
}

function print_sla_row($sla, $vars)
{
  echo generate_sla_row($sla, $vars);
}

function generate_sla_row($sla, $vars)
{
  global $config;

  humanize_sla($sla);

  $table_cols = "8";

  $graph_array = array();
  $graph_array['to'] = $config['time']['now'];
  $graph_array['id'] = $sla['sla_id'];
  $graph_array['type'] = $sla['graph_type'];
  $graph_array['legend'] = "no";
  $graph_array['width'] = 80;
  $graph_array['height'] = 20;
  $graph_array['bg'] = 'ffffff00'; # the 00 at the end makes the area transparent.
  $graph_array['from'] = $config['time']['day'];

  if ($sla['rtt_event'] && $sla['rtt_sense'])
  {
    $mini_graph = generate_graph_tag($graph_array);
  } else {
    // Do not show "Draw Error" minigraph
    $mini_graph = '';
  }

  $out =  '<tr class="' . $sla['row_class'] . '"><td class="state-marker"></td>';
  if ($vars['page'] != "device" && $vars['popup'] != TRUE )
  {
    $out .= '<td class="entity">' . generate_device_link($sla) . '</td>';
    $table_cols++;
  }
  $out .= '<td class="entity">'. generate_entity_link('sla', $sla) .'</td>';
  $out .= '<td>'. $sla['sla_owner'] .'</td>';
  $out .= '<td>'. $sla['rtt_label'] .'</td>';
  $out .= '<td>' . generate_entity_link('sla', $sla, $mini_graph, NULL, FALSE) . '</td>';
  $out .= '<td style="white-space: nowrap">' . generate_tooltip_link(NULL, formatUptime(($config['time']['now'] - $sla['rtt_last_change']), 'short-2') . ' ago', format_unixtime($sla['rtt_last_change'])) . '</td>';
  $out .= '<td style="text-align: right;"><strong><span class="' . $sla['rtt_class'] . '">' . $sla['rtt_event'] . '</span></strong></td>';
  $out .= '<td style="text-align: right;"><strong><span class="' . $sla['rtt_class'] . '">' . $sla['rtt_sense'] . '</span></strong></td>';
  $out .= '<td><span class="' . $sla['sla_class'] . '">' . $sla['human_value'] . $sla['human_unit'] . '</span></td>';
  $out .= '</tr>';

  if ($vars['graph'] || $vars['view'] == "graphs" || $vars['id'] == $sla['sla_id'])
  {
    // If id set in vars, display only specific graphs
    $graph_array = array();
    $graph_array['type'] = $sla['graph_type'];
    $graph_array['id'] = $sla['sla_id'];

    $out .= '<tr class="' . $sla['row_class'] . '">';
    $out .= '  <td class="state-marker"></td>';
    $out .= '  <td colspan="'.$table_cols.'">';
    $out .= generate_graph_row($graph_array, TRUE);
    $out .= '  </td>';
    $out .= '</tr>';
  }

  return $out;
}

// EOF
