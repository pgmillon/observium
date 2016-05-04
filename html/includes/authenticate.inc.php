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

/// FIXME. Need rewrite: do not save unencrypted passwords (in $_SESSION)

@ini_set('session.hash_function', '1');    // Use sha1 to generate the session ID
@ini_set('session.referer_check', '');     // This config was causing so much trouble with Chrome
@ini_set('session.name', 'OBSID');         // Session name
@ini_set('session.use_cookies', '1');      // Use cookies to store the session id on the client side
@ini_set('session.use_only_cookies', '1'); // This prevents attacks involved passing session ids in URLs
@ini_set('session.use_trans_sid', '0');    // Disable SID (no session id in url)

$currenttime     = time();
$lifetime        = 0;                          // Session lifetime (default until browser restart)
$lifetime_id     = 300;                        // Session ID lifetime (time before regenerate id, 300 sec)
$cookie_expire   = $currenttime + 60*60*24*14; // Cookies expire time (14 days)
$cookie_path     = '/';                        // Cookie path
$cookie_domain   = '';                         // RFC 6265, to have a "host-only" cookie is to NOT set the domain attribute.
/// FIXME. Some old browsers not supports secure/httponly cookies params.
$cookie_https    = is_ssl();
$cookie_httponly = TRUE;

// Use custom session lifetime
if (is_numeric($GLOBALS['config']['web_session_lifetime']) && $GLOBALS['config']['web_session_lifetime'] >= 0)
{
  $lifetime = intval($GLOBALS['config']['web_session_lifetime']);
}

@ini_set('session.gc_maxlifetime',  $lifetime); // Session lifetime
session_set_cookie_params($lifetime, $cookie_path, $cookie_domain, $cookie_https, $cookie_httponly);

register_shutdown_function('session_write_close'); //session_write_close();
if (!session_is_active())
{
  session_write_close(); // Prevent session auto start
  session_start();

  if (isset($_SESSION['starttime']))
  {
    if ($currenttime - $_SESSION['starttime'] >= $lifetime_id && !is_graph())
    {
      // ID Lifetime expired, regenerate
      session_regenerate_id(TRUE);
      // Clean cache from _SESSION first, this cache used in ajax calls
      if (isset($_SESSION['cache'])) { unset($_SESSION['cache']); }
      $_SESSION['starttime'] = $currenttime;
    }
  } else {
    $_SESSION['starttime']   = $currenttime;
  }

  //if (!is_graph())
  //{
  //  print_vars($vars); print_vars($_SESSION); print_vars($_COOKIE);
  //}
}

// Fallback to MySQL auth as default
if (!isset($config['auth_mechanism']))
{
  $config['auth_mechanism'] = "mysql";
}

// Trust Apache authenticated user, if configured to do so and username is available
if ($config['auth']['remote_user'] && $_SERVER['REMOTE_USER'] != '')
{
  $_SESSION['username'] = $_SERVER['REMOTE_USER'];
}

$auth_file = $config['html_dir'].'/includes/authentication/' . $config['auth_mechanism'] . '.inc.php';
if (is_file($auth_file))
{
  if (isset($_SESSION['auth_mechanism']) && $_SESSION['auth_mechanism'] != $config['auth_mechanism'])
  {
    // Logout if AUTH mechanism changed
    session_logout();
    header('Location: '.$config['base_url']);
    $auth_message = 'ERROR: auth_mechanism changed!';
    exit();
  } else {
    $_SESSION['auth_mechanism'] = $config['auth_mechanism'];
  }

  // Always load mysql as backup
  include($config['html_dir'].'/includes/authentication/mysql.inc.php');

  // Load primary module if not mysql
  if ($config['auth_mechanism'] != 'mysql') { include($auth_file); }

  // Include base auth functions calls
  include($config['html_dir'].'/includes/authenticate-functions.inc.php');
} else {
  session_logout();
  header('Location: '.$config['base_url']);
  $auth_message = 'ERROR: no valid auth_mechanism defined!';
  exit();
}

if ($vars['page'] == "logout" && $_SESSION['authenticated'])
{
  if (auth_can_logout())
  {
    session_logout(function_exists('auth_require_login'));
    $auth_message = "Logged Out";
  }
  header('Location: '.$config['base_url']);
  exit();
}

