<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package        observium
 * @subpackage     functions
 * @copyright  (C) 2006-2015 Adam Armstrong
 *
 */

/**
 * Returns icon tag (by default) or icon name for current device array
 *
 * @param array $device Array with device info (from DB)
 * @param bool $base_icon Return complete img tag with icon (by default) or just base icon name
 *
 * @return string Img tag with icon or base icon name
 */
function get_device_icon($device, $base_icon = FALSE)
{
  global $config;

  $icon = 'generic';
  $device['os'] = strtolower($device['os']);

  if ($device['icon'] && is_file($config['html_dir'] . '/images/os/' . $device['icon'] . '.png'))
  {
    // Custom device icon from DB
    $icon  = $device['icon'];
  }
  else if ($config['os'][$device['os']]['icon'] && is_file($config['html_dir'] . '/images/os/' . $config['os'][$device['os']]['icon'] . '.png'))
  {
    // Icon defined in os definition
    $icon  = $config['os'][$device['os']]['icon'];
  } else {
    if ($device['distro'])
    {
      // Icon by distro name
      $distro = safename(strtolower(trim($device['distro'])));
      if (is_file($config['html_dir'] . '/images/os/' . $distro . '.png'))
      {
        $icon  = $distro;
      }
    }

    if ($icon == 'generic' && is_file($config['html_dir'] . '/images/os/' . $device['os'] . '.png'))
    {
      // Icon by OS name
      $icon  = $device['os'];
    }
  }
  if ($icon == 'generic' && $config['os'][$device['os']]['vendor'])
  {
    // Icon by vendor name
    $vendor = safename(strtolower(trim($config['os'][$device['os']]['vendor'])));
    if (is_file($config['html_dir'] . '/images/os/' . $vendor . '.png'))
    {
      $icon  = $vendor;
    }
  }

  if ($base_icon)
  {
    return $icon;
  } else {
    // Image tag
    $image = '<img src="' . $config['base_url'] . '/images/os/' . $icon . '.png" alt="" />';
    return $image;
  }
}

// TESTME needs unit testing
// DOCME needs phpdoc block
function generate_device_url($device, $vars = array())
{
  return generate_url(array('page' => 'device', 'device' => $device['device_id']), $vars);
}

// TESTME needs unit testing
// DOCME needs phpdoc block
function generate_device_popup_header($device, $vars = array())
{
  global $config;

  humanize_device($device);

  if ($device['os'] == "ios")
  {
    formatCiscoHardware($device, TRUE);
  } // FIXME or generic function for more than just IOS? [and/or do this at poll time]

  $contents = '
<table class="table table-striped table-bordered table-rounded table-condensed">
  <tr class="' . $device['html_row_class'] . '" style="font-size: 10pt;">
    <td class="state-marker"></td>
    <td width="40" style="padding: 10px; text-align: center; vertical-align: middle;">' . get_device_icon($device) . '</td>
    <td width="200"><a href="#" class="' . $class . '" style="font-size: 15px; font-weight: bold;">' . escape_html($device['hostname']) . '</a><br />' . escape_html(truncate($device['location'], 64, '')) . '</td>
    <td>' . escape_html($device['hardware']) . ' <br /> ' . $device['os_text'] . ' ' . escape_html($device['version']) . '</td>
    <td>' . deviceUptime($device, 'short') . '<br />' . escape_html($device['sysName']) . '
  </tr>
</table>
';

  return $contents;
}

// TESTME needs unit testing
// DOCME needs phpdoc block
function generate_device_popup($device, $vars = array(), $start = 0, $end = 0)
{
  global $config;

  if (!$start)
  {
    $start = $config['time']['day'];
  }
  if (!$end)
  {
    $end = $config['time']['now'];
  }

  $contents = generate_device_popup_header($device, $vars = array());

  if (isset($config['os'][$device['os']]['over']))
  {
    $graphs = $config['os'][$device['os']]['over'];
  }
  elseif (isset($device['os_group']) && isset($config['os'][$device['os_group']]['over']))
  {
    $graphs = $config['os'][$device['os_group']]['over'];
  }
  else
  {
    $graphs = $config['os']['default']['over'];
  }

  // Preprocess device graphs array
  foreach ($device['graphs'] as $graph)
  {
    $graphs_enabled[] = $graph['graph'];
  }

  foreach ($graphs as $entry)
  {
    $graph = $entry['graph'];

    if ($graph && in_array(str_replace('device_', '', $graph), $graphs_enabled) !== FALSE)
    {
      if (isset($entry['text']))
      {
        // Text is provided in the array, this overrides the default
        $text = $entry['text'];
      }
      else
      {
        // No text provided for the minigraph, fetch from array
        preg_match('/^(?P<type>[a-z0-9A-Z-]+)_(?P<subtype>[a-z0-9A-Z-_]+)/', $graph, $graphtype);

        if (isset($graphtype['type']) && isset($graphtype['subtype']))
        {
          $type = $graphtype['type'];
          $subtype = $graphtype['subtype'];

          $text = $config['graph_types'][$type][$subtype]['descr'];
        }
        else
        {
          $text = nicecase($subtype); // Fallback to the type itself as a string, should not happen!
        }
      }

      $contents .= '
<div style="width: 730px">
  <span style="margin-left: 5px; font-size: 12px; font-weight: bold;">' . $text . '</span><br />
  <img src="graph.php?device=' . $device['device_id'] . '&amp;from=' . $start . '&amp;to=' . $end . '&amp;width=275&amp;height=100&amp;type=' . $graph . '&amp;legend=no&amp;draw_all=yes' . '" style="margin: 2px;">
  <img src="graph.php?device=' . $device['device_id'] . '&amp;from=' . $config['time']['week'] . '&amp;to=' . $end . '&amp;width=275&amp;height=100&amp;type=' . $graph . '&amp;legend=no&amp;draw_all=yes' . '" style="margin: 2px;">
</div>';
    }
  }

  return $contents;
}

// TESTME needs unit testing
// DOCME needs phpdoc block
function generate_device_link($device, $text = NULL, $vars = array(), $escape = TRUE)
{
  if (is_array($device) && !$device['hostname'])
  {
    $device = device_by_id_cache($device['device_id']);
  }
  if (!device_permitted($device['device_id']))
  {
    $text = ($escape ? escape_html($device['hostname']) : $device['hostname']);

    return $text;
  }

  $class = devclass($device);
  if (!$text)
  {
    $text = $device['hostname'];
  }

  $url = generate_device_url($device, $vars);
  //$link = overlib_link($url, $text, $contents, $class, $escape);

  if ($escape)
  {
    $text = escape_html($text);
  }

  return '<a href="' . $url . '" class="entity-popup ' . $class . '" data-eid="' . $device['device_id'] . '" data-etype="device">' . $text . '</a>';
}

// EOF
