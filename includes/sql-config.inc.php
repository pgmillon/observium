<?php

/**
 * Observium Network Management and Monitoring System
 * Copyright (C) 2006-2013, Observium Developers - http://www.observium.org
 *
 * @package    observium
 * @subpackage config
 * @author     Tom Laermans <sid3windr@observium.org>
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

// Load configuration file into $config variable
if (!isset($config['install_dir']))
{
  $base_dir = realpath(dirname(__FILE__) . '/..');
} else {
  $base_dir = $config['install_dir'];
}

// Clear config array, we're starting with a clean state
$config = array();

require($base_dir."/includes/defaults.inc.php");
require($base_dir."/config.php");

// Base dir, if it's not set in config
if (!isset($config['install_dir']))
{
  $config['install_dir'] = $base_dir;
}

// Include necessary supporting files
require_once($config['install_dir'] . "/includes/functions.inc.php");
require($config['install_dir'] . "/includes/definitions.inc.php");

// Common functions, for is_ssl and print_warning/print_error
include_once($config['install_dir'].'/includes/common.inc.php');

// Connect to database
if (!$GLOBALS[OBS_DB_LINK])
{
  if (defined('OBS_DB_SKIP') && OBS_DB_SKIP === TRUE)
  {
    print_warning("WARNING: In PHP Unit tests we can skip MySQL connect. But if you test mysql functions, check your configs.");
  } else {
    print_error("MySQL Error " . dbErrorNo() . ": " . dbError());
    die; // Die if not PHP Unit tests
  }
}
else if (!get_db_version() && !(isset($options['u']) || isset($options['V'])))
{
  if (!dbQuery('SELECT 1 FROM `devices` LIMIT 1;'))
  {
    // DB schema not installed, install first
    print_error("DB schema not installed, first install it.");
    die;
  }
} else {
  //register_shutdown_function('dbClose');
  // Maybe better in another place, but at least here it runs always; keep track of what svn revision we last saw, and eventlog the upgrade versions.
  // We have versions here from the includes above, and we just connected to the DB.
  $rev_old = @get_obs_attrib('current_rev');
  if ($rev_old < OBSERVIUM_REV || !is_numeric($rev_old))
  {
    set_obs_attrib('current_rev', OBSERVIUM_REV);
    log_event("Observium updated: $rev_old -> " . OBSERVIUM_REV); // FIXME log_event currently REQUIRES a device, the SQL query will fail.
  }
}

// Load SQL configuration into $config variable
load_sqlconfig($config);

/**
 * OHMYGOD, this is very dangerous, because this is secure hole for override static definitions,
 * now already defined configs skipped in load_sqlconfig().
 *
// Reload configuration file into $config variable to make sure it overrules all SQL-supplied and default settings
// Not the greatest hack, but array_merge was unfit for the job, unfortunately.
include($config['install_dir']."/config.php");

*/

// Disable nonexistant features in CE, do not try to turn on, it will not give effect
if (OBSERVIUM_EDITION == 'community')
{
  $config['enable_billing'] = 0;
  $config['api']['enabled'] = 0;

  // Disabled (not exist) modules
  unset($config['poller_modules']['oids'], $config['poller_modules']['loadbalancer'], $config['poller_modules']['aruba-controller']);
}

// Self hostname for observium server
// FIXME, used only in smokeping integration
if (!isset($config['own_hostname']))
{
  $config['own_hostname'] = get_localhost();
}

// Set web_url setting to default, add trailing slash if not present
if (!isset($config['web_url']))
{
  $config['web_url'] = isset($config['base_url']) ? $config['base_url'] : 'http://' . get_localhost();
}
if (substr($config['web_url'], -1) != '/') { $config['web_url'] .= '/'; }

if (!isset($config['base_url']))
{
  if (isset($_SERVER["SERVER_NAME"]) && isset($_SERVER["SERVER_PORT"]))
  {
    if (strpos($_SERVER["SERVER_NAME"] , ":"))
    {
      // Literal IPv6
      $config['base_url']  = "http://[" . $_SERVER["SERVER_NAME"] ."]" . ($_SERVER["SERVER_PORT"] != 80 ? ":".$_SERVER["SERVER_PORT"] : '') ."/";
    } else {
      $config['base_url']  = "http://" . $_SERVER["SERVER_NAME"] . ($_SERVER["SERVER_PORT"] != 80 ? ":".$_SERVER["SERVER_PORT"] : '') ."/";
    }
  }
  //} else {
  //  // Try to detect base_url in cli based on hostname
  //  /// FIXME. Here require get_localhost(), but this function loaded after definitions
  //  //$config['base_url'] = "http://" . get_localhost() . "/";
  //}
} else {
  // Add / to base_url if not there
  if (substr($config['base_url'], -1) != '/') { $config['base_url'] .= '/'; }
}

// If we're on SSL, let's properly detect it
if (is_ssl())
{
  $config['base_url'] = preg_replace('/^http:/','https:', $config['base_url']);
}

// Old variable backwards compatibility
if (isset($config['rancid_configs']) && !is_array($config['rancid_configs'])) { $config['rancid_configs'] = array($config['rancid_configs']); }
if (isset($config['auth_ldap_group']) && !is_array($config['auth_ldap_group'])) { $config['auth_ldap_group'] = array($config['auth_ldap_group']); }
if (isset($config['auth_ldap_kerberized']) && $config['auth_ldap_kerberized'] && $config['auth_mechanism'] == 'ldap') { $config['auth']['remote_user'] = TRUE; }

// Security fallback check
if (isset($config['auth']['remote_user']) && $config['auth']['remote_user'] && !isset($_SERVER['REMOTE_USER']))
{
  // Disable remote_user, Apache did not pass a username! Misconfigured?
  // FIXME log this somewhere?
  $config['auth']['remote_user'] = FALSE;
}

// Database currently stores v6 networks non-compressed, check for any compressed subnet and expand them
foreach ($config['ignore_common_subnet'] as $index => $content)
{
  if (strstr($content,':') !== FALSE) { $config['ignore_common_subnet'][$index] = Net_IPv6::uncompress($content); }
}

if (isset($config['rrdgraph_def_text']))
{
  $config['rrdgraph_def_text'] = str_replace("  ", " ", $config['rrdgraph_def_text']);
  $config['rrd_opts_array'] = explode(" ", trim($config['rrdgraph_def_text']));
}

// EOF