$mcrypt_exists = check_extension_exists('mcrypt');
$user_unique_id = session_unique_id(); // Get unique user id and check if IP changed (if required by config)

// Check if allowed auth by CIDR
$auth_allow_cidr = TRUE;
if (isset($config['web_session_cidr']) && count($config['web_session_cidr']))
{
  $auth_allow_cidr = match_network($_SERVER['REMOTE_ADDR'], $config['web_session_cidr']);
}

if (!$_SESSION['authenticated'] && isset($_GET['username']) && isset($_GET['password']))
{
  $_SESSION['username'] = $_GET['username'];
  $auth_password        = $_GET['password'];
}
else if (!$_SESSION['authenticated'] && isset($_POST['username']) && isset($_POST['password']))
{
  $_SESSION['username'] = $_POST['username'];
  $auth_password        = $_POST['password'];
}
else if ($mcrypt_exists && !$_SESSION['authenticated'] && isset($_COOKIE['ckey']))
{
  $ckey = dbFetchRow("SELECT * FROM `users_ckeys` WHERE `user_uniq` = ? AND `user_ckey` = ? LIMIT 1",
                          array($user_unique_id, $_COOKIE['ckey']));
  if (is_array($ckey))
  {
    if ($ckey['expire'] > $currenttime && $auth_allow_cidr)
    {
      $_SESSION['username']     = $ckey['username'];
      $auth_password            = decrypt($ckey['user_encpass'], $_COOKIE['dkey']);

      // Store encrypted password
      session_encrypt_password($auth_password, $user_unique_id);

      // If userlevel == 0 - user disabled an can not be logon
      if (auth_user_level($ckey['username']) < 1)
      {
        session_logout(FALSE, 'User disabled');
        header('Location: '.$config['base_url']);
        $auth_message = 'User login disabled';
        exit();
      }

      $_SESSION['user_ckey_id'] = $ckey['user_ckey_id'];
      $_SESSION['cookie_auth']  = TRUE;
      dbInsert(array('user'       => $_SESSION['username'],
                     'address'    => $_SERVER['REMOTE_ADDR'],
                     'user_agent' => $_SERVER['HTTP_USER_AGENT'],
                     'result'     => 'Logged In (cookie)'), 'authlog');
    }
  }
}

if ($_COOKIE['password']) { setcookie("password", NULL); }
if ($_COOKIE['username']) { setcookie("username", NULL); }
if ($_COOKIE['user_id'] ) { setcookie("user_id",  NULL); }

$auth_success = FALSE; // Variable for check if just logged

