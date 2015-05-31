<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage web
 * @author     Dennis de Houx <info@all-in-one.be>
 * @copyright  (C) 2006-2014 Adam Armstrong
 * @version    1.9.2
 *
 */

$vars['page'] = 'overview'; // Always set variable page (need for generate_query_permitted())

foreach ($config['frontpage']['order'] as $module)
{
  switch ($module)
  {
    case "status_summary":
      include("includes/status-summary.inc.php");
      break;
    case "map":
      show_map($config);
      break;
    case "device_status_boxes":
      show_status_boxes($config);
      break;
    case "device_status":
      show_status($config);
      break;
    case "alert_status":
      include("includes/alert-status.inc.php");
      break;
    case "overall_traffic":
      show_traffic($config);
      break;
    case "custom_traffic":
      show_customtraffic($config);
      break;
    case "splitlog":
      show_splitlog($config);
      break;
    case "syslog":
      show_syslog($config);
      break;
    case "eventlog":
      show_eventlog($config);
      break;
    case "minigraphs":
      show_minigraphs($config);
      break;
    case "micrographs":
      show_micrographs($config);
      break;
  }
}

function show_map($config)
{
  ?>
<div class="row" style="margin-bottom: 10px;">
  <div class="col-md-12">

  <style type="text/css">
    #chart_div label { width: auto; display:inline; }
    #chart_div img { max-width: none; }
  </style>
  <!-- <div id="reset" style="width: 100%; text-align: right;"><input type="button" onclick="resetMap();" value="Reset Map" /></div> -->
  <div id="chart_div" style="height: <?php echo($config['frontpage']['map']['height']); ?>px;"></div>

<?php
  switch ($config['frontpage']['map']['api'])
  {
    case 'google-mc':
    case 'google':
      $map = $config['frontpage']['map']['api'];
      break;
    default:
      $map = "google";
      break;
  }

  if (is_file("includes/map/$map.inc.php"))
  {
    include("includes/map/$map.inc.php");
  } else {
    print_error("Unknown map type: $map");
  }

?>
  </div>
</div>
<?php
}
  // End show_map

  function show_traffic($config)
  {
    // Show Traffic
    if ($_SESSION['userlevel'] >= '5')
    {
      unset ($separator);
      foreach (dbFetchRows("SELECT * FROM ports as I, devices as D WHERE port_descr_type = 'transit' AND I.device_id = D.device_id AND I.deleted = 0 ORDER BY I.ifAlias") as $interface)
      {
        $ports['transit'] .= $separator . $interface['port_id'];
        $separator = ",";
      }
      unset ($separator);
      foreach (dbFetchRows("SELECT * FROM ports as I, devices as D WHERE port_descr_type = 'peering' AND I.device_id = D.device_id AND I.deleted = 0 ORDER BY I.ifAlias") as $interface)
      {
        $ports['peering'] .= $separator . $interface['port_id'];
        $separator = ",";
      }
      unset ($separator);
      foreach (dbFetchRows("SELECT * FROM ports as I, devices as D WHERE port_descr_type = 'core' AND I.device_id = D.device_id AND I.deleted = 0 ORDER BY I.ifAlias") as $interface)
      {
        $ports['core'] .= $separator . $interface['port_id'];
        $separator = ",";
      }
      $links['transit']  = generate_url(array("page" => "iftype", "type" => "transit"));
      $links['peering']  = generate_url(array("page" => "iftype", "type" => "peering"));
      $links['peer_trans']  = generate_url(array("page" => "iftype", "type" => "peering,transit"));

      echo('<div class="row">');

      if ($ports['transit'] != '')
      {
        echo('  <div class="col-md-6 ">');
        echo('    <h3 class="bill">Overall Transit Traffic Today</h3>');
        echo('    <a href="'.$links['transit'].'"><img src="graph.php?type=multiport_bits_separate&amp;id='.$ports['transit'].'&amp;legend=no&amp;from='.$config['time']['day'].'&amp;to='.$config['time']['now'].'&amp;width=480&amp;height=100" alt="" /></a>');
        echo('  </div>');
      }

      if ($ports['peering'] != '')
      {
        echo('  <div class="col-md-6 ">');
        echo('    <h3 class="bill">Overall Peering Traffic Today</h3>');
        echo('    <a href="'.$links['peering'].'"><img src="graph.php?type=multiport_bits_separate&amp;id='.$ports['peering'].'&amp;legend=no&amp;from='.$config['time']['day'].'&amp;to='.$config['time']['now'].'&amp;width=480&amp;height=100" alt="" /></a>');
        echo('  </div>');
      }

      echo('</div>');

      if ($ports['transit'] != '' && $ports['peering'] != '')
      {
        echo('<div class="row">');
        echo('  <div class="col-md-12">');
        echo('    <h3 class="bill">Overall Transit &amp; Peering Traffic This Month</h3>');
        echo('    <a href="'.$links['peer_trans'].'"><img src="graph.php?type=multiport_bits_duo_separate&amp;id='.$ports['peering'].'&amp;idb='.$ports['transit'].'&amp;legend=no&amp;from='.$config['time']['month'].'&amp;to='.$config['time']['now'].'&amp;width=1100&amp;height=200" alt="" /></a>');
        echo('  </div>');
        echo('</div>');
      }

      unset($links);
    }
  }
  // End show_traffic

  function show_customtraffic($config)
  {
  // Show Custom Traffic
    if ($_SESSION['userlevel'] >= '5')
    {
      $config['frontpage']['custom_traffic']['title'] = (empty($config['frontpage']['custom_traffic']['title']) ? "Custom Traffic" : $config['frontpage']['custom_traffic']['title']);
      echo("<div class=\"row\">");
      echo("  <div class=\"col-md-6 \">");
      echo("    <h3 class=\"bill\">".$config['frontpage']['custom_traffic']['title']." Today</h3>");
      echo("    <img src=\"graph.php?type=multiport_bits&amp;id=".$config['frontpage']['custom_traffic']['ids']."&amp;legend=no&amp;from=".$config['time']['day']."&amp;to=".$config['time']['now']."&amp;width=480&amp;height=100\" alt=\"\" />");
      echo("  </div>");
      echo("  <div class=\"col-md-6 \">");
      echo("    <h3 class=\"bill\">".$config['frontpage']['custom_traffic']['title']." This Week</h3>");
      echo("    <img src=\"graph.php?type=multiport_bits&amp;id=".$config['frontpage']['custom_traffic']['ids']."&amp;legend=no&amp;from=".$config['time']['week']."&amp;to=".$config['time']['now']."&amp;width=480&amp;height=100\" alt=\"\" />");
      echo("  </div>");
      echo("</div>");
      echo("<div class=\"row\">");
      echo("  <div class=\"col-md-12 \">");
      echo("    <h3 class=\"bill\">".$config['frontpage']['custom_traffic']['title']." This Month</h3>");
      echo("    <img src=\"graph.php?type=multiport_bits&amp;id=".$config['frontpage']['custom_traffic']['ids']."&amp;legend=no&amp;from=".$config['time']['month']."&amp;to=".$config['time']['now']."&amp;width=1100&amp;height=200\" alt=\"\" />");
      echo("  </div>");
      echo("</div>");
    }
  }  // End show_customtraffic

  function show_minigraphs($config)
  {
    // Show Custom MiniGraphs
    if ($_SESSION['userlevel'] >= '5')
    {
      $minigraphs = explode(";", $config['frontpage']['minigraphs']['ids']);
      $legend = (($config['frontpage']['minigraphs']['legend'] == false) ? "no" : "yes");
      echo("<div class=\"row\">\n");
      echo("  <div class=\"col-md-12\">\n");
      if ($config['frontpage']['minigraphs']['title'])
      {
        echo("    <h3 class=\"bill\">".$config['frontpage']['minigraphs']['title']."</h3>\n");
      }

      foreach ($minigraphs as $graph)
      {
        list($device, $type, $header) = explode(",", $graph, 3);
        if (strpos($type, "device") === false)
        {
          $links = generate_url(array("page" => "graphs", "type" => $type, "id" => $device));
    //, "from" => $config['time']['day'], "to" => $config['time']['now']));
          echo("    <div class=\"pull-left\"><p style=\"text-align: center; margin-bottom: 0px;\"><strong>".$header."</strong></p><a href=\"".$links."\"><img src=\"graph.php?type=".$type."&amp;id=".$device."&amp;legend=".$legend."&amp;from=".$config['time']['day']."&amp;to=".$config['time']['now']."&amp;width=215&amp;height=100\"/></a></div>\n");
        } else {
          $links = generate_url(array("page" => "graphs", "type" => $type, "device" => $device));
    //, "from" => $config['time']['day'], "to" => $config['time']['now']));
          echo("    <div class=\"pull-left\"><p style=\"text-align: center; margin-bottom: 0px;\"><strong>".$header."</strong></p><a href=\"".$links."\"><img src=\"graph.php?type=".$type."&amp;device=".$device."&amp;legend=".$legend."&amp;from=".$config['time']['day']."&amp;to=".$config['time']['now']."&amp;width=215&amp;height=100\"/></a></div>\n");
        }
      }

      unset($links);
      echo("  </div>\n");
      echo("</div>\n");
    }
  } // End show_minigraphs

  function show_micrographs($config)
  {
    echo("<!-- Show custom micrographs -->\n");
    if ($_SESSION['userlevel'] >= '5')
    {
      $width = $config['frontpage']['micrograph_settings']['width'];
      $height = $config['frontpage']['micrograph_settings']['height'];
      echo("<div class=\"row\">\n");
      echo("  <div class=\"col-md-12\">\n");
      echo("  <table class=\"table table-bordered table-condensed-more table-rounded\">\n");
      echo("    <tbody>\n");
      foreach ($config['frontpage']['micrographs'] as $row)
      {
        $micrographs = explode(";", $row['ids']);
        $legend = (($row['legend'] == false) ? "no" : "yes");
        echo("    <tr>\n");
        if ($row['title'])
        {
          echo("      <th style=\"vertical-align: middle;\">".$row['title']."</th>\n");
        }

        echo("      <td>");
        foreach ($micrographs as $graph)
        {
          list($device, $type, $header) = explode(",", $graph, 3);
          if (strpos($type, "device") === false)
          {
            $which = "id";
          } else {
            $which = "device";
          }

          $links = generate_url(array("page" => "graphs", "type" => $type, $which => $device));
          echo("<div class=\"pull-left\">");
          if ($header)
          {
            echo("<p style=\"text-align: center; margin-bottom: 0px;\">".$header."</p>");
          }

          echo("<a href=\"".$links."\" style=\"margin-left: 5px\"><img src=\"graph.php?type=".$type."&amp;".$which."=".$device."&amp;legend=".$legend."&amp;width=".$width."&amp;height=".$height."\"/></a>");
          echo("</div>");
        }

        unset($links);
        echo("      </td>\n");
        echo("    </tr>\n");
      }

      echo("    </tbody>\n");
      echo("  </table>\n");
      echo("  </div>\n");
      echo("</div>\n");
    }
  } // End show_micrographs

  function show_status($config)
  {
    // Show Status
    echo('<div class="row">' . PHP_EOL);
    echo('  <div class="col-md-12">' . PHP_EOL);
    echo('    <h3 class="bill">Device Alerts</h3>' . PHP_EOL);
    print_status($config['frontpage']['device_status']);
    echo('  </div>' . PHP_EOL);
    echo('</div>' . PHP_EOL);
  } // End show_status

  function show_status_boxes($config)
  {
    // Show Status Boxes
    echo('<div class="row">' . PHP_EOL);
    echo('  <div class="col-md-12" style="padding-right: 0px;">' . PHP_EOL);
    print_status_boxes($config['frontpage']['device_status']);
    echo('  </div>' . PHP_EOL);
    echo('</div>' . PHP_EOL);
  } // End show_status_boxes

  function show_syslog($config)
  {
    // Show syslog
    echo('<div class="row">' . PHP_EOL);
    echo('  <div class="col-md-12 ">' . PHP_EOL);
    echo('    <h3 class="bill">Recent Syslog Messages</h3>' . PHP_EOL);
    print_syslogs(array('short' => TRUE, 'pagesize' => $config['frontpage']['syslog']['items'], 'priority' => $config['frontpage']['syslog']['priority']));
    echo('  </div>' . PHP_EOL);
    echo('</div>' . PHP_EOL);
  } // End show_syslog

  function show_eventlog($config)
  {
    // Show eventlog
    echo('<div class="row">' . PHP_EOL);
    echo('  <div class="col-md-12">' . PHP_EOL);
    echo('    <h3 class="bill">Recent Eventlog Entries</h3>' . PHP_EOL);
    print_events(array('short' => TRUE, 'pagesize' => $config['frontpage']['eventlog']['items']));
    echo('  </div>' . PHP_EOL);
    echo('</div>' . PHP_EOL);
  } // End show_eventlog

  function show_splitlog($config)
  {
    //Show syslog and eventlog
    echo('<div class="row">' . PHP_EOL);
    echo('  <div class="col-md-6">' . PHP_EOL);
    echo('    <h3 class="bill">Recent Eventlog Entries</h3>' . PHP_EOL);
    print_events(array('short' => true, 'pagesize' => $config['frontpage']['eventlog']['items']));
    echo('  </div>' . PHP_EOL);
    echo('  <div class="col-md-6">' . PHP_EOL);
    echo('    <h3 class="bill">Recent Syslog Messages</h3>' . PHP_EOL);
    print_syslogs(array('short' => true, 'pagesize' => $config['frontpage']['syslog']['items'], 'priority' => $config['frontpage']['syslog']['priority']));
    echo('  </div>' . PHP_EOL);
    echo('</div>' . PHP_EOL);
  }

// EOF
