<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage webui
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

# enable/disable ports/interfaces on devices.

$device_id = intval($vars['device']);
if (!device_permitted($device_id))
{
  print_error_permission('You have insufficient permissions to edit settings.');
  return;
}

$rows_updated = 0;
//r($vars);
$ports_attribs = get_device_entities_attribs($device_id, 'port'); // Get all attribs

$where = generate_query_values($vars['port'], 'port_id');
foreach (dbFetchRows("SELECT `port_id`, `ignore`, `disabled` FROM `ports` WHERE `device_id` = ?" . $where, array($device_id)) as $port)
{
  $updated = FALSE;
  $port_id = $port['port_id'];
  $update_array = array();

  if (isset($ports_attribs['port'][$port_id]))
  {
    $port = array_merge($port, $ports_attribs['port'][$port_id]);
  }

  // Check ignored and disabled port
  foreach (array('ignore', 'disabled') as $param)
  {
    $param_id = $param . '_' . $port_id;
    $old_param = $port[$param] ? 1 : 0;
    $new_param = (isset($vars[$param_id]) && $vars[$param_id]) ? 1 : 0;
    if ($old_param != $new_param)
    {
      $update_array[$param] = $new_param;
    }
  }

  if (count($update_array))
  {
    //r($update_array);
    dbUpdate($update_array, 'ports', '`device_id` = ? AND `port_id` = ?', array($device_id, $port_id));
    $updated = TRUE;
  }

  // Check custom ifSpeed

  $old_ifSpeed_bool = isset($port['ifSpeed_custom']);
  $new_ifSpeed_bool = isset($vars['ifSpeed_custom_bool_' . $port_id]);
  if ($new_ifSpeed_bool)
  {
    $vars['ifSpeed_custom_' . $port_id] = intval(unit_string_to_numeric($vars['ifSpeed_custom_' . $port_id], 1000));
    if ($vars['ifSpeed_custom_' . $port_id] <= 0)
    {
      // Wrong ifSpeed, skip
      print_warning("Passed incorrect value for port speed.");
      $old_ifSpeed_bool = $new_ifSpeed_bool = FALSE; // Skip change
    }
  }
  if ($old_ifSpeed_bool && $new_ifSpeed_bool)
  {
    // Both set, compare values
    if ($vars['ifSpeed_custom_' . $port_id] != $port['ifSpeed_custom'])
    {
      //r($vars['ifSpeed_custom_' . $port_id]); r($port['ifSpeed_custom']);
      set_entity_attrib('port', $port_id, 'ifSpeed_custom', $vars['ifSpeed_custom_' . $port_id]);
      $updated = TRUE;
    }
  }
  else if ($old_ifSpeed_bool !== $new_ifSpeed_bool)
  {
    // Added or removed
    if ($old_ifSpeed_bool)
    {
      del_entity_attrib('port', $port_id, 'ifSpeed_custom');
    } else {
      set_entity_attrib('port', $port_id, 'ifSpeed_custom', $vars['ifSpeed_custom_' . $port_id]);
    }
    $updated = TRUE;
  }

  // Count updates
  if ($updated)
  {
    $rows_updated++;
  }
}

if ($rows_updated > 0)
{
  $update_message =  $rows_updated . " Port entries updated.";
  $updated = 1;
} else {
  $update_message = "Port entries unchanged. No update necessary.";
  $updated = -1;
}

unset($ports_attribs);

// EOF
