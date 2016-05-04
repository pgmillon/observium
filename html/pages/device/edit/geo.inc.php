<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage webui
 * @author     Adam Armstrong <adama@observium.org>
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

if ($vars['editing'])
{
  if ($readonly)
  {
    print_error_permission('You have insufficient permissions to edit settings.');
  }
  else if (get_db_version() < 169)
  {
    // FIXME. Remove this block in r7000
    print_warning("DB scheme is old, must update first. Device Geolocation not changed.");
  } else {
    $updated = 0;

    if ($vars['submit'] == 'save')
    {
      if ($vars['reset_geolocation'] === 'on' || $vars['reset_geolocation'] === '1')
      {
        $updated = dbDelete('devices_locations', '`device_id` = ?', array($device['device_id']));
      }
      else if ((bool)$vars['location_manual'])
      {
        // Set manual coordinates if present
        $pattern = '/(?:^|[\[(])\s*(?<lat>[+-]?\d+(?:\.\d+)*)\s*[,; ]\s*(?<lon>[+-]?\d+(?:\.\d+)*)\s*(?:[\])]|$)/';
        if (preg_match($pattern, $vars['coordinates'], $matches))
        {
          //r($matches);
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
        $geo_db = dbFetchRow("SELECT * FROM `devices_locations` WHERE `device_id` = ?", array($device['device_id']));
        if (count($geo_db))
        {
          if ($vars['reset_geolocation'] === 'on' || $vars['reset_geolocation'] === '1')
          {
            print_warning("Device Geo location dropped. Country/city will be updated on next poll.");
          } else {
            print_success("Device Geolocation updated. Country/city will be updated on next poll.");
          }
        }
        $device = array_merge($device, $geo_db);
        unset($updated, $update_geo, $geo_db);
      } else {
        print_warning("Some input data wrong. Device Geolocation not changed.");
      }
    }
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

      $form = array('type'      => 'horizontal',
                    'id'        => 'edit',
                    //'space'     => '20px',
                    'title'     => 'Geolocation Options',
                    //'icon'      => 'oicon-gear',
                    //'class'     => 'box box-solid',
                    'fieldset'  => array('edit' => ''),
                    );

      $form['row'][0]['editing']   = array(
                                      'type'        => 'hidden',
                                      'value'       => 'yes');
      $form['row'][1]['sysLocation'] = array(
                                      'type'        => 'text',
                                      //'fieldset'    => 'edit',
                                      'name'        => 'sysLocation string',
                                      'placeholder' => '',
                                      'width'       => '66.6667%',
                                      //'readonly'    => $readonly,
                                      'disabled'    => TRUE, // Always disabled, just for see
                                      'value'       => escape_html($location['location_text']));
      if ($location['location_help'])
      {
        $form['row'][1]['location_help'] = array(
                                      'type'        => 'raw',
                                      'value'       => '<span class="help-block"><small>'.$location['location_help'].'</small></span>');
      }
      $form['row'][2]['location_geo'] = array(
                                      'type'        => 'text',
                                      //'fieldset'    => 'edit',
                                      'name'        => 'Location Place',
                                      'placeholder' => '',
                                      'width'       => '66.6667%',
                                      //'readonly'    => $readonly,
                                      'disabled'    => TRUE, // Always disabled, just for see
                                      'value'       => escape_html($location['location_geo']));
      $form['row'][3]['location_lat'] = array(
                                      'type'        => 'text',
                                      //'fieldset'    => 'edit',
                                      'name'        => 'Latitude/Longitude',
                                      'placeholder' => '',
                                      'width'       => '16.6667%',
                                      //'readonly'    => $readonly,
                                      'disabled'    => TRUE, // Always disabled, just for see
                                      'value'       => escape_html($location['location_lat']));
      if ($location['location_link'])
      {
        $form['row'][3]['location_link'] = array(
                                      'type'        => 'raw',
                                      'value'       => '<span class="help-block"><small>'.$location['location_link'].'</small></span>');
      }
      $form['row'][4]['location_geoapi'] = array(
                                      'type'        => 'text',
                                      //'fieldset'    => 'edit',
                                      'name'        => 'API used',
                                      'placeholder' => '',
                                      'width'       => '16.6667%',
                                      //'readonly'    => $readonly,
                                      'disabled'    => TRUE, // Always disabled, just for see
                                      'value'       => escape_html(strtoupper($location['location_geoapi'])));
      $form['row'][4]['help_link'] = array(
                                      'type'        => 'raw',
                                      'value'       => '<span class="help-inline"><small><a target="_blank" href="' . OBSERVIUM_URL . '/docs/config_options/#syslocation-configuration">
      <i class="oicon-question"></i> View available Geolocation APIs and other configuration options</a></small></span>');
      $form['row'][5]['location_updated'] = array(
                                      'type'        => 'text',
                                      //'fieldset'    => 'edit',
                                      'name'        => 'Last updated',
                                      'placeholder' => '',
                                      'width'       => '16.6667%',
                                      //'readonly'    => $readonly,
                                      'disabled'    => TRUE, // Always disabled, just for see
                                      'value'       => escape_html($location['location_updated']));
      $form['row'][6]['location_status'] = array(
                                      'type'        => 'textarea',
                                      //'fieldset'    => 'edit',
                                      'name'        => 'Last update status',
                                      'placeholder' => '',
                                      'width'       => '66.6667%',
                                      //'readonly'    => $readonly,
                                      'disabled'    => TRUE, // Always disabled, just for see
                                      'value'       => escape_html($location['location_status']));
      $form['row'][7]['coordinates'] = array(
                                      'type'        => 'text',
                                      //'fieldset'    => 'edit',
                                      'name'        => 'Manual coordinates',
                                      'placeholder' => '',
                                      'width'       => '16.6667%',
                                      'readonly'    => $readonly,
                                      'disabled'    => !$location['location_manual'],
                                      'value'       => escape_html($location['coordinates_manual']));
      $form['row'][7]['location_manual'] = array(
                                      'type'        => 'checkbox',
                                      'readonly'    => $readonly,
                                      'onchange'    => "toggleAttrib('disabled', 'coordinates')",
                                      'value'       => $location['location_manual']);

      $form['row'][8]['reset_geolocation'] = array(
                                      'type'        => 'switch',
                                      'name'        => 'Reset GEO location',
                                      //'fieldset'    => 'edit',
                                      'size'        => 'small',
                                      'readonly'    => $readonly,
                                      'on-color'    => 'danger',
                                      'off-color'   => 'primary',
                                      'on-text'     => 'Reset',
                                      'off-text'    => 'Leave',
                                      'value'       => 0);

      $form['row'][9]['submit']    = array(
                                      'type'        => 'submit',
                                      'name'        => 'Save Changes',
                                      'icon'        => 'icon-ok icon-white',
                                      //'right'       => TRUE,
                                      'class'       => 'btn-primary',
                                      'readonly'    => $readonly,
                                      'value'       => 'save');

      print_form($form);
      unset($form);

// EOF
