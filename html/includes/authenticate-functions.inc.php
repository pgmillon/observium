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
function authenticate($username, $password)
{
  global $config;

  if (function_exists($config['auth_mechanism'] . '_authenticate'))
  {
    return call_user_func($config['auth_mechanism'] . '_authenticate', $username, $password);
  } else {
    return call_user_func('mysql_authenticate', $username, $password);
  }
}

// DOCME needs phpdoc block
function auth_can_logout()
{
  global $config;

  if (function_exists($config['auth_mechanism'] . '_auth_can_logout'))
  {
    return call_user_func($config['auth_mechanism'] . '_auth_can_logout');
  } else {
    return call_user_func('mysql_auth_can_logout');
  }
}

// DOCME needs phpdoc block
function auth_can_change_password($username = "")
{
  global $config;

  if (function_exists($config['auth_mechanism'] . '_auth_can_change_password'))
  {
    return call_user_func($config['auth_mechanism'] . '_auth_can_change_password', $username);
  } else {
    return call_user_func('mysql_auth_can_change_password', $username);
  }
}

// DOCME needs phpdoc block
function auth_change_password($username, $password)
{
  global $config;

  if (function_exists($config['auth_mechanism'] . '_auth_change_password'))
  {
    return call_user_func($config['auth_mechanism'] . '_auth_change_password', $username, $password);
  } else {
    return call_user_func('mysql_auth_change_password', $username, $password);
  }
}

// DOCME needs phpdoc block
function auth_usermanagement()
{
  global $config;

  if (function_exists($config['auth_mechanism'] . '_auth_usermanagement'))
  {
    return call_user_func($config['auth_mechanism'] . '_auth_usermanagement');
  } else {
    return call_user_func('mysql_auth_usermanagement');
  }
}

// DOCME needs phpdoc block
function adduser($username, $password, $level, $email = "", $realname = "", $can_modify_passwd = '1', $description = "")
{
  global $config;

  if (function_exists($config['auth_mechanism'] . '_adduser'))
  {
    return call_user_func($config['auth_mechanism'] . '_adduser', $username, $password, $level, $email, $realname, $can_modify_passwd, $description);
  } else {
    return call_user_func('mysql_adduser', $username, $password, $level, $email, $realname, $can_modify_passwd, $description);
  }
}

// DOCME needs phpdoc block
function auth_user_exists($username)
{
  global $config;

  if (function_exists($config['auth_mechanism'] . '_auth_user_exists'))
  {
    return call_user_func($config['auth_mechanism'] . '_auth_user_exists', $username);
  } else {
    return call_user_func('mysql_auth_user_exists', $username);
  }
}

// DOCME needs phpdoc block
function auth_user_level($username)
{
  global $config;

  if (function_exists($config['auth_mechanism'] . '_auth_user_level'))
  {
    return call_user_func($config['auth_mechanism'] . '_auth_user_level', $username);
  } else {
    return call_user_func('mysql_auth_user_level', $username);
  }
}

// DOCME needs phpdoc block
function auth_user_id($username)
{
  global $config;

  if (function_exists($config['auth_mechanism'] . '_auth_user_id'))
  {
    return call_user_func($config['auth_mechanism'] . '_auth_user_id', $username);
  } else {
    return call_user_func('mysql_auth_user_id', $username);
  }
}

// DOCME needs phpdoc block
function deluser($username)
{
  global $config;

  if (function_exists($config['auth_mechanism'] . '_deluser'))
  {
    return call_user_func($config['auth_mechanism'] . '_deluser', $username);
  } else {
    return call_user_func('mysql_deluser', $username);
  }
}

// DOCME needs phpdoc block
function auth_user_list()
{
  global $config;

  if (function_exists($config['auth_mechanism'] . '_auth_user_list'))
  {
    return call_user_func($config['auth_mechanism'] . '_auth_user_list');
  } else {
    return call_user_func('mysql_auth_user_list');
  }
}

// EOF
