<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage geolocation
 * @author     Adam Armstrong <adama@memetic.org>
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

// This function returns an array of location data when given an address.
// The open&free geocoding APIs are not very flexible, so addresses must be in standard formats.

// DOCME needs phpdoc block
// TESTME needs unit testing
function get_geolocation($address, $hostname)
{
  global $config;

  switch (strtolower($config['geocoding']['api']))
  {
    case 'osm':
    case 'openstreetmap':
      $location['location_geoapi'] = 'openstreetmap';
      // Openstreetmap. The usage limits are stricter here. (http://wiki.openstreetmap.org/wiki/Nominatim_usage_policy)
      $url = "http://nominatim.openstreetmap.org/search?format=json&addressdetails=1&limit=1&q=";
      $reverse_url = "http://nominatim.openstreetmap.org/reverse?format=json&";
      break;
    case 'google':
      $location['location_geoapi'] = 'google';
      // See documentation here: https:// developers.google.com/maps/documentation/geocoding/
      // Use of the Google Geocoding API is subject to a query limit of 2,500 geolocation requests per day.
      $url = "http://maps.googleapis.com/maps/api/geocode/json?sensor=false&address=";
      $reverse_url = "http://maps.googleapis.com/maps/api/geocode/json?sensor=false&";
      break;
    case 'mapquest':
    default:
      $location['location_geoapi'] = 'mapquest';
      // Mapquest open data. There are no usage limits.
      $url = "http://open.mapquestapi.com/nominatim/v1/search.php?format=json&addressdetails=1&limit=1&q=";
      $reverse_url = "http://open.mapquestapi.com/nominatim/v1/reverse.php?format=json&";
  }

  if ($address != "Unknown" && $config['geocoding']['enable'])
  {
    $reverse = FALSE; // Forward geocoding by default

    // If location string contains coordinates ([33.234, -56.22]) use Reverse Geocoding.
    $pattern = '/\[\s*([+-]*\d+[\d\.]*)[,\s]+([+-]*\d+[\d\.]*)[\s\]]+/';
    if (preg_match($pattern, $address, $matches))
    {
      $location['location_lat'] = $matches[1];
      $location['location_lon'] = $matches[2];
      $reverse = TRUE;
    }

    // If DNS LOC support is enabled and DNS LOC record is set, use Reverse Geocoding.
    if ($config['geocoding']['dns'])
    {
      // Ack! dns_get_record not only cannot retrieve LOC records, but it also actively filters them when using
      // DNS_ANY as query type (which, admittedly would not be all that reliable as per the manual).

      // Example LOC:
      //   "20 31 55.893 N 4 57 38.269 E 45.00m 10m 100m 10m"
      //
      // From Wikipedia: d1 [m1 [s1]] {"N"|"S"}  d2 [m2 [s2]] {"E"|"W"}
      //
      // Parsing this is something for Net_DNS2 as it has the code for it.
      include_once('Net/DNS2.php');
      include_once('Net/DNS2/RR/LOC.php');

      $resolver = new Net_DNS2_Resolver();

      $response = $resolver->query($hostname, 'LOC', 'IN');
      if ($response)
      {
        foreach ($response->answer as $answer)
        {
          if (is_numeric($answer->degree_latitude))
          {
            $ns_multiplier = ($answer->ns_hem == 'N' ? 1 : -1);
            $ew_multiplier = ($answer->ew_hem == 'E' ? 1 : -1);

            $location['location_lat'] = round($answer->degree_latitude + $answer->min_latitude/60 + $answer->sec_latitude/3600,7) * $ns_multiplier;
            $location['location_lon'] = round($answer->degree_longitude + $answer->min_longitude/60 + $answer->sec_longitude/3600,7) * $ew_multiplier;
            $reverse = TRUE;
          }
        }
      }
    }

    if ($reverse)
    {
      if ($config['geocoding']['api'] == 'google')
      {
        // latlng=40.714224,-73.961452
        $request = $reverse_url . 'latlng=' . $location['location_lat'] . ',' . $location['location_lon'];
      } else {
        // lat=51.521435&lon=-0.162714
        $request = $reverse_url . 'lat=' . $location['location_lat'] . '&lon=' . $location['location_lon'];
      }
    } else {
      $request = $url.urlencode($address);
    }

    $mapresponse = get_http_request($request);
    $data = json_decode($mapresponse, true);

    if ($config['geocoding']['api'] == 'google')
    {
      if ($data['status'] == 'OVER_QUERY_LIMIT')
      {
        // Return empty array for overquery limit (for later recheck)
        return array();
      }

      // Use google data only with good status response
      if ($data['status'] == 'OK')
      {
        $data = $data['results'][0];
        if ($data['geometry']['location_type'] == 'APPROXIMATE')
        {
          // It might be that the first element of the address is a business name.
          // Lets drop the first element and see if we get anything better!
          list(, $address) = explode(',', $address, 2);
          $mapresponse = get_http_request($url.urlencode($address));
          $data_new = json_decode($mapresponse, true);
          if ($data_new['status'] == 'OK' && $data_new['results'][0]['geometry']['location_type'] != 'APPROXIMATE')
          {
            $data = $data_new['results'][0];
          }
        }
      }
    }
    elseif (!isset($location['location_lat']))
    {
      $data = $data[0];
      if (!count($data))
      {
        // We seem to have hit a snag geocoding. It might be that the first element of the address is a business name.
        // Lets drop the first element and see if we get anything better! This works more often than one might expect.
        list(, $address) = explode(',', $address, 2);
        $mapresponse = get_http_request($url.urlencode($address));
        $data = json_decode($mapresponse, true);
        // We only want the first entry in the returned data.
        $data = $data[0];
      }
    }
  }

  print_debug("GEO-API REQUEST: $request");

  // Put the values from the data array into the return array where they exist, else replace them with defaults or Unknown.
  if ($config['geocoding']['api'] == 'google')
  {
    $location['location_lat'] = $data['geometry']['location']['lat'];
    $location['location_lon'] = $data['geometry']['location']['lng'];
    foreach ($data['address_components'] as $entry)
    {
      switch ($entry['types'][0])
      {
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
  } else {
    $location['location_lat'] = $data['lat'];
    $location['location_lon'] = $data['lon'];
    $location['location_city'] = (strlen($data['address']['town'])) ? $data['address']['town'] : $data['address']['city'];

    // Would be nice to have an array of countries where we want state, and ones where we want County. For example, USA wants state, UK wants county.
    $location['location_county'] = $data['address']['county'];
    $location['location_state']  = $data['address']['state'];

    $location['location_country'] = $data['address']['country_code'];
  }

  // Use defaults if empty values
  if (!strlen($location['location_lat']))     { $location['location_lat'] = $config['geocoding']['default']['lat']; }
  if (!strlen($location['location_lon']))     { $location['location_lon'] = $config['geocoding']['default']['lon']; }
  if (!strlen($location['location_city']))    { $location['location_city']    = 'Unknown'; }
  if (!strlen($location['location_county']))  { $location['location_county']  = 'Unknown'; }
  if (!strlen($location['location_state']))   { $location['location_state']   = 'Unknown'; }
  if (!strlen($location['location_country'])) { $location['location_country'] = 'Unknown'; }

  return $location;
}

// EOF
