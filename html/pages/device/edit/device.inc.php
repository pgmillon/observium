<?php

/**
 * Observium Network Management and Monitoring System
 * Copyright (C) 2006-2015, Adam Armstrong - http://www.observium.org
 *
 * @package    observium
 * @subpackage webui
 * @author     Adam Armstrong <adama@observium.org>
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

$ping_skip = get_entity_attrib('device', $device, 'ping_skip');

if ($vars['editing'])
{
  if ($readonly)
  {
    print_error_permission('You have insufficient permissions to edit settings.');
  } else {
    if (OBS_DEBUG) { print_vars($vars); }
    $updated = 0;

    $override_sysLocation_bool = $vars['override_sysLocation'];
    if (isset($vars['sysLocation'])) { $override_sysLocation_string = $vars['sysLocation']; }

    if (get_entity_attrib('device', $device, 'override_sysLocation_bool')   != $override_sysLocation_bool ||
        get_entity_attrib('device', $device, 'override_sysLocation_string') != $override_sysLocation_string)
    {
      $updated = 2;
    }

    if ($override_sysLocation_bool) { set_entity_attrib('device', $device, 'override_sysLocation_bool', '1'); }
    else                            { del_entity_attrib('device', $device, 'override_sysLocation_bool'); }
    if (isset($override_sysLocation_string)) { set_entity_attrib('device', $device, 'override_sysLocation_string', $override_sysLocation_string); };

    $ping_skip_set = isset($vars['ping_skip']) && ($vars['ping_skip'] == 'on' || $vars['ping_skip'] == '1');
    if ($ping_skip != $ping_skip_set)
    {
      if ($ping_skip_set) { set_entity_attrib('device', $device, 'ping_skip', '1'); }
      else                { del_entity_attrib('device', $device, 'ping_skip'); }
      $ping_skip = get_entity_attrib('device', $device, 'ping_skip');
      $updated++;
    }
    # FIXME needs more sanity checking! and better feedback
    # FIXME -- update location too? Need to trigger geolocation!

    $param = array('purpose' => $vars['descr'], 'type' => $vars['type'], 'ignore' => $vars['ignore'], 'disabled' => $vars['disabled']);

    $rows_updated = dbUpdate($param, 'devices', '`device_id` = ?', array($device['device_id']));

    if ($rows_updated > 0 || $updated)
    {
      if ((bool)$vars['ignore'] != (bool)$device['ignore'])
      {
        log_event('Device '.((bool)$vars['ignore'] ? 'ignored' : 'attended').': '.$device['hostname'], $device['device_id'], 'device', $device['device_id'], 5);
      }
      if ((bool)$vars['disabled'] != (bool)$device['disabled'])
      {
        log_event('Device '.((bool)$vars['disabled'] ? 'disabled' : 'enabled').': '.$device['hostname'], $device['device_id'], 'device', $device['device_id'], 5);
      }
      $update_message = "Device record updated.";
      if ($updated == 2) { $update_message.= " Please note that the updated sysLocation string will only be visible after the next poll."; }
      $updated = 1;
      $device = dbFetchRow("SELECT * FROM `devices` WHERE `device_id` = ?", array($device['device_id']));
    } elseif ($rows_updated = '-1') {
      $update_message = "Device record unchanged. No update necessary.";
      $updated = -1;
    } else {
      $update_message = "Device record update error.";
    }
  }
}

$override_sysLocation_bool   = get_entity_attrib('device', $device, 'override_sysLocation_bool');
$override_sysLocation_string = get_entity_attrib('device', $device, 'override_sysLocation_string');

if ($updated && $update_message)
{
  print_message($update_message);
}
else if ($update_message)
{
  print_error($update_message);
}

$types = array();
foreach ($config['device_types'] as $type)
{
  $types[$type['type']] = array('name' => nicecase($type['type']), 'icon' => $type['icon']);
}
if (!in_array($device['type'], array_keys($types)))
{
  $types[$device['type']] = array('name' => 'Other', 'icon' => 'oicon-question');
}

      $form = array('type'      => 'horizontal',
                    'id'        => 'edit',
                    //'space'     => '20px',
                    'title'     => 'General Device Settings',
                    'icon'      => 'oicon-gear',
                    //'class'     => 'box box-solid',
                    'fieldset'  => array('edit' => ''),
                    );

      $form['row'][0]['editing']   = array(
                                      'type'        => 'hidden',
                                      'value'       => 'yes');
      $form['row'][1]['descr']     = array(
                                      'type'        => 'text',
                                      //'fieldset'    => 'edit',
                                      'name'        => 'Description',
                                      //'class'       => 'input-xlarge',
                                      'width'       => '250px',
                                      'readonly'    => $readonly,
                                      'value'       => escape_html($device['purpose']));
      $form['row'][2]['type']      = array(
                                      'type'        => 'select',
                                      //'fieldset'    => 'edit',
                                      'name'        => 'Type',
                                      'width'       => '250px',
                                      'readonly'    => $readonly,
                                      'values'      => $types,
                                      'value'       => $device['type']);
      $form['row'][3]['sysLocation'] = array(
                                      'type'        => 'text',
                                      //'fieldset'    => 'edit',
                                      'name'        => 'Custom location',
                                      'placeholder' => '',
                                      'width'       => '250px',
                                      'readonly'    => $readonly,
                                      'disabled'    => !$override_sysLocation_bool,
                                      'value'       => escape_html($override_sysLocation_string));
      $form['row'][3]['override_sysLocation'] = array(
                                      'type'        => 'switch',
                                      //'fieldset'    => 'edit',
                                      //'placeholder' => 'Use custom location below.',
                                      'onchange'    => "toggleAttrib('disabled', 'sysLocation')",
                                      'readonly'    => $readonly,
                                      'value'       => $override_sysLocation_bool);
      $form['row'][4]['ping_skip'] = array(
                                      'type'        => 'checkbox',
                                      'name'        => 'Skip ping',
                                      //'fieldset'    => 'edit',
                                      'placeholder' => 'Skip ICMP echo checks, only SNMP availability.',
                                      'readonly'    => $readonly,
                                      'value'       => $ping_skip);
      // FIXME (Mike): $device['ignore'] and get_dev_attrib($device,'disable_notify') it is same/redundant options?
      $form['row'][5]['ignore'] = array(
                                      'type'        => 'checkbox',
                                      'name'        => 'Device ignore',
                                      //'fieldset'    => 'edit',
                                      'placeholder' => 'Ignore device for alerting and notifications.',
                                      'readonly'    => $readonly,
                                      'value'       => $device['ignore']);
      $form['row'][6]['disabled'] = array(
                                      'type'        => 'checkbox',
                                      'name'        => 'Disable',
                                      //'fieldset'    => 'edit',
                                      'placeholder' => 'Disables polling and discovery.',
                                      'readonly'    => $readonly,
                                      'value'       => $device['disabled']);
      $form['row'][7]['submit']    = array(
                                      'type'        => 'submit',
                                      'name'        => 'Save Changes',
                                      'icon'        => 'icon-ok icon-white',
                                      //'right'       => TRUE,
                                      'class'       => 'btn-primary',
                                      'readonly'    => $readonly,
                                      'value'       => 'save');

      print_form($form);
      unset($form);

// EOF
