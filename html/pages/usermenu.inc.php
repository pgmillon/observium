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

$isUserlist = (isset($vars['user_id']) ? true : false);

$navbar['class'] = 'navbar-narrow';
$navbar['brand'] = 'Users';

if (auth_usermanagement())
{
  $navbar['options']['add']['url']  = generate_url(array('page' => 'adduser'));
  $navbar['options']['add']['text'] = 'Add User';
  $navbar['options']['add']['icon'] = 'oicon-user--plus';
  if ($vars['page'] == 'adduser') { $navbar['options']['add']['class'] = 'active'; };
}

$navbar['options']['edit']['url']  = generate_url(array('page' => 'edituser'));
$navbar['options']['edit']['text'] = 'Edit Users';
$navbar['options']['edit']['icon'] = 'oicon-user--pencil';
if ($vars['page'] == 'edituser') { $navbar['options']['edit']['class'] = 'active'; };

$navbar['options']['log']['url']  = generate_url(array('page' => 'authlog'));
$navbar['options']['log']['text'] = 'Authlog';
$navbar['options']['log']['icon'] = 'oicon-clipboard-eye';
if ($vars['page'] == 'authlog') { $navbar['options']['log']['class'] = 'active'; };

if ($isUserlist)
{
  $navbar['options_right']['edit']['url']  = generate_url(array('page' => 'edituser'));
  $navbar['options_right']['edit']['text'] = 'Back to userlist';
  $navbar['options_right']['edit']['icon'] = 'icon-chevron-left';
}

print_navbar($navbar);
unset($navbar);

// EOF
