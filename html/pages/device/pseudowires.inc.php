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

$pagetitle[] = "Pseudowires";

if(!isset($vars['view'])) { $vars['view'] = 'basic'; }

$link_array = array('page' => 'pseudowires');

$link_array = array('page'    => 'device',
                    'device'  => $device['device_id'],
                    'tab'     => 'pseudowires');

$navbar = array('brand' => "Pseudowires", 'class' => "navbar-narrow");

$navbar['options']['basic']['text']   = 'Basic';
// $navbar['options']['details']['text'] = 'Details';
$navbar['options']['minigraphs']     = array('text' => 'Mini Graphs', 'class' => 'pull-right', 'icon' => 'oicon-system-monitor');

foreach ($navbar['options'] as $option => $array)
{
  if ($vars['view'] == $option) { $navbar['options'][$option]['class'] .= " active"; }
  $navbar['options'][$option]['url'] = generate_url($link_array,array('view' => $option));
}

foreach (array('minigraphs') as $type)
{
  foreach ($config['graph_types']['port'] as $option => $data)
  {
    if ($vars['view'] == $type && $vars['graph'] == $option)
    {
      $navbar['options'][$type]['suboptions'][$option]['class'] = 'active';
      $navbar['options'][$type]['text'] .= ' ('.$data['name'].')';
    }
    $navbar['options'][$type]['suboptions'][$option]['text'] = $data['name'];
    $navbar['options'][$type]['suboptions'][$option]['url'] = generate_url($link_array, array('view' => $type, 'graph' => $option));
  }

}

print_navbar($navbar);
unset($navbar);

?>

<table class="table table-condensed table-striped table-bordered">
  <thead>
    <tr>
      <th width=10%>Pseudowire Id</th>
      <th width=40%>Local Port</th>
      <th width=5%></th>
      <th width=40%>Remote Port</th>
      <th></th>
    </tr>
  </thead>

<?php

foreach (dbFetchRows("SELECT * FROM pseudowires AS P, ports AS I WHERE P.port_id = I.port_id AND I.device_id = ? ORDER BY I.ifDescr", array($device['device_id'])) as $pw_a)
{
  $i = 0;
  while ($i < count($linkdone))
  {
    $thislink = $pw_a['device_id'] . $pw_a['port_id'];
    if ($linkdone[$i] == $thislink) { $skip = "yes"; }
    $i++;
  }

  if ($peer_device_id) { $peer_device = get_device_by_id_cache($peer_device_id); }

  $pw_b = dbFetchRow("SELECT * from `devices` AS D, `ports` AS I, `pseudowires` AS P WHERE D.device_id = ? AND D.device_id = I.device_id
                      AND P.cpwVcID = ? AND P.port_id = I.port_id", array($pw_a['peer_device_id'], $pw_a['cpwVcID']));

  if (!port_permitted($pw_a['port_id'])) { $skip = "yes"; }
  if (!port_permitted($pw_b['port_id'])) { $skip = "yes"; }

  if ($skip)
  {
    unset($skip);
  } else {
    echo('<tr><td style="font-size: 18px; padding: 4px;">'.$pw_a['cpwVcID'].'</td>
              <td>'.generate_port_link($pw_a).'<br />'.$pw_a['ifAlias']);

    if ($vars['view'] == "minigraphs")
    {
      echo '<br />';
      if ($pw_a)
      {
        $pw_a['width'] = "150";
        $pw_a['height'] = "30";
        $pw_a['from'] = $config['time']['day'];
        $pw_a['to'] = $config['time']['now'];
        $pw_a['bg'] = $bg;
        $types = array('bits','upkts','errors');
        foreach ($types as $graph_type)
        {
          $pw_a['graph_type'] = "port_".$graph_type;
          generate_port_thumbnail($pw_a);
        }
      }
    }
    echo '</td>';

    echo('    <td> <i class="oicon-arrow_right"></i> </td>
              <td>'.generate_device_link($peer_device));

    echo('</td><td>'.generate_port_link($pw_b).'<br />'.$pw_b['ifAlias']);

    if ($vars['view'] == "minigraphs")
    {
      echo '<br />';
      if ($pw_b)
      {
        $pw_b['width'] = "150";
        $pw_b['height'] = "30";
        $pw_b['from'] = $config['time']['day'];
        $pw_b['to'] = $config['time']['now'];
        $pw_b['bg'] = $bg;
        $types = array('bits','upkts','errors');
        foreach ($types as $graph_type)
        {
          $pw_b['graph_type'] = "port_".$graph_type;
          generate_port_thumbnail($pw_b);
        }
      }
    }

    echo('</td></tr>');

    $linkdone[] = $pw_b['device_id'] . $pw_b['port_id'];
  }
}

echo("</table>");

?>
