<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage geolocation
 * @author     Adam Armstrong <adama@observium.org>
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

// This function returns an array of location data when given an address.
// The open&free geocoding APIs are not very flexible, so addresses must be in standard formats.

// DOCME needs phpdoc block
// TESTME needs unit testing
function get_geolocation($address, $geo_db = array(), $dns_only = FALSE)
{
  global $config;

  $ok       = FALSE;
  $location = array('location' => $address); // Init location array
  $location['location_geoapi'] = strtolower(trim($config['geocoding']['api']));
  if (!isset($config['geo_api'][$location['location_geoapi']]))
  {
    // Use default if unknown api
    $location['location_geoapi'] = 'openstreetmap';
  }

  $api_params = &$config['geo_api'][$location['location_geoapi']]; // Link to api specific params
  $params     = $api_params['params'];                             // Init base request params

  // GEO API KEY and rate limits
  $ratelimit = FALSE;
  if (strlen($config['geocoding']['api_key']) && isset($api_params['request_params']['key']))
  {
    $param = $api_params['request_params']['key'];
    $params[$param] = escape_html($config['geocoding']['api_key']); // KEYs is never used special characters
    if (isset($api_params['ratelimit_key']))
    {
      $ratelimit = $api_params['ratelimit_key'];
    }
  } else {
    if (isset($api_params['ratelimit']))
    {
      $ratelimit = $api_params['ratelimit'];
    }
  }

  if (isset($api_params['request_params']['id']))
  {
    $params[$api_params['request_params']['id']] = OBSERVIUM_PRODUCT . '-' . substr(get_unique_id(), 0, 8);
  }

  if (isset($api_params['request_params']['uuid']))
  {
    $params[$api_params['request_params']['uuid']] = get_unique_id();
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
        //include_once('Net/DNS2.php');
        //include_once('Net/DNS2/RR/LOC.php');

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
          if ($dns_only)
          {
            // If we check only DNS LOC records but it not found, exit
            print_debug($debug_msg);
            return FALSE;
          }
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

        $url = $api_params['reverse_url'];
        if (isset($api_params['reverse_params']))
        {
          // Additional params for reverse query
          $params = array_merge($params, $api_params['reverse_params']);
        }

        if (!is_numeric($location['location_lat']) || !is_numeric($location['location_lat']))
        {
          // Do nothing for empty, skip requests for empty coordinates
        } else {
          if (isset($api_params['request_params']['lat']) && isset($api_params['request_params']['lon']))
          {
            $ok = TRUE;
            $param = $api_params['request_params']['lat'];
            $params[$param] = $location['location_lat'];
            $param = $api_params['request_params']['lon'];
            $params[$param] = $location['location_lon'];
          }
          else if (isset($api_params['request_params']['latlon']))
          {
            $ok = TRUE;
            $param = $api_params['request_params']['latlon'];
            $params[$param] = $location['location_lat'] . ',' . $location['location_lon'];
          }
        }
      } else {
        $debug_msg .= '  by PARSING sysLocation (API: '.strtoupper($config['geocoding']['api']).') - ';

        $url = $api_params['direct_url'];
        if (isset($api_params['direct_params']))
        {
          // Additional params for reverse query
          $params = array_merge($params, $api_params['direct_params']);
        }

        if ($address != '')
        {
          $ok = TRUE;
          $param = $api_params['request_params']['address'];
          $params[$param] = urlencode($address);
          //$request = $url . urlencode($address);
        }
      }
      if (OBS_DEBUG > 1)
      {
        print_vars($api_params);
        print_vars($params);
      }

      if ($ok)
      {
        // Build request query
        $request = build_request_url($url, $params, $api_params['method']);

        // First request
        $mapresponse = get_http_request($request, NULL, $ratelimit);
        switch ($GLOBALS['response_headers']['code'][0])
        {
          case '4': // 4xx (timeout, rate limit, forbidden)
          case '5': // 5xx (server error)
            $geo_status = strtoupper($GLOBALS['response_headers']['status']);
            $debug_msg .= $geo_status . PHP_EOL;
            if (OBS_DEBUG < 2)
            {
              // Hide API KEY from output
              $request  = str_replace($api_params['request_params']['key'] . '=' . escape_html($config['geocoding']['api_key']), $api_params['request_params']['key'] . '=' . '***', $request);
            }
            $debug_msg .= '  GEO API REQUEST: ' . $request;
            print_debug($debug_msg);
            // Return old array with new status (for later recheck)
            unset($geo_db['hostname'], $geo_db['location_updated']);
            $location['location_status']  = $debug_msg;
            $location['location_updated'] = format_unixtime($config['time']['now'], 'Y-m-d G:i:s');
            //print_vars($location);
            //print_vars($geo_db);
            return array_merge($geo_db, $location);
        }

        $data        = json_decode($mapresponse, TRUE);
        //print_vars($data);
        $geo_status  = 'NOT FOUND';

        $api_specific = is_file($config['install_dir'] . '/includes/geolocation/' . $location['location_geoapi'] . '.inc.php');
        if ($api_specific)
        {
          // API specific parser
          require_once($config['install_dir'] . '/includes/geolocation/' . $location['location_geoapi'] . '.inc.php');

          if ($data === FALSE)
          {
            // Return old array with new status (for later recheck)
            unset($geo_db['hostname'], $geo_db['location_updated']);
            //$location['location_status']  = $debug_msg;
            $location['location_updated'] = format_unixtime($config['time']['now'], 'Y-m-d G:i:s');
            //print_vars($location);
            //print_vars($geo_db);
            return array_merge($geo_db, $location);
          }
        }
        else if (!isset($location['location_lat']))
        {
          $data = $data[0];
          if (!count($data) && strpos($address, ','))
          {
            // We seem to have hit a snag geocoding. It might be that the first element of the address is a business name.
            // Lets drop the first element and see if we get anything better! This works more often than one might expect.
            list(, $address_new) = explode(',', $address, 2);
            //$request_new = $url.urlencode($address);
            $param = $api_params['request_params']['address'];
            $params[$param] = urlencode($address_new);
            $request_new = build_request_url($url, $params, $api_params['method']);
            $mapresponse = get_http_request($request_new, NULL, $ratelimit);
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

  if (!$api_specific)
  {
    // Nominatum
    if (!$reverse)
    {
      // If using reverse queries, do not change lat/lon
      $location['location_lat'] = $data['lat'];
      $location['location_lon'] = $data['lon'];
    }
    foreach (array('town', 'city', 'hamlet', 'village') as $param)
    {
      if (isset($data['address'][$param]))
      {
        $location['location_city'] = $data['address'][$param];
        break;
      }
    }
    $location['location_state']   = $data['address']['state'];
    $location['location_county']  = isset($data['address']['county']) ? $data['address']['county'] : $data['address']['state_district'];
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

  foreach (array('city', 'county', 'state') as $entry)
  {
    // Remove duplicate County/State words
    $param = 'location_' . $entry;
    $location[$param] = strlen($location[$param]) ? str_ireplace(' '.$entry, '', $location[$param]) : 'Unknown';
  }
  if (strlen($location['location_country']))
  {
    $location['location_country'] = strtolower($location['location_country']);
    $geo_status = 'FOUND';
  } else {
    $location['location_country'] = 'Unknown';
  }

  // Print some debug informations
  $debug_msg .= $geo_status . PHP_EOL;
  if (OBS_DEBUG < 2)
  {
    // Hide API KEY from output
    $request  = str_replace($api_params['request_params']['key'] . '=' . escape_html($config['geocoding']['api_key']), $api_params['request_params']['key'] . '=' . '***', $request);
  }
  $debug_msg .= '  GEO API REQUEST: ' . $request;
  if ($geo_status == 'FOUND')
  {
    $debug_msg .= PHP_EOL . '  GEO LOCATION: ';
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