if (isset($_SESSION['username']))
{
  // User authenticated, but not allowed by CIDR range
  if (!$auth_allow_cidr)
  {
    session_logout(FALSE, 'Remote IP not allowed in CIDR ranges');
    header('Location: '.$config['base_url']);
    $auth_message = 'Remote IP not allowed in CIDR ranges';
    exit();
  }

  // Auth from COOKIEs
  if ($_SESSION['cookie_auth'])
  {
    $_SESSION['authenticated'] = TRUE;
    $auth_success              = TRUE;
    dbUpdate(array('expire' => $cookie_expire), 'users_ckeys', '`user_ckey_id` = ?', array($_SESSION['user_ckey_id']));
    unset($_SESSION['user_ckey_id'], $_SESSION['cookie_auth']);
  }

  // Auth from ...
  if (!$_SESSION['authenticated'] && (authenticate($_SESSION['username'], $auth_password) ||                       // login/password
                                     (auth_usermanagement() && auth_user_level($_SESSION['origusername']) >= 10))) // FIXME?
  {
    // Store encrypted password
    session_encrypt_password($auth_password, $user_unique_id);

    // If userlevel == 0 - user disabled an can not be logon
    if (auth_user_level($_SESSION['username']) < 1)
    {
      session_logout(FALSE, 'User disabled');
      header('Location: '.$config['base_url']);
      $auth_message = 'User login disabled';
      exit();
    }
    
    $_SESSION['authenticated'] = TRUE;
    $auth_success              = TRUE;
    dbInsert(array('user'       => $_SESSION['username'],
                   'address'    => $_SERVER['REMOTE_ADDR'],
                   'user_agent' => $_SERVER['HTTP_USER_AGENT'],
                   'result'     => 'Logged In'), 'authlog');

    // Generate keys for cookie auth
    if (isset($_POST['remember']) && $mcrypt_exists)
    {
      $ckey = md5(strgen());
      $dkey = md5(strgen());
      $encpass = encrypt($auth_password, $dkey);
      dbDelete('users_ckeys', "`username` = ? AND `expire` < ?", array($_SESSION['username'], $currenttime - 3600)); // Remove old ckeys from DB
      dbInsert(array('user_encpass' => $encpass,
                     'expire'       => $cookie_expire,
                     'username'     => $_SESSION['username'],
                     'user_uniq'    => $user_unique_id,
                     'user_ckey'    => $ckey), 'users_ckeys');
      setcookie("ckey", $ckey, $cookie_expire, $cookie_path, $cookie_domain, $cookie_https, $cookie_httponly);
      setcookie("dkey", $dkey, $cookie_expire, $cookie_path, $cookie_domain, $cookie_https, $cookie_httponly);
      unset($_SESSION['user_ckey_id']);
    }
  }

  // Retrieve user ID and permissions
  if ($_SESSION['authenticated'])
  {
    if (!is_numeric($_SESSION['userlevel']) || !is_numeric($_SESSION['user_id']))
    {
      $_SESSION['userlevel'] = auth_user_level($_SESSION['username']);
      $_SESSION['user_id']   = auth_user_id($_SESSION['username']);
    }

    // If userlevel == 0 - user disabled an can not be logon
    if ($_SESSION['userlevel'] < 1)
    {
      session_logout(FALSE, 'User disabled');
      header('Location: '.$config['base_url']);
      $auth_message = 'User login disabled';
      exit();
    }

    // Now we can enable debug if required
    if (defined('OBS_DEBUG_WUI')) // OBS_DEBUG_WUI defined in definitions
    {
      if ($_SESSION['userlevel'] < 7)
      {
        // DO NOT ALLOW show debug output for users with privilege level less than "global secure read"
        define('OBS_DEBUG', 0);
        ini_set('display_errors', 0);
        ini_set('display_startup_errors', 0);
        ini_set('log_errors', 1);
        //ini_set('error_reporting', 0); // Default
      } else {
        define('OBS_DEBUG', 1);
      }
    }

    $permissions = permissions_cache($_SESSION['user_id']);

    // Add feeds & api keys after first auth
    if ($mcrypt_exists && !get_user_pref($_SESSION['user_id'], 'atom_key'))
    {
      // Generate unique token
      do
      {
        $atom_key = md5(strgen());
      }
      while (dbFetchCell("SELECT COUNT(*) FROM `users_prefs` WHERE `pref` = ? AND `value` = ?;", array('atom_key', $atom_key)) > 0);
      set_user_pref($_SESSION['user_id'], 'atom_key', $atom_key);
    }
  }
  else if (isset($_SESSION['username']))
  {
    $auth_message = "Authentication Failed";
    session_logout(function_exists('auth_require_login'));
  }

  if ($auth_success)
  {
    // If just logged in go to request uri
    if (!OBS_DEBUG)
    {
      header("Location: ".$_SERVER['REQUEST_URI']);
    } else {
      print_message("Debugging mode disabled redirect to front page; please click <a href=\"" . $_SERVER['REQUEST_URI'] . "\">here</a> to continue.");
    }
    exit();
  }
}

///r($_SESSION);
///r($_COOKIE);

// DOCME needs phpdoc block
function session_is_active()
{
  if (!is_cli())
  {
    if (version_compare(PHP_VERSION, '5.4.0', '>='))
    {
      return session_status() === PHP_SESSION_ACTIVE ? TRUE : FALSE;
    } else {
      return session_id() === '' ? FALSE : TRUE;
    }
  }
  return FALSE;
}

/**
 * Generate unique id for current user/browser, based on some unique params
 *
 * @return string
 */
