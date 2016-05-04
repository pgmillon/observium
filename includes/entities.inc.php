<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage functions
 * @author     Adam Armstrong <adama@memetic.org>
 * @copyright  (C) 2006-2015 Adam Armstrong
 *
 */

// DOCME needs phpdoc block
// TESTME needs unit testing
function get_entity_by_id_cache($entity_type, $entity_id)
{
  global $cache;

  $translate = entity_type_translate_array($entity_type);

  if (is_array($cache[$entity_type][$entity_id]))
  {

    return $cache[$entity_type][$entity_id];

  } else {

    switch($entity_type)
    {
      case "port":
        $entity = get_port_by_id($entity_id);
        break;
      default:
        $entity = dbFetchRow("SELECT * FROM `".$translate['table']."` WHERE `".$translate['id_field']."` = ?", array($entity_id));
        if (function_exists('humanize_'.$entity_type)) { $do = 'humanize_'.$entity_type; $do($entity); }
        elseif (isset($translate['humanize_function']) && function_exists('humanize_'.$translate['humanize_function'])) { $do = 'humanize_'.$translate['humanize_function']; $do($entity); }
        break;
    }

    if (is_array($entity))
    {
      entity_rewrite($entity_type, $entity);
      $cache[$entity_type][$entity_id] = $entity;
      return $entity;
    }
  }

  return FALSE;
}

// DOCME needs phpdoc block
// TESTME needs unit testing
function entity_type_translate($entity_type)
{
  $data = entity_type_translate_array($entity_type);
  if (!is_array($data)) { return NULL; }

  return array($data['table'], $data['id_field'], $data['name_field'], $data['ignore_field'], $data['entity_humanize']);
}

// Returns a text name from an entity type and an id
// A little inefficient.
// DOCME needs phpdoc block
// TESTME needs unit testing
function entity_name($type, $entity)
{
  global $config, $entity_cache;

  if (is_numeric($entity))
  {
    $entity = get_entity_by_id_cache($type, $entity);
  }

  $translate = entity_type_translate_array($type);

  $text = $entity[$translate['name_field']];

  return($text);
}

// Returns a text name from an entity type and an id
// A little inefficient.
// DOCME needs phpdoc block
// TESTME needs unit testing
function entity_short_name($type, $entity)
{
  global $config, $entity_cache;

  if (is_numeric($entity))
  {
    $entity = get_entity_by_id_cache($type, $entity);
  }

  $translate = entity_type_translate_array($type);

  $text = $entity[$translate['name_field']];

  return($text);
}

// Returns a text description from an entity type and an id
// A little inefficient.
// DOCME needs phpdoc block
// TESTME needs unit testing
function entity_descr($type, $entity)
{
  global $config, $entity_cache;

  if (is_numeric($entity))
  {
    $entity = get_entity_by_id_cache($type, $entity);
  }

  $translate = entity_type_translate_array($type);

  $text = $entity[$translate['entity_descr_field']];

  return($text);
}

/**
 * Translate an entity type to the relevant table and the identifier field name
 *
 * @param string entity_type
 * @return string entity_table
 * @return string entity_id
*/
// TESTME needs unit testing
function entity_type_translate_array($entity_type)
{
  global $config;

  foreach (array('id_field', 'name_field', 'info_field', 'table', 'ignore_field', 'disable_field', 'deleted_field',
                 'humanize_function', 'parent_type', 'where', 'icon', 'graph') AS $field)
  {
    if (isset($config['entities'][$entity_type][$field]))
    {
      $data[$field] = $config['entities'][$entity_type][$field];
    }
    elseif(isset($config['entities']['default'][$field]))
    {
      $data[$field] = $config['entities']['default'][$field];
    }
  }

  return $data;
}

/**
 * Returns TRUE if the logged in user is permitted to view the supplied entity.
 *
 * @param $entity_id
 * @param $entity_type
 * @param $device_id
 *
 * @return bool
 */
// TESTME needs unit testing
function is_entity_permitted($entity_id, $entity_type, $device_id = NULL)
{
  global $permissions; // 

  if (!is_numeric($device_id)) { $device_id = get_device_id_by_entity_id($entity_id, $entity_type); }

  if ($_SESSION['userlevel'] >= 7) // 7 is global read
  {
    $allowed = TRUE;
  }
  else if (is_numeric($device_id) && device_permitted($device_id))
  {
    $allowed = TRUE;
  }
  else if (isset($permissions[$entity_type][$entity_id]) && $permissions[$entity_type][$entity_id])
  {
    $allowed = TRUE;
  } else {
    $allowed = FALSE;
  }

  print_debug("PERMISSIONS CHECK. Entity type: $entity_type, Entity ID: $entity_id, Device ID: ".($device_id ? $device_id : 'NULL').", Allowed: ".($allowed ? 'TRUE' : 'FALSE').".");
  return $allowed;
}

