<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage webui
 * @author     Adam Armstrong <adama@memetic.org>
 * @copyright  (C) 2006-2015 Adam Armstrong
 *
 */

if ($vars['editing'])
{
  if (get_db_version() < 169)
  {
    //FIXME. Remove this block in r7000
    print_warning("DB scheme is old, must update first. Device Geolocation not changed.");
  }
  else if ($_SESSION['userlevel'] > 7)
  {
    $updated = 0;

    if ($vars['submit'] == 'save')
    {
      if ((bool)$vars['location_manual'])
      {
        // Set manual coordinates if present
        $pattern = '/(?:^|[\[(])\s*(?<lat>[+-]?\d+(?:\.\d+)*)\s*[,; ]\s*(?<lon>[+-]?\d+(?:\.\d+)*)\s*(?:[\])]|$)/';
        if (preg_match($pattern, $vars['coordinates'], $matches))
        {
          if ($matches['lat'] >= -90 && $matches['lat'] <= 90 &&
              $matches['lon'] >= -180 && $matches['lon'] <= 180)
          {
            $update_geo['location_lat']     = $matches['lat'];
            $update_geo['location_lon']     = $matches['lon'];
            $update_geo['location_country'] = '';
            $update_geo['location_manual']  = 1;
            $updated++;
          }
        }
        if (!$updated) { unset($vars); } // If manual set, but coordinates wrong - reset edit
        //r($vars);
      }
      if ((bool)$device['location_manual'] && !(bool)$vars['location_manual'])
      {
        // Reset manual flag, rediscover geo info
        $update_geo['location_lat']    = array('NULL');
        $update_geo['location_lon']    = array('NULL');
        $update_geo['location_manual'] = 0;
        $updated++;
      }

      if ($updated)
      {
        //r($update_geo);
        dbUpdate($update_geo, 'devices_locations', '`location_id` = ?', array($device['location_id']));
        print_success("Device Geolocation updated. Country/city will be updated on next poll.");
        $geo_db = dbFetchRow("SELECT * FROM `devices_locations` WHERE `device_id` = ?", array($device['device_id']));
        $device = array_merge($device, $geo_db);
        unset($updated, $update_geo, $geo_db);
      } else {
        print_warning("Some input data wrong. Device Geolocation not changed.");
      }
    }
  } else {
    include("includes/error-no-perm.inc.php");
  }
}

$location = array('location_text' => $device['location']);

$override_sysLocation_bool = get_dev_attrib($device,'override_sysLocation_bool');
if ($override_sysLocation_bool)
{
  $override_sysLocation_string = get_dev_attrib($device,'override_sysLocation_string');
  if ($override_sysLocation_string != $device['location'])
  {
    // Device not polled since location overrided
    $location['location_help'] = 'NOTE, device not polled since location overridden, Geolocation is old.';
    $location['location_text'] = $override_sysLocation_string;
  }
}

if ($location['location_text'] == '') { $location['location_text'] = OBS_VAR_UNSET; }
foreach (array('location_lat', 'location_lon', 'location_city', 'location_county', 'location_state', 'location_country',
               'location_geoapi', 'location_status', 'location_manual', 'location_updated') as $param)
{
  $location[$param] = $device[$param];
}
if (is_numeric($location['location_lat']) && is_numeric($location['location_lon']))
{
  // Generate link to Google maps
  // http://maps.google.com/maps?q=46.090271,6.657248+description+(name)
  $location['coordinates'] = $location['location_lat'].','.$location['location_lon'];
  $location['coordinates_manual'] = $location['coordinates'];
  $location['location_link'] = '<a target="_blank" href="http://maps.google.com/maps?q='.urlencode($location['coordinates']).'"><i class="oicon-map"></i> View this location on a map</a>';
  $location['location_geo']  = country_from_code($location['location_country']).' (Country), '.$location['location_state'].' (State), ';
  $location['location_geo'] .= $location['location_county'] .' (County), ' .$location['location_city'] .' (City)';
  switch ($location['location_geoapi'])
  {
    //case 'yandex':
    //  // Generate link to Yandex maps
    //  $location['location_link'] = '<a target="_blank" href="http://maps.google.com/maps?q='.urlencode($location['coordinates']).'"><i class="oicon-map"></i> View this location on a map</a>';
    //  break;
    default:
      // Generate link to Google maps
      // http://maps.google.com/maps?q=46.090271,6.657248+description+(name)
      $location['location_link'] = '<a target="_blank" href="http://maps.google.com/maps?q='.urlencode($location['coordinates']).'"><i class="oicon-map"></i> View this location on a map</a>';
  }
} else {
  $location['coordinates_manual'] = $config['geocoding']['default']['lat'].','.$config['geocoding']['default']['lon'];
}

