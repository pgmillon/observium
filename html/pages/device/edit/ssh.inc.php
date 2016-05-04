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

if ($vars['editing'])
{
  if ($readonly)
  {
    print_error_permission('You have insufficient permissions to edit settings.');
  } else {
    $ssh_port = $vars['ssh_port'];

    if (!is_numeric($ssh_port))
    {
      $update_message = "SSH port must be numeric!";
      $updated = 0;
    } else {
      $update = array(
        'ssh_port' => $ssh_port
      );

      $rows_updated = dbUpdate($update, 'devices', '`device_id` = ?',array($device['device_id']));

      if ($rows_updated > 0)
      {
        $update_message = $rows_updated . " Device record updated.";
        $updated = 1;
      } elseif ($rows_updated = '-1') {
        $update_message = "Device record unchanged. No update necessary.";
        $updated = -1;
      } else {
        $update_message = "Device record update error.";
        $updated = 0;
      }
    }
  }
}

$device = dbFetchRow("SELECT * FROM `devices` WHERE `device_id` = ?", array($device['device_id']));

if ($updated && $update_message)
{
  print_message($update_message);
} elseif ($update_message) {
  print_error($update_message);
}

print_warning("For now this option used only by 'libvirt-vminfo' discovery module (on linux devices).");

      $form = array('type'      => 'horizontal',
                    'id'        => 'edit',
                    //'space'     => '20px',
                    'title'     => 'SSH Connectivity',
                    //'icon'      => 'oicon-gear',
                    //'class'     => 'box box-solid',
                    'fieldset'  => array('edit' => ''),
                    );
 
      $form['row'][0]['editing']   = array(
                                      'type'        => 'hidden',
                                      'value'       => 'yes');
      $form['row'][1]['ssh_port']  = array(
                                      'type'        => 'text',
                                      'name'        => 'SSH Port',
                                      'width'       => '250px',
                                      'readonly'    => $readonly,
                                      'value'       => escape_html($device['ssh_port']));
      $form['row'][2]['submit']    = array(
                                      'type'        => 'submit',
                                      'name'        => 'Save Changes',
                                      'icon'        => 'icon-ok icon-white',
                                      'class'       => 'btn-primary',
                                      'readonly'    => $readonly,
                                      'value'       => 'save');

      print_form($form);
      unset($form);
      
// EOF
