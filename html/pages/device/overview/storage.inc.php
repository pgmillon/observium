<?php

/**
 * Observium Network Management and Monitoring System
 * Copyright (C) 2006-2015, Adam Armstrong - http://www.observium.org
 *
 * @package    observium
 * @subpackage webui
 * @author     Adam Armstrong <adama@observium.org>
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

$graph_type = "storage_usage";

$sql  = "SELECT * FROM `storage`";
$sql .= " LEFT JOIN `storage-state` USING(`storage_id`)";
$sql .= " WHERE `device_id` = ?";

$drives = dbFetchRows($sql, array($device['device_id']));

if (count($drives))
{
  $drives = array_sort_by($drives, 'storage_descr', SORT_ASC, SORT_STRING);

?>

  <div class="box box-solid">
    <div class="box-header ">
      <a href="<?php echo(generate_url(array('page' => 'device', 'device' => $device['device_id'], 'tab' => 'health', 'metric' => 'storage'))); ?>">
        <i class="oicon-drive"></i><h3 class="box-title">Storage</h3>
      </a>
    </div>
    <div class="box-body no-padding">

<?php
  echo('<table class="table table-condensed table-striped">');

  foreach ($drives as $drive)
  {
    $skipdrive = FALSE;

    if ($device["os"] == "junos")
    {
      foreach ($config['ignore_junos_os_drives'] as $jdrive)
      {
        if (preg_match($jdrive, $drive["storage_descr"]))
        {
          $skipdrive = TRUE;
        }
      }
      $drive["storage_descr"] = preg_replace("/.*mounted on: (.*)/", "\\1", $drive["storage_descr"]);
    }

    if ($device['os'] == "freebsd")
    {
      foreach ($config['ignore_bsd_os_drives'] as $jdrive)
      {
        if (preg_match($jdrive, $drive["storage_descr"]))
        {
          $skipdrive = TRUE;
        }
      }
    }

    if ($skipdrive) { continue; }
    $drive["storage_descr"] = preg_replace("/(.*), type: (.*), dev: (.*)/", "\\1", $drive["storage_descr"]); // '/mnt/Media, type: zfs, dev: Media'
    $drive["storage_descr"] = preg_replace("/(.*) Label:(.*) Serial Number (.*)/", "\\1", $drive["storage_descr"]); // E:\ Label:Large Space Serial Number 26ad0d98
    $percent  = round($drive['storage_perc'], 0);
    $total = formatStorage($drive['storage_size']);
    $free = formatStorage($drive['storage_free']);
    $used = formatStorage($drive['storage_used']);
    $background = get_percentage_colours($percent);

    $graph_array           = array();
    $graph_array['height'] = "100";
    $graph_array['width']  = "210";
    $graph_array['to']     = $config['time']['now'];
    $graph_array['id']     = $drive['storage_id'];
    $graph_array['type']   = $graph_type;
    $graph_array['from']   = $config['time']['day'];
    $graph_array['legend'] = "no";

    $link_array = $graph_array;
    $link_array['page'] = "graphs";
    unset($link_array['height'], $link_array['width'], $link_array['legend']);
    $link = generate_url($link_array);

    $overlib_content = generate_overlib_content($graph_array, $device['hostname'] . " - " . $drive['storage_descr']);

    $graph_array['width'] = 80; $graph_array['height'] = 20; $graph_array['bg'] = 'ffffff00'; # the 00 at the end makes the area transparent.
//    $graph_array['style'][] = 'margin-top: -6px';

    $minigraph =  generate_graph_tag($graph_array);

    echo('<tr class="'.$background['class'].'">
           <td class="state-marker"></td>
           <td class="entity" style="max-width: 100px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">'.generate_entity_link('storage', $drive).'</td>
           <td style="width: 90px">'.overlib_link($link, $minigraph, $overlib_content).'</td>
           <td style="width: 200px">'.overlib_link($link, print_percentage_bar (200, 20, $percent, $used."/".$total." (".$percent . "%)", "ffffff", $background['left'],
                                                   $free . " (" . (100 - $percent) . "%)", "ffffff", $background['right']), $overlib_content).'</td>

         </tr>');

  }

  echo("</table>");
  echo("</div>");
  echo("</div>");
}

unset ($drive_rows);

// EOF
