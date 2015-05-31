<?php

/**
 * Observium Network Management and Monitoring System
 * Copyright (C) 2006-2014, Adam Armstrong - http://www.observium.org
 *
 * @package    observium
 * @subpackage webui
 * @author     Adam Armstrong <adama@memetic.org>
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

$link_array = array('page'    => 'device',
                    'device'  => $device['device_id'],
                    'tab'     => 'latency');

$navbar['brand'] = "Latency";
$navbar['class'] = "navbar-narrow";

foreach (array('incoming', 'outgoing') as $view)
{
  if (!strlen($vars['view'])) { $vars['view'] = $view; }

  if (count($smokeping_files[$view][$device['hostname']]))
  {
    if ($vars['view'] == $view) { $navbar['options'][$view]['class'] = "active"; }
    $navbar['options'][$view]['url'] = generate_url(array('page' => 'device', 'device' => $device['device_id'], 'tab' => 'latency', 'view' => $view));
    $navbar['options'][$view]['text'] = ucwords($view);
  }
}

print_navbar($navbar);

echo('<table class="table table-striped">');

if($vars['view'] == "incoming")
{
  if (count($smokeping_files['incoming'][$device['hostname']]))
  {
    $graph_array['type']        = "device_smokeping_in_all_avg";
    $graph_array['device']      = $device['device_id'];
    echo('<tr><td>');
    echo('<h3>Average</h3>');

    print_graph_row($graph_array);

    echo('</td></tr>');

    $graph_array['type']        = "device_smokeping_in_all";
    $graph_array['legend']      = 'no';
    echo('<tr><td>');
    echo('<h3>Aggregate</h3>');

    print_graph_row($graph_array);

    echo('</td></tr>');

    unset($graph_array['legend']);

    ksort($smokeping_files['incoming'][$device['hostname']]);
    foreach ($smokeping_files['incoming'][$device['hostname']] AS $src => $host)
    {
      $hostname = str_replace(".rrd", "", $host);
      $host = device_by_name($src);
      if (is_numeric($host['device_id']))
      {
        echo('<tr><td>');
        echo('<h3>'.generate_device_link($host).'</h3>');
        $graph_array['type']    = "smokeping_in";
        $graph_array['device']  = $device['device_id'];
        $graph_array['src']     = $host['device_id'];

        print_graph_row($graph_array);

        echo('</td></tr>');
      }
    }
  }
}
elseif ($vars['view'] == "outgoing")
{
  if (count($smokeping_files['outgoing'][$device['hostname']]))
  {
    $graph_array['type']        = "device_smokeping_out_all_avg";
    $graph_array['device']      = $device['device_id'];
    echo('<tr><td>');
    echo('<h3>Average</h3>');

    print_graph_row($graph_array);

    echo('</td></tr>');

    $graph_array['type']        = "device_smokeping_out_all";
    $graph_array['legend']      = 'no';
    echo('<tr><td>');
    echo('<h3>Aggregate</h3>');

    print_graph_row($graph_array);

    echo('</td></tr>');

    unset($graph_array['legend']);

    asort($smokeping_files['outgoing'][$device['hostname']]);
    foreach ($smokeping_files['outgoing'][$device['hostname']] AS $host)
    {
      $hostname = basename($host,".rrd");
      list($hostname) = explode("~", $hostname);
      if ($config['smokeping']['suffix']) $hostname = $hostname.$config['smokeping']['suffix'];
      if ($config['smokeping']['split_char']) $hostname = str_replace($config['smokeping']['split_char'],".",$hostname);
      $host = device_by_name($hostname);
      if (is_numeric($host['device_id']))
      {
        echo('<tr><td>');
        echo('<h3>'.generate_device_link($host).'</h3>');
        $graph_array['type']    = "smokeping_out";
        $graph_array['device']  = $device['device_id'];
        $graph_array['dest']    = $host['device_id'];

        print_graph_row($graph_array);

        echo('</td></tr>');
      }
    }
  }
}

echo('</table>');

$pagetitle[] = "Latency";

// EOF
