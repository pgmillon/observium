<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage authentication
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

// DOCME needs phpdoc block
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

// DOCME needs phpdoc block
function radius_authenticate($username,$password)
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

// DOCME needs phpdoc block
function radius_auth_can_change_password($username = "")
{
  return 0;
}

// DOCME needs phpdoc block
function radius_auth_change_password($username,$newpassword)
{
  # Not supported
}

// DOCME needs phpdoc block
function radius_auth_can_logout()
{
  return TRUE;
}

// DOCME needs phpdoc block
function radius_auth_usermanagement()
{
  return 0;
}

// DOCME needs phpdoc block
function radius_adduser($username, $password, $level, $email = "", $realname = "", $can_modify_passwd = '1')
{
  # Not supported
  return 0;
}

// DOCME needs phpdoc block
function radius_auth_user_exists($username)
{
  return 0;
}

// DOCME needs phpdoc block
function radius_auth_user_level($username)
{
  return (isset($username) ? 10 : 0);
}

// DOCME needs phpdoc block
function radius_auth_user_id($username)
{
  return -1;
}

// DOCME needs phpdoc block
function radius_deluser($username)
{
  # Not supported
  return 0;
}

// DOCME needs phpdoc block
function radius_auth_user_list()
{
  $userlist = array();
  return $userlist;
}

// EOF
