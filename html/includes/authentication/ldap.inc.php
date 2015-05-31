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

// Warn if authentication will be impossible.
check_extension_exists('ldap', 'LDAP selected as authentication module, but PHP does not have LDAP support! Please load the PHP LDAP module.', TRUE);

// If kerberized login is used, take user from Apache to bypass login screen
if ($config['auth_ldap_kerberized'])
{
  $_SESSION['username'] = $_SERVER['REMOTE_USER'];
}

// Set LDAP debugging level to 7 (dumped to Apache daemon error log) (not virtualhost error log!)
if ($debug)
{
  // Disabled by default, VERY chatty.
  // ldap_set_option(NULL, LDAP_OPT_DEBUG_LEVEL, 7);
}

// If a single server is specified, convert it to array anyway for use in functions below
if (!is_array($config['auth_ldap_server']))
{
  // If no server set and domain is specified, get domain controllers from SRV records
  if ($config['auth_ldap_server'] == '' && $config['auth_ldap_ad_domain'] != '')
  {
    $config['auth_ldap_server'] = ldap_domain_servers_from_dns($config['auth_ldap_ad_domain']);
  } else {
    $config['auth_ldap_server'] = array($config['auth_ldap_server']);
  }
}

// DOCME needs phpdoc block
function ldap_init()
{
  global $ds, $config;

  if (!is_resource($ds))
  {
    print_debug("LDAP[Connecting to " . implode(",",$config['auth_ldap_server']) . "]");
    $ds = @ldap_connect(implode(",",$config['auth_ldap_server']), $config['auth_ldap_port']);
    print_debug("LDAP[Connected]");

    if ($config['auth_ldap_starttls'] && ($config['auth_ldap_starttls'] == 'optional' || $config['auth_ldap_starttls'] == 'require'))
    {
      $tls = ldap_start_tls($ds);
      if ($config['auth_ldap_starttls'] == 'require' && $tls == FALSE)
      {
        session_logout();
        print_error("Fatal error: LDAP TLS required but not successfully negotiated [" . ldap_error($ds) . "]");
        exit;
      }
    }

    if ($config['auth_ldap_referrals'])
    {
      ldap_set_option($ds, LDAP_OPT_REFERRALS, $config['auth_ldap_referrals']);
      print_debug("LDAP[Referrals][Set to " . $config['auth_ldap_referrals'] . "]");
    } else {
      ldap_set_option($ds, LDAP_OPT_REFERRALS, FALSE);
      print_debug("LDAP[Referrals][Disabled]");
    }

    if ($config['auth_ldap_version'])
    {
      ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, $config['auth_ldap_version']);
      print_debug("LDAP[Version][Set to " . $config['auth_ldap_version'] . "]");
    }
  }
}

// DOCME needs phpdoc block
function ldap_authenticate($username, $password)
{
  global $config, $ds;

  ldap_init();
  if ($username && $ds)
  {
    if (ldap_bind_dn($username, $password)) { return 0; }

    $binduser = ldap_internal_dn_from_username($username);

    if ($binduser)
    {
      print_debug("LDAP[Authenticate][User: $username][Bind user: $binduser]");

      // Auth via Apache Kerberos module + LDAP fallback -> automatically authenticated
      if ($config['auth_ldap_kerberized'] || ldap_bind($ds, $binduser, $password))
      {
        if (!$config['auth_ldap_group'])
        {
          return 1;
        }
        else
        {
          $userdn = ($config['auth_ldap_groupmembertype'] == 'fulldn' ? $binduser : $username);

          foreach ($config['auth_ldap_group'] as $ldap_group)
          {
            print_debug("LDAP[Authenticate][Comparing: " . $ldap_group . "][".$config['auth_ldap_groupmemberattr']."=$userdn]");
            $compare = ldap_compare($ds, $ldap_group, $config['auth_ldap_groupmemberattr'], $userdn);

            if ($compare === -1)
            {
              print_debug("LDAP[Authenticate][Compare LDAP error: " . ldap_error($ds) . "]");
              continue;
            } elseif ($compare === FALSE) {
              print_debug("LDAP[Authenticate][Processing group: $ldap_group][Not matched]");
            } else {
              // $compare === TRUE
              print_debug("LDAP[Authenticate][Processing group: $ldap_group][Matched]");
              return 1;
            } // FIXME does not support nested groups
          }
        }
      }
      else
      {
        print_debug(ldap_error($ds));
      }
    }
  }

  session_logout();
  return 0;
}

// DOCME needs phpdoc block
function ldap_auth_can_logout()
{
  global $config;

  // If kerberized, login is handled through apache; if not, we can log out.
  return (!$config['auth_ldap_kerberized']);
}

// DOCME needs phpdoc block
function ldap_auth_can_change_password($username = "")
{
  return 0;
}

