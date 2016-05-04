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

CAS authentication support.
Uses mysql (same schema as mysql module) for authorization but CAS for authentication.
Requires phpCAS https://wiki.jasig.org/display/casc/phpcas
New configuration settings:

auth_cas_host
auth_cas_port
auth_cas_context
auth_cas_ca_cert

FIXME these should go into defaults and sql-config!
*/

require_once('CAS.php');

phpCAS::client(CAS_VERSION_2_0, $config['auth_cas_host'], $config['auth_cas_port'], $config['auth_cas_context']);
phpCAS::setCasServerCACert($config['auth_cas_ca_cert']);
phpCAS::handleLogoutRequests(false);
phpCAS::forceAuthentication();

if (phpCAS::getUser())
{
  $_SESSION['username'] = phpCAS::getUser();
}

/**
 * Check username against CAS authentication backend. User needs to exist in MySQL to be able to log in.
 *
 * @param string $username User name to check
 * @param string $password User password to check
 * @return int Authentication success (0 = fail, 1 = success) FIXME bool
 */
function cas_authenticate($username, $password)
{
  $row = dbFetchRow("SELECT `username`, `password` FROM `users` WHERE `username`= ?", array($username));
  if ($row['username'] && $row['username'] == $username)
  {
    if ($username == phpCAS::getUser())
    {
      return 1;
    }

    dbInsert(array('user' => $_SESSION['username'], 'address' => $_SERVER["REMOTE_ADDR"], 'result' => 'CAS: username does not match CAS user'), 'authlog');
  } else {
    dbInsert(array('user' => $_SESSION['username'], 'address' => $_SERVER["REMOTE_ADDR"], 'result' => 'CAS: NOT found in DB'), 'authlog');
  }
  session_logout();
  return 0;
}

/**
 * Check if the backend allows users to log out.
 * As the login is done outside our system, we don't allow users to log out.
 *
 * @return bool TRUE if logout is possible, FALSE if it is not
 */
function cas_auth_can_logout()
{
  return FALSE;
}

/**
 * Check if the backend allows a specific user to change their password.
 * This is not currently possible using the CAS backend.
 *
 * @param string $username Username to check
 * @return bool TRUE if password change is possible, FALSE if it is not
 */
function cas_auth_can_change_password($username = "")
{
  return FALSE;
}

/**
 * Check if the backend allows user management at all (create/delete/modify users).
 * The CAS module requires users to exist in MySQL first, so we allow MySQL user management.
 *
 * @return bool TRUE if user management is possible, FALSE if it is not
 */
function cas_auth_usermanagement()
{
  return 1;
}

/**
 * Adds a new user to the user backend.
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
function cas_adduser($username, $password, $level, $email = "", $realname = "", $can_modify_passwd='1', $description = "")
{
  if (!cas_auth_user_exists($username))
  {
    $encrypted = crypt($password,'$1$' . strgen(8).'$');
    return dbInsert(array('username' => $username, 'password' => $encrypted, 'level' => $level, 'email' => $email, 'realname' => $realname, 'can_modify_passwd' => $can_modify_passwd, 'descr' => $description), 'users');
  } else {
    return FALSE;
  }
}

// EOF
