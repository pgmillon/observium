<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage functions
 * @author     Adam Armstrong <adama@observium.org>
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

/**
 *
 * Get attribute value for entity
 *
 * @param string $entity_type
 * @param mixed $entity_id
 * @param string $attrib_type
 * @return string
 */
function get_entity_attrib($entity_type, $entity_id, $attrib_type)
{
  if (is_array($entity_id))
  {
    // Passed entity array, instead id
    $translate = entity_type_translate_array($entity_type);
    $entity_id = $entity_id[$translate['id_field']];
  }
  if (!$entity_id) { return NULL; }

  if (isset($GLOBALS['cache']['entity_attribs'][$entity_type][$entity_id][$attrib_type]))
  {
    return $GLOBALS['cache']['entity_attribs'][$entity_type][$entity_id][$attrib_type];
  }

  if ($entity_type == 'device' && get_db_version() < 240)
  {
    // CLEANME. Compatibility, remove in r8000, but not before CE 0.16.1 (Oct 7, 2015)
    if ($row = dbFetchRow("SELECT `attrib_value` FROM `devices_attribs` WHERE `device_id` = ? AND `attrib_type` = ?", array($entity_id, $attrib_type)))
    {
      return $row['attrib_value'];
    }
  }
  else if ($row = dbFetchRow("SELECT `attrib_value` FROM `entity_attribs` WHERE `entity_type` = ? AND `entity_id` = ? AND `attrib_type` = ?", array($entity_type, $entity_id, $attrib_type)))
  {
    return $row['attrib_value'];
  }

  return NULL;
}

/**
 *
 * Get all attributes for entity
 *
 * @param string $entity_type
 * @param mixed $entity_id
 * @return array
 */
function get_entity_attribs($entity_type, $entity_id)
{
  if (is_array($entity_id))
  {
    // Passed entity array, instead id
    $translate = entity_type_translate_array($entity_type);
    $entity_id = $entity_id[$translate['id_field']];
  }
  if (!$entity_id) { return NULL; }

  if (!isset($GLOBALS['cache']['entity_attribs'][$entity_type][$entity_id]))
  {
    $attribs = array();
    if ($entity_type == 'device' && get_db_version() < 240)
    {
      // CLEANME. Compatibility, remove in r8000, but not before CE 0.16.1 (Oct 7, 2015)
      foreach (dbFetchRows("SELECT * FROM `devices_attribs` WHERE `device_id` = ?", array($entity_id)) as $entry)
      {
        $attribs[$entry['attrib_type']] = $entry['attrib_value'];
      }
    } else {
      foreach (dbFetchRows("SELECT * FROM `entity_attribs` WHERE `entity_type` = ? AND `entity_id` = ?", array($entity_type, $entity_id)) as $entry)
      {
        $attribs[$entry['attrib_type']] = $entry['attrib_value'];
      }
    }
    $GLOBALS['cache']['entity_attribs'][$entity_type][$entity_id] = $attribs;
  }
  return $GLOBALS['cache']['entity_attribs'][$entity_type][$entity_id];
}

/**
 *
 * Set value for specific attribute and entity
 *
 * @param string $entity_type
 * @param mixed $entity_id
 * @param string $attrib_type
 * @param string $attrib_value
 * @return boolean
 */
