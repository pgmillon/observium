<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage map
 * @author     Adam Armstrong <adama@observium.org>
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

$links = 1;

include_once("../includes/sql-config.inc.php");

include($config['html_dir'] . "/includes/functions.inc.php");
include($config['html_dir'] . "/includes/authenticate.inc.php");

if ($_SESSION['authenticated'])
{
  // Do various queries which we use in multiple places
  include($config['html_dir'] . "/includes/cache-data.inc.php");

  if (!is_file($config['dot']))
  {
    print_error("Package 'graphviz' not installed. Map can not be displayed!");
    return;
  }
}
else if (!is_file($config['dot']))
{
  exit;
}

$vars = get_vars('GET');

if (strpos($_SERVER['REQUEST_URI'], "anon")) { $anon = 1; }

if (is_array($config['branding']))
{
  if ($config['branding'][$_SERVER['SERVER_NAME']])
  {
    foreach ($config['branding'][$_SERVER['SERVER_NAME']] as $confitem => $confval)
    {
      eval("\$config['" . $confitem . "'] = \$confval;");
    }
  } else {
    foreach ($config['branding']['default'] as $confitem => $confval)
    {
      eval("\$config['" . $confitem . "'] = \$confval;");
    }
  }
}

if (isset($vars['device']) && is_numeric($vars['device'])) { $where = "WHERE D.`device_id` = ".$vars['device']; } else { $where = "WHERE 1"; }

// FIXME this shit probably needs tidied up.