// DOCME needs phpdoc block
function ldap_auth_change_password($username, $newpassword)
{
  // Not supported (for now?)
}

// DOCME needs phpdoc block
function ldap_auth_usermanagement()
{
  return 0;
}

// DOCME needs phpdoc block
function ldap_adduser($username, $password, $level, $email = "", $realname = "", $can_modify_passwd = '1')
{
  // Not supported
  return 0;
}

// DOCME needs phpdoc block
function ldap_auth_user_exists($username)
{
  global $config, $ds;

  ldap_init();
  if (ldap_bind_dn()) { return 0; } // Will not work without bind user or anon bind

  $binduser = ldap_internal_dn_from_username($username);

  if ($binduser)
  {
    return 1;
  }

  return 0;
}

// DOCME needs phpdoc block
function ldap_auth_user_level($username)
{
  global $config, $ds, $cache;

  if (!isset($cache['ldap']['level'][$username]))
  {
    $userlevel = 0;

    ldap_init();
    ldap_bind_dn();

    // Find all defined groups $username is in
    $userdn  = ($config['auth_ldap_groupmembertype'] == 'fulldn' ? ldap_internal_dn_from_username($username) : $username);
    print_debug("LDAP[UserLevel][UserDN: $userdn]");

    // This used to be done with a filter, but AD seems to be really retarded with regards to escaping.
    //
    // Particularly:
    //   CN=Name\, User,OU=Team,OU=Region,OU=Employees,DC=corp,DC=example,DC=com
    // Has 2 methods of escaping, we automatically do the first:
    //   CN=Name\2C, User,OU=Team,OU=Region,OU=Employees,DC=corp,DC=example,DC=com
    // Yet the filter used here before only worked doing this:
    //   CN=Name\\, User,OU=Team,OU=Region,OU=Employees,DC=corp,DC=example,DC=com
    //
    // Yay for arbitrary escapes. Don't know how to handle; this is most likely (hopefully) AD specific.
    // So, we foreach our locally known groups instead.
    foreach ($config['auth_ldap_groups'] as $ldap_group => $ldap_group_info)
    {
      $compare = ldap_compare($ds, 'cn=' . $ldap_group . ',' . $config['auth_ldap_groupbase'], $config['auth_ldap_groupmemberattr'], $userdn);

      if ($compare === -1)
      {
        print_debug("LDAP[UserLevel][Compare LDAP error: " . ldap_error($ds) . "]");
        continue;
      } elseif ($compare === FALSE) {
        print_debug("LDAP[UserLevel][Processing group: $ldap_group][Not matched]");
      } else {
        // $compare === TRUE
        print_debug("LDAP[UserLevel][Processing group: $ldap_group][Level: " . $ldap_group_info['level'] . "]");
        if ($ldap_group_info['level'] > $userlevel)
        {
          $userlevel = $ldap_group_info['level'];
          print_debug("LDAP[UserLevel][Accepted group level]");
        } else {
          print_debug("LDAP[UserLevel][Ignoring group level]");
        }
      }
    }

    print_debug("LDAP[Userlevel][Final level: $userlevel]");

    $cache['ldap']['level'][$username] = $userlevel;
  }

  return $cache['ldap']['level'][$username];
}

// DOCME needs phpdoc block
function ldap_auth_user_id($username)
{
  global $config, $ds;

  $userid = -1;

  ldap_init();
  ldap_bind_dn();

  $userdn = ($config['auth_ldap_groupmembertype'] == 'fulldn' ? ldap_internal_dn_from_username($username) : $config['auth_ldap_prefix'] . $username . $config['auth_ldap_suffix']);

  $filter = "(" . str_ireplace($config['auth_ldap_suffix'], '', $userdn) . ")";
  print_debug("LDAP[Filter][$filter][" . trim($config['auth_ldap_suffix'], ', ') . "]");
  $search = ldap_search($ds, trim($config['auth_ldap_suffix'], ', '), $filter);
  $entries = ldap_get_entries($ds, $search);

  if ($entries['count'])
  {
    $userid = ldap_internal_auth_user_id($entries[0]);
    print_debug("LDAP[UserID][$userid]");
  } else {
    print_debug("LDAP[UserID][User not found through filter]");
  }

  return $userid;
}

// DOCME needs phpdoc block
function ldap_deluser($username)
{
  $user_id = auth_user_id($username);

  dbDelete('entity_permissions', "`user_id` =  ?", array($user_id));
  dbDelete('users_prefs',        "`user_id` =  ?", array($user_id));
  dbDelete('users_ckeys',       "`username` =  ?", array($username));

  // Not supported
  return 0;
}

