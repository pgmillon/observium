<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage functions
 * @author     Adam Armstrong <adama@memetic.org>
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

// DOCME needs phpdoc block
// TESTME needs unit testing
function get_entity_by_id_cache($type, $id)
{
  global $cache;

  $translate = entity_type_translate_array($type);

  if (is_array($cache[$type][$id]))
  {
    return $cache[$type][$id];
  } else {
    switch($type)
    {
      case "port":
        $entity = get_port_by_id($id);
        break;
      default:
        $entity = dbFetchRow("SELECT * FROM `".$translate['table']."` WHERE `".$translate['id_field']."` = ?", array($id));
        if (function_exists('humanize_'.$type)) { $do = 'humanize_'.$type; $do($entity); }
        elseif (isset($translate['humanize_function']) && function_exists('humanize_'.$translate['humanize_function'])) { $do = 'humanize_'.$translate['humanize_function']; $do($entity); }
        break;
    }

    if (is_array($entity))
    {
      entity_rewrite($type, $entity);
      $cache[$type][$id] = $entity;
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

// DOCME needs phpdoc block
// TESTME needs unit testing
function entity_rewrite($type, &$entity)
{
  $translate = entity_type_translate_array($type);

  switch($type)
  {
    case "bgp_peer":
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
    default:
      // By default, fill $entity['entity_name'] with name_field contents.
      if (isset($translate['name_field'])) { $entity['entity_name'] = $entity[$translate['name_field']]; }

      // By default, fill $entity['entity_shortname'] with shortname_field contents. Fallback to entity_name when field name is not set.
      if (isset($translate['shortname_field'])) { $entity['entity_shortname'] = $entity[$translate['name_field']]; } else { $entity['entity_shortname'] = $entity['entity_name']; }

      // By default, fill $entity['entity_descr'] with descr_field contents.
      if (isset($translate['descr_field'])) { $entity['entity_descr'] = $entity[$translate['descr_field']]; }
      break;
  }
}

// DOCME needs phpdoc block
// TESTME needs unit testing
function generate_entity_link($type, $entity, $text=NULL, $graph_type=NULL, $escape = TRUE)
{
  global $config, $entity_cache;

  if (is_numeric($entity))
  {
    $entity = get_entity_by_id_cache($type, $entity);
  }

  // Rewrite sensor subtypes to 'sensor'
  $translate = entity_type_translate_array($type);
  if (isset($translate['parent_type'])) { $type = $translate['parent_type']; }

  switch($type)
  {
    case "device":
      if (empty($text)) { $text = $entity['hostname']; } // FIXME use name_field property for all of these? Like entity_rewrite above does.
      $link = generate_link($text, array('page' => 'device', 'device' => $entity['device_id']), array(), $escape);
      break;
    case "mempool":
      if (empty($text)) { $text = $entity['mempool_descr']; }
      $link = generate_link($text, array('page' => 'device', 'device' => $entity['device_id'], 'tab' => 'health', 'metric' => 'mempool'), array(), $escape);
      break;
    case "processor":
      if (empty($text)) { $text = $entity['processor_descr']; }
      $link = generate_link($text, array('page' => 'device', 'device' => $entity['device_id'], 'tab' => 'health', 'metric' => 'processor'), array(), $escape);
      break;
    case "sensor":
      if (empty($text)) { $text = $entity['sensor_descr']; }
      $link = generate_link($text, array('page' => 'device', 'device' => $entity['device_id'], 'tab' => 'health', 'metric' => $entity['sensor_class']), array(), $escape);
      break;
    case "toner":
      if (empty($text)) { $text = $entity['toner_descr']; }
      $link = generate_link($text, array('page' => 'device', 'device' => $entity['device_id'], 'tab' => 'printing'), array(), $escape);
      break;
    case "port":
      $link = generate_port_link($entity, $text, $graph_type, $escape);
      break;
    case "storage":
      if (empty($text)) { $text = $entity['storage_descr']; }
      $link = generate_link($text, array('page' => 'device', 'device' => $entity['device_id'], 'tab' => 'health', 'metric' => 'storage'), array(), $escape);
      break;
    case "bgp_peer":
      if (Net_IPv6::checkIPv6($entity['bgpPeerRemoteAddr']))
      {
        $addr = Net_IPv6::compress($entity['bgpPeerRemoteAddr']);
      } else {
        $addr = $entity['bgpPeerRemoteAddr'];
      }
      if (empty($text)) { $text = $addr ." (AS".$entity['bgpPeerRemoteAs'] .")"; }
      $link = generate_link($text, array('page' => 'device', 'device' => $entity['device_id'], 'tab' => 'routing', 'proto' => 'bgp'), array(), $escape);
      break;
    case "netscaler_vsvr":
      if (empty($text)) { $text = $entity['vsvr_label']; }
      $link = generate_link($text, array('page' => 'device', 'device' => $entity['device_id'], 'tab' => 'loadbalancer', 'type' => 'netscaler_vsvr', 'vsvr' => $entity['vsvr_id']), array(), $escape);
      break;
    case "netscaler_svc":
      if (empty($text)) { $text = $entity['svc_label']; }
      $link = generate_link($text, array('page' => 'device', 'device' => $entity['device_id'], 'tab' => 'loadbalancer', 'type' => 'netscaler_services', 'svc' => $entity['svc_id']), array(), $escape);
      break;

    default:
      $link = $entity[$type.'_id'];
  }
  return($link);
}

// EOF
