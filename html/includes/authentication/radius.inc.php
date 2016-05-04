<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage authentication
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

/**
 * Initializes the RADIUS connection to the specified server(s). Cycles through all servers, throws error when no server can be reached.
 * Private function for this RADIUS module only.
 */
function radius_init()
{
  global $rad, $config;

  if (!is_resource($rad))
  {
    $success = 0;
    $rad = radius_auth_open();

    foreach ($config['auth_radius_server'] as $server)
    {
      if (radius_add_server($rad, $server, $config['auth_radius_port'], $config['auth_radius_secret'], $config['auth_radius_timeout'], $config['auth_radius_retries']))
      {
        $success = 1;
      }
    }

    if (!$success)
    {
      print_error("Fatal error: Could not connect to configured RADIUS server(s).");
      session_logout();
      exit;
    }
  }
}

/**
 * Check username and password against RADIUS authentication backend.
 *
 * @param string $username User name to check
 * @param string $password User password to check
 * @return int Authentication success (0 = fail, 1 = success) FIXME bool
 */
function radius_authenticate($username, $password)
{
  global $config, $rad;

  radius_init();
  if ($username && $rad)
  {
    radius_create_request($rad, RADIUS_ACCESS_REQUEST);
    radius_put_string($rad, 1, $username);
    radius_put_string($rad, 2, $password);
    radius_put_string($rad, 4, $_SERVER['SERVER_ADDR']);

    $response = radius_send_request($rad);
    if ($response == RADIUS_ACCESS_ACCEPT)
    {
      return 1;
    }
  }

  session_logout();
  return 0;
}

/**
 * Check if the backend allows a specific user to change their password.
 * This is not currently possible using the RADIUS backend.
 *
 * @param string $username Username to check
 * @return bool TRUE if password change is possible, FALSE if it is not
 */
function radius_auth_can_change_password($username = "")
{
  return 0;
}

/**
 * Changes a user's password.
 * This is not currently possible using the RADIUS backend.
 *
 * @param string $username Username to modify the password for
 * @param string $password New password
 * @return bool TRUE if password change is successful, FALSE if it is not
 */
function radius_auth_change_password($username,$newpassword)
{
  # Not supported
  return FALSE;
}

/**
 * Check if the backend allows user management at all (create/delete/modify users).
 * This is not currently possible using the RADIUS backend.
 *
 * @return bool TRUE if user management is possible, FALSE if it is not
 */
function radius_auth_usermanagement()
{
  return 0;
}

/**
 * Adds a new user to the user backend.
 * This is not currently possible using the RADIUS backend.
 *
 * @param string $username User's username
 * @param string $password User's password (plain text)
 * @param int $level User's auth level
 * @param string $email User's e-mail address
 * @param string $realname User's real name
 * @param bool $can_modify_passwd TRUE if user can modify their own password, FALSE if not
 * @param string $description User's description
 * @return bool TRUE if user addition is successful, FALSE if it is not
 */
function radius_adduser($username, $password, $level, $email = "", $realname = "", $can_modify_passwd = '1')
{
  // Not supported
  return FALSE;
}

/**
 * Check if a user, specified by username, exists in the user backend.
 * This is not currently possible using the RADIUS backend.
 *
 * @param string $username Username to check
 * @return bool TRUE if the user exists, FALSE if they do not
 */
function radius_auth_user_exists($username)
{
  return FALSE;
}

/**
 * Retrieve user auth level for specified user.
 * Always returns 10, currently.
 *
 * @param string $username Username to retrieve the auth level for
 * @return int User's auth level
 */
function radius_auth_user_level($username)
{
  return (isset($username) ? 10 : 0);
}

/**
 * Retrieve user id for specified user.
 * Always returns -1, currently.
 *
 * @param string $username Username to retrieve the ID for
 * @return int User's ID
 */
function radius_auth_user_id($username)
{
  return -1;
}

/**
 * Deletes a user from the user database.
 * This is not currently possible using the RADIUS backend.
 *
 * @param string $username Username to delete
 * @return bool TRUE if user deletion is successful, FALSE if it is not
 */
function radius_deluser($username)
{
  // Not supported
  return FALSE;
}

/**
 * Retrieve list of users with all details.
 * This is not currently possible using the RADIUS backend.
 *
 * @return array Rows of user data
 */
function radius_auth_user_list()
{
  $userlist = array();
  return $userlist;
}

// EOF
