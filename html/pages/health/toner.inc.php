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

$graph_type = "toner_usage";

$sql  = 'SELECT * FROM `toner`';
$sql .= ' WHERE 1' . generate_query_permitted(array('device'));
$toners = array();
foreach (dbFetchRows($sql) as $toner)
{
  if (isset($cache['devices']['id'][$toner['device_id']]))
  {
    $toner['hostname']       = $cache['devices']['id'][$toner['device_id']]['hostname'];
    $toner['html_row_class'] = $cache['devices']['id'][$toner['device_id']]['html_row_class'];
    $toners[] = $toner;
  }
}
$toners = array_sort_by($toners, 'hostname', SORT_ASC, SORT_STRING, 'toner_descr', SORT_ASC, SORT_STRING);
$toners_count = count($toners);

// Pagination
$pagination_html = pagination($vars, $toners_count);
echo $pagination_html;

if ($vars['pageno'])
{
  $toners = array_chunk($toners, $vars['pagesize']);
  $toners = $toners[$vars['pageno']-1];
}
// End Pagination

if ($vars['view'] == "graphs") { $stripe_class = "table-striped-two"; } else { $stripe_class = "table-striped"; }

echo('<table class="table '.$stripe_class.' table-bordered table-condensed">');
echo('  <thead>');

echo('<tr class="strong">
        <th style="width: 280px;">Device</th>
        <th>Toner</th>
        <th style="width: 100px;"></th>
        <th style="width: 200px;">Level</th>
        <th style="width: 70px;">Remaining</th>
      </tr>');

echo('</thead>');

foreach ($toners as $toner)
{
  $total = $toner['toner_capacity'];
  $perc = $toner['toner_current'];

  $graph_array['type']        = $graph_type;
  $graph_array['id']          = $toner['toner_id'];
  $graph_array['from']        = $config['time']['day'];
  $graph_array['to']          = $config['time']['now'];
  $graph_array['height']      = "20";
  $graph_array['width']       = "80";
  $graph_array_zoom           = $graph_array;
  $graph_array_zoom['height'] = "150";
  $graph_array_zoom['width']  = "400";
  $link = "graphs/id=" . $graph_array['id'] . "/type=" . $graph_array['type'] . "/from=" . $graph_array['from'] . "/to=" . $graph_array['to'] . "/";
  $mini_graph = overlib_link($link, generate_graph_tag($graph_array), generate_graph_tag($graph_array_zoom), NULL);

  $background = get_percentage_colours(100 - $perc);

  /// FIXME - popup for toner entity.

  echo('<tr class="'.$toner['html_row_class'].'"><td class="entity">' . generate_device_link($toner) . '</td><td class="strong">' . htmlentities($toner['toner_descr']) . '</td>
       <td>'.$mini_graph.'</td>
       <td>
        <a href="'.$link.'">'.print_percentage_bar (400, 20, $perc, "$perc%", "ffffff", $background['left'], $free, "ffffff", $background['right']).'</a>
        </td><td>'.$perc.'%</td></tr>');

  if ($vars['view'] == "graphs")
  {
    echo("<tr></tr><tr class='health'><td colspan=5>");

    unset($graph_array['height'], $graph_array['width'], $graph_array['legend']);
    $graph_array['to']     = $config['time']['now'];
    $graph_array['id']     = $toner['toner_id'];
    $graph_array['type']   = $graph_type;

    print_graph_row($graph_array);

    echo("</td></tr>");
  } # endif graphs
}

echo("</table>");

echo $pagination_html;

// EOF
