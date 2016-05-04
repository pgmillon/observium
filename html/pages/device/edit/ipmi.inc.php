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
    if ($vars['ipmi_hostname']  != '')  { set_dev_attrib($device, 'ipmi_hostname' , $vars['ipmi_hostname']);  } else { del_dev_attrib($device, 'ipmi_hostname'); }
    if ($vars['ipmi_username']  != '')  { set_dev_attrib($device, 'ipmi_username' , $vars['ipmi_username']);  } else { del_dev_attrib($device, 'ipmi_username'); }
    if ($vars['ipmi_password']  != '')  { set_dev_attrib($device, 'ipmi_password' , $vars['ipmi_password']);  } else { del_dev_attrib($device, 'ipmi_password'); }
    if (is_numeric($vars['ipmi_port'])) { set_dev_attrib($device, 'ipmi_port'     , $vars['ipmi_port']);      } else { del_dev_attrib($device, 'ipmi_port'); }

    // We check interface & userlevel input from the dropdown against the allowed values in the definition array.
    if ($vars['ipmi_interface'] != '' && array_search($vars['ipmi_interface'], array_keys($config['ipmi']['interfaces'])) !== FALSE)
    {
      set_dev_attrib($device, 'ipmi_interface', $vars['ipmi_interface']);
    } else {
      del_dev_attrib($device, 'ipmi_interface');
      print_error('Invalid interface specified (' . $vars['ipmi_interface'] . ').');
    }

    if ($vars['ipmi_userlevel'] != '' && array_search($vars['ipmi_userlevel'], array_keys($config['ipmi']['userlevels'])) !== FALSE)
    {
      set_dev_attrib($device, 'ipmi_userlevel', $vars['ipmi_userlevel']);
    } else {
      del_dev_attrib($device, 'ipmi_userlevel');
      print_error('Invalid user level specified (' . $vars['ipmi_userlevel'] . ').');
    }

    $update_message = "Device IPMI data updated.";
    $updated = 1;
  }

  if ($updated && $update_message)
  {
    print_message($update_message);
  }
  else if ($update_message)
  {
    print_error($update_message);
  }
}

$ipmi_userlevels = array();
foreach ($config['ipmi']['userlevels'] as $type => $descr)
{
  $ipmi_userlevels[$type] = array('name' => $descr['text']);
}
$ipmi_interfaces = array();
foreach ($config['ipmi']['interfaces'] as $type => $descr)
{
  $ipmi_interfaces[$type] = array('name' => $descr['text']);
}

      $form = array('type'      => 'horizontal',
                    'id'        => 'edit',
                    //'space'     => '20px',
                    'title'     => 'IPMI Settings',
                    //'icon'      => 'oicon-gear',
                    //'class'     => 'box box-solid',
                    'fieldset'  => array('edit' => ''),
                    );

      $form['row'][0]['editing']   = array(
                                      'type'        => 'hidden',
                                      'value'       => 'yes');
      $form['row'][1]['ipmi_hostname'] = array(
                                      'type'        => 'text',
                                      'name'        => 'IPMI Hostname',
                                      'width'       => '250px',
                                      'readonly'    => $readonly,
                                      'value'       => escape_html(get_dev_attrib($device, 'ipmi_hostname')));
      $form['row'][2]['ipmi_port'] = array(
                                      'type'        => 'text',
                                      'name'        => 'IPMI Port',
                                      'width'       => '250px',
                                      'readonly'    => $readonly,
                                      'value'       => escape_html(get_dev_attrib($device, 'ipmi_port')));
      $form['row'][3]['ipmi_username'] = array(
                                      'type'        => 'text',
                                      'name'        => 'IPMI Username',
                                      'width'       => '250px',
                                      'readonly'    => $readonly,
                                      'value'       => escape_html(get_dev_attrib($device, 'ipmi_username')));
      $form['row'][4]['ipmi_password'] = array(
                                      'type'        => 'password',
                                      'name'        => 'IPMI Password',
                                      'width'       => '250px',
                                      'readonly'    => $readonly,
                                      'show_password' => !$readonly,
                                      'value'       => escape_html(get_dev_attrib($device, 'ipmi_password')));
      $form['row'][5]['ipmi_userlevel'] = array(
                                      'type'        => 'select',
                                      'name'        => 'IPMI Userlevel',
                                      'width'       => '250px',
                                      'readonly'    => $readonly,
                                      'values'      => $ipmi_userlevels,                                      
                                      'value'       => escape_html(get_dev_attrib($device, 'ipmi_userlevel')));
      $form['row'][6]['ipmi_interface'] = array(
                                      'type'        => 'select',
                                      'name'        => 'IPMI Interface',
                                      'width'       => '250px',
                                      'readonly'    => $readonly,
                                      'values'      => $ipmi_interfaces,                                
                                      'value'       => escape_html(get_dev_attrib($device, 'ipmi_interface')));
      $form['row'][7]['submit']    = array(
                                      'type'        => 'submit',
                                      'name'        => 'Save Changes',
                                      'icon'        => 'icon-ok icon-white',
                                      'class'       => 'btn-primary',
                                      'readonly'    => $readonly,
                                      'value'       => 'save');
      print_form($form);
      unset($form);

// EOF