function set_entity_attrib($entity_type, $entity_id, $attrib_type, $attrib_value)
{
  if (is_array($entity_id))
  {
    // Passed entity array, instead id
    $translate = entity_type_translate_array($entity_type);
    $entity_id = $entity_id[$translate['id_field']];
  }
  if (!$entity_id) { return NULL; }

  if (isset($GLOBALS['cache']['entity_attribs'][$entity_type][$entity_id]))
  {
    // Reset entity attribs
    unset($GLOBALS['cache']['entity_attribs'][$entity_type][$entity_id]);
  }

  if ($entity_type == 'device' && get_db_version() < 240)
  {
    // // CLEANME. Compatibility, remove in r8000, but not before CE 0.16.1 (Oct 7, 2015)
    if (dbFetchCell("SELECT COUNT(*) FROM `devices_attribs` WHERE `device_id` = ? AND `attrib_type` = ?", array($entity_id, $attrib_type)))
    {
      $return = dbUpdate(array('attrib_value' => $attrib_value), 'devices_attribs', '`device_id` = ? AND `attrib_type` = ?', array($entity_id, $attrib_type));
    } else {
      $return = dbInsert(array('device_id' => $entity_id, 'attrib_type' => $attrib_type, 'attrib_value' => $attrib_value), 'devices_attribs');
      if ($return !== FALSE) { $return = TRUE; } // Note dbInsert return IDs if exist or 0 for not indexed tables
    }
  } else {
    if (dbFetchCell("SELECT COUNT(*) FROM `entity_attribs` WHERE `entity_type` = ? AND `entity_id` = ? AND `attrib_type` = ?", array($entity_type, $entity_id, $attrib_type)))
    {
      $return = dbUpdate(array('attrib_value' => $attrib_value), 'entity_attribs', '`entity_type` = ? AND `entity_id` = ? AND `attrib_type` = ?', array($entity_type, $entity_id, $attrib_type));
    } else {
      $return = dbInsert(array('entity_type' => $entity_type, 'entity_id' => $entity_id, 'attrib_type' => $attrib_type, 'attrib_value' => $attrib_value), 'entity_attribs');
      if ($return !== FALSE) { $return = TRUE; } // Note dbInsert return IDs if exist or 0 for not indexed tables
    }
  }
  return $return;
}

/**
 *
 * Delete specific attribute for entity
 *
 * @param string $entity_type
 * @param mixed $entity_id
 * @param string $attrib_type
 * @return boolean
 */
function del_entity_attrib($entity_type, $entity_id, $attrib_type)
{
  if (is_array($entity_id))
  {
    // Passed entity array, instead id
    $translate = entity_type_translate_array($entity_type);
    $entity_id = $entity_id[$translate['id_field']];
  }
  if (!$entity_id) { return NULL; }

  if (isset($GLOBALS['cache']['entity_attribs'][$entity_type][$entity_id]))
  {
    // Reset entity attribs
    unset($GLOBALS['cache']['entity_attribs'][$entity_type][$entity_id]);
  }

  if ($entity_type == 'device' && get_db_version() < 240)
  {
    // CLEANME. Compatibility, remove in r8000, but not before CE 0.16.1 (Oct 7, 2015)
    return dbDelete('devices_attribs', '`device_id` = ? AND `attrib_type` = ?', array($entity_id, $attrib_type));
  } else {
    return dbDelete('entity_attribs', '`entity_type` = ? AND `entity_id` = ? AND `attrib_type` = ?', array($entity_type, $entity_id, $attrib_type));
  }
}

/**
 *
 * Get array of entities (id) linked to device
 *
 * @param mixed $device_id Device array of id
 * @param mixed $entity_types List of entities as array, if empty get all
 * @return array
 */
function get_device_entities($device_id, $entity_types = NULL)
{
  if (is_array($device_id))
  {
    // Passed device array, instead id
    $device_id = $device_id['device_id'];
  }
  if (!$device_id) { return NULL; }

  if (!is_array($entity_types) && strlen($entity_types))
  {
    // Single entity type passed, convert to array
    $entity_types = array($entity_types);
  }
  $all = empty($entity_types);
  $entities = array();
  foreach ($GLOBALS['config']['entities'] as $entity_type => $entry)
  {
    if ($all || in_array($entity_type, $entity_types))
    {
      $query = 'SELECT `' . $entry['id_field'] . '` FROM `' . $entry['table'] . '` WHERE `device_id` = ?;';
      $entity_ids = dbFetchColumn($query, array($device_id));
      if (count($entity_ids))
      {
        $entities[$entity_type] = $entity_ids;
      }
    }
  }
  return $entities;
}

/**
 *
 * Get all attributes for all entities from device
 *
 * @param string $entity_type
 * @param mixed $entity_id
 * @return array
 */
