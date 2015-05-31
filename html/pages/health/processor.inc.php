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

$graph_type = "processor_usage";

$sql  = "SELECT *, `processors`.`processor_id` AS `processor_id` FROM `processors`";
$sql .= " LEFT JOIN  `processors-state` ON `processors`.`processor_id` = `processors-state`.`processor_id`";
$sql .= ' WHERE 1' . generate_query_permitted(array('device'));

// Groups
if (isset($vars['group']))
{
  $values = get_group_entities($vars['group']);
  $sql .= generate_query_values($values, 'processors.processor_id');
}

$processors = array();
foreach (dbFetchRows($sql) as $proc)
{
  if (isset($cache['devices']['id'][$proc['device_id']]))
  {
    $proc['hostname']       = $cache['devices']['id'][$proc['device_id']]['hostname'];
    $proc['html_row_class'] = $cache['devices']['id'][$proc['device_id']]['html_row_class'];
    $processors[] = $proc;
  }
}
$processors = array_sort_by($processors, 'hostname', SORT_ASC, SORT_STRING, 'processor_descr', SORT_ASC, SORT_STRING);
$processors_count = count($processors);

// Pagination
$pagination_html = pagination($vars, $processors_count);
echo $pagination_html;

if ($vars['pageno'])
{
  $processors = array_chunk($processors, $vars['pagesize']);
  $processors = $processors[$vars['pageno']-1];
}
// End Pagination

if ($vars['view'] == "graphs") { $stripe_class = "table-striped-two"; } else { $stripe_class = "table-striped"; }

echo('<table class="table '.$stripe_class.' table-condensed table-bordered">');
echo('  <thead>');
echo('    <tr>');
echo('      <th style="width: 200px;">Device</th>');
echo('      <th>Processor</th>');
echo('      <th style="width: 100px;"></th>');
echo('      <th style="width: 250px;">Usage</th>');
echo('    </tr>');
echo('  </thead>');

foreach ($processors as $proc)
{
  // FIXME should that really be done here? :-)
  // FIXME - not it shouldn't. we need some per-os rewriting on discovery-time.
  $text_descr = $proc['processor_descr'];
  $text_descr = str_replace("Routing Processor", "RP", $text_descr);
  $text_descr = str_replace("Switching Processor", "SP", $text_descr);
  $text_descr = str_replace("Sub-Module", "Module ", $text_descr);
  $text_descr = str_replace("DFC Card", "DFC", $text_descr);

  $graph_array           = array();
  $graph_array['to']     = $config['time']['now'];
  $graph_array['id']     = $proc['processor_id'];
  $graph_array['type']   = $graph_type;
  $graph_array['legend'] = "no";

  $link_array = $graph_array;
  $link_array['page'] = "graphs";
  unset($link_array['height'], $link_array['width'], $link_array['legend']);
  $link_graph = generate_url($link_array);

  $link = generate_url(array("page" => "device", "device" => $proc['device_id'], "tab" => "health", "metric" => 'processor'));

  $overlib_content = generate_overlib_content($graph_array, $proc['hostname'] ." - " . htmlentities($text_descr), NULL);

  $graph_array['width'] = 80; $graph_array['height'] = 20; $graph_array['bg'] = 'ffffff00'; # the 00 at the end makes the area transparent.
  $graph_array['from'] = $config['time']['day'];
  $mini_graph =  generate_graph_tag($graph_array);

  $perc = round($proc['processor_usage']);
  $background = get_percentage_colours($perc);

  echo('<tr class="'.$proc['html_row_class'].'">
        <td class="entity">' . generate_device_link($proc) . '</td>
        <td>'.overlib_link($link, htmlentities($text_descr),$overlib_content).'</td>
        <td>'.overlib_link($link_graph, $mini_graph, $overlib_content).'</td>
        <td><a href="'.$link_graph.'">
          '.print_percentage_bar (400, 20, $perc, $perc."%", "ffffff", $background['left'], (100 - $perc)."%" , "ffffff", $background['right']).'
          </a>
        </td>
      </tr>
   ');

  if ($vars['view'] == "graphs")
  {
    echo("<tr><td colspan=4>");

    unset($graph_array['height'], $graph_array['width'], $graph_array['legend']);
    $graph_array['to']     = $config['time']['now'];
    $graph_array['id']     = $proc['processor_id'];
    $graph_array['type']   = $graph_type;

    print_graph_row($graph_array);

    echo("</td></tr>");
  } # endif graphs
}

echo("</table>");

echo $pagination_html;

// EOF