// DOCME needs phpdoc block
function ldap_auth_user_list()
{
  global $config, $ds;

  ldap_init();
  ldap_bind_dn();

  $filter = '(objectClass=' . $config['auth_ldap_objectclass'] . ')';

  print_debug("LDAP[UserList][Filter][$filter][" . trim($config['auth_ldap_suffix'], ', ') . "]");
  $search = ldap_search($ds, trim($config['auth_ldap_suffix'], ', '), $filter);
  print_debug(ldap_error($ds));

  $entries = ldap_get_entries($ds, $search);

  if ($entries['count'])
  {
    for ($i = 0; $i < $entries['count']; $i++)
    {
      $username = $entries[$i][strtolower($config['auth_ldap_attr']['uid'])][0];
      $realname = $entries[$i][strtolower($config['auth_ldap_attr']['cn'])][0];
      $user_id  = ldap_internal_auth_user_id($entries[$i]);

      $userdn = ($config['auth_ldap_groupmembertype'] == 'fulldn' ? $entries[$i]['dn'] : $username);

      print_debug("LDAP[UserList][Compare: " . implode('|',$config['auth_ldap_group']) . "][".$config['auth_ldap_groupmemberattr']."][$userdn]");

      foreach ($config['auth_ldap_group'] as $ldap_group)
      {
        $authorized = 0;
        $compare = ldap_compare($ds, $ldap_group, $config['auth_ldap_groupmemberattr'], $userdn);

        if ($compare === -1)
        {
          print_debug("LDAP[UserList][Compare LDAP error: " . ldap_error($ds) . "]");
          continue;
        } elseif ($compare === FALSE) {
          print_debug("LDAP[UserList][Processing group: $ldap_group][Not matched]");
        } else {
          // $$compare === TRUE
          print_debug("LDAP[UserList][Authorized: $userdn for group $ldap_group]");
          $authorized = 1;
          break;
        } // FIXME does not support nested groups
      }

      if (!isset($config['auth_ldap_group']) || $authorized)
      {
        $userlist[] = array('username' => $username, 'realname' => $realname, 'user_id' => $user_id);
      }
    }
  }

  return $userlist;
}

// Private function for this ldap module only
// Returns the textual SID for Active Directory
// DOCME needs phpdoc block
function ldap_bin_to_str_sid($binsid)
{
  $hex_sid = bin2hex($binsid);
  $rev = hexdec(substr($hex_sid, 0, 2));
  $subcount = hexdec(substr($hex_sid, 2, 2));
  $auth = hexdec(substr($hex_sid, 4, 12));
  $result  = "$rev-$auth";

  for ($x=0;$x < $subcount; $x++)
  {
    $subauth[$x] = hexdec(ldap_little_endian(substr($hex_sid, 16 + ($x * 8), 8)));
    $result .= "-" . $subauth[$x];
  }

  // Cheat by tacking on the S-
  return 'S-' . $result;
}

// Private function for this ldap module only
// Converts a little-endian hex-number to one, that 'hexdec' can convert
// DOCME needs phpdoc block
function ldap_little_endian($hex)
{
  for ($x = strlen($hex) - 2; $x >= 0; $x = $x - 2)
  {
    $result .= substr($hex, $x, 2);
  }

  return $result;
}

// Private function for this ldap module only
// Bind with either the configured bind DN, the user's configured DN, or anonymously, depending on config.
// DOCME needs phpdoc block
function ldap_bind_dn($username = "", $password = "")
{
  global $config, $ds;

  print_debug("LDAP[Bind DN called]");

  if ($config['auth_ldap_binddn'])
  {
    print_debug("LDAP[Bind][" . $config['auth_ldap_binddn'] . "]");
    $bind = ldap_bind($ds, $config['auth_ldap_binddn'], $config['auth_ldap_bindpw']);
  } else {
    // Try anonymous bind if configured to do so
    if ($config['auth_ldap_bindanonymous'])
    {
      print_debug("LDAP[Bind][anonymous]");
      $bind = ldap_bind($ds);
    } else {
      if (($username == '' || $password == '') && isset($_SESSION['password']))
      {
        // Use session credintials
        $username = $_SESSION['username'];
        $password = $_SESSION['password'];
      }
      print_debug("LDAP[Bind][" . $config['auth_ldap_prefix'] . $username . $config['auth_ldap_suffix'] . "]");
      $bind = ldap_bind($ds, $config['auth_ldap_prefix'] . $username . $config['auth_ldap_suffix'], $password);
    }
  }

  if ($bind)
  {
    return 0;
  } else {
    print_debug("Error binding to LDAP server: " . $config['auth_ldap_server'] . ": " . ldap_error($ds));
    session_logout();
    return 1;
  }
}

