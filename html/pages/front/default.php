<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage web
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

$vars['page'] = 'overview'; // Always set variable page (need for generate_query_permitted())

foreach ($config['frontpage']['order'] as $module)
{
  switch ($module)
  {
    case "status_summary":
      $div_class = "col-md-6"; // Class for each block
      echo('<div class="row hidden-xl">' . PHP_EOL); // Moved on XL screen to panel side
      include($config['html_dir']."/includes/status-summary.inc.php");
      echo('</div>' . PHP_EOL);
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
    case "alert_table":

      print_alert_table(array('status' => 'failed', 'pagination' => FALSE, 'short' => TRUE,
                              'header' => array('title' => 'Current Alerts',
                                                'url' => '/alerts/'
                                                )
                       ));

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
<div class="row">
  <div class="col-sm-12">

  <div class="box box-solid">

  <style type="text/css">
    #chart_div label { width: auto; display:inline; }
    #chart_div img { max-width: none; }
  </style>
  <!-- <div id="reset" style="width: 100%; text-align: right;"><input type="button" onclick="resetMap();" value="Reset Map" /></div> -->
  <div id="chart_div" style="height: <?php echo($config['frontpage']['map']['height']); ?>px;"></div>

  </div>

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

  if (is_file($config['html_dir']."/includes/map/$map.inc.php"))
  {
    include($config['html_dir']."/includes/map/$map.inc.php");
  } else {
    print_error("Unknown map type: $map");
  }
?>
  </div>
</div>
<?php
} // End show_map

  function show_traffic($config)
  {
    // Show Traffic
    if ($_SESSION['userlevel'] >= '5')
    {
      $ports = array();
      $query_permitted = generate_query_permitted(array('port'));
      foreach (dbFetchRows("SELECT * FROM `ports` WHERE `port_descr_type` IS NOT NULL $query_permitted ORDER BY `ifAlias`") as $port)
      {
        switch ($port['port_descr_type'])
        {
          case 'transit':
          case 'peering':
          case 'core':
            $ports[$port['port_descr_type']][] = $port['port_id'];
            break;
        }
      }

      echo('<div class="row">');

      if (count($ports['transit']))
      {
        $links['transit']      = generate_url(array("page" => "iftype", "type" => "transit"));
        $ports['transit_list'] = implode(',', $ports['transit']);
        echo('  <div class="col-sm-6 ">');
        echo('    <h3><a href="/iftype/type=transit">Overall Transit Traffic Today</a></h3>');
        echo('    <a href="'.$links['transit'].'"><img src="graph.php?type=multi-port_bits_separate&amp;id='.$ports['transit_list'].'&amp;legend=no&amp;from='.$config['time']['day'].'&amp;to='.$config['time']['now'].'&amp;width=480&amp;height=100" alt="" /></a>');
        echo('  </div>');

        if (empty($ports['peering']))
        {
          echo('  <div class="col-sm-6 ">');
          echo('    <h3><a href="/iftype/type=transit">Overall Transit Traffic This Week</a></h3>');
          echo('    <a href="'.$links['transit'].'"><img src="graph.php?type=multi-port_bits_separate&amp;id='.$ports['transit_list'].'&amp;legend=no&amp;from='.$config['time']['week'].'&amp;to='.$config['time']['now'].'&amp;width=480&amp;height=100" alt="" /></a>');
          echo('  </div>');
        }
      }

      if (count($ports['peering']))
      {
        $links['peering']      = generate_url(array("page" => "iftype", "type" => "peering"));
        $ports['peering_list'] = implode(',', $ports['peering']);
        echo('  <div class="col-sm-6 ">');
        echo('    <h3><a href="/iftype/type=peering">Overall Peering Traffic Today</a></h3>');
        echo('    <a href="'.$links['peering'].'"><img src="graph.php?type=multi-port_bits_separate&amp;id='.$ports['peering_list'].'&amp;legend=no&amp;from='.$config['time']['day'].'&amp;to='.$config['time']['now'].'&amp;width=480&amp;height=100" alt="" /></a>');
        echo('  </div>');

        if (empty($ports['transit']))
        {
          echo('  <div class="col-sm-6 ">');
          echo('    <h3><a href="/iftype/type=peering">Overall Peering Traffic This Week</a></h3>');
          echo('    <a href="'.$links['peering'].'"><img src="graph.php?type=multi-port_bits_separate&amp;id='.$ports['peering_list'].'&amp;legend=no&amp;from='.$config['time']['week'].'&amp;to='.$config['time']['now'].'&amp;width=480&amp;height=100" alt="" /></a>');
          echo('  </div>');
        }
      }

      echo('</div>');

      if ($ports['transit_list'] && $ports['peering_list'])
      {
        $links['peer_trans']  = generate_url(array("page" => "iftype", "type" => "peering,transit"));
        echo('<div class="row">');
        echo('  <div class="col-sm-12">');
        echo('    <h3><a href="/iftype/type=transit%2Cpeering">Overall Transit &amp; Peering Traffic This Month</a></h3>');
        echo('    <a href="'.$links['peer_trans'].'"><img src="graph.php?type=multi-port_bits_duo_separate&amp;id='.$ports['peering_list'].'&amp;idb='.$ports['transit_list'].'&amp;legend=no&amp;from='.$config['time']['month'].'&amp;to='.$config['time']['now'].'&amp;width=1100&amp;height=200" alt="" /></a>');
        echo('  </div>');
        echo('</div>');
      }
      else if ($ports['transit_list'] && !$ports['peering_list'])
      {
        echo('<div class="row">');
        echo('  <div class="col-sm-12">');
        echo('    <h3><a href="/iftype/type=transit">Overall Transit Traffic This Month</a></h3>');
        echo('    <a href="'.$links['transit'].'"><img src="graph.php?type=multi-port_bits_separate&amp;id='.$ports['transit_list'].'&amp;legend=no&amp;from='.$config['time']['month'].'&amp;to='.$config['time']['now'].'&amp;width=1100&amp;height=200" alt="" /></a>');
        echo('  </div>');
        echo('</div>');
      }
      else if (!$ports['transit_list'] && $ports['peering_list'])
      {
        echo('<div class="row">');
        echo('  <div class="col-sm-12">');
        echo('    <h3><a href="/iftype/type=peering">Overall Peering Traffic This Month</a></h3>');
        echo('    <a href="'.$links['peering'].'"><img src="graph.php?type=multi-port_bits_separate&amp;id='.$ports['peering_list'].'&amp;legend=no&amp;from='.$config['time']['month'].'&amp;to='.$config['time']['now'].'&amp;width=1100&amp;height=200" alt="" /></a>');
        echo('  </div>');
        echo('</div>');
      }
    }
  } // End show_traffic

  function show_customtraffic($config)
  {
    // Show Custom Traffic
    if ($_SESSION['userlevel'] >= '5')
    {
      $config['frontpage']['custom_traffic']['title'] = (empty($config['frontpage']['custom_traffic']['title']) ? "Custom Traffic" : $config['frontpage']['custom_traffic']['title']);
      echo('<div class="row">');
      echo('  <div class="col-sm-6 ">');
      echo('    <h3 class="bill">'.$config['frontpage']['custom_traffic']['title'].' Today</h3>');
      echo('    <img src="graph.php?type=multi-port_bits&amp;id='.$config['frontpage']['custom_traffic']['ids'].'&amp;legend=no&amp;from='.$config['time']['day'].'&amp;to='.$config['time']['now'].'&amp;width=480&amp;height=100" alt="" />');
      echo('  </div>');
      echo('  <div class="col-sm-6 ">');
      echo('    <h3 class="bill">'.$config['frontpage']['custom_traffic']['title'].' This Week</h3>');
      echo('    <img src="graph.php?type=multi-port_bits&amp;id='.$config['frontpage']['custom_traffic']['ids'].'&amp;legend=no&amp;from='.$config['time']['week'].'&amp;to='.$config['time']['now'].'&amp;width=480&amp;height=100" alt="" />');
      echo('  </div>');
      echo('</div>');
      echo('<div class="row">');
      echo('  <div class="col-sm-12 ">');
      echo('    <h3 class="bill">'.$config['frontpage']['custom_traffic']['title'].' This Month</h3>');
      echo('    <img src="graph.php?type=multi-port_bits&amp;id='.$config['frontpage']['custom_traffic']['ids'].'&amp;legend=no&amp;from='.$config['time']['month'].'&amp;to='.$config['time']['now'].'&amp;width=1100&amp;height=200" alt="" />');
      echo('  </div>');
      echo('</div>');
    }
  }  // End show_customtraffic

  function show_minigraphs($config)
  {
    // Show Custom MiniGraphs
    if ($_SESSION['userlevel'] >= '5')
    {
      $minigraphs = explode(';', $config['frontpage']['minigraphs']['ids']);
      $width = $config['frontpage']['minigraphs']['width'];
      $height = $config['frontpage']['minigraphs']['height'];
      $legend = (($config['frontpage']['minigraphs']['legend'] == false) ? 'no' : 'yes');
      echo('<div class="row">');
      echo('  <div class="col-sm-12">');
      if ($config['frontpage']['minigraphs']['title'])
      {
        echo('    <h3 class="bill">'.$config['frontpage']['minigraphs']['title'].'</h3>');
      }

      foreach ($minigraphs as $graph)
      {
        if (!$graph) { continue; } // Skip empty graphs from excess semicolons
        list($device, $type, $header) = explode(',', $graph, 3);
        if (strpos($type, 'device') === false)
        {
          if (strpos($type, 'multi') !== false) // Copy/pasted id= from multi graph url works, prevents broken uri
          {
            $links = generate_url(array('page' => 'graphs', 'type' => $type, 'id' => urldecode($device)));
          } else {
            $links = generate_url(array('page' => 'graphs', 'type' => $type, 'id' => $device));
          }
          //, 'from' => $config['time']['day'], 'to' => $config['time']['now']));
          echo('    <div class="pull-left"><p style="text-align: center; margin-bottom: 0px;"><strong>'.$header.'</strong></p><a href="'.$links.'"><img src="graph.php?type='.$type.'&amp;id='.$device.'&amp;legend='.$legend.'&amp;from='.$config['time']['day'].'&amp;to='.$config['time']['now'].'&amp;width='.$width.'&amp;height='.$height.'"/></a></div>');
        } else {
          $links = generate_url(array('page' => 'graphs', 'type' => $type, 'device' => $device));
          //, 'from' => $config['time']['day'], 'to' => $config['time']['now']));
          echo('    <div class="pull-left"><p style="text-align: center; margin-bottom: 0px;"><strong>'.$header.'</strong></p><a href="'.$links.'"><img src="graph.php?type='.$type.'&amp;device='.$device.'&amp;legend='.$legend.'&amp;from='.$config['time']['day'].'&amp;to='.$config['time']['now'].'&amp;width='.$width.'&amp;height='.$height.'"/></a></div>'); // Apply custom dimensions to device graphs
        }
      }

      unset($links);
      echo('  </div>');
      echo('</div>');
    }
  } // End show_minigraphs

  function show_micrographs($config)
  {
    echo('<!-- Show custom micrographs -->');
    if ($_SESSION['userlevel'] >= '5')
    {
      $width = $config['frontpage']['micrograph_settings']['width'];
      $height = $config['frontpage']['micrograph_settings']['height'];
      echo('<div class="row">');
      echo('  <div class="col-sm-12">');
      echo('  <table class="box box-solid table  table-condensed-more table-rounded">');
      echo('    <tbody>');
      foreach ($config['frontpage']['micrographs'] as $row)
      {
        $micrographs = explode(';', $row['ids']);
        $legend = (($row['legend'] == false) ? 'no' : 'yes');
        echo('    <tr>');
        if ($row['title'])
        {
          echo('      <th style="vertical-align: middle;">'.$row['title'].'</th>');
        }

        echo('      <td>');
        foreach ($micrographs as $graph)
        {
          if (!$graph) { continue; } // Skip empty graphs from excess semicolons
          list($device, $type, $header) = explode(',', $graph, 3);
          if (strpos($type, 'device') === false)
          {
            $which = 'id';
            if (strpos($type, 'multi') !== false) // Copy/pasted id= from multi graph url works, prevents broken uri
            {
              $links = generate_url(array('page' => 'graphs', 'type' => $type, 'id' => urldecode($device)));
            } else {
              $links = generate_url(array('page' => 'graphs', 'type' => $type, 'id' => $device));
            }
          } else {
            $which = 'device';
            $links = generate_url(array('page' => 'graphs', 'type' => $type, 'device' => $device));
          }

          echo('<div class="pull-left">');
          if ($header)
          {
            echo('<p style="text-align: center; margin-bottom: 0px;">'.$header.'</p>');
          }

          echo('<a href="'.$links.'" style="margin-left: 5px"><img src="graph.php?type='.$type.'&amp;'.$which.'='.$device.'&amp;legend='.$legend.'&amp;width='.$width.'&amp;height='.$height.'"/></a>');
          echo('</div>');
        }

        unset($links);
        echo('      </td>');
        echo('    </tr>');
      }

      echo('    </tbody>');
      echo('  </table>');
      echo('  </div>');
      echo('</div>');
    }
  } // End show_micrographs

  function show_status($config)
  {
    // Show Status

    echo generate_box_open(array('title' => '<a href="/alerts/">Device Status Notifications</a>', 'header-border' => TRUE));
    print_status($config['frontpage']['device_status']);
    echo generate_box_close();
  } // End show_status

  function show_status_boxes($config)
  {
    // Show Status Boxes
    echo('<div class="row">' . PHP_EOL);
    echo('  <div class="col-sm-12" style="padding-right: 0px;">' . PHP_EOL);
    print_status_boxes($config['frontpage']['device_status']);
    echo('  </div>' . PHP_EOL);
    echo('</div>' . PHP_EOL);
  } // End show_status_boxes

  function show_syslog($config)
  {
    // Show syslog

    print_syslogs(array('short' => TRUE, 'pagesize' => $config['frontpage']['syslog']['items'], 'priority' => $config['frontpage']['syslog']['priority'],
                        'header' => array('url' => '/syslog/', 'title' => 'Recent Syslog Messages', 'header-border' => TRUE)));

  } // End show_syslog

  function show_eventlog($config)
  {
    print_events(array('short' => TRUE, 'pagesize' => $config['frontpage']['eventlog']['items'], 'severity' => $config['frontpage']['eventlog']['severity'],
                       'header' => array('url' => '/eventlog/', 'title' => 'Recent Events', 'header-border' => TRUE)));

  } // End show_eventlog

  function show_splitlog($config)
  {
    echo '<div class="row">' . PHP_EOL;
    echo '  <div class="col-sm-6">' . PHP_EOL;
    print_events(array('short' => TRUE, 'pagesize' => $config['frontpage']['eventlog']['items'], 'severity' => $config['frontpage']['eventlog']['severity'],
                       'header' => array('url' => '/eventlog/', 'title' => 'Recent Events', 'header-border' => TRUE)));
    echo '  </div>';

    echo '  <div class="col-sm-6">' . PHP_EOL;
    print_syslogs(array('short' => TRUE, 'pagesize' => $config['frontpage']['syslog']['items'], 'priority' => $config['frontpage']['syslog']['priority'],
                        'header' => array('url' => '/syslog/', 'title' => 'Recent Syslog Messages', 'header-border' => TRUE)));
    echo '  </div>';
    echo '</div>';
  }

// EOF
