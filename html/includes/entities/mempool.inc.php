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

function build_mempool_query($vars)
{

  global $config, $cache;

  $sql = 'SELECT *, `mempools`.`mempool_id` AS `mempool_id` FROM `mempools`';
  $sql .= ' LEFT JOIN `mempools-state` ON `mempools`.`mempool_id` = `mempools-state`.`mempool_id`';
  $sql .= ' WHERE 1' . generate_query_permitted(array('device'));

  // Build query
  foreach ($vars as $var => $value)
  {
    switch ($var)
    {
      case "group":
      case "group_id":
        $values = get_group_entities($value);
        $sql .= generate_query_values($values, 'mempools.mempool_id');
        break;
      case "device":
      case "device_id":
        $sql .= generate_query_values($value, 'mempools.device_id');
        break;

    }
  }

  return $sql;
}


function print_mempool_table($vars)
{

  global $cache;

  $sql = build_mempool_query($vars);

  $mempools = array();
  foreach (dbFetchRows($sql) as $mempool)
  {
    if (isset($cache['devices']['id'][$mempool['device_id']]))
    {
      $mempool['hostname'] = $cache['devices']['id'][$mempool['device_id']]['hostname'];
      $mempool['html_row_class'] = $cache['devices']['id'][$mempool['device_id']]['html_row_class'];
      $mempools[] = $mempool;
    }
  }
  $mempools = array_sort_by($mempools, 'hostname', SORT_ASC, SORT_STRING, 'mempool_descr', SORT_ASC, SORT_STRING);
  $mempools_count = count($mempools);

  // Pagination
  $pagination_html = pagination($vars, $mempools_count);
  echo $pagination_html;

  if ($vars['pageno'])
  {
    $mempools = array_chunk($mempools, $vars['pagesize']);
    $mempools = $mempools[$vars['pageno'] - 1];
  }
  // End Pagination

  echo generate_box_open();

  print_mempool_table_header($vars);

  foreach ($mempools as $mempool)
  {
    print_mempool_row($mempool, $vars);
  }

  echo("</table>");

  echo generate_box_close();

  echo $pagination_html;

}

function print_mempool_table_header($vars)
{

  if ($vars['view'] == "graphs")
  {
    $stripe_class = "table-striped-two";
  }
  else
  {
    $stripe_class = "table-striped";
  }

  echo '<table class="table ' . $stripe_class . '  table-condensed">';
  echo '  <thead>';
  echo '    <tr>';
  echo '      <th class="state-marker"></th>';
  echo '      <th style="width: 1px;"></th>';
  if ($vars['page'] != "device")
  {
    echo '      <th style="width: 200px;">Device</th>';
  }
  echo '      <th>Memory</th>';
  echo '      <th style="width: 100px;"></th>';
  echo '      <th style="width: 280px;">Usage</th>';
  echo '      <th style="width: 50px;">Used</th>';
  echo '    </tr>';
  echo '  </thead>';

}

function print_mempool_row($mempool, $vars)
{
 echo generate_mempool_row($mempool, $vars);
}

function generate_mempool_row($mempool, $vars)
{

  global $config;

  $table_cols = 7;
  if ($vars['page'] != "device" && $vars['popup'] != TRUE)  { $table_cols++; } // Add a column for device.

  $graph_array = array();
  $graph_array['to'] = $config['time']['now'];
  $graph_array['id'] = $mempool['mempool_id'];
  $graph_array['type'] = "mempool_usage";
  $graph_array['legend'] = "no";

  $link_array = $graph_array;
  $link_array['page'] = "graphs";
  unset($link_array['height'], $link_array['width'], $link_array['legend']);
  $link_graph = generate_url($link_array);

  $link = generate_url(array("page" => "device", "device" => $mempool['device_id'], "tab" => "health", "metric" => 'mempool'));

  $overlib_content = generate_overlib_content($graph_array, $mempool['hostname'] . " - " . $mempool['mempool_descr']);

  $graph_array['width'] = 80;
  $graph_array['height'] = 20;
  $graph_array['bg'] = 'ffffff00'; # the 00 at the end makes the area transparent.
  $graph_array['from'] = $config['time']['day'];
  $mini_graph = generate_graph_tag($graph_array);

  if ($mempool['mempool_total'] != '100')
  {
    $total = formatStorage($mempool['mempool_total']);
    $used = formatStorage($mempool['mempool_used']);
    $free = formatStorage($mempool['mempool_free']);
  }
  else
  {
    // If total == 100, than memory not have correct size and uses percents only
    $total = $mempool['mempool_total'] . '%';
    $used = $mempool['mempool_used'] . '%';
    $free = $mempool['mempool_free'] . '%';
  }

  $background = get_percentage_colours($mempool['mempool_perc']);

  $mempool['html_row_class'] = $background['class'];

  $row .= '<tr class="' . $mempool['html_row_class'] . '">
            <td class="state-marker"></td>
            <td width="1px"></td>';
  if ($vars['page'] != "device" && $vars['popup'] != TRUE)
  {
    $row .= '<td class="entity">' . generate_device_link($mempool) . '</td>';
  }

  $row .= '<td class="entity">' . generate_entity_link('mempool', $mempool) . '</td>
        <td>' . overlib_link($link_graph, $mini_graph, $overlib_content) . '</td>
        <td><a href="' . $link_graph . '">
          ' . print_percentage_bar(400, 20, $mempool['mempool_perc'], $used . '/' . $total . ' (' . $mempool['mempool_perc'] . '%)', "ffffff", $background['left'], $free . ' (' . (100 - $mempool['mempool_perc']) . '%)', "ffffff", $background['right']) . '
          </a>
        </td>
        <td>' . $mempool['mempool_perc'] . '%</td>
      </tr>
   ';

  if ($vars['view'] == "graphs")
  {
    $vars['graph'] = "usage";
  }

  if ($vars['graph'])
  {
    $row .= '<tr class="' . $mempool['html_row_class'] . '">';
    $row .= '<td class="state-marker"></td>';
    $row .= '<td colspan="' . $table_cols . '">';

    unset($graph_array['height'], $graph_array['width'], $graph_array['legend']);
    $graph_array['to'] = $config['time']['now'];
    $graph_array['id'] = $mempool['mempool_id'];
    $graph_array['type'] = 'mempool_' . $vars['graph'];

    print_graph_row($graph_array, TRUE);

    $row .= '</td></tr>';
  } # endif graphs

  return $row;

}
