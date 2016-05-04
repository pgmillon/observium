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

foreach ($vars as $var => $value)
{
  if ($value != "")
  {
    switch ($var)
    {
      case 'name':
        $where .= generate_query_values($value, $var);
        break;
    }
  }
}

echo generate_box_open();

echo '<table class="table table-condensed table-striped">';
echo '  <thead>';
echo '    <tr>';
echo '      <th style="width: 300px;">Package</th>';
echo '      <th>Version</th>';
echo '    </tr>';
echo '  </thead>';
echo '  <tbody>';

// Build array of packages - faster than SQL
// foreach (dbFetchRows("SELECT * FROM `packages`", $param) as $entry)
// {
// }

foreach (dbFetchRows("SELECT * FROM `packages` WHERE 1 $where GROUP BY `name`", $param) as $entry)
{
  echo("    <tr>\n");
  echo("      <td><a href=\"". generate_url($vars, array('name' => $entry['name']))."\">".$entry['name']."</a></td>\n");
  echo("      <td>");

  foreach (dbFetchRows("SELECT * FROM `packages` WHERE `name` = ? ORDER BY version, build", array($entry['name'])) as $entry_v)
  {
    $entry['blah'][$entry_v['version']][$entry_v['build']][$entry_v['device_id']] = 1;
  }

  $first = true;
  foreach ($entry['blah'] as $version => $bleu)
  {

    //$content = '<div style="width: 800px;">';
    $content = "";
    foreach ($bleu as $build => $bloo)
    {
      if ($build) { $dbuild = '-' . $build; } else { $dbuild = ''; }
      //$content .= '<div style="background-color: #eeeeee; margin: 5px;"><span style="font-weight: bold; ">'.$version.$dbuild.'</span>';
      $content .= $version.$dbuild;
      foreach ($bloo as $device_id => $no)
      {
        $this_device = device_by_id_cache($device_id);
        //$content .= '<span style="background-color: #f5f5f5; margin: 5px;">'.$this_device['hostname'].'</span> ';
        if (!empty($this_device['hostname'])) {
          $content .= " - ".$this_device['hostname'];
        }
      }
      //$content .= "</div>";
    }
    //$content .= "</div>";
    if (empty($vars['name']))
    {
      if ($first) { $first = false; $middot = ""; } else { $middot = "&nbsp;&nbsp;&middot;&nbsp;&nbsp;"; }
      //echo("<span style='margin:5px;'>".overlib_link("", $version, $content,  NULL)."</span>");
      echo($middot . generate_tooltip_link('', $version . $dbuild, $content));
    } else {
      echo("$version $content <br />");
    }
  }

  echo("      </td>\n");
  echo("    </tr>\n");
}

echo '  </tbody>';
echo '</table>';

echo generate_box_close();

// EOF
