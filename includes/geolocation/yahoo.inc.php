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
  if (isset($data['ResultSet']))
  {
    $data_tmp = $data['ResultSet']['Results'][0];
    if ($data['ResultSet']['Found'] == '0')
    {
      // It might be that the first element of the address is a business name.
      // Lets drop the first element and see if we get anything better!
      list(, $address_new) = explode(',', $address, 2);
      $param = $api_params['request_params']['address'];
      $params[$param] = urlencode($address_new);
      $request_new = build_request_url($url, $params, $api_params['method']);
      $mapresponse = get_http_request($request_new, NULL, $ratelimit);
      $data_new = json_decode($mapresponse, TRUE);
      if ($data_new['ResultSet']['Found'] > 0)
      {
        $request = $request_new;
        $data_tmp = $data_new['ResultSet']['Results'][0];
      }
    }
    $data = $data_tmp;

    if (!$reverse)
    {
      // If using reverse queries, do not change lat/lon
      $location['location_lat'] = $data['latitude'];
      $location['location_lon'] = $data['longitude'];
    }

    $location['location_city']    = $data['city'];
    $location['location_state']   = $data['state'];
    $location['location_county']  = $data['county'];
    $location['location_country'] = $data['countrycode'];
  } else {
    $data = FALSE;
  }

// EOF
