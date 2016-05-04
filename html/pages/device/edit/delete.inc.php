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

if ($_SESSION['userlevel'] < 10)
{
  print_error_permission();
  return;
}

      $form = array('type'      => 'horizontal',
                    'id'        => 'delete_host',
                    //'space'     => '20px',
                    'title'     => 'Delete device',
                    'icon'      => 'oicon-server--minus',
                    //'class'     => 'box box-solid',
                    'url'       => 'delhost/'
                    );

      $form['row'][0]['id']   = array(
                                      'type'        => 'hidden',
                                      'value'       => $device['device_id']);
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

  print_warning("<h3>Warning!</h4>
      This will delete this device from Observium including all logging entries, but will not delete the RRDs.");

      print_form($form);
      unset($form);

// EOF
