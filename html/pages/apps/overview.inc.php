<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage webui
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

$graph_array['width']       = "218";
$graph_array['height']      = "100";

$graph_array['to']          = $config['time']['now'];
$graph_array['from']        = $config['time']['day'];
$graph_array_zoom           = $graph_array;
$graph_array_zoom['height'] = "150";
$graph_array_zoom['width']  = "400";
$graph_array['legend']      = "no";

foreach (dbFetchRows("SELECT * FROM `applications` WHERE 1 ".generate_query_values(array_keys($app_types), 'app_type').$GLOBALS['cache']['where']['devices_permitted'].' ORDER BY `app_type`;') as $app)
{
  if (isset($cache['devices']['id'][$app['device_id']]))
  {
    $app_types[$app['app_type']][] = array_merge($app, $cache['devices']['id'][$app['device_id']]);
  }
}

foreach (array_keys($app_types) as $app_type)
{
  echo('<div class="row"><div class="col-md-12">');

  echo('<h4>'.generate_link(nicecase($app_type),array('page' => 'apps', 'app' => $app_type)).'</h4>');
  $app_devices = array_sort_by($app_types[$app_type], 'hostname', SORT_ASC, SORT_STRING);

  foreach ($app_devices as $app)
  {
    $graph_type = $config['app'][$app['app_type']]['top'][0];

    $graph_array['type']   = "application_".$app['app_type']."_".$graph_type;
    $graph_array['id']     = $app['app_id'];
    $graph_array_zoom['type']   = "application_".$app['app_type']."_".$graph_type;
    $graph_array_zoom['id']     = $app['app_id'];

    $link_array = $graph_array;
    $link_array['page']   = "device";
    $link_array['device'] = $app['device_id'];
    $link_array['tab']    = "apps";
    $link_array['app']    = $app['app_type'];
    unset($link_array['height'], $link_array['width']);
    $overlib_url = generate_url($link_array);

    $overlib_link    = '<span style="float:left; margin-left: 10px; font-weight: bold;">'.short_hostname($app['hostname'])."</span>";
    if (!empty($app['app_instance']))
    {
      $overlib_link  .= '<span style="float:right; margin-right: 10px; font-weight: bold;">'.$app['app_instance']."</span>";
      $app['content_add']   = '('.$app['app_instance'].')';
    }
    $overlib_link   .= "<br/>";
    $overlib_link   .= generate_graph_tag($graph_array);
    $overlib_content = generate_overlib_content($graph_array, $app['hostname'] . " - ". $app['app_type'] . $app['content_add']);

    echo("<div style='display: block; padding: 1px; padding-top: 3px; margin: 2px; min-width: ".$width_div."px; max-width:".$width_div."px; min-height:165px; max-height:165px;
                      text-align: center; float: left; background-color: #f5f5f5;'>");
    echo(overlib_link($overlib_url, $overlib_link, $overlib_content));
    echo("</div>");
  }
  echo('</div></div>');
}

// EOF
