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

unset($vars['page']);

// Setup here

//if (isset($_SESSION['widescreen']))
//{
//  $graph_width=1700;
//  $thumb_width=180;
//} else {
  $graph_width=1152;
  $thumb_width=113;
//}

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

if (OBS_DEBUG) { print_vars($graphtype); }

$type = $graphtype['type'];
$subtype = $graphtype['subtype'];

if (is_numeric($vars['device']))
{
  $device = device_by_id_cache($vars['device']);
} elseif (!empty($vars['device'])) {
  $device = device_by_name($vars['device']);
}

if (is_file($config['html_dir']."/includes/graphs/".$type."/auth.inc.php"))
{
  include($config['html_dir']."/includes/graphs/".$type."/auth.inc.php");
}

if (!$auth)
{
  print_error_permission();
  return;
}

  // If there is no valid device specified in the URL, generate an error.
  ## Not all things here have a device (multiple-port graphs or location graphs)
  //if (!is_array($device))
  //{
  //  print_error('<h3 class="box-title">No valid device specified</h4>
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
    $title .= " :: ".nicecase($subtype);
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
      $navbar['options']['graph'] = array('text' => nicecase($type).' ('.$subtype.')',
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

  echo '<div class="box box-solid" style="padding-bottom: 5px;">';

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

  echo('<table style="width: 100%; background: transparent;"><tr>');

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

  if ($type == "port")
  {
    if ($subtype == "bits")
    {
      $speed_list = array('auto' => 'Autoscale', 'speed'  => 'Interface Speed ('.formatRates($port['ifSpeed'], 4, 4).')');
      foreach ($config['graphs']['ports_scale_list'] as $entry)
      {
        $speed = intval(unit_string_to_numeric($entry, 1000));
        $speed_list[$entry] = formatRates($speed, 4, 4);
      }
      $search[] = array('type'    => 'select',          // Type
                        'name'    => 'Scale',           // Displayed title for item
                        'id'      => 'scale',           // Item id and name
                        'width'   => '200px',
                        'value'   => (isset($vars['scale']) ? $vars['scale'] : $config['graphs']['ports_scale_default']),
                        'values'  => $speed_list);
    }
    if (in_array($subtype, array('bits', 'percent', 'upkts', 'pktsize')))
    {
      $search[] = array('type'    => 'select',
                        'name'    => 'Graph style',
                        'id'      => 'style',
                        'width'   => '200px',
                        'value'   => (isset($vars['style']) ? $vars['style'] : $config['graphs']['style']),
                        'values'  => array('default' => 'Default', 'mrtg' => 'MRTG'));
    }
  }

  print_search($search, NULL, 'update', 'graphs'.generate_url($vars));
  unset($search, $speed_list, $speed);

// Run the graph to get data array out of it

$vars = array_merge($vars, $graph_array);
$vars['command_only'] = 1;

include($config['html_dir']."/includes/graphs/graph.inc.php");

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

$navbar['options_right']['graph_link']  =  array('text' => 'Link to Graph', 'url' => generate_graph_url($graph_array), 'link_opts' => 'target="_blank"');

print_navbar($navbar);
unset($navbar);

/*

?>


    <script type="text/javascript" src="js/jsrrdgraph/sprintf.js"></script>
    <script type="text/javascript" src="js/jsrrdgraph/strftime.js"></script>
    <script type="text/javascript" src="js/jsrrdgraph/RrdRpn.js"></script>
    <script type="text/javascript" src="js/jsrrdgraph/RrdTime.js"></script>
    <script type="text/javascript" src="js/jsrrdgraph/RrdGraph.js"></script>
    <script type="text/javascript" src="js/jsrrdgraph/RrdGfxCanvas.js"></script>
    <script type="text/javascript" src="js/jsrrdgraph/RrdGfxSvg.js"></script>
    <script type="text/javascript" src="js/jsrrdgraph/base64.js"></script>
    <script type="text/javascript" src="js/jsrrdgraph/RrdGfxPdf.js"></script>
    <script type="text/javascript" src="js/jsrrdgraph/binaryXHR.js"></script>
    <script type="text/javascript" src="js/jsrrdgraph/rrdFile.js"></script>
    <script type="text/javascript" src="js/jsrrdgraph/RrdDataFile.js"></script>
    <script type="text/javascript" src="js/jsrrdgraph/RrdCmdLine.js"></script>

<script type="application/x-javascript">
			var mouse_move = function (e) {
				if (this.rrdgraph.mousedown) {
					var factor = (this.rrdgraph.end - this.rrdgraph.start) / this.rrdgraph.xsize;
					var x = e.pageX - this.offsetLeft;
					var diff = x - this.rrdgraph.mousex;
					var difffactor = Math.abs(Math.round(diff*factor));
					if (diff > 0) {
						this.rrdgraph.end -= difffactor;
						this.rrdgraph.start -= difffactor;
					} else {
						this.rrdgraph.end += difffactor;
						this.rrdgraph.start += difffactor;
					}
					this.rrdgraph.mousex = x;
					var start = new Date();
					try {
						this.rrdgraph.graph_paint();
					} catch (e) {
						alert(e+"\n"+e.stack);
					}
					var end = new Date();
					document.getElementById("draw").innerHTML = 'Draw time: '+(end.getTime()-start.getTime())+"ms";
				}
			};
			var mouse_up = function (e) { 
				this.rrdgraph.mousedown = false;
				this.style.cursor="default";
			};
			var mouse_down = function (e) {
				var x = e.pageX - this.offsetLeft;
				this.rrdgraph.mousedown = true;
				this.rrdgraph.mousex = x;
				this.style.cursor="move";
			};
			var mouse_scroll = function (e) {
				e = e ? e : window.event;
				var wheel = e.detail ? e.detail * -1 : e.wheelDelta / 40;
				var cstime = this.stime[this.stidx];
				if (wheel > 0) {
					this.stidx++;
					if (this.stidx >= this.stlen) this.stidx = this.stlen-1;
				} else {
					this.stidx--;
					if (this.stidx < 0) this.stidx = 0;
				}
				if (cstime !== this.stime[this.stidx])  {
					var middle = this.rrdgraph.start + Math.abs(Math.round((this.rrdgraph.end - this.rrdgraph.start)/2));
					this.rrdgraph.start = Math.round(middle - this.stime[this.stidx]/2);
					this.rrdgraph.end = this.rrdgraph.start + this.stime[this.stidx];
					var start = new Date();
					try {
						this.rrdgraph.graph_paint();
					} catch (e) {
						alert(e+"\n"+e.stack);
					}
					var end = new Date();
					document.getElementById("draw").innerHTML = 'Draw time: '+(end.getTime()-start.getTime())+"ms";
				}
				if(e.stopPropagation)
					e.stopPropagation();
				if(e.preventDefault)
					e.preventDefault();
				e.cancelBubble = true;
				e.cancel = true;
				e.returnValue = false;
				return false; 
			};
			function draw() {
				RrdGraph.prototype.mousex = 0;
				RrdGraph.prototype.mousedown = false;
				var cmdline = document.getElementById("cmdline").value;
				var gfx = new RrdGfxCanvas("canvas");
        var fetch = new RrdDataFile();
        var rrdcmdline = null;
        var start = new Date();
        try {
          rrdcmdline = new RrdCmdLine(gfx, fetch, cmdline);
				} catch (e) {
					alert(e+"\n"+e.stack);
				}
				var rrdgraph = rrdcmdline.graph;
				
				gfx.canvas.stime = [ 300, 600, 900, 1200, 1800, 3600, 7200, 21600, 43200, 86400, 172800, 604800, 2592000, 5184000, 15768000, 31536000 ];
				gfx.canvas.stlen = gfx.canvas.stime.length;
				gfx.canvas.stidx = 0;
				gfx.canvas.rrdgraph = rrdgraph;
				gfx.canvas.removeEventListener('mousemove', mouse_move, false);
				gfx.canvas.addEventListener('mousemove', mouse_move, false);
				gfx.canvas.removeEventListener('mouseup', mouse_up, false);
				gfx.canvas.addEventListener('mouseup', mouse_up, false);
				gfx.canvas.removeEventListener('mousedown', mouse_down, false);
				gfx.canvas.addEventListener('mousedown', mouse_down, false);
				gfx.canvas.removeEventListener('mouseout', mouse_up, false);
				gfx.canvas.addEventListener('mouseout', mouse_up, false);
				gfx.canvas.removeEventListener('DOMMouseScroll', mouse_scroll, false);  
				gfx.canvas.addEventListener('DOMMouseScroll', mouse_scroll, false);  
				gfx.canvas.removeEventListener('mousewheel', mouse_scroll, false);
				gfx.canvas.addEventListener('mousewheel', mouse_scroll, false);
				var end = new Date();
				document.getElementById("parse").innerHTML = 'Parse time: '+(end.getTime()-start.getTime())+"ms";
				var diff = rrdgraph.end - rrdgraph.start;
				for (var i=0; i < gfx.canvas.stlen; i++) {
					if (gfx.canvas.stime[i] >= diff)  break;
				}
				if (i === gfx.canvas.stlen) gfx.canvas.stidx = gfx.canvas.stlen-1;
				else gfx.canvas.stidx = i;
				var start = new Date();
				try {
					rrdgraph.graph_paint();
				} catch (e) {
					alert(e+"\n"+e.stack);
				}
				var end = new Date();
				document.getElementById("draw").innerHTML = 'Draw time: '+(end.getTime()-start.getTime())+"ms";
			}
		</script>



<?php

 //list(,$cmd) = explode("png ", $graph_return['cmd']);

 $cmd = '
--start 1440149292 --end 1440235692 --width 1159 --height 300 -R normal
-c BACK#FFFFFF -c SHADEA#EEEEEE -c SHADEB#EEEEEE -c FONT#000000 -c CANVAS#FFFFFF -c GRID#a5a5a5 -c MGRID#FF9999 -c FRAME#EEEEEE -c ARROW#5e5e5e
--font-render-mode normal
-E
"COMMENT:Bits/s   Last       Avg      Max      95th\\n"
DEF:outoctets=/rrd/omega.memetic.org/port-2.rrd:OUTOCTETS:AVERAGE
DEF:inoctets=/rrd/omega.memetic.org/port-2.rrd:INOCTETS:AVERAGE
DEF:outoctets_max=/rrd/omega.memetic.org/port-2.rrd:OUTOCTETS:MAX
DEF:inoctets_max=/rrd/omega.memetic.org/port-2.rrd:INOCTETS:MAX
CDEF:alloctets=outoctets,inoctets,+
CDEF:wrongin=alloctets,UN,INF,UNKN,IF
CDEF:wrongout=wrongin,-1,*
"CDEF:octets=inoctets,outoctets,+"
CDEF:doutoctets=outoctets,-1,* CDEF:outbits=outoctets,8,* CDEF:outbits_max=outoctets_max,8,* CDEF:doutoctets_max=outoctets_max,-1,* CDEF:doutbits=doutoctets,8,* CDEF:doutbits_max=doutoctets_max,8,* CDEF:inbits=inoctets,8,* CDEF:inbits_max=inoctets_max,8,* 
"VDEF:totout=outoctets,TOTAL"
"VDEF:totin=inoctets,TOTAL"
"VDEF:tot=octets,TOTAL"
VDEF:95thin=inbits,95,PERCENT VDEF:95thout=outbits,95,PERCENT VDEF:d95thout=doutbits,5,PERCENT
"AREA:inbits#92B73F"
"LINE1.25:inbits#4A8328:In " "GPRINT:inbits:LAST:%6.2lf%s" "GPRINT:inbits:AVERAGE:%6.2lf%s" "GPRINT:inbits_max:MAX:%6.2lf%s" "GPRINT:95thin:%6.2lf%s\\n" "AREA:doutbits#7075B8" "LINE1.25:doutbits#323B7C:Out"
GPRINT:outbits:LAST:%6.2lf%s GPRINT:outbits:AVERAGE:%6.2lf%s GPRINT:outbits_max:MAX:%6.2lf%s "GPRINT:95thout:%6.2lf%s\\n"
"GPRINT:tot:Total %6.2lf%s"
"GPRINT:totin:(In %6.2lf%s"
"GPRINT:totout:Out %6.2lf%s)\\l"
LINE1:95thin#aa0000
LINE1:d95thout#aa0000';

 $cmd = str_replace("/mnt/ramdisk/observium_dev/", "rrd/", $cmd);
 $cmd = str_replace("'", '"', $cmd);
?>

<textarea id="cmdline" rows="10" cols="120" style="width: 800px"><?php echo $cmd; ?></textarea>

<canvas id="canvas"></canvas>

<p id="parse"></p>
<p id="draw"></p>

<script>javascript:draw();</script>

<?php
*/

/// End options navbar

  echo generate_graph_js_state($graph_array);

  echo('<div class="box box-solid">');
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

  <div class="box box-solid">
    <div class="box-header with-border">
      <h3 class="box-title">Performance &amp; Output</h3>
    </div>
    <div class="box-body">
      <?php echo("RRDTool Output: ".$return."<br />"); ?>
      <?php echo("<p>Total time: ".$graph_return['total_time']." | RRDtool time: ".$graph_return['rrdtool_time']."s</p>"); ?>
    </div>
  </div>

  <div class="box box-solid">
    <div class="box-header with-border">
      <h3 class="box-title">RRDTool Command</h3>
    </div>
    <div class="box-body">
      <?php echo($graph_return['cmd']); ?>
    </div>
  </div>

  <div class="box box-solid">
    <div class="box-header with-border">
      <h3 class="box-title">RRDTool Files Used</h3>
    </div>
    <div class="box-body">
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

// EOF
