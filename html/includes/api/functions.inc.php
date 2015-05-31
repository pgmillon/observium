<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    Simple Observium API
 * @subpackage functions
 * @author     Dennis de Houx <dennis@aio.be>
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

include_once("errorcodes.inc.php");

/**
 * Show the debug information
 *
 * @param txt
 * @param value
 *
*/
function api_show_debug($txt, $value) {
  global $vars;
  if ($vars['debug']) {
    echo "<pre>\n";
    echo "DEBUG ".$txt.":\n";
    print_vars($value);
    echo "</pre>\n";
  }
}


/**
 * Show the manual include pages
 *
*/
function api_show_manual() {
  global $config;
  foreach ($config['api']['module'] as $item=>$value) {
    if ($value) {
      $file = "./pages/api/manual.".$item.".inc.php";
      if (file_exists($file)) {
        include_once($file);
      }
    }
  }
}


/**
 * Show the enabled modules
 *
 * @return string
 *
*/
function api_show_modules() {
  global $config;
  $res     = "";
  foreach ($config['api']['module'] as $item=>$value) {
    $img   = ($value ? "ok" : "ban");
    $res  .= "<i class=\"oicon-".$img."-circle\" style=\"margin-top: 1px;\"></i> <strong>".ucfirst($item)."</strong><br />";
  }
  return $res;
}




/**
 * Load the requested API module
 *
 * @return string
 * @param  module
 *
*/
function api_load_module($module) {
  $res     = false;
  $file    = "includes/api/module.".$module.".inc.php";
  if (file_exists($file)) {
    include_once($file);
    $res   = true;
  }
  api_show_debug("Loading module", array("module"=>$module, "file"=>$file));
  return $res;
}


/**
 * Encrypt the API data
 *
 * @return string
 * @param  data
 * @param  key
 *
*/
function api_encrypt_data($data, $key) {
  $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
  $iv      = mcrypt_create_iv($iv_size, MCRYPT_RAND);
  $res     = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, $key, $data, MCRYPT_MODE_ECB, $iv);
  api_show_debug("Returned Encrypted data", $res);
  return $res;
}


/**
 * Decrypt the API data
 *
 * @return string
 * @param  data
 * @param  key
 *
*/
function api_decrypt_data($data, $key) {
  $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_256, MCRYPT_MODE_ECB);
  $iv      = mcrypt_create_iv($iv_size, MCRYPT_RAND);
  $res     = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, $key, $data, MCRYPT_MODE_ECB, $iv);
  api_show_debug("Returned Decrypted data", $res);
  return $res;
}


/**
 * Return the error message
 *
 * @return array
 * @param  section
 * @param  code
 *
*/
function api_errorcodes($code, $section="error") {
  global $errorcodes;
  //api_show_debug("All Error Codes", $errorcodes);
  if (isset($errorcodes[$code])) {
    $res   = array($section=>array("code"=>$code, "msg"=>$errorcodes[$code]['msg']));
  } else {
    $res   = array("error"=>array("code"=>"401", "msg"=>$errorcodes['401']['msg']));
  }
  api_show_debug("Returned Error Code", $res);
  return $res;
}


/**
 * Authenticate the user login
 *
 * @return array
 * @param  username
 * @param  password
 *
*/
function api_authenticate_user($username, $password)
{
	global $config, $ds, $rad;

        $auth_file = $config['html_dir'].'/includes/authentication/' . $config['auth_mechanism'] . '.inc.php';
	if (!is_file($auth_file))
	{
		print_error('ERROR: no valid auth_mechanism defined!');
		exit();
	}

	include($auth_file);
        // Include base auth functions calls
        include($config['html_dir'].'/includes/authenticate-functions.inc.php');

	$res = array('id' => '', 'level' => 0);

	if (authenticate($username, $password))
	{
		$res['id'] = auth_user_id($username);
		$res['level'] = auth_user_level($username);
	}

	/**
	$row     = dbFetchRow("SELECT user_id, username, password, level FROM `users` WHERE `username` = ?", array($username));

	if ($row['username'] && $row['username'] == $username) {
		if ($row['password'] == crypt($password, $row['password'])) {
			$res = array("id"=>$row['user_id'], "level"=>$row['level']);
		}
	}
	*/

	api_show_debug("Returned User authentification", $res);
	return $res;
}


/**
 * Check user permissions to access the device
 *
 * @return boolean
 * @param  device_id
 *
*/
function api_device_permitted($device_id) {
  global $vars;
  $res     = false;
  if ($vars['user']['level'] >= 10) {
    $res   = true;
  } else {
    api_show_debug("Checking permission for device", $vars['device']);
    $row = dbFetchRow("SELECT * FROM `entity_permissions` WHERE `entity_type` = 'device' AND `user_id` = ? AND `entity_id`= ? LIMIT 1", array($vars['user']['id'], $vars['device']));
    if (is_array($row)) {
      $res  = true;
    }
  }
  api_show_debug("Returned device permitted", $res);
  return $res;
}


/**
 * Encrypt data into json format
 *
 * @return string
 * @param  data
 *
*/
function api_json_data($data) {
  if (is_array($data)) {
    $res    = json_encode($data);
  } else {
    $error  = api_errorcodes("402");
    $res    = json_encode($error);
  }
  api_show_debug("Returned JSON data", $data);
  api_show_debug("Returned Encrypted JSON data", $res);
  return $res;
}


// EOF
