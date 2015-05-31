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

unset($vars['page']);

// Setup here

if (isset($_SESSION['widescreen']))
{
  $graph_width=1700;
  $thumb_width=180;
} else {
  $graph_width=1159;
  $thumb_width=113;
}

$timestamp_pattern = '/^(\d{4})-(\d{2})-(\d{2}) ([01][0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])$/';
if (isset($vars['timestamp_from']) && preg_match($timestamp_pattern, $vars['timestamp_from']))
{
  $vars['from'] = strtotime($vars['timestamp_from']);
  unset($vars['timestamp_from']);
}
if (isset($vars['timestamp_to'])   && preg_match($timestamp_pattern, $vars['timestamp_to']))
{
  $vars['to'] = strtotime($vars['timestamp_to']);
  unset($vars['timestamp_to']);
}
if (!is_numeric($vars['from'])) { $vars['from'] = $config['time']['day']; }
if (!is_numeric($vars['to']))   { $vars['to']   = $config['time']['now']; }

preg_match('/^(?P<type>[a-z0-9A-Z-]+)_(?P<subtype>.+)/', $vars['type'], $graphtype);

if ($debug) print_vars($graphtype);

$type = $graphtype['type'];
$subtype = $graphtype['subtype'];

if (is_numeric($vars['device']))
{
  $device = device_by_id_cache($vars['device']);
} elseif (!empty($vars['device'])) {
  $device = device_by_name($vars['device']);
}

if (is_file("includes/graphs/".$type."/auth.inc.php"))
{
  include("includes/graphs/".$type."/auth.inc.php");
}

