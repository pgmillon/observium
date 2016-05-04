<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage webui
 * @copyright  (C) 2006-2015 Adam Armstrong
 *
 */

echo('<div style="margin: 10px;">');

if ($_SESSION['userlevel'] < '10')
{
  include("includes/error-no-perm.inc.php");
} else {
  if (auth_usermanagement())
  {
    if ($vars['action'] == "deleteuser")
    {
      $delete_username = dbFetchCell("SELECT `username` FROM `users` WHERE `user_id` = ?", array($vars['user_id']));

      if ($vars['confirm'] == "yes")
      {
        if (deluser($delete_username))
        {
          print_success('User "' . escape_html($delete_username) . '" deleted!');
        } else {
          print_error('Error deleting user "' . escape_html($delete_username) . '"!');
        }
      } else {
        print_error('You have requested deletion of the user "' . escape_html($delete_username) . '". This action can not be reversed.<br /><a href="edituser/action=deleteuser/user_id=' . $vars['user_id'] . '/confirm=yes/">Click to confirm</a>');
      }
    }
  } else {
    print_error("Authentication module does not allow user management!");
  }
}

echo('</div>');

// EOF