/**
 * Generates standardised set of array fields for use in entity-generic functions and code.
 * Has no return value, it modifies the $entity array in-place.
 *
 * @param $entity_type string
 * @param $entity array
 *
 */
// TESTME needs unit testing
function entity_rewrite($entity_type, &$entity)
{
  $translate = entity_type_translate_array($entity_type);

  // By default, fill $entity['entity_name'] with name_field contents.
  if (isset($translate['name_field'])) { $entity['entity_name'] = $entity[$translate['name_field']]; }

  // By default, fill $entity['entity_shortname'] with shortname_field contents. Fallback to entity_name when field name is not set.
  if (isset($translate['shortname_field'])) { $entity['entity_shortname'] = $entity[$translate['name_field']]; } else { $entity['entity_shortname'] = $entity['entity_name']; }

  // By default, fill $entity['entity_descr'] with descr_field contents.
  if (isset($translate['descr_field'])) { $entity['entity_descr'] = $entity[$translate['descr_field']]; }

  // By default, fill $entity['entity_id'] with id_field contents.
  if (isset($translate['id_field'])) { $entity['entity_id'] = $entity[$translate['id_field']]; }

  switch($entity_type)
  {
    case "bgp_peer":

      // Special handling of name/shortname/descr for bgp_peer, since it combines multiple elements.

      if (Net_IPv6::checkIPv6($entity['bgpPeerRemoteAddr']))
      {
        $addr = Net_IPv6::compress($entity['bgpPeerRemoteAddr']);
      } else {
        $addr = $entity['bgpPeerRemoteAddr'];
      }

      $entity['entity_name']      = "AS".$entity['bgpPeerRemoteAs'] ." ". $addr;
      $entity['entity_shortname'] = $addr;

      $entity['entity_descr']     = $entity['astext'];
      break;
  }
}

/**
 * Generates a URL to reach the entity's page (or the most specific list page the entity appears on)
 * Has no return value, it modifies the $entity array in-place.
 *
 * @param $entity_type string
 * @param $entity array
 *
 */
// TESTME needs unit testing
function generate_entity_link($entity_type, $entity, $text=NULL, $graph_type=NULL, $escape = TRUE)
{
  global $config, $entity_cache;

  if (is_numeric($entity))
  {
    $entity = get_entity_by_id_cache($entity_type, $entity);
  }

  entity_rewrite($entity_type, $entity);

  // Rewrite sensor subtypes to 'sensor'
  // $translate = entity_type_translate_array($type);
  // if (isset($translate['parent_type'])) { $type = $translate['parent_type']; }

  switch($entity_type)
  {
    case "device":
      $link = generate_device_link($entity);
      break;
    case "mempool":
      $url = generate_url(array('page' => 'device', 'device' => $entity['device_id'], 'tab' => 'health', 'metric' => 'mempool'));
      break;
    case "processor":
      $url = generate_url(array('page' => 'device', 'device' => $entity['device_id'], 'tab' => 'health', 'metric' => 'processor'));
      break;
    case "status":
      $url = generate_url(array('page' => 'device', 'device' => $entity['device_id'], 'tab' => 'health', 'metric' => 'status'));
      break;
    case "sensor":
      $url = generate_url(array('page' => 'device', 'device' => $entity['device_id'], 'tab' => 'health', 'metric' => $entity['sensor_class']));
      break;
    case "toner":
      $url = generate_url(array('page' => 'device', 'device' => $entity['device_id'], 'tab' => 'printing'));
      break;
    case "port":
      $link = generate_port_link($entity, NULL, $graph_type, $escape);
      break;
    case "storage":
      $url = generate_url(array('page' => 'device', 'device' => $entity['device_id'], 'tab' => 'health', 'metric' => 'storage'));
      break;
    case "bgp_peer":
      $url = generate_url(array('page' => 'device', 'device' => $entity['device_id'], 'tab' => 'routing', 'proto' => 'bgp'));
      break;
    case "netscaler_vsvr":
      $url = generate_url(array('page' => 'device', 'device' => $entity['device_id'], 'tab' => 'loadbalancer', 'type' => 'netscaler_vsvr', 'vsvr' => $entity['vsvr_id']));
      break;
    case "netscaler_svc":
      $url = generate_url(array('page' => 'device', 'device' => $entity['device_id'], 'tab' => 'loadbalancer', 'type' => 'netscaler_services', 'svc' => $entity['svc_id']));
      break;
    case "sla":
      $url = generate_url(array('page' => 'device', 'device' => $entity['device_id'], 'tab' => 'slas'));
      break;
    default:
      $url = NULL;
  }

  if (!isset($link))
  {
    if (!isset($text)) { $text = $entity['entity_name']; }
    if ($escape) { $text = escape_html($text); }

    $link = '<a href="' . $url . '" class="entity-popup ' . $entity['html_class'] . '" data-eid="' . $entity['entity_id'] . '" data-etype="' . $entity_type . '">' . $text . '</a>';
  }
  return($link);
}

// EOF
