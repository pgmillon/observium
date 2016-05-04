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
 * Check username and password against MySQL authentication backend.
 * Cut short if remote_user setting is on, as we assume the user has already authed against Apache.
 *
 * @param string $username User name to check
 * @param string $password User password to check
 * @return int Authentication success (0 = fail, 1 = success) FIXME bool
 */
function mysql_authenticate($username, $password)
{
  $encrypted_old = md5($password);
  $row = dbFetchRow("SELECT `username`, `password` FROM `users` WHERE `username`= ?", array($username));
  if ($row['username'] && $row['username'] == $username)
  {
    // Migrate from old, unhashed password
    // CLEANME remove this at r8000 but not before CE late 2015
    if ($row['password'] == $encrypted_old)
    {
      $row = dbFetchRow("DESCRIBE `users` `password`");
      if ($row['Type'] == 'varchar(34)')
      {
        mysql_auth_change_password($username, $password);
      }
      return 1;
    }
    if ($config['auth']['remote_user'] || $row['password'] == crypt($password, $row['password']))
    {
      return 1;
    }
  }

  session_logout();
  return 0;
}

/**
 * Check if the backend allows users to log out.
 * We don't check for Apache authentication (remote_user) as this is done already before calling into this function.
 *
 * @return bool TRUE if logout is possible, FALSE if it is not
 */
function mysql_auth_can_logout()
{
  return TRUE;
}

/**
 * Check if the backend allows a specific user to change their password.
 * Default is yes, unless the existing user is explicitly prohibited to do so.
 * Also, if user authed to Apache, we can't change his password.
 *
 * @param string $username Username to check
 * @return bool TRUE if password change is possible, FALSE if it is not
 */
function mysql_auth_can_change_password($username = "")
{
  global $config;

  if ((empty($username) || !mysql_auth_user_exists($username)) && !$config['auth']['remote_user'])
  {
    return TRUE;
  } else {
    return dbFetchCell("SELECT `can_modify_passwd` FROM `users` WHERE `username` = ?", array($username)); // FIXME should return BOOL
  }
}

/**
 * Changes a user's password.
 *
 * @param string $username Username to modify the password for
 * @param string $password New password
 * @return bool TRUE if password change is successful, FALSE if it is not
 */
function mysql_auth_change_password($username,$password)
{
  $encrypted = crypt($password,'$1$' . strgen(8).'$');
  return dbUpdate(array('password' => $encrypted), 'users', '`username` = ?', array($username)); // FIXME should return BOOL
}

/**
 * Check if the backend allows user management at all (create/delete/modify users).
 *
 * @return bool TRUE if user management is possible, FALSE if it is not
 */
function mysql_auth_usermanagement()
{
  return TRUE;
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
function mysql_adduser($username, $password, $level, $email = "", $realname = "", $can_modify_passwd='1', $description = "")
{
  if (!mysql_auth_user_exists($username))
  {
    $encrypted = crypt($password,'$1$' . strgen(8).'$');
    return dbInsert(array('username' => $username, 'password' => $encrypted, 'level' => $level, 'email' => $email, 'realname' => $realname, 'can_modify_passwd' => $can_modify_passwd, 'descr' => $description), 'users');
  } else {
    return FALSE;
  }
}

/**
 * Check if a user, specified by username, exists in the user backend.
 *
 * @param string $username Username to check
 * @return bool TRUE if the user exists, FALSE if they do not
 */
function mysql_auth_user_exists($username)
{
  return @dbFetchCell("SELECT COUNT(*) FROM `users` WHERE `username` = ?", array($username)); // FIXME should return BOOL
}

/**
 * Find the user's username by specifying their user ID.
 *
 * @param int $user_id The user's ID to look up the username for
 * @return string The user's user name, or FALSE if the user ID is not found
 */
function mysql_auth_username_by_id($user_id)
{
  return dbFetchCell("SELECT `username` FROM `users` WHERE `user_id` = ?", array($user_id)); // FIXME should return FALSE if not found
}
  
/**
 * Retrieve user auth level for specified user.
 *
 * @param string $username Username to retrieve the auth level for
 * @return int User's auth level
 */
function mysql_auth_user_level($username)
{
  return dbFetchCell("SELECT `level` FROM `users` WHERE `username` = ?", array($username));
}

/**
 * Retrieve user id for specified user.
 *
 * @param string $username Username to retrieve the ID for
 * @return int User's ID
 */
function mysql_auth_user_id($username)
{
  return dbFetchCell("SELECT `user_id` FROM `users` WHERE `username` = ?", array($username));
}

/**
 * Deletes a user from the user database.
 *
 * @param string $username Username to delete
 * @return bool TRUE if user deletion is successful, FALSE if it is not
 */
function mysql_deluser($username)
{
  $user_id = mysql_auth_user_id($username);

  dbDelete('entity_permissions', "`user_id` =  ?", array($user_id));
  dbDelete('users_prefs',        "`user_id` =  ?", array($user_id));
  dbDelete('users_ckeys',       "`username` =  ?", array($username));

  return dbDelete('users', "`username` =  ?", array($username)); // FIXME should return BOOL
}

/**
 * Retrieve list of users with all details.
 *
 * @return array Rows of user data
 */
function mysql_auth_user_list()
{
  return dbFetchRows("SELECT * FROM `users`"); // FIXME hardcode list of returned fields as in all other backends; array content should not depend on db changes/column names.
}

// EOF
