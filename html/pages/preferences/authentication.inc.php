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

if (auth_can_change_password($_SESSION['username']))
{
      $form = array('type'    => 'horizontal',
                    //'space'   => '20px',
                    'title'   => 'Change Password',
                    //'icon'    => 'oicon-key',
                    //'class'   => 'box'
                    );
                    //'fieldset'  => array('change_password' => 'Change Password'));
      $form['row'][0]['old_pass'] = array(
                                      'type'        => 'password',
                                      'name'        => 'Old Password',
                                      'width'       => '95%',
                                      'value'       => '');
      $form['row'][1]['new_pass'] = array(
                                      'type'        => 'password',
                                      'name'        => 'New Password',
                                      'width'       => '95%',
                                      'value'       => '');
      $form['row'][2]['new_pass2']  = array(
                                      'type'        => 'password',
                                      'name'        => 'Retype Password',
                                      'width'       => '95%',
                                      'value'       => '');
      $form['row'][3]['password']   = array(
                                      'type'        => 'submit',
                                      'name'        => 'Save&nbsp;Password',
                                      'icon'        => 'oicon-lock-warning',
                                      'right'       => TRUE,
                                      'value'       => 'save');
      echo('  <div class="col-md-6">' . PHP_EOL);
      print_form($form);
      unset($form, $i);
      echo('  </div>' . PHP_EOL);
}

// EOF
