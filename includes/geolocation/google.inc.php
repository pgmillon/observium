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

/**
 * Here passed $data array with decoded first request response
 * At end returned main $location variables
 */

  // Use google data only with good status response
  if ($data['status'] == 'OK')
  {
    $data       = $data['results'][0];
    if ($data['geometry']['location_type'] == 'APPROXIMATE' && strpos($address, ','))
    {
      // It might be that the first element of the address is a business name.
      // Lets drop the first element and see if we get anything better!
      list(, $address_new) = explode(',', $address, 2);
      //$request_new = $url.urlencode($address);
      $param = $api_params['request_params']['address'];
      $params[$param] = urlencode($address_new);
      $request_new = build_request_url($url, $params, $api_params['method']);
      $mapresponse = get_http_request($request_new, NULL, $ratelimit);
      $data_new = json_decode($mapresponse, TRUE);
      if ($data_new['status'] == 'OK' && $data_new['results'][0]['geometry']['location_type'] != 'APPROXIMATE')
      {
        $request = $request_new;
        $data    = $data_new['results'][0];
      }
    }

    if (!$reverse)
    {
      // If using reverse queries, do not change lat/lon
      $location['location_lat'] = $data['geometry']['location']['lat'];
      $location['location_lon'] = $data['geometry']['location']['lng'];
    }
    foreach ($data['address_components'] as $entry)
    {
      switch ($entry['types'][0])
      {
        case 'sublocality_level_1':
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
  } else {
    $data = FALSE;
  }

// EOF
