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

$page_title[] = "User preferences";

$navbar['class'] = 'navbar-narrow';
$navbar['brand'] = 'User Preferences';

$pages = array('general' => 'General',
               'authentication' => 'Authentication',
               'authlog' => 'Auth Log');

if(!auth_can_change_password($_SESSION['username'])) { unset ($pages['authentication']); }

if(!$vars['section']) { $vars['section'] = "general"; }

foreach ($pages as $page_name => $page_desc)
{
    if ($vars['section'] == $page_name)
    {
        $navbar['options'][$page_name]['class'] = "active";
    }

    $navbar['options'][$page_name]['url'] = generate_url(array('page' => 'preferences', 'section' => $page_name));
    $navbar['options'][$page_name]['text'] = escape_html($page_desc);
}

/*
$navbar['options_right']['add']['url']       = '#add_contact_modal';
$navbar['options_right']['add']['link_opts'] = 'data-toggle="modal"';
$navbar['options_right']['add']['text']      = 'Add Contact';
$navbar['options_right']['add']['icon']      = 'oicon-mail--plus';
*/

// Print out the navbar defined above
print_navbar($navbar);
unset($navbar);

// Change password
if ($vars['password'] == "save")
{
  if (authenticate($_SESSION['username'], $vars['old_pass']))
  {
    if ($vars['new_pass'] == "" || $vars['new_pass2'] == "")
    {
      print_warning("Password must not be blank.");
    }
    else if ($vars['new_pass'] == $vars['new_pass2'])
    {
      auth_change_password($_SESSION['username'], $vars['new_pass']);
      print_success("Password Changed.");
    } else {
      print_warning("Passwords don't match.");
    }
  } else {
    print_warning("Incorrect password");
  }
}

unset($prefs);
if (is_numeric($_SESSION['user_id']))
{
  $user_id = $_SESSION['user_id'];
  $prefs = get_user_prefs($user_id);

  // Reset RSS/Atom key
  if ($vars['atom_key'] == "toggle")
  {
    if (set_user_pref($user_id, 'atom_key', md5(strgen())))
    {
      print_success('RSS/Atom key updated.');
      $prefs = get_user_prefs($user_id);
    } else {
      print_error('Error generating RSS/Atom key.');
    }
  }

  // Reset API key
  if ($vars['api_key'] == "toggle")
  {
    if (set_user_pref($user_id, 'api_key', md5(strgen())))
    {
      print_success('API key updated.');
      $prefs = get_user_prefs($user_id);
    } else {
      print_error('Error generating API key.');
    }
  }
}
$atom_key_updated = (isset($prefs['atom_key']['updated']) ? formatUptime(time() - strtotime($prefs['atom_key']['updated']), 'shorter').' ago' : 'Never');
$api_key_updated  = (isset($prefs['api_key']['updated'])  ? formatUptime(time() - strtotime($prefs['api_key']['updated']),  'shorter').' ago' : 'Never');

  $filename = $config['html_dir'] . '/pages/preferences/' . $vars['section'] . '.inc.php';
  if (is_file($filename))
  {
    $vars = get_vars('POST'); // Note, on edit pages use only method POST!

    include($filename);
  } else {
    print_error('<h4>Page does not exist</h4>
The requested page does not exist. Please correct the URL and try again.');
  }

// EOF
