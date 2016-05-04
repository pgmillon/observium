<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage webui
 * @copyright  (C) 2006-2015 Adam Armstrong
 *
 */

global $status_type;

$sql  = "SELECT *, `status`.`status_id` AS `status_id` FROM `status`";
$sql .= " LEFT JOIN `status-state` ON `status`.`status_id` = `status-state`.`status_id`";
$sql .= " WHERE 1" . generate_query_permitted(array('device'));

// Groups
if (isset($vars['group']))
{
  $values = get_group_entities($vars['group']);
  $sql .= generate_query_values($values, 'status.status_id');
}

$status_list = array();
foreach (dbFetchRows($sql) as $status)
{
  if (isset($cache['devices']['id'][$status['device_id']]))
  {
    $status['hostname']       = $cache['devices']['id'][$status['device_id']]['hostname'];
    $status_list[] = $status;
  }
}
$status_list = array_sort_by($status_list, 'hostname', SORT_ASC, SORT_STRING, 'status_descr', SORT_ASC, SORT_STRING);
$status_count = count($status_list);

// Pagination
$pagination_html = pagination($vars, $status_count);
echo $pagination_html;

if ($vars['pageno'])
{
  $status_list = array_chunk($status_list, $vars['pagesize']);
  $status_list = $status_list[$vars['pageno']-1];
}
// End Pagination

if ($vars['view'] == "graphs") { $stripe_class = "table-striped-two"; } else { $stripe_class = "table-striped"; }

echo('<table class="table '.$stripe_class.' table-condensed table-bordered">'. PHP_EOL);
$cols = array(
              array(NULL,             'class="state-marker"'),
  'device' => array('Device',         'style="width: 250px;"'),
              array('Description'),
  'class'  => array('Physical Class', 'style="width: 180px;"'),
              array('History',        'style="width: 100px;"'),
  'status' => array('Status',         'style="width: 100px;"'),
);
echo(get_table_header($cols)); // , $vars); // Actually sorting is disabled now
echo('<tbody>' . PHP_EOL);

foreach ($status_list as $status)
{
  humanize_status($status);

  $alert = ($status['state_event'] == 'alert' ? 'oicon-exclamation-red' : '');

  // FIXME - make this "four graphs in popup" a function/include and "small graph" a function.
  // FIXME - DUPLICATED IN device/overview/status

  $graph_array           = array();
  $graph_array['to']     = $config['time']['now'];
  $graph_array['id']     = $status['status_id'];
  $graph_array['type']   = "status_graph";
  $graph_array['legend'] = "no";
  $graph_array['width'] = 80;
  $graph_array['height'] = 20;
  $graph_array['bg'] = 'ffffff00'; # the 00 at the end makes the area transparent.
  $graph_array['from'] = $config['time']['day'];

  $mini_graph = generate_graph_tag($graph_array);

  $status_misc = '<span class="label">'.$status['entPhysicalClass'].'</span>';


  echo('<tr class="'.$status['row_class'].'">
        <td class="state-marker"></td>
        <td class="entity">' . generate_device_link($status) . '</td>
        <td class="entity">' . generate_entity_link('status', $status) . '</td>
        <td><span class="label">' . $status['entPhysicalClass'] . '</span></td>
        <td>'.generate_entity_link('status', $status, $mini_graph, NULL, FALSE).'</td>
        <td style="text-align: right;"><strong>'.overlib_link($link, '<span class="'.$status['state_class'].'">' . $status['status_name'] . '</span>', $overlib_content).'</strong></td>
        </tr>' . PHP_EOL);

  if ($vars['view'] == "graphs")
  {
    echo('<tr class="' . $status['row_class'] . '">
      <td class="state-marker"></td>
      <td colspan=5>');

    unset($graph_array['height'], $graph_array['width'], $graph_array['legend']);
    $graph_array['to']     = $config['time']['now'];
    $graph_array['id']     = $status['status_id'];
    $graph_array['type']   = "status_graph";

    print_graph_row($graph_array, TRUE);

    echo("</td></tr>");
  } # endif graphs
}

echo("</tbody>");
echo("</table>");

echo $pagination_html;

// EOF
