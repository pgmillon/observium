<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage graphs
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

// Get port ID by ifIndex/ifDescr/ifAlias or customer circuit
// variables: ifindex, ifdescr (or port), ifalias, circuit
if (!is_numeric($vars['id']))
{
  if (is_numeric($device['device_id']))
  {
    if (is_numeric($vars['ifindex']))
    {
      // Get port by ifIndex
      $port = get_port_by_index_cache($device['device_id'], $vars['ifindex']);
      if ($port) { $vars['id'] = $port['port_id']; }
    }
    else if (!empty($vars['ifdescr']))
    {
      // Get port by ifDescr
      $port_id = get_port_id_by_ifDescr($device['device_id'], $vars['ifdescr']);
      if ($port_id) { $vars['id'] = $port_id; }
    }
    else if (!empty($vars['port']))
    {
      // Get port by ifDescr (backward compatibility)
      $port_id = get_port_id_by_ifDescr($device['device_id'], $vars['port']);
      if ($port_id) { $vars['id'] = $port_id; }
    }
    else if (!empty($vars['ifalias']))
    {
      // Get port by ifAlias
      $port_id = get_port_id_by_ifAlias($device['device_id'], $vars['ifalias']);
      if ($port_id) { $vars['id'] = $port_id; }
    }
  }
  else if (!empty($vars['circuit']))
  {
    // Get port by circuit id
    $port_id = get_port_id_by_customer(array('circuit' => $vars['circuit']));
    if ($port_id) { $vars['id'] = $port_id; }
  }
}

if (is_numeric($vars['id']) && ($auth || port_permitted($vars['id'])))
{
  $auth   = TRUE;

  $port   = get_port_by_id($vars['id']);
  $device = device_by_id_cache($port['device_id']);
  $title  = generate_device_link($device);
  $title .= " :: Port  <b>".generate_port_link($port) ."</b>";

  $title_array   = array();
  $title_array[] = array('text' => $device['hostname'], 'url' => generate_url(array('page' => 'device', 'device' => $device['device_id'])));
  $title_array[] = array('text' => 'Ports', 'url' => generate_url(array('page' => 'device', 'device' => $device['device_id'], 'tab' => 'ports')));
  $title_array[] = array('text' => rewrite_ifname($port['port_label']), 'url' => generate_url(array('page' => 'device', 'device' => $device['device_id'], 'tab' => 'port', 'port' => $port['port_id'])));

  $graph_title = short_hostname($device['hostname']) . " :: " . strtolower(short_ifname($port['ifDescr'], NULL, FALSE));
  $rrd_filename = get_port_rrdfilename($port, NULL, TRUE);

  if ($vars['type'] == 'port_bits')
  {
    $scale = (isset($vars['scale']) ? $vars['scale'] : $config['graphs']['ports_scale_default']);

    if ($scale != 'auto')
    {
      if ($scale == 'speed' && $port['ifSpeed'] > 0)
      {
        $scale_max = $port['ifSpeed'];
        if ($graph_style != 'mrtg')
        {
          $scale_min = -1 * $scale_max;
        }
      } else {
        $scale = intval(unit_string_to_numeric($scale, 1000));
        if (is_numeric($scale) && $scale > 0)
        {
          $scale_max = $scale;
          if ($graph_style != 'mrtg')
          {
            $scale_min = -1 * $scale_max;
          }
        }
      }
      $scale_rigid = isset($config['graphs']['ports_scale_force']) && $config['graphs']['ports_scale_force'];
    }
  }
}

// EOF

