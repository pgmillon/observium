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

  if (isset($data['response']))
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
      list(, $address_new) = explode(',', $address, 2);
      //$request_new = $url.urlencode($address);
      $param = $api_params['request_params']['address'];
      $params[$param] = urlencode($address_new);
      $request_new = build_request_url($url, $params, $api_params['method']);
      $mapresponse = get_http_request($request_new, NULL, $ratelimit);
      $data_new = json_decode($mapresponse, TRUE);
      if ($data_new['response']['GeoObjectCollection']['metaDataProperty']['GeocoderResponseMetaData']['found'] > 0 &&
          $data_new['response']['GeoObjectCollection']['featureMember'][0]['GeoObject']['metaDataProperty']['GeocoderMetaData']['precision'] != 'other')
      {
        $request = $request_new;
        $data    = $data_new['response']['GeoObjectCollection']['featureMember'][0];
      }
    }

    if (!$reverse)
    {
      // If using reverse queries, do not change lat/lon
      list($location['location_lon'], $location['location_lat']) = explode(' ', $data['GeoObject']['Point']['pos']);
    }
    $data = $data['GeoObject']['metaDataProperty']['GeocoderMetaData']['AddressDetails'];
    $location['location_country'] = strtolower($data['Country']['CountryNameCode']);
    $location['location_state']   = $data['Country']['AdministrativeArea']['AdministrativeAreaName'];
    if (isset($data['Country']['AdministrativeArea']['SubAdministrativeArea']))
    {
      $location['location_county']  = $data['Country']['AdministrativeArea']['SubAdministrativeArea']['SubAdministrativeAreaName'];
      $location['location_city']    = $data['Country']['AdministrativeArea']['SubAdministrativeArea']['Locality']['LocalityName'];
    } else {
      $location['location_county']  = $data['Country']['AdministrativeArea']['Locality']['DependentLocality']['DependentLocalityName'];
      $location['location_city']    = $data['Country']['AdministrativeArea']['Locality']['DependentLocality']['DependentLocality']['DependentLocalityName'];
    }
  } else {
    $data = FALSE;
  }

// EOF
