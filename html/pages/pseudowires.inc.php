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

$link_array = array('page' => 'pseudowires');

$navbar['brand'] = "Pseudowires";
$navbar['class'] = "navbar-narrow";

$nav_options = array('detail' => 'Details', 'minigraphs' => 'Mini Graphs');

foreach ($nav_options as $type => $text)
{
  if (!$vars['view']) { $vars['view'] = $type; }
  if ($vars['view'] == $type) { $navbar['options'][$type]['class'] = "active"; }

  $navbar['options'][$type]['url']  = generate_url(array('page' => 'pseudowires', 'view' => $type));
  $navbar['options'][$type]['text'] = $text;
}
print_navbar($navbar);

  if ($vars['view'] == "minigraphs") { $table_class = "table-striped-two";  } else { $table_class = "table-striped"; }

  echo('<table class="table table-hover table-condensed table-rounded table-bordered '.$table_class.'">');
  echo('  <thead>');

  echo('<tr>');

  $cols = array(
              'id' => 'id',
              'hostname_a' => 'Device',
              'port_a' => 'Port',
              'NONE' => NULL,
              'hostname_b' => 'Device',
              'port_b' => 'Port');

foreach ($cols as $sort => $col)
{
  if ($col == NULL)
  {
    echo('<th></th>');
  }
  elseif ($vars['sort'] == $sort)
  {
    echo('<th>'.$col.' *</th>');
  } else {
    echo('<th><a href="'. generate_url($vars, array('sort' => $sort)).'">'.$col.'</a></th>');
  }
}

  echo("      </tr>");
  echo('  </thead>');

/// FIXME - we should walk to database to build an array of pseudowires with all details so we can then use SORT on both A and B end variables.

foreach (dbFetchRows("SELECT * FROM pseudowires AS P, ports AS I, devices AS D WHERE P.port_id = I.port_id AND I.device_id = D.device_id ORDER BY D.hostname,I.ifDescr") as $pw_a)
{
  $i = 0;
  while ($i < count($linkdone))
  {
    $thislink = $pw_a['device_id'] . $pw_a['port_id'];
    if ($linkdone[$i] == $thislink) { $skip = "yes"; }
    $i++;
  }

  $pw_b = dbFetchRow("SELECT * from `devices` AS D, `ports` AS I, `pseudowires` AS P WHERE D.device_id = ? AND D.device_id = I.device_id
                      AND P.cpwVcID = ? AND P.port_id = I.port_id", array($pw_a['peer_device_id'], $pw_a['cpwVcID']));

  if (!port_permitted($pw_a['port_id'])) { $skip = "yes"; }
  if (!port_permitted($pw_b['port_id'])) { $skip = "yes"; }

  if ($skip)
  {
    unset($skip);
  } else {
    echo("<tr><td><strong>".$pw_a['cpwVcID']."</strong></td>
              <td>".generate_device_link($pw_a)."<br /></td>
              <td>".generate_port_link($pw_a)."<br />".$pw_a['ifAlias']."</td>
              <td style='vertical-align: middle;'> <img src='images/16/arrow_right.png'></td>
              <td>".generate_device_link($pw_b)."</td>
              <td>".generate_port_link($pw_b)."<br />".$pw_b['ifAlias']."</td></tr>");

    if ($vars['view'] == "minigraphs")
    {
      echo("<tr><td></td><td colspan=2>");

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
      echo("</td><td></td><td colspan=2>");

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

      echo("</td></tr>");
    }

    $linkdone[] = $pw_b['device_id'] . $pw_b['port_id'];
  }
}

echo("</table>");

// EOF
