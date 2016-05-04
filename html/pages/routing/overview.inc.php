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

foreach ($datas as $type)
{
  if ($type != "overview")
  {
    $filename = $config['html_dir'] . '/pages/routing/overview/'.$type.'.inc.php';
    if (is_file($filename))
    {
      $g_i++;
      if (!is_integer($g_i/2)) { $row_colour = $list_colour_a; } else { $row_colour = $list_colour_b; }

      echo('<div style="background-color: '.$row_colour.';">');
      echo('<div style="padding:4px 0px 0px 8px;"><span class=graphhead>'.$type_text[$type].'</span>');

      include($filename);

      echo('</div>');
      echo('</div>');
    } else {
      $graph_title = $type_text[$type];
      $graph_type = "device_".$type;

      include("includes/print-device-graph.php");
    }
  }
}

// EOF
