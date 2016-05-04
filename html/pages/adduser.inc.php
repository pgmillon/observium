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

include($config['html_dir']."/pages/usermenu.inc.php");

  $page_title[] = "Add User";

  if (auth_usermanagement())
  {
    if ($vars['submit'] == 'add_user')
    {
      if ($vars['new_username'])
      {
        $vars['new_username'] = strip_tags($vars['new_username']);
        if (!auth_user_exists($vars['new_username']))
        {
          if (isset($vars['can_modify_passwd']))
          {
            $vars['can_modify_passwd'] = 1;
          } else {
            $vars['can_modify_passwd'] = 0;
          }

          if (!$vars['new_password'])
          {
            print_warning("Please enter a password!");
          }
          else if (adduser($vars['new_username'], $vars['new_password'], $vars['new_level'], $vars['new_email'], $vars['new_realname'], $vars['can_modify_passwd'], $vars['new_description']))
          {
            print_success('User ' . escape_html($vars['new_username']) . ' added!');
          }
        } else {
          print_error('User with this name already exists!');
        }
      } else {
        print_warning("Please enter a username!");
      }
    }

      $form = array('type'      => 'horizontal',
                    'id'        => 'add_user',
                    //'space'     => '20px',
                    //'title'     => 'Add User',
                    //'icon'      => 'oicon-gear',
                    );
      // top row div
      $form['fieldset']['user']    = array('div'   => 'top',
                                           'title' => 'User Properties',
                                           'icon'  => 'oicon-user--pencil',
                                           'class' => 'col-md-6');
      $form['fieldset']['info']    = array('div'   => 'top',
                                           'title' => 'Optional Information',
                                           'icon'  => 'oicon-information',
                                           //'right' => TRUE,
                                           'class' => 'col-md-6 col-md-pull-0');
      // bottom row div
      $form['fieldset']['submit']  = array('div'   => 'bottom',
                                           'style' => 'padding: 0px;',
                                           'class' => 'col-md-12');

      //$form['row'][0]['editing']   = array(
      //                                'type'        => 'hidden',
      //                                'value'       => 'yes');
      // left fieldset
      $form['row'][1]['new_username'] = array(
                                      'type'        => 'text',
                                      'fieldset'    => 'user',
                                      'name'        => 'Username',
                                      'width'       => '250px',
                                      'value'       => escape_html($vars['new_username']));
      $form['row'][2]['new_password'] = array(
                                      'type'        => 'password',
                                      'fieldset'    => 'user',
                                      'name'        => 'Password',
                                      'width'       => '250px',
                                      'show_password' => TRUE,
                                      'value'       => escape_html($vars['new_password'])); // FIXME. For passwords we should use filter instead escape!
      $form['row'][3]['can_modify_passwd'] = array(
                                      'type'        => 'checkbox',
                                      'fieldset'    => 'user',
                                      'name'        => '',
                                      'placeholder' => 'Allow the user to change his password',
                                      'value'       => 1);
      $form['row'][4]['new_realname'] = array(
                                      'type'        => 'text',
                                      'fieldset'    => 'user',
                                      'name'        => 'Real Name',
                                      'width'       => '250px',
                                      'value'       => escape_html($vars['new_realname']));
      $form['row'][5]['new_level'] = array(
                                      'type'        => 'select',
                                      'fieldset'    => 'user',
                                      'name'        => 'User Level',
                                      'width'       => '250px',
                                      'subtext'     => TRUE,
                                      'values'      => $GLOBALS['config']['user_level'],
                                      'value'       => (isset($vars['new_level']) ? escape_html($vars['new_level']) : 1));

      // right fieldset
      $form['row'][15]['new_email'] = array(
                                      'type'        => 'text',
                                      'fieldset'    => 'info',
                                      'name'        => 'E-mail',
                                      'width'       => '250px',
                                      'value'       => escape_html($vars['new_email']));
      $form['row'][16]['new_description'] = array(
                                      'type'        => 'text',
                                      'fieldset'    => 'info',
                                      'name'        => 'Description',
                                      'width'       => '250px',
                                      'value'       => escape_html($vars['new_description']));

      $form['row'][30]['submit']    = array(
                                      'type'        => 'submit',
                                      'fieldset'    => 'submit',
                                      'name'        => 'Add User',
                                      'icon'        => 'icon-ok icon-white',
                                      //'right'       => TRUE,
                                      'class'       => 'btn-primary',
                                      'value'       => 'add_user');

      print_form_box($form);
      unset($form);

  } else {
    print_error('Auth module does not allow user management!');
  }

// EOF
