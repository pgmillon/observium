<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage web
 * @copyright  (C) 2006-2015 Adam Armstrong
 *
 */

function show_status($config)
{
  // Show Status
  echo('    <div class="col-md-6 sortable">');
  echo('      <div class="widget widget-table">');
  echo('        <div class="widget-header"><i class="oicon-gear"></i><h3>Status</h3></div>');
  echo('        <div class="widget-content">');
  print_status($config['frontpage']['device_status']);
  echo('        </div>');
  echo('      </div>');
  echo('    </div>' . PHP_EOL);
} // End show_status

function show_status_boxes($config)
{
  // Show Status Boxes
  echo('    <div class="col-md-6 sortable">');
  echo('      <div class="widget widget-table">');
  echo('        <div class="widget-header"><i class="oicon-gear"></i><h3>Status</h3></div>');
  echo('        <div class="widget-content">');
  print_status_boxes($config['frontpage']['device_status']);
  echo('        </div>');
  echo('      </div>');
  echo('    </div>' . PHP_EOL);
} // End show_status_boxes

function show_eventlog($config)
{
  // Show eventlog
  echo('    <div class="col-md-6 sortable">');
  echo('      <div class="widget widget-table">');
  echo('        <div class="widget-header"><i class="oicon-gear"></i><h3>Eventlog</h3></div>');
  echo('        <div class="widget-content">');
  print_events(array('short' => TRUE, 'pagesize' => 5));
  echo('        </div>');
  echo('      </div>');
  echo('    </div>');
} // End show_eventlog

function show_syslog($config)
{
  // Show eventlog
  echo('    <div class="col-md-6 sortable">');
  echo('      <div class="widget widget-table">');
  echo('        <div class="widget-header"><i class="oicon-gear"></i><h3>Syslog</h3></div>');
  echo('        <div class="widget-content">');
  print_syslog(array('short' => TRUE, 'pagesize' => 5));
  echo('        </div>');
  echo('      </div>');
  echo('    </div>');
} // End show_syslog

function show_map($config)
{
  echo('    <div class="col-md-12 sortable">');
  echo('      <div class="widget widget-nopad">');
  echo('        <div class="widget-header"><i class="oicon-map"></i><h3>Device Map</h3></div>');
  echo('        <div class="widget-content">');
  ?>
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
<?php
  echo('        </div>');
  echo('      </div>');
  echo('    </div>');
} // End show_map

?>

<div class="row sortable-row">

  <?php // show_status_boxes($config); ?>

  <?php show_status($config); ?>

</div>
<div class="row sortable-row">

  <?php show_map($config); ?>

  <?php show_eventlog($config); ?>
</div>
<div class="row sortable-row">

  <?php show_eventlog($config); ?>

</div>

<script>
  $(function() {

    $('.sortable-row').sortable({
      connectWith: '.sortable-row',
      items: '.sortable',
      handle: '.widget-header',
      placeholder: 'well',
      forcePlaceholderSize: 'TRUE',

    });

  });
</script>
