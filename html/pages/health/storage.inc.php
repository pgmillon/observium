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

$graph_type = "storage_usage";

$sql  = "SELECT *, `storage`.`storage_id` AS `storage_id` FROM `storage`";
$sql .= " LEFT JOIN `storage-state` ON `storage`.storage_id = `storage-state`.storage_id";
$sql .= ' WHERE 1' . generate_query_permitted(array('device'));

// Groups
if (isset($vars['group']))
{
  $values = get_group_entities($vars['group']);
  $sql .= generate_query_values($values, 'storage.storage_id');
}

$storages = array();
foreach (dbFetchRows($sql) as $storage)
{
  if (isset($cache['devices']['id'][$storage['device_id']]))
  {
    $storage['hostname']       = $cache['devices']['id'][$storage['device_id']]['hostname'];
    $storage['html_row_class'] = $cache['devices']['id'][$storage['device_id']]['html_row_class'];
    $storages[] = $storage;
  }
}
switch($vars['sort'])
{
  case 'usage':
    $storages = array_sort_by($storages, 'storage_perc', SORT_DESC, SORT_NUMERIC, 'hostname', SORT_ASC, SORT_STRING);
    break;
  case 'mountpoint':
    $storages = array_sort_by($storages, 'storage_descr', SORT_DESC, SORT_STRING, 'hostname', SORT_ASC, SORT_STRING);
    break;
  case 'size':
  case 'free':
  case 'used':
    $storages = array_sort_by($storages, 'storage_'.$vars['sort'], SORT_DESC, SORT_NUMERIC, 'hostname', SORT_ASC, SORT_STRING);
    break;
  default:
    $storages = array_sort_by($storages, 'hostname', SORT_ASC, SORT_STRING, 'storage_descr', SORT_ASC, SORT_STRING);
    break;
}
$storages_count = count($storages);

// Pagination
$pagination_html = pagination($vars, $storages_count);
echo $pagination_html;

if ($vars['pageno'])
{
  $storages = array_chunk($storages, $vars['pagesize']);
  $storages = $storages[$vars['pageno']-1];
}
// End Pagination

if ($vars['view'] == "graphs") { $stripe_class = "table-striped-two"; } else { $stripe_class = "table-striped"; }

echo('<table class="table '.$stripe_class.' table-condensed table-bordered">');
echo('  <thead>');
echo('    <tr>');
echo('      <th style="width: 250px;"><a href="'. generate_url($vars, array('sort' => 'hostname')).'">Device</a></th>');
echo('      <th><a href="'. generate_url($vars, array('sort' => 'mountpoint')).'">Mountpoint</a></th>');
echo('      <th><a href="'. generate_url($vars, array('sort' => 'size')).'">Size</a></th>');
echo('      <th><a href="'. generate_url($vars, array('sort' => 'used')).'">Used</a></th>');
echo('      <th><a href="'. generate_url($vars, array('sort' => 'free')).'">Free</a></th>');
echo('      <th></th>');
echo('      <th style="width: 200px;"><a href="'. generate_url($vars, array('sort' => 'usage')).'">Usage %</a></th>');
echo('    </tr>');
echo('  </thead>');

foreach ($storages as $storage)
{
  $graph_array           = array();
  $graph_array['to']     = $config['time']['now'];
  $graph_array['id']     = $storage['storage_id'];
  $graph_array['type']   = $graph_type;
  $graph_array['legend'] = "no";

  $link_array = $graph_array;
  $link_array['page'] = "graphs";
  unset($link_array['height'], $link_array['width'], $link_array['legend']);
  $link_graph = generate_url($link_array);

  $link = generate_url( array("page" => "device", "device" => $storage['device_id'], "tab" => "health", "metric" => 'storage'));

  $overlib_content = generate_overlib_content($graph_array, $storage['hostname'] ." - " . htmlentities($storage['storage_descr']), NULL);

  $graph_array['width'] = 80; $graph_array['height'] = 20; $graph_array['bg'] = 'ffffff00'; # the 00 at the end makes the area transparent.
  $graph_array['from'] = $config['time']['day'];
  $mini_graph =  generate_graph_tag($graph_array);

  $total = formatStorage($storage['storage_size']);
  $used = formatStorage($storage['storage_used']);
  $free = formatStorage($storage['storage_free']);

  $background = get_percentage_colours($storage['storage_perc']);

  echo('<tr class="'.$storage['html_row_class'].'">
        <td class="entity">' . generate_device_link($storage) . '</td>
        <td>'.overlib_link($link, htmlentities($storage['storage_descr']),$overlib_content).'</td>
        <td>'.$total.'</td>
        <td>'.$used.'</td>
        <td>'.$free.'</td>
        <td>'.overlib_link($link_graph, $mini_graph, $overlib_content).'</td>
        <td><a href="'.$link_graph.'">
          '.print_percentage_bar (400, 20, $storage['storage_perc'], $storage['storage_perc'].'%', "ffffff", $background['left'], 100-$storage['storage_perc']."%" , "ffffff", $background['right']).'
          </a>
        </td>
      </tr>
   ');

  if ($vars['view'] == "graphs")
  {
    echo("<tr><td colspan=7>");

    unset($graph_array['height'], $graph_array['width'], $graph_array['legend']);
    $graph_array['to']     = $config['time']['now'];
    $graph_array['id']     = $storage['storage_id'];
    $graph_array['type']   = $graph_type;

    print_graph_row($graph_array);

    echo("</td></tr>");
  } # endif graphs
}

echo("</table>");

echo $pagination_html;

// EOF