function get_device_entities_attribs($device_id, $entity_types = NULL)
{
  $attribs = array();
  foreach (get_device_entities($device_id, $entity_types) as $entity_type => $entities)
  {
    $where = generate_query_values($entities, 'entity_id');
    foreach (dbFetchRows("SELECT * FROM `entity_attribs` WHERE `entity_type` = ?" . $where, array($entity_type)) as $entry)
    {
      $attribs[$entry['entity_type']][$entry['entity_id']][$entry['attrib_type']] = $entry['attrib_value'];
    }
  }
  $GLOBALS['cache']['entity_attribs'] = $attribs;

  return $GLOBALS['cache']['entity_attribs'];
}

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
      case "bill":
        if (function_exists('get_bill_by_id'))
        {
          $entity = get_bill_by_id($entity_id);
        }
        break;

      case "port":
        $entity = get_port_by_id($entity_id);
        break;

      default:
        $sql = 'SELECT * FROM `'.$translate['table'].'`';
        if (isset($translate['state_table']))
        {
          $sql .= ' LEFT JOIN `'.$translate['state_table'].'` USING (`'.$translate['id_field'].'`)';
        }

        $sql .= ' WHERE `'.$translate['table'].'`.`'.$translate['id_field'].'` = ?';

        $entity = dbFetchRow($sql, array($entity_id));
        if (function_exists('humanize_'.$entity_type)) { $do = 'humanize_'.$entity_type; $do($entity); }
        else if (isset($translate['humanize_function']) && function_exists($translate['humanize_function'])) { $do = $translate['humanize_function']; $do($entity); }
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
 * @return array entity_id
*/
// TESTME needs unit testing
function entity_type_translate_array($entity_type)
{
  $transtale = $GLOBALS['config']['entities'][$entity_type];

  // Base fields
  // FIXME, not listed here: agg_graphs, metric_graphs
  $fields = array('table', 'table_fields', 'state_table', 'state_fields', 'humanize_function', 'parent_type', 'where', 'icon', 'graph');
  foreach ($fields as $field)
  {
    if (isset($transtale[$field]))
    {
      $data[$field] = $transtale[$field];
    }
    else if(isset($GLOBALS['config']['entities']['default'][$field]))
    {
      $data[$field] = $GLOBALS['config']['entities']['default'][$field];
    }
  }

  // Table fields
  $fields_table = array('id', 'index', 'mib', 'name', 'shortname', 'descr', 'ignore', 'disable', 'deleted', 'limit_high', 'limit_low');
  if (isset($transtale['table_fields']))
  {
    // New definition style
    foreach ($transtale['table_fields'] as $field => $entry)
    {
      if (in_array($field, $fields_table))
      {
        // Add old style name (ie 'id_field') for compatibility
        $data[$field . '_field'] = $entry;
      }
    }
  } else {
    // Old definition style
    foreach ($fields_table as $field)
    {
      $field_old = $field . '_field';
      if (isset($transtale[$field_old]))
      {
        $data[$field_old] = $transtale[$field_old];
        // Additionally convert to new 'table_fields' array
        $data['table_fields'][$field] = $transtale[$field_old];
      }
    }
  }

  // State fields. Note, state fields not converted to old style (*_field), since not used before
  $fields_state = array('value', 'status', 'event', 'uptime', 'last_change');
  //r($data);

  return $data;
}

/**
 * Returns TRUE if the logged in user is permitted to view the supplied entity.
 *
 * @param $entity_id
 * @param $entity_type
 * @param $device_id
 * @param $permissions Permissions array, by default used global var $permissions generated by permissions_cache()
 *
 * @return bool
 */
