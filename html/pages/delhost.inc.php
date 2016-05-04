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

// Global write permissions required.
if ($_SESSION['userlevel'] < 10)
{
  print_error_permission();
  return;
}

$page_title[] = "Delete devices";

if (is_numeric($vars['id']))
{
  $device = device_by_id_cache($vars['id']);
  if ($device && $vars['confirm'])
  {
    $delete_rrd = ($vars['deleterrd'] == 'confirm') ? TRUE : FALSE;
    print_message(delete_device($vars['id'], $delete_rrd), 'console');
    //echo('<div class="btn-group ">
    //        <button type="button" class="btn btn-default"><a href="/"><i class="oicon-globe-model"></i> Overview</a></button>
    //        <button type="button" class="btn btn-default"><a href="/devices/"><i class="oicon-servers"></i> Devices List</a></button>
    //      </div>');
  } else {
    print_warning("Are you sure you want to delete device <strong>" . $device['hostname'] . "</strong>?");

      $form = array('type'      => 'horizontal',
                    'id'        => 'delete_host',
                    //'space'     => '20px',
                    'title'     => 'Delete device <strong>'. $device['hostname'] . '</strong>',
                    'icon'      => 'oicon-server--minus',
                    //'class'     => 'box box-solid',
                    'url'       => 'delhost/'
                    );

      $form['row'][0]['id'] = array(
                                      'type'        => 'hidden',
                                      'value'       => $vars['id']);
      $form['row'][4]['deleterrd'] = array(
                                      'type'        => 'checkbox',
                                      'name'        => 'Delete RRDs',
                                      'value'       => (bool)$vars['deleterrd']);
      $form['row'][5]['confirm'] = array(
                                      'type'        => 'checkbox',
                                      'name'        => 'Confirm Deletion',
                                      'onchange'    => "javascript: toggleAttrib('disabled', 'delete');",
                                      'value'       => 'confirm');
      $form['row'][6]['delete']    = array(
                                      'type'        => 'submit',
                                      'name'        => 'Delete device',
                                      'icon'        => 'icon-remove icon-white',
                                      //'right'       => TRUE,
                                      'class'       => 'btn-danger',
                                      'disabled'    => TRUE);
      print_form($form);
      unset($form);
  }
} else {

  foreach ($cache['devices']['hostname'] as $hostname => $device_id)
  {
    $form_devices[$device_id] = array('name' => $hostname);
    if ($cache['devices']['id'][$device_id]['disabled'])
    {
      $form_devices[$device_id]['subtext'] = 'Disabled';
      $form_devices[$device_id]['class']   = 'text-warning';
    }
    else if (!$cache['devices']['id'][$device_id]['status'])
    {
      $form_devices[$device_id]['subtext'] = 'Down';
      $form_devices[$device_id]['class']   = 'red';
    }
  }

      $form = array('type'      => 'horizontal',
                    'id'        => 'delete_host',
                    //'space'     => '20px',
                    'title'     => 'Delete device',
                    'icon'      => 'oicon-server--minus',
                    //'class'     => 'box box-solid',
                    'url'       => 'delhost/'
                    );

      $form['row'][1]['id'] = array(
                                      'type'        => 'select',
                                      'name'        => 'Device',
                                      'values'      => $form_devices);
      $form['row'][4]['deleterrd'] = array(
                                      'type'        => 'checkbox',
                                      'name'        => 'Delete RRDs',
                                      'onchange'    => "javascript: showDiv(this.checked);",
                                      'value'       => 'confirm');
      $form['row'][5]['confirm'] = array(
                                      'type'        => 'checkbox',
                                      'name'        => 'Confirm Deletion',
                                      'onchange'    => "javascript: toggleAttrib('disabled', 'delete');",
                                      'value'       => 'confirm');
      $form['row'][6]['delete']    = array(
                                      'type'        => 'submit',
                                      'name'        => 'Delete device',
                                      'icon'        => 'icon-remove icon-white',
                                      //'right'       => TRUE,
                                      'class'       => 'btn-danger',
                                      'disabled'    => TRUE);

  print_warning("<h4>Warning!</h4>
      This will delete this device from Observium including all logging entries, but will not delete the RRDs.");

      print_form($form);
      unset($form, $form_devices);
}

// EOF