if ($updated && $update_message)
{
  print_message($update_message);
} elseif ($update_message) {
  print_error($update_message);
}

//FIXME. Make better page view (use correct bootstrap classes instead forms)
?>

 <form id="edit" name="edit" method="post" class="form-horizontal" action="<?php echo($url); ?>">

  <fieldset>
  <legend>Geolocation options</legend>
  <input type=hidden name="editing" value="yes">

  <div class="control-group">
    <label class="control-label" for="sysLocation">Location Text</label>
    <div class="controls" id="location_text">
      <input type=text name="sysLocation" style="width: 66.6667%;" disabled="disabled" value="<?php echo(escape_html($location['location_text'])); ?>" />
      <?php
      if ($location['location_help'])
      {
        echo('      <span class="help-block"><small>'.$location['location_help'].'</small></span>'.PHP_EOL);
      }
      ?>
    </div>
  </div>

  <div class="control-group">
    <label class="control-label" for="location_geo">Location Geo</label>
    <div class="controls" id="location_geo">
      <input type=text name="location_geo" style="width: 66.6667%;" disabled="disabled" value="<?php echo(escape_html($location['location_geo'])); ?>" />
    </div>
  </div>

  <div class="control-group">
    <label class="control-label" for="location_lat">Latitude/Longitude</label>
    <div class="controls">
      <input name="location_lat" type=text style="width: 16.6667%; margin-right:10px;" disabled="disabled" value="<?php echo(escape_html($location['location_lat'])); ?>" />
      <input name="location_lon" type=text style="width: 16.6667%;" disabled="disabled" value="<?php echo(escape_html($location['location_lon'])); ?>" />
      <?php
      if ($location['location_link'])
      {
        echo('      <span class="help-inline"><small>'.$location['location_link'].'</small></span>'.PHP_EOL);
      }
      ?>
    </div>
  </div>

  <div class="control-group">
    <label class="control-label" for="location_geoapi">API used</label>
    <div class="controls">
      <input name="location_geoapi" type=text style="width: 16.6667%;" disabled="disabled" value="<?php echo(escape_html(strtoupper($location['location_geoapi']))); ?>" />
      <span class="help-inline"><small><a target="_blank" href="<?php echo(OBSERVIUM_URL); ?>/wiki/Configuration_Options#sysLocation_Configuration">
      <i class="oicon-question"></i> View available Geolocation APIs and other configuration options</a></small></span>
    </div>
  </div>

  <div class="control-group">
    <label class="control-label" for="location_updated">Last updated</label>
    <div class="controls">
      <input name="location_updated" type=text style="width: 16.6667%;" disabled="disabled" value="<?php echo(escape_html($location['location_updated'])); ?>" />
    </div>
  </div>
  <div class="control-group">
    <label class="control-label" for="location_status">Last update status</label>
    <div class="controls">
      <textarea name="location_status" style="width: 66.6667%; cursor: default;" disabled="disabled"><?php echo(escape_html($location['location_status'])); ?></textarea>
    </div>
  </div>

  <div class="control-group">
    <label class="control-label" for="coordinates">Manual coordinates</label>
    <div class="controls">
      <input name="coordinates" type=text style="width: 16.6667%;" <?php if (!$location['location_manual']) { echo(' disabled="disabled"'); } ?> value="<?php echo(escape_html($location['coordinates_manual'])); ?>" />
      <input type="checkbox" onclick="edit.coordinates.disabled=!edit.location_manual.checked"
            name="location_manual" <?php if ($location['location_manual']) { echo(' checked="checked"'); } ?> />
<!--      <input type="checkbox" data-toggle="switch" id="location_manual" name="location_manual" <?php if ($location['location_manual']) { echo(' checked="checked"'); } ?>
             onclick="edit.coordinates.disabled=!edit.location_manual.checked" />-->
    </div>
  </div>

<script>

$('#location_manual').click(function() {
  $('#coordinates').attr('disabled',! this.checked)
});

</script>

  </fieldset>

  <div class="form-actions">
    <button type="submit" class="btn btn-primary" name="submit" value="save"><i class="icon-ok icon-white"></i> Save Changes</button>
  </div>

</form>

<?php

// EOF
