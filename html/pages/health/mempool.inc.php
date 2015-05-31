<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage webui
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

$graph_type = "mempool_usage";

$sql  = 'SELECT *, `mempools`.`mempool_id` AS `mempool_id` FROM `mempools`';
$sql .= ' LEFT JOIN `mempools-state` ON `mempools`.`mempool_id` = `mempools-state`.`mempool_id`';
$sql .= ' WHERE 1' . generate_query_permitted(array('device'));

// Groups
if (isset($vars['group']))
{
  $values = get_group_entities($vars['group']);
  $sql .= generate_query_values($values, 'mempools.mempool_id');
}

$mempools = array();
foreach (dbFetchRows($sql) as $mempool)
{
  if (isset($cache['devices']['id'][$mempool['device_id']]))
  {
    $mempool['hostname']       = $cache['devices']['id'][$mempool['device_id']]['hostname'];
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
  $mempools = $mempools[$vars['pageno']-1];
}
// End Pagination

if ($vars['view'] == "graphs") { $stripe_class = "table-striped-two"; } else { $stripe_class = "table-striped"; }

echo('<table class="table '.$stripe_class.' table-bordered table-condensed">');
echo('  <thead>');
echo('    <tr>');
echo('      <th style="width: 250px;">Device</th>');
echo('      <th>Memory</th>');
echo('      <th style="width: 100px;"></th>');
echo('      <th style="width: 280px;">Usage</th>');
echo('      <th style="width: 50px;">Used</th>');
echo('    </tr>');
echo('  </thead>');

foreach ($mempools as $mempool)
{
  $graph_array           = array();
  $graph_array['to']     = $config['time']['now'];
  $graph_array['id']     = $mempool['mempool_id'];
  $graph_array['type']   = $graph_type;
  $graph_array['legend'] = "no";

  $link_array = $graph_array;
  $link_array['page'] = "graphs";
  unset($link_array['height'], $link_array['width'], $link_array['legend']);
  $link_graph = generate_url($link_array);

  $link = generate_url( array("page" => "device", "device" => $mempool['device_id'], "tab" => "health", "metric" => 'mempool'));

  $overlib_content = generate_overlib_content($graph_array, $mempool['hostname'] ." - " . $mempool['mempool_descr'], NULL);

  $graph_array['width'] = 80; $graph_array['height'] = 20; $graph_array['bg'] = 'ffffff00'; # the 00 at the end makes the area transparent.
  $graph_array['from'] = $config['time']['day'];
  $mini_graph = generate_graph_tag($graph_array);

  if ($mempool['mempool_total'] != '100')
  {
    $total = formatStorage($mempool['mempool_total']);
    $used  = formatStorage($mempool['mempool_used']);
    $free  = formatStorage($mempool['mempool_free']);
  } else {
    // If total == 100, than memory not have correct size and uses percents only
    $total = $mempool['mempool_total'].'%';
    $used  = $mempool['mempool_used'].'%';
    $free  = $mempool['mempool_free'].'%';
  }

  $background = get_percentage_colours($mempool['mempool_perc']);

  echo('<tr class="'.$mempool['html_row_class'].'">
        <td class="entity">' . generate_device_link($mempool) . '</td>
        <td>'.overlib_link($link, htmlentities($mempool['mempool_descr']), $overlib_content).'</td>
        <td>'.overlib_link($link_graph, $mini_graph, $overlib_content).'</td>
        <td><a href="'.$link_graph.'">
          '.print_percentage_bar(400, 20, $mempool['mempool_perc'], $used.'/'.$total.' ('.$mempool['mempool_perc'].'%)', "ffffff", $background['left'], $free.' ('.(100 - $mempool['mempool_perc']).'%)', "ffffff", $background['right']).'
          </a>
        </td>
        <td>'.$mempool['mempool_perc'].'%</td>
      </tr>
   ');

  if ($vars['view'] == "graphs")
  {
    echo("<tr><td colspan=5>");

    unset($graph_array['height'], $graph_array['width'], $graph_array['legend']);
    $graph_array['to']     = $config['time']['now'];
    $graph_array['id']     = $mempool['mempool_id'];
    $graph_array['type']   = $graph_type;

    print_graph_row($graph_array);

    echo("</td></tr>");
  } # endif graphs
}

echo("</table>");

echo $pagination_html;

// EOF