// Private function for this ldap module only
// DOCME needs phpdoc block
function ldap_internal_dn_from_username($username)
{
  global $config, $ds, $cache;

  if (!isset($cache['ldap']['dn'][$username]))
  {
    ldap_init();
    $filter = "(" . $config['auth_ldap_attr']['uid'] . '=' . $username . ")";
    print_debug("LDAP[Filter][$filter][" . trim($config['auth_ldap_suffix'], ', ') . "]");
    $search = ldap_search($ds, trim($config['auth_ldap_suffix'], ', '), $filter);
    $entries = ldap_get_entries($ds, $search);

    if ($entries['count'])
    {
      list($cache['ldap']['dn'][$username],) = ldap_escape_filter_value($entries[0]['dn']);
    }
  }

  return $cache['ldap']['dn'][$username];
}

// Private function for this ldap module only
// DOCME needs phpdoc block
function ldap_internal_auth_user_id($result)
{
  global $config;

  // For AD, convert SID S-1-5-21-4113566099-323201010-15454308-1104 to 1104 as our numeric unique ID
  if ($config['auth_ldap_attr']['uidNumber'] == "objectSid")
  {
    $sid = explode('-', ldap_bin_to_str_sid($result['objectsid'][0]));
    $userid = $sid[count($sid)-1];
    print_debug("LDAP[UserID][Converted objectSid " . ldap_bin_to_str_sid($result['objectsid'][0]) . " to user ID " . $userid . "]");
  } else {
    $userid = $result[strtolower($config['auth_ldap_attr']['uidNumber'])][0];
    print_debug("LDAP[UserID][Attribute " . $config['auth_ldap_attr']['uidNumber'] . " yields user ID " . $userid . "]");
  }

  return $userid;
}

/**
* Retrieves list of domain controllers from DNS through SRV records.
*
* @param string Domain name (fqdn-style) for the AD domain.
*
* @return array Array of server names to be used for LDAP.
*/
function ldap_domain_servers_from_dns($domain)
{
  global $config;

  include_once('Net/DNS2.php');
  include_once('Net/DNS2/RR/SRV.php');

  $servers = array();

  $resolver = new Net_DNS2_Resolver();

  $response = $resolver->query("_ldap._tcp.dc._msdcs.$domain", 'SRV', 'IN');
  if ($response)
  {
    foreach ($response->answer as $answer)
    {
      $servers[] = $answer->target;
    }
  }

  return $servers;
}

/**
* Escapes the given VALUES according to RFC 2254 so that they can be safely used in LDAP filters.
*
* Any control characters with an ACII code < 32 as well as the characters with special meaning in
* LDAP filters "*", "(", ")", and "\" (the backslash) are converted into the representation of a
* backslash followed by two hex digits representing the hexadecimal value of the character.
*
* @param array $values Array of values to escape
*
* @return array Array $values, but escaped
*/
function ldap_escape_filter_value($values = array())
{
  // Parameter validation
  if (!is_array($values))
  {
    $values = array($values);
  }

  foreach ($values as $key => $val)
  {
    // Escaping of filter meta characters
    $val = str_replace('\\', '\5c', $val);
    $val = str_replace('\5c,', '\2c', $val);
    $val = str_replace('*',  '\2a', $val);
    $val = str_replace('(',  '\28', $val);
    $val = str_replace(')',  '\29', $val);

    // ASCII < 32 escaping
    $val = asc2hex32($val);

    if (NULL === $val) { $val = '\0'; }  // apply escaped "null" if string is empty

    $values[$key] = $val;
  }

  return $values;
}

/**
* Undoes the conversion done by {@link ldap_escape_filter_value()}.
*
* Converts any sequences of a backslash followed by two hex digits into the corresponding character.
*
* @param array $values Array of values to escape
*
* @return array Array $values, but unescaped
*/
function ldap_unescape_filter_value($values = array())
{
  // Parameter validation
  if (!is_array($values))
  {
    $values = array($values);
  }

  foreach ($values as $key => $value)
  {
    // Translate hex code into ascii
    $values[$key] = hex2asc($value);
  }

  return $values;
}

/**
* Converts all ASCII chars < 32 to "\HEX"
*
* @param string $string String to convert
*
* @return string
*/
function asc2hex32($string)
{
  for ($i = 0; $i < strlen($string); $i++)
  {
    $char = substr($string, $i, 1);
    if (ord($char) < 32)
    {
      $hex = dechex(ord($char));
      if (strlen($hex) == 1) { $hex = '0'.$hex; }
      $string = str_replace($char, '\\'.$hex, $string);
    }
  }
  return $string;
}

/**
* Converts all Hex expressions ("\HEX") to their original ASCII characters
*
* @param string $string String to convert
*
* @author beni@php.net, heavily based on work from DavidSmith@byu.net
* @return string
*/
function hex2asc($string)
{
  $string = preg_replace("/\\\([0-9A-Fa-f]{2})/e", "''.chr(hexdec('\\1')).''", $string);
  return $string;
}

// EOF
