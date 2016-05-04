<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage geolocation
 * @author     Adam Armstrong <adama@memetic.org>
 * @copyright  (C) 2006-2015 Adam Armstrong
 *
 */

// This function returns an array of location data when given an address.
// The open&free geocoding APIs are not very flexible, so addresses must be in standard formats.

// DOCME needs phpdoc block
// TESTME needs unit testing
function get_geolocation($address, $geo_db = array())
{
  global $config;

  $location = array('location' => $address); // Init location array
  switch (strtolower($config['geocoding']['api']))
  {
    case 'osm':
    case 'openstreetmap':
      $location['location_geoapi'] = 'openstreetmap';
      // Openstreetmap. The usage limits are stricter here. (http://wiki.openstreetmap.org/wiki/Nominatim_usage_policy)
      $url         = "http://nominatim.openstreetmap.org/search?format=json&accept-language=en&addressdetails=1&limit=1&q=";
      $reverse_url = "http://nominatim.openstreetmap.org/reverse?format=json&accept-language=en&";
      break;
    case 'google':
      $location['location_geoapi'] = 'google';
      // See documentation here: https:// developers.google.com/maps/documentation/geocoding/
      // Use of the Google Geocoding API is subject to a query limit of 2,500 geolocation requests per day.
      $url         = "http://maps.googleapis.com/maps/api/geocode/json?sensor=false&language=en&address=";
      $reverse_url = "http://maps.googleapis.com/maps/api/geocode/json?sensor=false&language=en&";
      break;
    case 'yandex':
      $location['location_geoapi'] = 'yandex';
      $url         = "http://geocode-maps.yandex.ru/1.x/?format=json&lang=en_US&results=1&geocode=";
      $reverse_url = "http://geocode-maps.yandex.ru/1.x/?format=json&lang=en_US&results=1&sco=latlong&";
      break;
    case 'mapquest':
    default:
      $location['location_geoapi'] = 'mapquest';
      // Mapquest open data. There are no usage limits.
      $url         = "http://open.mapquestapi.com/nominatim/v1/search.php?format=json&accept-language=en&addressdetails=1&limit=1&q=";
      $reverse_url = "http://open.mapquestapi.com/nominatim/v1/reverse.php?format=json&accept-language=en&";
  }

  if (isset($config['geocoding']['enable']) && $config['geocoding']['enable'])
  {
    $reverse = FALSE; // by default forward geocoding
    $debug_msg = "Geocoding ENABLED, try detect device coordinates:".PHP_EOL;

    // If device coordinates set manually, use Reverse Geocoding.
    if ($geo_db['location_manual'])
    {
      $location['location_lat'] = $geo_db['location_lat'];
      $location['location_lon'] = $geo_db['location_lon'];
      $reverse = TRUE;
      $debug_msg .= '  MANUAL coordinates - SET'.PHP_EOL;
    }
    // If DNS LOC support is enabled and DNS LOC record is set, use Reverse Geocoding.
    else if ($config['geocoding']['dns'])
    {
      /**
       * Ack! dns_get_record not only cannot retrieve LOC records, but it also actively filters them when using
       * DNS_ANY as query type (which, admittedly would not be all that reliable as per the manual).
       *
       * Example LOC:
       *   "20 31 55.893 N 4 57 38.269 E 45.00m 10m 100m 10m"
       *
       * From Wikipedia: d1 [m1 [s1]] {"N"|"S"}  d2 [m2 [s2]] {"E"|"W"}
       *
       * Parsing this is something for Net_DNS2 as it has the code for it.
       */
      if ($geo_db['hostname'])
      {
        include_once('Net/DNS2.php');
        include_once('Net/DNS2/RR/LOC.php');

        $resolver = new Net_DNS2_Resolver();
        try {
          $response = $resolver->query($geo_db['hostname'], 'LOC', 'IN');
        } catch(Net_DNS2_Exception $e) {
          print_debug('  '.$e->getMessage().' ('.$geo_db['hostname'].')');
        }
      } else {
        $response = FALSE;
        print_debug("  DNS LOC enabled, but device hostname empty.");
      }
      if ($response)
      {
        if (OBS_DEBUG > 1) { var_dump($response->answer); }
        foreach ($response->answer as $answer)
        {
          if (is_numeric($answer->latitude) && is_numeric($answer->longitude))
          {
            $location['location_lat'] = $answer->latitude;
            $location['location_lon'] = $answer->longitude;
            $reverse = TRUE;
            break;
          }
          else if (is_numeric($answer->degree_latitude) && is_numeric($answer->degree_longitude))
          {
            $ns_multiplier = ($answer->ns_hem == 'N' ? 1 : -1);
            $ew_multiplier = ($answer->ew_hem == 'E' ? 1 : -1);

            $location['location_lat'] = round($answer->degree_latitude + $answer->min_latitude/60 + $answer->sec_latitude/3600,7) * $ns_multiplier;
            $location['location_lon'] = round($answer->degree_longitude + $answer->min_longitude/60 + $answer->sec_longitude/3600,7) * $ew_multiplier;
            $reverse = TRUE;
            break;
          }
        }
        if (isset($location['location_lat']))
        {
          $debug_msg .= '  DNS LOC records - FOUND'.PHP_EOL;
        } else {
          $debug_msg .= '  DNS LOC records - NOT FOUND'.PHP_EOL;
        }
      }
    }

    if ($reverse || !preg_match('/^<?(unknown|none)>?$/i', $address))
    {
      /**
       * If location string contains coordinates use Reverse Geocoding.
       * Valid strings:
       *   Some location [33.234, -56.22]
       *   Some location (33.234 -56.22)
       *   Some location [33.234;-56.22]
       *   33.234,-56.22
       */
      $pattern = '/(?:^|[\[(])\s*(?<lat>[+-]?\d+(?:\.\d+)*)\s*[,; ]\s*(?<lon>[+-]?\d+(?:\.\d+)*)\s*(?:[\])]|$)/';
      if (!$reverse && preg_match($pattern, $address, $matches))
      {
        if ($matches['lat'] >= -90 && $matches['lat'] <= 90 &&
            $matches['lon'] >= -180 && $matches['lon'] <= 180)
        {
          $location['location_lat'] = $matches['lat'];
          $location['location_lon'] = $matches['lon'];
          $reverse = TRUE;
        }
      }
  
      if ($reverse)
      {
        $debug_msg .= '  by REVERSE query (API: '.strtoupper($config['geocoding']['api']).', LAT: '.$location['location_lat'].', LON: '.$location['location_lon'].') - ';

        if (!is_numeric($location['location_lat']) || !is_numeric($location['location_lat']))
        {
          // Do nothing for empty, skip requests for empty coordinates
        }
        else if ($config['geocoding']['api'] == 'google')
        {
          // latlng=40.714224,-73.961452
          $request = $reverse_url . 'latlng=' . $location['location_lat'] . ',' . $location['location_lon'];
        }
        else if ($config['geocoding']['api'] == 'yandex')
        {
          // geocode=40.714224,-73.961452
          $request = $reverse_url . 'geocode=' . $location['location_lat'] . ',' . $location['location_lon'];
        } else {
          // lat=51.521435&lon=-0.162714
          $request = $reverse_url . 'lat=' . $location['location_lat'] . '&lon=' . $location['location_lon'];
        }
      } else {
        $debug_msg .= '  by PARSING sysLocation (API: '.strtoupper($config['geocoding']['api']).') - ';
        if ($address != '') { $request = $url.urlencode($address); }
      }

      if ($request)
      {
        // First request
        $mapresponse = get_http_request($request);
        $data        = json_decode($mapresponse, TRUE);
        $geo_status  = 'NOT FOUND';

        if ($config['geocoding']['api'] == 'google')
        {
          if ($data['status'] == 'OVER_QUERY_LIMIT')
          {
            $debug_msg .= $geo_status;
            print_debug($debug_msg);
            // Return empty array for overquery limit (for later recheck)
            return array('location_status' => $debug_msg);
          }

          // Use google data only with good status response
          if ($data['status'] == 'OK')
          {
            $data       = $data['results'][0];
            if ($data['geometry']['location_type'] == 'APPROXIMATE')
            {
              // It might be that the first element of the address is a business name.
              // Lets drop the first element and see if we get anything better!
              list(, $address) = explode(',', $address, 2);
              $request_new = $url.urlencode($address);
              $mapresponse = get_http_request($request_new);
              $data_new = json_decode($mapresponse, TRUE);
              if ($data_new['status'] == 'OK' && $data_new['results'][0]['geometry']['location_type'] != 'APPROXIMATE')
              {
                $request = $request_new;
                $data    = $data_new['results'][0];
              }
            }
          }
        }
        else if ($config['geocoding']['api'] == 'yandex')
        {
          $try_new = FALSE;
          if ($data['response']['GeoObjectCollection']['metaDataProperty']['GeocoderResponseMetaData']['found'] > 0)
          {
            $data = $data['response']['GeoObjectCollection']['featureMember'][0];
            if ($data['GeoObject']['metaDataProperty']['GeocoderMetaData']['precision'] == 'other')
            {
              $try_new = TRUE;
            }
          } else {
            $try_new = TRUE;
          }
          if ($try_new && strpos($address, ','))
          {
            // It might be that the first element of the address is a business name.
            // Lets drop the first element and see if we get anything better!
            list(, $address) = explode(',', $address, 2);
            $request_new = $url.urlencode($address);
            $mapresponse = get_http_request($request_new);
            $data_new = json_decode($mapresponse, TRUE);
            if ($data_new['response']['GeoObjectCollection']['metaDataProperty']['GeocoderResponseMetaData']['found'] > 0 &&
                $data_new['response']['GeoObjectCollection']['featureMember'][0]['GeoObject']['metaDataProperty']['GeocoderMetaData']['precision'] != 'other')
            {
              $request = $request_new;
              $data    = $data_new['response']['GeoObjectCollection']['featureMember'][0];
            }
          }
        }
        else if (!isset($location['location_lat']))
        {
          $data = $data[0];
          if (!count($data) && strpos($address, ','))
          {
            // We seem to have hit a snag geocoding. It might be that the first element of the address is a business name.
            // Lets drop the first element and see if we get anything better! This works more often than one might expect.
            list(, $address) = explode(',', $address, 2);
            $request_new = $url.urlencode($address);
            $mapresponse = get_http_request($request_new);
            $data_new = json_decode($mapresponse, TRUE);
            if (count($data_new[0]))
            {
              // We only want the first entry in the returned data.
              $data = $data_new[0];
              $request = $request_new;
            }
          }
        }
        if (OBS_DEBUG > 1 && count($data)) { var_dump($data); }
      } else {
        $geo_status  = 'NOT REQUESTED';
      }
    }
  }

  // Put the values from the data array into the return array where they exist, else replace them with defaults or Unknown.
  if ($config['geocoding']['api'] == 'google')
  {
    $location['location_lat'] = $data['geometry']['location']['lat'];
    $location['location_lon'] = $data['geometry']['location']['lng'];
    foreach ($data['address_components'] as $entry)
    {
      switch ($entry['types'][0])
      {
        case 'postal_town':
        case 'locality':
          $location['location_city'] = $entry['long_name'];
          break;
        case 'administrative_area_level_2':
          $location['location_county'] = $entry['long_name'];
          break;
        case 'administrative_area_level_1':
          $location['location_state'] = $entry['long_name'];
          break;
        case 'country':
          $location['location_country'] = strtolower($entry['short_name']);
          break;
      }
    }
  }
  else if ($config['geocoding']['api'] == 'yandex')
  {
    list($location['location_lon'], $location['location_lat']) = explode(' ', $data['GeoObject']['Point']['pos']);
    $data = $data['GeoObject']['metaDataProperty']['GeocoderMetaData']['AddressDetails'];
    $location['location_country'] = strtolower($data['Country']['CountryNameCode']);
    $location['location_state']   = $data['Country']['AdministrativeArea']['AdministrativeAreaName'];
    $location['location_county']  = $data['Country']['AdministrativeArea']['SubAdministrativeArea']['SubAdministrativeAreaName'];
    $location['location_city']    = $data['Country']['AdministrativeArea']['SubAdministrativeArea']['Locality']['LocalityName'];
  } else {
    $location['location_lat'] = $data['lat'];
    $location['location_lon'] = $data['lon'];
    $location['location_city']    = (strlen($data['address']['town'])) ? $data['address']['town'] : $data['address']['city'];
    // Would be nice to have an array of countries where we want state, and ones where we want County. For example, USA wants state, UK wants county.
    $location['location_county']  = $data['address']['county'];
    $location['location_state']   = $data['address']['state'];
    $location['location_country'] = $data['address']['country_code'];
  }

  // Use defaults if empty values
  if (!strlen($location['location_lat']) || !strlen($location['location_lon']))
  {
    // Reset to empty coordinates
    $location['location_lat'] = array('NULL');
    $location['location_lon'] = array('NULL');
    //$location['location_lat'] = $config['geocoding']['default']['lat'];
    //$location['location_lon'] = $config['geocoding']['default']['lon'];
    //if (is_numeric($config['geocoding']['default']['lat']) && is_numeric($config['geocoding']['default']['lon']))
    //{
    //  $location['location_manual']     = 1; // Set manual key for ability reset from WUI
    //}
  } else {
    // Always round lat/lon same as DB precision (DECIMAL(10,7))
    $location['location_lat'] = round($location['location_lat'], 7);
    $location['location_lon'] = round($location['location_lon'], 7);
  }

  if (!strlen($location['location_city']))    { $location['location_city']    = 'Unknown'; }
  if (!strlen($location['location_county']))  { $location['location_county']  = 'Unknown'; }
  if (!strlen($location['location_state']))   { $location['location_state']   = 'Unknown'; }
  if (!strlen($location['location_country']))
  {
    $location['location_country'] = 'Unknown';
  } else {
    $geo_status = 'FOUND';
  }

  // Print some debug informations
  $debug_msg .= $geo_status . PHP_EOL;
  $debug_msg .= '  GEO API REQUEST: ' . $request;
  if ($geo_status == 'FOUND')
  {
    $debug_msg .= PHP_EOL . '  GEOLOCATION: ';
    $debug_msg .= country_from_code($location['location_country']).' (Country), '.$location['location_state'].' (State), ';
    $debug_msg .= $location['location_county'] .' (County), ' .$location['location_city'] .' (City)';
    $debug_msg .= PHP_EOL . '  GEO COORDINATES: ';
    $debug_msg .= $location['location_lat'] .' (Latitude), ' .$location['location_lon'] .' (Longitude)';
  } else {
    $debug_msg .= PHP_EOL . '  QUERY DATE: '.date('r'); // This is requered for increase data in DB
  }
  print_debug($debug_msg);
  $location['location_status'] = $debug_msg;

  return $location;
}

// EOF