// TESTME needs unit testing
function is_entity_permitted($entity_id, $entity_type, $device_id = NULL, $permissions = NULL)
{
  if (is_null($permissions) && isset($GLOBALS['permissions']))
  {
    // Note, pass permissions array by param used in permissions_cache()
    $permissions = $GLOBALS['permissions'];
  }

  //if (OBS_DEBUG)
  //{
  //  print_vars($permissions);
  //  print_vars($_SESSION);
  //  print_vars($GLOBALS['auth']);
  //  print_vars(is_graph());
  //}

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
  }
  else if (isset($GLOBALS['auth']) && is_graph())
  {
    $allowed = $GLOBALS['auth'];
  } else {
    $allowed = FALSE;
  }

  if (OBS_DEBUG)
  {
    $debug_msg = "PERMISSIONS CHECK. Entity type: $entity_type, Entity ID: $entity_id, Device ID: ".($device_id ? $device_id : 'NULL').", Allowed: ".($allowed ? 'TRUE' : 'FALSE').".";
    if (isset($GLOBALS['notifications']))
    {
      $GLOBALS['notifications'][] = array('text' => $debug_msg, 'severity' => 'debug');
    } else {
      print_debug($debug_msg);
    }
  }
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

    case "sla":
      $entity['entity_name']      = "SLA #". $entity['sla_index'] . " (". $entity['sla_tag'] . ")";
      $entity['entity_shortname'] = "#". $entity['sla_index'] . " (". $entity['sla_tag'] . ")";
      break;

    case "pseudowire":
      $entity['entity_name']      = $entity['pwID'] . ($entity['pwDescr'] ? " (". $entity['pwDescr'] . ")" : '');
      $entity['entity_shortname'] = $entity['pwID'];
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
function generate_entity_link($entity_type, $entity, $text = NULL, $graph_type = NULL, $escape = TRUE, $short = FALSE)
{

  if (is_numeric($entity))
  {
    $entity = get_entity_by_id_cache($entity_type, $entity);
  }

  entity_rewrite($entity_type, $entity);

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
      $url = generate_url(array('page' => 'device', 'device' => $entity['device_id'], 'tab' => 'health', 'metric' => 'status', 'id' => $entity['status_id']));
      break;
    case "sensor":
      $url = generate_url(array('page' => 'device', 'device' => $entity['device_id'], 'tab' => 'health', 'metric' => $entity['sensor_class'], 'id' => $entity['sensor_id']));
      break;
    case "toner":
      $url = generate_url(array('page' => 'device', 'device' => $entity['device_id'], 'tab' => 'printing', 'toner' => $entity['toner_id']));
      break;
    case "port":
      $link = generate_port_link($entity, NULL, $graph_type, $escape, $short);
      break;
    case "storage":
      $url = generate_url(array('page' => 'device', 'device' => $entity['device_id'], 'tab' => 'health', 'metric' => 'storage'));
      break;
    case "bgp_peer":
      $url = generate_url(array('page' => 'device', 'device' => ($entity['peer_device_id'] ? $entity['peer_device_id'] : $entity['device_id']), 'tab' => 'routing', 'proto' => 'bgp'));
      break;
    case "netscaler_vsvr":
      $url = generate_url(array('page' => 'device', 'device' => $entity['device_id'], 'tab' => 'loadbalancer', 'type' => 'netscaler_vsvr', 'vsvr' => $entity['vsvr_id']));
      break;
    case "netscaler_svc":
      $url = generate_url(array('page' => 'device', 'device' => $entity['device_id'], 'tab' => 'loadbalancer', 'type' => 'netscaler_services', 'svc' => $entity['svc_id']));
      break;
    case "sla":
      $url = generate_url(array('page' => 'device', 'device' => $entity['device_id'], 'tab' => 'slas', 'id' => $entity['sla_id']));
      break;
    case "pseudowire":
      $url = generate_url(array('page' => 'device', 'device' => $entity['device_id'], 'tab' => 'pseudowires', 'id' => $entity['pseudowire_id']));
      break;
    case "maintenance":
      $url = generate_url(array('page' => 'alert_maintenance', 'maintenance' => $entity['maint_id']));
      break;
    case "group":
      $url = generate_url(array('page' => 'group', 'group_id' => $entity['group_id']));
      break;
    default:
      $url = NULL;
  }

  if (!isset($link))
  {
    if (!isset($text))
    {
      if ($short && $entity['entity_shortname'])
      {
        $text = $entity['entity_shortname'];
      } else {
        $text = $entity['entity_name'];
      }
    }
    if ($escape) { $text = escape_html($text); }
    $link = '<a href="' . $url . '" class="entity-popup ' . $entity['html_class'] . '" data-eid="' . $entity['entity_id'] . '" data-etype="' . $entity_type . '">' . $text . '</a>';
  }

  return($link);
}

// EOF
