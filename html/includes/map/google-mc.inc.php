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

  $where  = ' WHERE 1 ';
  $where .= generate_query_permitted(array('device'), array('hide_ignored' => TRUE));
  //Detect map center
  if (!is_numeric($config['frontpage']['map']['center']['lat']) || !is_numeric($config['frontpage']['map']['center']['lng']))
  {
    $map_center = dbFetchRow('SELECT MAX(`location_lon`) AS `lng_max`, MIN(`location_lon`) AS `lng_min`,
                             MAX(`location_lat`) AS `lat_max`, MIN(`location_lat`) AS `lat_min`
                             FROM `devices_locations` '.$where);
    $map_center['lat'] = ($map_center['lat_max'] + $map_center['lat_min']) / 2;
    $map_center['lng'] = ($map_center['lng_max'] + $map_center['lng_min']) / 2;
    $config['frontpage']['map']['center']['lat'] = $map_center['lat'];
    $config['frontpage']['map']['center']['lng'] = $map_center['lng'];

    //Also auto-zoom
    if (!is_numeric($config['frontpage']['map']['zoom']))
    {
      $map_center['lat_size'] = abs($map_center['lat_max'] - $map_center['lat_min']);
      $map_center['lng_size'] = abs($map_center['lng_max'] - $map_center['lng_min']);
      $l_max = max($map_center['lng_size'], $map_center['lat_size'] * 2);
      // This is the magic array (2: min zoom, 10: max zoom).
      foreach (array(1 => 10, 2 => 9, 4 => 8, 6 => 7, 15 => 5, 45 => 4, 90 => 3, 360 => 2) as $g => $z)
      {
        if ($l_max <= $g)
        {
          $config['frontpage']['map']['zoom'] = $z;
          break;
        }
      }
    }
    //r($map_center);
  } else {
    if (!is_numeric($config['frontpage']['map']['zoom'])) { $config['frontpage']['map']['zoom'] = 4; }
  } ?>

  <script type='text/javascript' src='//www.google.com/jsapi'></script>
  <script type="text/javascript" src="js/google/markerclusterer.js"></script>

  <?php
  if ($config['frontpage']['map']['clouds'])
  {
    echo '<script src="//maps.googleapis.com/maps/api/js?v=3.exp&sensor=false&libraries=weather"></script>';
  } else {
    echo '<script src="//maps.google.com/maps/api/js?sensor=false"></script>';
  }
  ?>

  <script type="text/javascript">
    google.load('visualization', '1.1', {'packages': ['geochart']});
    function getMapData() {
    var data = new google.visualization.DataTable();
    data.addColumn('number', 'Latitude');
    data.addColumn('number', 'Longitude');
    data.addColumn('number', 'Number up');
    data.addColumn('number', 'Number down');
    data.addColumn({type: 'string', role: 'tooltip'});
    data.addColumn('string', 'Location');
    data.addColumn('string', 'url');
    data.addRows([
    <?php
    $locations = array();
    foreach ($GLOBALS['cache']['devices']['id'] as $device)
    {
      if (!$config['web_show_disabled'] && $device["disabled"]) { continue; }
      $lat = (is_numeric($device['location_lat']) ? $device['location_lat'] : $config['geocoding']['default']['lat']);
      $lon = (is_numeric($device['location_lon']) ? $device['location_lon'] : $config['geocoding']['default']['lon']);
      if ($device["status"] == "0")
      {
        if ($device["ignore"] == "0")
        {
          $locations[$lat][$lon]["down_hosts"][] = $device;
        }
      } else {
        $locations[$lat][$lon]["up_hosts"][] = $device;
      }
    }

    foreach ($locations as $la => $lat)
    {
      foreach ($lat as $lo => $lon)
      {
        $num_up = count($lon["up_hosts"]);
        $num_down = count($lon["down_hosts"]);
        $total_hosts = $num_up + $num_down;
        $tooltip = "$total_hosts Hosts";

        $location_name = "";
        if ($num_down > 0)
        {
          $location_name = ($lon['down_hosts'][0]['location'] === '' ? OBS_VAR_UNSET : $lon['down_hosts'][0]['location']);
          $location_url  = generate_location_url($lon['down_hosts'][0]['location']);
          $tooltip .= "\\n\\nDown hosts:";

          foreach ($lon["down_hosts"] as $down_host) {
            $tooltip .= "\\n" . escape_html($down_host['hostname']);
          }
        }
        elseif ($num_up > 0)
        {
          $location_name = ($lon['up_hosts'][0]['location'] === '' ? OBS_VAR_UNSET : $lon['up_hosts'][0]['location']);
          $location_url  = generate_location_url($lon['up_hosts'][0]['location']);
        }
        // Google Map JS not allowed chars: ', \
        $location_name = strtr(escape_html($location_name), "'\\", "`/");

        echo "[$la, $lo, $num_up, $num_down, \"$tooltip\", '$location_name', '$location_url'],\n      ";
      }
    }
?>

    ]);
    return data;
  }

  function initialize() {
    var data = getMapData();
    var markers = [];
    var base_link = '<?php echo generate_url(array("page" => "devices")); ?>';
    for (var i = 0; i < data.getNumberOfRows(); i++) {
      var latLng = new google.maps.LatLng(data.getValue(i, 0), data.getValue(i, 1));
      icon_ = '//maps.gstatic.com/mapfiles/ridefinder-images/mm_20_green.png';

      var num_up = data.getValue(i, 2);
      var num_down = data.getValue(i, 3);
      var location = data.getValue(i, 5);
      var ratio_up = num_up / (num_up + num_down);

      if (ratio_up < 0.9999) {
        icon_ = '//maps.gstatic.com/mapfiles/ridefinder-images/mm_20_red.png';
      }

      var marker = new google.maps.Marker({
        position: latLng,
        icon: icon_,
        title: data.getValue(i, 4),
        location: location,
        num_up: num_up,
        num_down: num_down,
        url: data.getValue(i, 6)
      });

//      marker.num_up = num_up;
//      marker.num_down = num_down;

      markers.push(marker);

      google.maps.event.addDomListener(marker, 'click', function() {
        window.location.href = this.url;
      });
    }
    var styles = [];
    for (var i = 0; i < 4; i++) {
      image_path = "/images/mapclusterer/";
      image_ext = ".png";
      styles.push({
        url: image_path + i + image_ext,
        height: 52,
        width: 53
      });
    }

    var mcOptions = {gridSize: 30,
                      maxZoom: 15,
                      zoomOnClick: false,
                      styles: styles
                    };
    var markerClusterer = new MarkerClusterer(map, markers, mcOptions);

    var iconCalculator = function(markers, numStyles) {
      var total_up = 0;
      var total_down = 0;
      for (var i = 0; i < markers.length; i++) {
        total_up += markers[i].num_up;
        total_down += markers[i].num_down;
      }

      var ratio_up = total_up / (total_up + total_down);

      //The map clusterer really does seem to use index-1...
      index_ = 1;
      if (ratio_up < 0.9999) {
        index_ = 4; // Could be 2, and then more code to use all 4 images
      }

      return {
        text: (total_up + total_down),
        index: index_
      };
    }

    markerClusterer.setCalculator(iconCalculator);
  }

  var center_ = new google.maps.LatLng(<?php echo $config['frontpage']['map']['center']['lat']; ?>, <?php echo $config['frontpage']['map']['center']['lng']; ?>);
  var map = new google.maps.Map(document.getElementById('chart_div'), {
    zoom: <?php echo $config['frontpage']['map']['zoom']?>,
    scrollwheel: false,
    streetViewControl: false,
    center: center_,
    mapTypeId: google.maps.MapTypeId.ROADMAP
  });

  <?php if ($config['frontpage']['map']['clouds']) { ?>
  var cloudLayer = new google.maps.weather.CloudLayer();
  cloudLayer.setMap(map);
  <?php } ?>

  function resetMap() {
    map.setZoom(4);
    map.panTo(center_);
  }

  google.maps.event.addListener(map, 'click', function(event) {
    map.setZoom(map.getZoom() + <?php echo $config['frontpage']['map']['zooms_per_click']; ?>);
    map.panTo(event.latLng);
  });
  google.maps.event.addDomListener(window, 'load', initialize);

  </script>
<?php

// EOF