function session_unique_id()
{
  $id  = $_SERVER['HTTP_USER_AGENT']; // User agent
  $id .= $_SERVER['HTTP_ACCEPT'];     // Browser accept headers
  
  if ($GLOBALS['config']['web_session_ip'])
  {
    $id .= $_SERVER['REMOTE_ADDR'];   // User IP address

    // Force reauth if remote IP changed
    if ($_SESSION['authenticated'])
    {
      if (isset($_SESSION['PREV_REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] != $_SESSION['PREV_REMOTE_ADDR'])
      {
        unset($_SESSION['authenticated'],
              $_SESSION['user_id'],
              $_SESSION['username'],
              $_SESSION['user_encpass'], $_SESSION['password'],
              $_SESSION['userlevel']);
      }
      $_SESSION['PREV_REMOTE_ADDR'] = $_SERVER['REMOTE_ADDR']; // Store current remote IP
    }
  }

  // Next required JS cals:
  // resolution = screen.width+"x"+screen.height+"x"+screen.colorDepth;
  // timezone   = new Date().getTimezoneOffset();

  return md5($id);
}

/**
 * Store encrypted password in $_SESSION['user_encpass'], required for some auth mechanism, ie ldap
 *
 * @param  string $auth_password Plain password
 * @param  string $key           Key for password encrypt
 * @return string                Encrypted password
 */
function session_encrypt_password($auth_password, $key)
{
  // Store encrypted password
  if ($GLOBALS['config']['auth_mechanism'] == 'ldap' &&
      !($GLOBALS['config']['auth_ldap_bindanonymous'] || strlen($GLOBALS['config']['auth_ldap_binddn'].$GLOBALS['config']['auth_ldap_bindpw'])))
  {
    if (check_extension_exists('mcrypt'))
    {
      // For some admin LDAP functions required store encrypted password in session (userslist)
      $_SESSION['user_encpass'] = encrypt($auth_password, $key . get_unique_id());
    } else {
      $_SESSION['user_encpass'] = base64_encode($auth_password);
      $_SESSION['mcrypt_required'] = 1;
    }
  }

  return $_SESSION['user_encpass'];
}

// DOCME needs phpdoc block
function session_logout($relogin = FALSE, $message = NULL)
{
  if ($_SESSION['authenticated'])
  {
    $auth_log = 'Logged Out';
  } else {
    $auth_log = 'Authentication Failure';
  }
  if ($message)
  {
    $auth_log .= ' (' . $message . ')';
    $debug_log = $GLOBALS['config']['log_dir'].'/'.date("Y-m-d_H:i:s").'.log';
    //file_put_contents($debug_log, var_export($_SERVER,  TRUE), FILE_APPEND);
    //file_put_contents($debug_log, var_export($_SESSION, TRUE), FILE_APPEND);
    //file_put_contents($debug_log, var_export($_COOKIE,  TRUE), FILE_APPEND);
  }
  dbInsert(array('user'       => $_SESSION['username'],
                 'address'    => $_SERVER['REMOTE_ADDR'],
                 'user_agent' => $_SERVER['HTTP_USER_AGENT'],
                 'result'     => $auth_log), 'authlog');
  if (isset($_COOKIE['ckey'])) dbDelete('users_ckeys', "`username` = ? AND `user_ckey` = ?", array($_SESSION['username'], $_COOKIE['ckey'])); // Remove old ckeys from DB
  // Unset cookies
  $cookie_params = session_get_cookie_params();
  $past = time() - 3600;
  foreach ($_COOKIE as $cookie => $value)
  {
    if (empty($cookie_params['domain']))
    {
      setcookie($cookie, '', $past, $cookie_params['path']);
    } else {
      setcookie($cookie, '', $past, $cookie_params['path'], $cookie_params['domain']);
    }
  }
  unset($_COOKIE);

  // Unset session
  if ($relogin)
  {
    // Reset session and relogin (for example: HTTP auth)
    $_SESSION['relogin'] = TRUE;
    unset($_SESSION['authenticated'],
          $_SESSION['user_id'],
          $_SESSION['username'],
          $_SESSION['user_encpass'], $_SESSION['password'],
          $_SESSION['userlevel']);
  } else {
    session_unset();
    session_destroy();
    unset($_SESSION);
  }
}

// EOF