if (!$auth)
{
  include("includes/error-no-perm.inc.php");
} else {

  // If there is no valid device specified in the URL, generate an error.
  ## Not all things here have a device (multiple-port graphs or location graphs)
  //if (!is_array($device))
  //{
  //  print_error('<h3>No valid device specified</h3>
  //                  A valid device was not specified in the URL. Please retype and try again.');
  //  break;
  //}

  // Print the device header
  if (isset($device) && is_array($device))
  {
    print_device_header($device);
  }

  if (isset($config['graph_types'][$type][$subtype]['descr']))
  {
    $title .= " :: ".$config['graph_types'][$type][$subtype]['descr'];
  } else {
    $title .= " :: ".ucfirst($subtype);
  }

  // Generate navbar with subtypes
  $graph_array = $vars;
  $graph_array['height'] = "60";
  $graph_array['width']  = $thumb_width;
  $graph_array['legend'] = "no";
  $graph_array['to']     = $config['time']['now'];

  $navbar = array('brand' => "Graph", 'class' => "navbar-narrow");

  switch ($type)
  {
    case 'device':
    case 'sensor':
    case 'cefswitching':
    case 'munin':
      $navbar['options']['graph'] = array('text' => ucfirst($type).' ('.$subtype.')',
                                          'url' => generate_url($vars, array('type' => $type."_".$subtype, 'page' => "graphs")));
      break;
    default:
      # Load our list of available graphtypes for this object
      /// FIXME not all of these are going to be valid
      /// This is terrible. --mike
      /// The future solution is to keep a 'registry' of which graphtypes apply to which entities and devices.
      /// I'm not quite sure if this is going to be too slow. --adama 2013-11-11
      if ($handle = opendir($config['html_dir'] . "/includes/graphs/".$type."/"))
      {
        while (false !== ($file = readdir($handle)))
        {
          if ($file != "." && $file != ".." && $file != "auth.inc.php" && $file != "graph.inc.php" && strstr($file, ".inc.php"))
          {
            $types[] = str_replace(".inc.php", "", $file);
          }
        }
        closedir($handle);
      }

      foreach ($title_array as $key => $element)
      {
        $navbar['options'][$key] = $element;
      }

      $navbar['options']['graph']     = array('text' => 'Graph');

      sort($types);

      foreach ($types as $avail_type)
      {
        if ($subtype == $avail_type)
        {
          $navbar['options']['graph']['suboptions'][$avail_type]['class'] = 'active';
          $navbar['options']['graph']['text'] .= ' ('.$avail_type.')';
        }
        $navbar['options']['graph']['suboptions'][$avail_type]['text'] = $avail_type;
        $navbar['options']['graph']['suboptions'][$avail_type]['url'] = generate_url($vars, array('type' => $type."_".$avail_type, 'page' => "graphs"));
      }
  }

  print_navbar($navbar);

  // Start form for the custom range.

  echo '<div class="well well-shaded">';

  $thumb_array = array('sixhour' => '6 Hours',
                       'day' => '24 Hours',
                       'twoday' => '48 Hours',
                       'week' => 'One Week',
                       //'twoweek' => 'Two Weeks',
                       'month' => 'One Month',
                       //'twomonth' => 'Two Months',
                       'year' => 'One Year',
                       'twoyear' => 'Two Years'
                      );

  echo('<table width=100% style="background: transparent;"><tr>');

  foreach ($thumb_array as $period => $text)
  {
    $graph_array['from']   = $config['time'][$period];

    $link_array = $vars;
    $link_array['from'] = $graph_array['from'];
    $link_array['to'] = $graph_array['to'];
    $link_array['page'] = "graphs";
    $link = generate_url($link_array);

    echo('<td style="text-align: center;">');
    echo('<span class="device-head">'.$text.'</span><br />');
    echo('<a href="'.$link.'">');
    echo(generate_graph_tag($graph_array));
    echo('</a>');
    echo('</td>');

  }

  echo('</tr></table>');

  $graph_array = $vars;
  $graph_array['height'] = "300";
  $graph_array['width']  = $graph_width;

  print_optionbar_end();

  $search = array();
  $search[] = array('type'    => 'datetime',
                    'id'      => 'timestamp',
                    'presets' => TRUE,
                    'min'     => '2007-04-03 16:06:59',  // Hehe, who will guess what this date/time means? --mike
                                                         // First commit! Though Observium was already 7 months old by that point. --adama
                    'max'     => date('Y-m-d 23:59:59'), // Today
                    'from'    => date('Y-m-d H:i:s', $vars['from']),
                    'to'      => date('Y-m-d H:i:s', $vars['to']));
  print_search($search, NULL, 'update');
  unset($search);

// Run the graph to get data array out of it

$vars = array_merge($vars, $graph_array);
$vars['command_only'] = 1;

include("includes/graphs/graph.inc.php");

unset($vars['command_only']);

// Print options navbar

$navbar = array();
$navbar['brand'] = "Options";
$navbar['class'] = "navbar-narrow";

$navbar['options']['legend']   =  array('text' => 'Show Legend', 'inverse' => TRUE);
$navbar['options']['previous'] =  array('text' => 'Graph Previous');
$navbar['options']['trend']    =  array('text' => 'Graph Trend');
$navbar['options']['max']      =  array('text' => 'Graph Maximum');

$navbar['options_right']['showcommand'] =  array('text' => 'RRD Command');

foreach (array('options' => $navbar['options'], 'options_right' => $navbar['options_right'] ) as $side => $options)
{
  foreach ($options AS $option => $array)
  {
    if ($array['inverse'] == TRUE)
    {
      if ($vars[$option] == "no")
      {
        $navbar[$side][$option]['url'] = generate_url($vars, array('page' => "graphs", $option => NULL));
      } else {
        $navbar[$side][$option]['url'] = generate_url($vars, array('page' => "graphs", $option => 'no'));
        $navbar[$side][$option]['class'] .= " active";
      }
    } else {
      if ($vars[$option] == "yes")
      {
        $navbar[$side][$option]['url'] = generate_url($vars, array('page' => "graphs", $option => NULL));
        $navbar[$side][$option]['class'] .= " active";
      } else {
        $navbar[$side][$option]['url'] = generate_url($vars, array('page' => "graphs", $option => 'yes'));
      }
    }
  }
}

print_navbar($navbar);
unset($navbar);

/// End options navbar

  echo generate_graph_js_state($graph_array);

  echo('<div style="width: '.$graph_array['width'].'; margin: auto;">');
  echo(generate_graph_tag($graph_array));
  echo("</div>");

  if (isset($graph_return['descr']))
  {

    print_optionbar_start();
    echo('<div style="float: left; width: 30px;">
          <div style="margin: auto auto;">
            <i class="oicon-information"></i>
          </div>
          </div>');
    echo($graph_return['descr']);
    print_optionbar_end();
  }

#print_vars($graph_return);

  if (isset($vars['showcommand']))
  {
?>

  <div class="well info_box">
    <div class="title">
      <i class="oicon-clock"></i> Performance &amp; Output
    </div>
    <div class="content">
      <?php echo("RRDTool Output: ".$return."<br />"); ?>
      <?php echo("<p>Total time: ".$graph_return['total_time']." | RRDtool time: ".$graph_return['rrdtool_time']."s</p>"); ?>
    </div>
  </div>

  <div class="well info_box">
    <div class="title">
      <i class="oicon-application-terminal"></i> RRDTool Command
    </div>
    <div class="content">
      <?php echo($graph_return['cmd']); ?>
    </div>
  </div>

  <div class="well info_box">
    <div class="title">
      <i class="oicon-database"></i> RRDTool Files Used
    </div>
    <div class="content">
      <?php
        if (is_array($graph_return['rrds']))
        {
          foreach ($graph_return['rrds'] as $rrd)
          {
            echo("$rrd <br />");
          }
        } else {
            echo("No RRD information returned. This may be because the graph module doesn't yet return this data. <br />");
        }
      ?>
    </div>
  </div>
<?php
  }
}

// EOF
