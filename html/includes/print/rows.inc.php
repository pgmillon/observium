<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage web
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

// DOCME needs phpdoc block
function print_graph_row_port($graph_array, $port)
{

  global $config;

  $graph_array['to']     = $config['time']['now'];
  $graph_array['id']     = $port['port_id'];

  print_graph_row($graph_array);

}

// DOCME needs phpdoc block
function print_graph_row($graph_array)
{

  global $config;

  if ($_SESSION['widescreen'])
  {
    if ($_SESSION['big_graphs'])
    {
      if (!$graph_array['height']) { $graph_array['height'] = "110"; }
      if (!$graph_array['width']) { $graph_array['width']  = "353"; }
      $periods = array('sixhour', 'week', 'month', 'year');
    } else {
      if (!$graph_array['height']) { $graph_array['height'] = "110"; }
      if (!$graph_array['width']) { $graph_array['width']  = "215"; }
      $periods = array('sixhour', 'day', 'week', 'month', 'year', 'twoyear');
    }
  } else {
    if ($_SESSION['big_graphs'])
    {
      if (!$graph_array['height']) { $graph_array['height'] = "100"; }
      if (!$graph_array['width']) { $graph_array['width']  = "323"; }
      $periods = array('day', 'week', 'month');
    } else {
      if (!$graph_array['height']) { $graph_array['height'] = "100"; }
      if (!$graph_array['width']) { $graph_array['width']  = "228"; }
      $periods = array('day', 'week', 'month', 'year');
    }
  }

  if ($graph_array['shrink']) { $graph_array['width'] = $graph_array['width'] - $graph_array['shrink']; }

  $graph_array['to']     = $config['time']['now'];

  foreach ($periods as $period)
  {
    $graph_array['from']        = $config['time'][$period];
    $graph_array_zoom           = $graph_array;
    $graph_array_zoom['height'] = "175";
    $graph_array_zoom['width']  = "600";

    $link_array = $graph_array;
    $link_array['page'] = "graphs";
    unset($link_array['height'], $link_array['width']);
    $link = generate_url($link_array);

    echo(overlib_link($link, generate_graph_tag($graph_array), generate_graph_tag($graph_array_zoom),  NULL));
  }

}

// DOCME needs phpdoc block
function print_vm_row($vm, $device = NULL)
{
  echo('<tr>');

  echo('<td>');

  if (getidbyname($vm['vmwVmDisplayName']))
  {
    echo(generate_device_link(device_by_name($vm['vmwVmDisplayName'])));
  } else {
    echo $vm['vmwVmDisplayName'];
  }

  echo("</td>");
  echo('<td>' . $vm['vmwVmState'] . "</td>");

  if ($vm['vmwVmGuestOS'] == "E: tools not installed")
  {
    echo('<td class="small">Unknown (VMware Tools not installed)</td>');
  }
  else if ($vm['vmwVmGuestOS'] == "E: tools not running")
  {
    echo('<td class="small">Unknown (VMware Tools not running)</td>');
  }
  else if ($vm['vmwVmGuestOS'] == "")
  {
    echo('<td class="small"><i>(Unknown)</i></td>');
  }
  elseif (isset($config['vmware_guestid'][$vm['vmwVmGuestOS']]))
  {
    echo('<td>' . $config['vmware_guestid'][$vm['vmwVmGuestOS']] . "</td>");
  }
  else
  {
    echo('<td>' . $vm['vmwVmGuestOS'] . "</td>");
  }

  if ($vm['vmwVmMemSize'] >= 1024)
  {
    echo("<td class=list>" . sprintf("%.2f",$vm['vmwVmMemSize']/1024) . " GB</td>");
  } else {
    echo("<td class=list>" . sprintf("%.2f",$vm['vmwVmMemSize']) . " MB</td>");
  }

  echo('<td>' . $vm['vmwVmCpus'] . " CPU</td>");

}

// EOF
