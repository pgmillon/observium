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

$page_title[] = "Locations";

if (!$vars['view']) { $vars['view'] = "basic"; }

$navbar['brand'] = 'Locations';
$navbar['class'] = 'navbar-narrow';

foreach (array('basic', 'traffic') as $type)
{
  if ($vars['view'] == $type) { $navbar['options'][$type]['class'] = 'active'; }
  $navbar['options'][$type]['url'] = generate_url(array('page' => 'locations', 'view' => $type));
  $navbar['options'][$type]['text'] = ucfirst($type);
}
print_navbar($navbar);
unset($navbar);

echo generate_box_open();

echo('<table class="table table-hover  table-striped table-condensed ">'. PHP_EOL);

//$location_where = generate_query_values($vars['location'], 'location');

$cols = array(
                 array(NULL, 'class="state-marker"'),
  'location'  => array('Location', 'style="width: 300px;"'),
  'total'     => array('Devices: Total', 'style="width: 50px; text-align: right;"')
);

foreach (array_keys($cache['device_types']) as $type)
{
  $cols[$type] = array(nicecase($type), 'style="width: 40px;"');
}
echo get_table_header($cols); //, $vars); // Currently sorting is not available

echo('<tr class="success">
          <td class="state-marker"></td>
          <td class="entity">ALL</td>
          <td style="text-align: right;"><strong class="label label-success">' . $devices['count'] . '</strong></td>' . PHP_EOL);
foreach ($cache['device_types'] as $type => $type_count)
{
  echo('<td><strong class="label label-info">' . $type_count . '</strong></td>' . PHP_EOL);
}
echo('      </tr>');

foreach (get_locations() as $location)
{
  $location_where = ' WHERE 1 ' . generate_query_values($location, 'location');
  $location_where .= $GLOBALS['cache']['where']['devices_permitted'];

  $num        = dbFetchCell("SELECT COUNT(*) FROM `devices`" . $location_where);

  $hostalerts = dbFetchCell("SELECT COUNT(*) FROM `devices`" . $location_where . " AND `status` = ?", array(0));
  if ($hostalerts) { $row_class = 'error'; } else { $row_class = ''; }

  if ($location === '') { $location = OBS_VAR_UNSET; }
  $value = var_encode($location);
  $name  = escape_html($location);

  echo('<tr class="'.$row_class.'">
          <td class="state-marker"></td>
          <td class="entity">' . generate_link($name, array('page' => 'devices', 'location' => $value)) . '</td>
          <td style="text-align: right;"><strong class="label label-success">' . $num . '</strong></td>' . PHP_EOL);
  foreach (array_keys($cache['device_types']) as $type)
  {
    $location_count = dbFetchCell("SELECT COUNT(*) FROM `devices`" . $location_where . " AND `type` = ?", array($type));
    if ($location_count > 0)
    {
      $location_count = '<span class="label">' . $location_count . '</span>';
    }
    echo('<td>' . $location_count . '</td>' . PHP_EOL);
  }
  echo('      </tr>');

  if ($vars['view'] == "traffic")
  {
    echo('<tr></tr><tr class="locations"><td colspan="'.count($cols).'">');

    $graph_array['type']   = "location_bits";
    $graph_array['height'] = "100";
    $graph_array['width']  = "220";
    $graph_array['to']     = $config['time']['now'];
    $graph_array['legend'] = "no";
    $graph_array['id']     = $value;

    print_graph_row($graph_array);

    echo("</tr></td>");
  }
  $done = "yes";
}

echo("</table>");
echo generate_box_close();

// EOF
