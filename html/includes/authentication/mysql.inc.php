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
function mysql_authenticate($username, $password)
{
  $encrypted_old = md5($password);
  $row = dbFetchRow("SELECT `username`, `password` FROM `users` WHERE `username`= ?", array($username));
  if ($row['username'] && $row['username'] == $username)
  {
    // Migrate from old, unhashed password
    if ($row['password'] == $encrypted_old)
    {
      $row = dbFetchRow("DESCRIBE `users` `password`");
      if ($row['Type'] == 'varchar(34)')
      {
        mysql_auth_change_password($username, $password);
      }
      return 1;
    }
    if ($row['password'] == crypt($password, $row['password']))
    {
      return 1;
    }
  }

  session_logout();
  return 0;
}

// DOCME needs phpdoc block
function mysql_auth_can_logout()
{
  return TRUE;
}

// DOCME needs phpdoc block
function mysql_auth_can_change_password($username = "")
{
  /*
   * By default allow the password to be modified, unless the existing
   * user is explicitly prohibited to do so.
   */

  if (empty($username) || !mysql_auth_user_exists($username))
  {
    return 1;
  } else {
    return dbFetchCell("SELECT `can_modify_passwd` FROM `users` WHERE `username` = ?", array($username));
  }
}

// DOCME needs phpdoc block
function mysql_auth_change_password($username,$password)
{
  $encrypted = crypt($password,'$1$' . strgen(8).'$');
  return dbUpdate(array('password' => $encrypted), 'users', '`username` = ?', array($username));
}

// DOCME needs phpdoc block
function mysql_auth_usermanagement()
{
  return 1;
}

// DOCME needs phpdoc block
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

// DOCME needs phpdoc block
function mysql_auth_user_exists($username)
{
  return @dbFetchCell("SELECT COUNT(*) FROM `users` WHERE `username` = ?", array($username));
}

// DOCME needs phpdoc block
function mysql_auth_user_level($username)
{
  return dbFetchCell("SELECT `level` FROM `users` WHERE `username` = ?", array($username));
}

// DOCME needs phpdoc block
function mysql_auth_user_id($username)
{
  return dbFetchCell("SELECT `user_id` FROM `users` WHERE `username` = ?", array($username));
}

// DOCME needs phpdoc block
function mysql_deluser($username)
{
  $user_id = mysql_auth_user_id($username);

  dbDelete('entity_permissions', "`user_id` =  ?", array($user_id));
  dbDelete('users_prefs',        "`user_id` =  ?", array($user_id));
  dbDelete('users_ckeys',       "`username` =  ?", array($username));

  return dbDelete('users', "`username` =  ?", array($username));
}

// DOCME needs phpdoc block
function mysql_auth_user_list()
{
  return dbFetchRows("SELECT * FROM `users`");
}

// EOF
