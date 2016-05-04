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
    $agent_port = $vars['agent_port'];

    if ($agent_port == "")
    {
      del_dev_attrib($device, 'agent_port');
      $updated = 1;
      $update_message = "Agent settings updated.";
    }
    elseif (!is_numeric($agent_port))
    {
      $update_message = "Agent port must be numeric!";
      $updated = 0;
    }
    else
    {
      set_dev_attrib($device, 'agent_port', $agent_port);
      $updated = 1;
      $update_message = "Agent settings updated.";
    }
  }

  if ($updated && $update_message)
  {
    print_message($update_message);
    log_event('Device Agent configuration changed.', $device['device_id'], 'device', $device, 5); // severity 5, for logging user info
  }
  else if ($update_message)
  {
    print_error($update_message);
  }
}

$device = dbFetchRow("SELECT * FROM `devices` WHERE `device_id` = ?", array($device['device_id']));

      $form = array('type'      => 'horizontal',
                    'id'        => 'edit',
                    //'space'     => '20px',
                    'title'     => 'Agent Connectivity',
                    //'icon'      => 'oicon-gear',
                    //'class'     => 'box box-solid',
                    'fieldset'  => array('edit' => ''),
                    );
 
      $form['row'][0]['editing']   = array(
                                      'type'        => 'hidden',
                                      'value'       => 'yes');
      $form['row'][1]['agent_port'] = array(
                                      'type'        => 'text',
                                      'name'        => 'Agent Port',
                                      'width'       => '250px',
                                      'readonly'    => $readonly,
                                      'value'       => escape_html(get_dev_attrib($device, 'agent_port')));
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
