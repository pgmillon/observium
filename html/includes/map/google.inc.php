<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage map
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

?>
  <script type='text/javascript' src='//www.google.com/jsapi'></script>
  <script type='text/javascript'>
    google.load('visualization', '1.1', {'packages': ['geochart']});
    google.setOnLoadCallback(drawRegionsMap);
    function drawRegionsMap() {
      var data = new google.visualization.DataTable();
      data.addColumn('number', 'Latitude');
      data.addColumn('number', 'Longitude');
      data.addColumn('string', 'Location');
      data.addColumn('number', 'Status');
      data.addColumn('number', 'Devices');
      data.addColumn({type: 'string', role: 'tooltip'});
      data.addColumn('string', 'url');
      data.addRows([
        <?php
        $locations_up = array();
        $locations_down = array();
        foreach (get_locations() as $location)
        {
          $location_name = ($location === '' ? OBS_VAR_UNSET : strtr(escape_html($location), "'\\", "`/"));
          $location_url = generate_location_url($location);
          $devices_down = array();
          $devices_up = array();
          $count = $GLOBALS['cache']['device_locations'][$location];
          $down  = 0;
          foreach ($GLOBALS['cache']['devices']['id'] as $device)
          {
            if ($device['location'] == $location)
            {
              if ($device['status'] == "0" && $device['ignore'] == "0")
              {
                $down++;
                $devices_down[] = $device['hostname'];
                $lat = (is_numeric($device['location_lat']) ? $device['location_lat'] : $config['geocoding']['default']['lat']);
                $lon = (is_numeric($device['location_lon']) ? $device['location_lon'] : $config['geocoding']['default']['lon']);
              }
              else if ($device['status'] == "1")
              {
                $devices_up[]   = $device['hostname'];
                $lat = (is_numeric($device['location_lat']) ? $device['location_lat'] : $config['geocoding']['default']['lat']);
                $lon = (is_numeric($device['location_lon']) ? $device['location_lon'] : $config['geocoding']['default']['lon']);
              }
            }
          }
          $count = (($count < 100) ? $count : 100);
          if ($down > 0)
          {
            $locations_down[] = "[$lat, $lon, '$location_name', $down, ".$count*$down.", '".count($devices_up). " Devices UP, " . count($devices_down). " Devices DOWN: (". implode(", ", $devices_down).")', '$location_url']";
          } else if ($count) {
            $locations_up[]   = "[".$lat.", ".$lon.", '".$location_name."',         0,       ".$count.", '".count($devices_up). " Devices UP: (". implode(", ", $devices_up).")', '$location_url']";
          }
        }
        echo(implode(",\n        ", array_merge($locations_up, $locations_down)));
      ?>
      ]);

      var options = {
        region: '<?php echo $config['frontpage']['map']['region']; ?>',
        resolution: '<?php echo $config['frontpage']['map']['resolution']; ?>',
        displayMode: 'markers',
        keepAspectRatio: 0,
        //width: 1240,
        //height: 480,
        is3D: true,
        legend: 'none',
        enableRegionInteractivity: true,
        <?php if ($config['frontpage']['map']['realworld']) { echo "\t\t  datalessRegionColor: '#93CA76',"; }
              else { echo "\t\t  datalessRegionColor: '#d5d5d5',"; }
              if ($config['frontpage']['map']['realworld']) { echo "\t\t  backgroundColor: {fill: '#000000'},"; } ?>
        backgroundColor: {fill: 'transparent'},
        magnifyingGlass: {enable: true, zoomFactor: 5},
        colorAxis: {values: [0, 1, 2, 3], colors: ['darkgreen', 'orange', 'orangered', 'red']},
        markerOpacity: 0.75,
        sizeAxis: {minValue: 1,  maxValue: 10, minSize: 10, maxSize: 40}
      };

      var view = new google.visualization.DataView(data);
      // exclude last url column in the GeoChart
      view.setColumns([0, 1, 2, 3, 4, 5]);

      var chart = new google.visualization.GeoChart(document.getElementById('chart_div'));
      google.visualization.events.addListener(chart, 'ready', onReady);
      function onReady() {
        google.visualization.events.addListener(chart, 'select', gotoLocation);
      }
      function gotoLocation() {
        var selection = chart.getSelection();
        var item = selection[0];
        var url = data.getValue(item.row, 6);
        window.location = url;
      }
      chart.draw(view, options);
    };
  </script>