if (isset($vars['format']) && preg_match("/^[a-z]*$/", $vars['format']))
{
#  $map = 'digraph G { bgcolor=transparent; splines=true; overlap=scale; concentrate=0; epsilon=0.001; rankdir=LR
  $map = 'digraph G { bgcolor=transparent; splines=true; overlap=scale; rankdir=LR
     node [ fontname="helvetica", fontstyle=bold, style=filled, color=white, fillcolor=lightgrey, overlap=false];
     edge [ bgcolor=white, fontname="helvetica", fontstyle=bold, arrowhead=dot, arrowtail=dot];
     graph [bgcolor=transparent;];

';

  if (!$_SESSION['authenticated'])
  {
    $map .= "\"Not authenticated\" [fontsize=20 fillcolor=\"lightblue\", URL=\"/\" shape=box3d]\n";
  } else {
    $loc_count = 1;

    $cache['where']['devices_permitted'] = generate_query_permitted(array('device'), array('device_table' => 'D'));
    foreach (dbFetch("SELECT D.*, COUNT(L.port_id) FROM devices AS D LEFT JOIN (ports AS I, neighbours AS L) ON (D.device_id = I.device_id AND I.port_id = L.port_id) ". $where . $cache['where']['devices_permitted'] . " GROUP BY D.hostname ORDER BY COUNT(L.port_id) DESC") as $device)
    {
      if ($device)
      {
        $links = dbFetch("SELECT * from ports AS I, neighbours AS L WHERE I.device_id = ? AND L.port_id = I.port_id ORDER BY L.remote_hostname", array($device['device_id']));
        if (count($links))
        {
          $ranktype = substr($device['hostname'], 0, 2);
          $ranktype2 = substr($device['hostname'], 0, 3);
          if (!strncmp($device['hostname'], "c", 1) && !strstr($device['hostname'], "kalooga"))
          {
            $ranks[$ranktype][] = $device['hostname'];
          } else {
            $ranks[$ranktype2][] = $device['hostname'];
          }
          if ($anon) { $device['hostname'] = md5($device['hostname']); }
          if (!isset($locations[$device['location']])) { $locations[$device['location']] = $loc_count; $loc_count++; }
          #$loc_id = $locations[$device['location']];
          $loc_id = '"'.$ranktype.'"';

          $map .= "\"".$device['hostname']."\" [fontsize=20, fillcolor=\"lightblue\", group=".$loc_id." URL=\"".generate_url(array('page' => 'device', 'device' => $device['device_id'],'tab' => 'ports', 'view' => 'map'))."\" shape=box3d]\n";
        }

        foreach ($links as $link)
        {
          $local_port_id = $link['port_id'];
          $remote_port_id = $link['remote_port_id'];

          $i = 0; $done = 0;
          if ($linkdone[$remote_port_id][$local_port_id]) { $done = 1; }

          if (!$done)
          {
            $linkdone[$local_port_id][$remote_port_id] = TRUE;

            $links++;

            if ($link['ifSpeed'] >= "10000000000")
            {
              $info = "color=red3 style=\"setlinewidth(6)\"";
            } elseif ($link['ifSpeed'] >= "1000000000") {
              $info = "color=lightblue style=\"setlinewidth(4)\"";
            } elseif ($link['ifSpeed'] >= "100000000") {
              $info = "color=lightgrey style=\"setlinewidth(2)\"";
            } elseif ($link['ifSpeed'] >= "10000000") {
              $info = "style=\"setlinewidth(1)\"";
            } else {
              $info = "style=\"setlinewidth(1)\"";
            }

            $src = $device['hostname'];
            if ($anon) { $src = md5($src); }
            if ($remote_port_id)
            {
              $dst_query = dbFetchRow("SELECT D.`device_id`, `hostname` FROM `devices` AS D, `ports` AS I WHERE I.`port_id` = ? AND D.`device_id` = I.`device_id`".$cache['where']['devices_permitted'], array($remote_port_id));
              $dst       = $dst_query['hostname'];
              $dst_host  = $dst_query['device_id'];              
            } else {
              unset($dst_host);
              $dst = $link['remote_hostname'];
            }

            if ($anon) { $dst = md5($dst); $src = md5($src);}

            $sif = dbFetchRow("SELECT * FROM `ports` WHERE `port_id` = ?".$cache['where']['ports_permitted'], array($link['port_id']));
            //humanize_port($sif);
            if ($remote_port_id)
            {
              $dif = dbFetchRow("SELECT * FROM `ports` WHERE `port_id` = ?".$cache['where']['ports_permitted'], array($link['remote_port_id']));
              //humanize_port($dif);
            } else {
              $dif['port_label'] = $link['remote_port'];
              $dif['port_id'] = $link['remote_hostname'] . '/' . $link['remote_port'];
            }

            if (!is_numeric($device['device_id']))
            {
              if (!$ifdone[$dst][$dif['port_id']] && !$ifdone[$src][$sif['port_id']])
              {
                $map .= "\"$src\" -> \"" . $dst . "\" [weight=500000, arrowsize=0, len=0, $info];\n";
              }
              $ifdone[$src][$sif['port_id']] = 1;
            } else {
              $map .= "\"" . $sif['port_id'] . "\" [label=\"" . $sif['port_label'] . "\", fontsize=12, fillcolor=lightblue, URL=\"".generate_url(array('page' => 'device', 'device' => $device['device_id'],'tab' => 'port', 'port' => $local_port_id))."\"]\n";
              if (!$ifdone[$src][$sif['port_id']])
              {
                $map .= "\"$src\" -> \"" . $sif['port_id'] . "\" [weight=500000, arrowsize=0, len=0];\n";
                $ifdone[$src][$sif['port_id']] = 1;
              }

              if ($dst_host)
              {
                $map .= "\"$dst\" [URL=\"".generate_url(array('page' => 'device', 'device' => $dst_host,'tab' => 'ports', 'view' => 'map'))."\", fontsize=20, shape=box3d]\n";
              } else {
                $map .= "\"$dst\" [ fontsize=20 shape=box3d]\n";
              }

              if ($dst_host == $device['device_id'] || !is_numeric($device['device_id']))
              {
                $map .= "\"" . $dif['port_id'] . "\" [label=\"" . $dif['port_label'] . "\", fontsize=12, fillcolor=lightblue, URL=\"".generate_url(array('page' => 'device', 'device' => $dst_host,'tab' => 'port', 'port' => $remote_port_id))."\"]\n";
              } else {
                $map .= "\"" . $dif['port_id'] . "\" [label=\"" . $dif['port_label'] . " \", fontsize=12, fillcolor=lightgray";
                if ($dst_host)
                {
                  $map .= ", URL=\"".generate_url(array('page' => 'device', 'device' => $dst_host,'tab' => 'port', 'port' => $remote_port_id))."\"";
                }
                $map .= "]\n";
              }

              if (!$ifdone[$dst][$dif['port_id']])
              {
                $map .= "\"" . $dif['port_id'] . "\" -> \"$dst\" [weight=500000, arrowsize=0, len=0];\n";
                $ifdone[$dst][$dif['port_id']] = 1;
              }
              $map .= "\"" . $sif['port_id'] . "\" -> \"" . $dif['port_id'] . "\" [weight=1, arrowhead=normal, arrowtail=normal, len=2, $info] \n";
            }
          }
        }

        $done = 0;
      }
    }
  }

  foreach ($ranks as $rank)
  {
    if (substr($rank[0], 0, 2) == "cr")
    {
      $map .= '{ rank=min; "' . implode('"; "', $rank) . "\"};\n";
    } else {
      $map .= '{ rank=same; "' . implode('"; "', $rank) . "\"};\n";
    }
  }

  $map .= "\n};";

/*
  if ($links > 30) // Unflatten if there are more than 10 links. beyond that it gets messy
  {
    $maptool = $config['unflatten'];
  } else {
*/
    $maptool = $config['dot'];
/*
  }
*/

  if ($where == '')
  {
#    $maptool = $config['unflatten'] . ' -f -l 5 | ' . $config['sfdp'] . ' -Gpack -Goverlap=prism -Gcharset=latin1 | dot';
#    $maptool = $config['sfdp'] . ' -Gpack -Goverlap=prism -Gcharset=latin1 -Gsize=20,20';
     $maptool = $config['dot'];
  }

  switch ($vars['format'])
  {
    case 'svg':
      header("Content-type: image/svg+xml");
      break;
    case 'png':
    default:
      $vars['format'] = 'png:gd';
      header("Content-type: image/png");
      break;
  }

  $descriptorspec = array(0 => array("pipe", "r"),1 => array("pipe", "w") );

  $mapfile = $config['temp_dir'] . "/"  . strgen() . ".png";

  $process = proc_open($maptool . ' -T' . $vars['format'], $descriptorspec, $pipes);

  if (is_resource($process))
  {
    fwrite($pipes[0], $map);
    fclose($pipes[0]);
    while (! feof($pipes[1])) { $img .= fgets($pipes[1]);}
    fclose($pipes[1]);
    $return_value = proc_close($process);
  }

  switch ($vars['format'])
  {
    case 'svg':
      $img = str_replace("<a ", '<a target="_parent" ', $img);
      break;
    case 'dot':
      $img = "<pre>\n$map\n</pre>";
      break;
    default:
      break;
  }

  echo($img);
} else {
   // No format specified
  if ($_SESSION['authenticated']) // FIXME level 7+ only?
  {
    echo('<center>
    <object width=1200 height=1000 data="'. $config['base_url'] . 'map.php?format=svg" type="image/svg+xml">
    </object>
</center>');
  }
}

// EOF
