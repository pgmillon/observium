<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage common
 * @author     Adam Armstrong <adama@memetic.org>
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

// Common Functions
/// FIXME. There should be functions that use only standard php (and self) functions.

// Get current DB Schema version
// DOCME needs phpdoc block
// TESTME needs unit testing
// MOVEME to includes/functions.inc.php
function get_db_version()
{
  return dbFetchCell('SELECT `version` FROM `dbSchema`');
}

// Get local hostname
// DOCME needs phpdoc block
function get_localhost()
{
  global $cache;

  if (!isset($cache['localhost']))
  {
    $cache['localhost'] = php_uname('n');
    if (!strpos($cache['localhost'], '.'))
    {
      // try use hostname -f for get FQDN hostname
      $localhost_t = external_exec('/bin/hostname -f');
      if (strpos($localhost_t, '.'))
      {
        $cache['localhost'] = $localhost_t;
      }
    }
  }

  return $cache['localhost'];
}

// DOCME needs phpdoc block
// TESTME needs unit testing
// MOVEME to includes/alerts.inc.php
function get_alert_entry_by_id($id)
{
  return dbFetchRow("SELECT * FROM `alert_table`".
                    " LEFT JOIN `alert_table-state` ON  `alert_table`.`alert_table_id` =  `alert_table-state`.`alert_table_id`".
                    " WHERE  `alert_table`.`alert_table_id` = ?", array($id));
}

/**
 * Percent Class
 *
 * Given a percentage value return a class name (for CSS).
 *
 * @param int|string $percent
 * @return string
 */
function percent_class($percent)
{
  if ($percent < "25")
  {
    $class = "info";
  } elseif ($percent < "50") {
    $class = "";
  } elseif ($percent < "75") {
    $class = "success";
  } elseif ($percent < "90") {
    $class = "warning";
  } else {
    $class = "danger";
  }

  return $class;
}

/**
 * Percent Colour
 *
 * This function returns a colour based on a 0-100 value
 * It scales from green to red from 0-100 as default.
 *
 * @param integer $percent
 * @param integer $brightness
 * @param integer $max
 * @param integer $min
 * @param integer $thirdColorHex
 * @return string
 */
function percent_colour($value, $brightness = 128, $max = 100, $min = 0, $thirdColourHex = '00')
{
  if ($value > $max) { $value = $max; }
  if ($value < $min) { $value = $min; }

  // Calculate first and second colour (Inverse relationship)
  $first = (1-($value/$max))*$brightness;
  $second = ($value/$max)*$brightness;

  // Find the influence of the middle Colour (yellow if 1st and 2nd are red and green)
  $diff = abs($first-$second);
  $influence = ($brightness-$diff)/2;
  $first = intval($first + $influence);
  $second = intval($second + $influence);

  // Convert to HEX, format and return
  $firstHex = str_pad(dechex($first),2,0,STR_PAD_LEFT);
  $secondHex = str_pad(dechex($second),2,0,STR_PAD_LEFT);

  return '#'.$secondHex . $firstHex . $thirdColourHex;

  // alternatives:
  // return $thirdColourHex . $firstHex . $secondHex;
  // return $firstHex . $thirdColourHex . $secondHex;
}

// DOCME needs phpdoc block
// Write a line to the specified logfile (or default log if not specified)
// We open & close for every line, somewhat lower performance but this means multiple concurrent processes could write to the file.
// Now marking process and pid, if things are running simultaneously you can still see what's coming from where.
// TESTME needs unit testing
function logfile($filename, $string = NULL)
{
  global $config, $argv;

  // Use default logfile if none specified
  if ($string == NULL) { $string = $filename; $filename = $config['log_file']; }

  // Place logfile in log directory if no path specified
  if (basename($filename) == $filename) { $filename = $config['log_dir'] . '/' . $filename; }
  // Create logfile if not exist
  if (file_exists($filename))
  {
    $fd = fopen($filename, 'a');
  } else {
    $fd = fopen($filename, 'wb');
    // Check writable file (only after creation for speedup)
    if (!is_writable($filename))
    {
      print_debug("Log file '$filename' is not writable or not created.");
      fclose($fd);
      return FALSE;
    }
  }

  $string = '[' . date('Y/m/d H:i:s O') . '] ' . basename($argv[0]) . '(' . getmypid() . '): ' . trim($string) . PHP_EOL;
  fputs($fd, $string);
  fclose($fd);
}

// DOCME needs phpdoc block
// Observium's SQL debugging. Chooses nice output depending upon web or cli
// TESTME needs unit testing
function print_sql($query)
{
  if ($GLOBALS['cli'])
  {
    print_vars($query);
  } else {
    if (class_exists('SqlFormatter'))
    {
      // Hide it under a "database icon" popup.
      #echo overlib_link('#', '<i class="oicon-databases"> </i>', SqlFormatter::highlight($query));
      echo '<p>',SqlFormatter::highlight($query),'</p>';
    } else {
      print_vars($query);
    }
  }
}

// DOCME needs phpdoc block
// Observium's variable debugging. Choses nice output depending upon web or cli
// TESTME needs unit testing
function print_vars($vars)
{
  if ($GLOBALS['cli'])
  {
    if (function_exists('rt'))
    {
      print_r($vars);
      rt($vars);
    } else {
      print_r($vars);
    }
  } else {
    if (function_exists('r'))
    {
      r($vars);
    } else {
      print_r($vars);
    }
  }
}

/**
 * Convert SNMP timeticks string into seconds
 *
 * SNMP timeticks can be in two different formats: "(2100)" or "0:0:00:21.00".
 * Parse the timeticks string and convert it to seconds.
 *
 * @param string $timetick
 * @param bool $float - Return a float with microseconds
 *
 * @return int|float
 */
function timeticks_to_sec($timetick, $float = FALSE)
{
  $timetick = trim($timetick, '()');
  $timetick_array = explode(':', $timetick);
  if (count($timetick_array) == 1 && is_numeric($timetick))
  {
    $secs = $timetick;
    $microsecs = 0;
  } else {
    list($days, $hours, $mins, $secs) = $timetick_array;
    list($secs, $microsecs) = explode('.', $secs);

    $hours += $days  * 24;
    $mins  += $hours * 60;
    $secs  += $mins  * 60;
  }
  $time   = ($float ? (float)$secs + $microsecs/100 : (int)$secs);

  return $time;
}

// DOCME needs phpdoc block
# If a device is up, return its uptime, otherwise return the
# time since the last time we were able to poll it.  This
# is not very accurate, but better than reporting what the
# uptime was at some time before it went down.
// TESTME needs unit testing
function deviceUptime($device, $format="long")
{
  if ($device['status'] == 0) {
    if ($device['last_polled'] == 0) {
      return "Never polled";
    }
    $since = time() - strtotime($device['last_polled']);
    return "Down " . formatUptime($since, $format);
  } else {
    return formatUptime($device['uptime'], $format);
  }
}

/**
 * Format seconds to requested time format.
 *
 * Default format is "long".
 *
 * Supported formats:
 *   long    => '1 year, 1 day, 1h 1m 1s'
 *   longest => '1 year, 1 day, 1 hour 1 minute 1 second'
 *   short-3 => '1y 1d 1h'
 *   short-2 => '1y 1d'
 *   shorter => *same as short-2 above
 *   (else)  => '1y 1d 1h 1m 1s'
 *
 * @param int|string $uptime Time is seconds
 * @param string $format Optional format
 *
 * @return string
 */
function formatUptime($uptime, $format = "long")
{
  $uptime = (int)$uptime;
  if ($uptime === 0) { return '0s'; }

  $up['y'] = floor($uptime / 31536000);
  $up['d'] = floor($uptime % 31536000 / 86400);
  $up['h'] = floor($uptime % 86400 / 3600);
  $up['m'] = floor($uptime % 3600 / 60);
  $up['s'] = floor($uptime % 60);

  $result = '';

  if ($format == 'long' || $format == 'longest')
  {
    if ($up['y'] > 0) {
      $result .= $up['y'] . ' year'. ($up['y'] != 1 ? 's' : '');
      if ($up['d'] > 0 || $up['h'] > 0 || $up['m'] > 0 || $up['s'] > 0) { $result .= ', '; }
    }

    if ($up['d'] > 0)  {
      $result .= $up['d']  . ' day' . ($up['d'] != 1 ? 's' : '');
      if ($up['h'] > 0 || $up['m'] > 0 || $up['s'] > 0) { $result .= ', '; }
    }

    if ($format == 'longest')
    {
      if ($up['h'] > 0) { $result .= $up['h'] . ' hour'   . ($up['h'] != 1 ? 's ' : ' '); }
      if ($up['m'] > 0) { $result .= $up['m'] . ' minute' . ($up['m'] != 1 ? 's ' : ' '); }
      if ($up['s'] > 0) { $result .= $up['s'] . ' second' . ($up['s'] != 1 ? 's ' : ' '); }
    } else {
      if ($up['h'] > 0) { $result .= $up['h'] . 'h '; }
      if ($up['m'] > 0) { $result .= $up['m'] . 'm '; }
      if ($up['s'] > 0) { $result .= $up['s'] . 's '; }
    }
  } else {
    $count = 6;
    if ($format == 'short-3') { $count = 3; }
    elseif ($format == 'short-2' || $format == 'shorter') { $count = 2; }

    foreach ($up as $period => $value)
    {
      if ($value == 0) { continue; }
      $result .= $value.$period.' ';
      $count--;
      if ($count == 0) { break; }
    }
  }

  return trim($result);
}

/**
 * Get current timezones for mysql and php.
 * Use this function when need display timedate from mysql
 * for fix diffs betwen this timezones
 *
 */
// MOVEME to includes/functions.inc.php
function get_timezone()
{
  global $cache;

  if (!isset($cache['timezone']))
  {
    $cache['timezone']['mysql']  = dbFetchCell('SELECT TIMEDIFF(NOW(), UTC_TIMESTAMP);');   // return '04:00:00'
    if ($cache['timezone']['mysql'][0] != '-')
    {
      $cache['timezone']['mysql'] = '+'.$cache['timezone']['mysql'];
    }
    $cache['timezone']['mysql']  = preg_replace('/:00$/', '', $cache['timezone']['mysql']); // convert to '+04:00'
    $cache['timezone']['php']    = date('P');                                               // return '+04:00'
    //$cache['timezone']['system'] = external_exec('/bin/date "+%::z"'); // Not used now, return '+04:00:00'

    $cache['timezone']['diff'] = strtotime($cache['timezone']['mysql']) - strtotime($cache['timezone']['php']);  // Set TRUE if timezones different
  }

  return $cache['timezone'];
}

// DOCME needs phpdoc block
function humanspeed($speed)
{
  if ($speed == '')
  {
    return '-';
  } else {
    return formatRates($speed);
  }
}

// DOCME needs phpdoc block
// TESTME needs unit testing
// MOVEME to includes/rewrites.inc.php
function formatCiscoHardware(&$device, $short = FALSE)
{
  if ($device['os'] == "ios")
  {
    if ($device['hardware'])
    {
      if (preg_match("/^WS-C([A-Za-z0-9]+).*/", $device['hardware'], $matches))
      {
        if (!$short)
        {
           $device['hardware'] = "Cisco " . $matches[1] . " (" . $device['hardware'] . ")";
        }
        else
        {
           $device['hardware'] = "Cisco " . $matches[1];
        }
      }
      elseif (preg_match("/^CISCO([0-9]+)$/", $device['hardware'], $matches))
      {
        $device['hardware'] = "Cisco " . $matches[1];
      }
    }
    else
    {
      if (preg_match("/Cisco IOS Software, C([A-Za-z0-9]+) Software.*/", $device['sysDescr'], $matches))
      {
        $device['hardware'] = "Cisco " . $matches[1];
      }
      elseif (preg_match("/Cisco IOS Software, ([0-9]+) Software.*/", $device['sysDescr'], $matches))
      {
        $device['hardware'] = "Cisco " . $matches[1];
      }
    }
  }
}

// DOCME needs phpdoc block
// TESTME needs unit testing
function format_mac($mac)
{
  // Strip out non-hex digits
  $mac = preg_replace('/[[:^xdigit:]]/', '', strtolower($mac));
  // Add colons
  $mac = preg_replace('/([[:xdigit:]]{2})(?!$)/', '$1:', $mac);
  // Convert fake MACs to IP
  //if (preg_match('/ff:fe:([[:xdigit:]]+):([[:xdigit:]]+):([[:xdigit:]]+):([[:xdigit:]]{1,2})/', $mac, $matches))
  if (preg_match('/ff:fe:([[:xdigit:]]{2}):([[:xdigit:]]{2}):([[:xdigit:]]{2}):([[:xdigit:]]{2})/', $mac, $matches))
  {
    if ($matches[1] == '00' && $matches[2] == '00')
    {
      $mac = hexdec($matches[3]).'.'.hexdec($matches[4]).'.X.X'; // Cisco, why you convert 192.88.99.1 to 0:0:c0:58 (should be c0:58:63:1)
    } else {
      $mac = hexdec($matches[1]).'.'.hexdec($matches[2]).'.'.hexdec($matches[3]).'.'.hexdec($matches[4]);
    }
  }

  return $mac;
}

// DOCME needs phpdoc block
// TESTME needs unit testing
function format_number_short($number, $sf)
{
  // This formats a number so that we only send back three digits plus an optional decimal point.
  // Example: 723.42 -> 723    72.34 -> 72.3    2.23 -> 2.23

  list($whole, $decimal) = explode (".", $number);

  if (strlen($whole) >= $sf || !is_numeric($decimal))
  {
    $number = $whole;
  } elseif(strlen($whole) < $sf) {
    $diff = $sf - strlen($whole);
    $number = $whole .".".substr($decimal, 0, $diff);
  }
  return $number;
}

// DOCME needs phpdoc block
function external_exec($command, $timeout = NULL)
{
  global $exec_status;

  $exec_status = array('command' => $command);
  if (is_numeric($timeout) && $timeout > 0)
  {
    $timeout_usec = $timeout * 1000000;
    $timeout = 0;
  } else {
    // set timeout to null (not to 0!), see stream_select() description
    $timeout = $timeout_usec = NULL;
  }

  $descriptorspec = array(
    //0 => array('pipe', 'r'), // stdin
    1 => array('pipe', 'w'), // stdout
    2 => array('pipe', 'w')  // stderr
  );

  $process = proc_open($command, $descriptorspec, $pipes);
  //stream_set_blocking($pipes[0], 0); // Make stdin/stdout/stderr non-blocking
  stream_set_blocking($pipes[1], 0);
  stream_set_blocking($pipes[2], 0);

  $stdout = $stderr = '';
  $runtime = 0;
  if (is_resource($process))
  {
    $start = microtime(TRUE);
    //while ($status['running'] !== FALSE)
    while (feof($pipes[1]) === FALSE || feof($pipes[2]) === FALSE)
    {
      stream_select(
        $read = array($pipes[1], $pipes[2]),
        $write = NULL,
        $except = NULL,
        $timeout,
        $timeout_usec
      );

      // Read the contents from the buffers
      foreach ($read as $pipe)
      {
        if ($pipe === $pipes[1])
        {
          $stdout .= fread($pipe, 8192);
        }
        else if ($pipe === $pipes[2])
        {
          $stderr .= fread($pipe, 8192);
        }
      }
      $runtime = microtime(TRUE) - $start;

      // Get the status of the process
      $status = proc_get_status($process);

      // Break from this loop if the process exited before timeout
      if (!$status['running'])
      {
        break;
      }
      // Break from this loop if the process exited by timeout
      if ($timeout !== NULL)
      {
        $timeout_usec -= $runtime * 1000000;
        if ($timeout_usec < 0)
        {
          $status['running']  = FALSE;
          $status['exitcode'] = -1;
          break;
        }
      }
    }
    if ($status['running'])
    {
      // Fix sometimes wrong status, wait for 10 milliseconds
      usleep(10000);
      $status = proc_get_status($process);
    }
    $exec_status['exitcode'] = $status['exitcode'];
    $exec_status['stderr']   = rtrim($stderr);
    $stdout = preg_replace('/(?:\n|\r\n|\r)$/D', '', $stdout); // remove last (only) eol
  } else {
    $stdout = FALSE;
    $exec_status['stderr']   = '';
    $exec_status['exitcode'] = -1;
  }
  proc_terminate($process, 9);
  //fclose($pipes[0]);
  fclose($pipes[1]);
  fclose($pipes[2]);

  $exec_status['runtime']  = $runtime;
  $exec_status['stdout']   = $stdout;

  if ($GLOBALS['debug'])
  {
    if ($GLOBALS['config']['snmp']['hide_auth'] && preg_match("/snmp(?:bulk)?(?:get|walk)\s+(?:-(?:t|r|Cr)['\d\s]+){0,3}-v[123]c?\s+/", $command))
    {
      // Hide snmp auth params from debug cmd out,
      // for help users who want send debug output to developers
      $pattern = "/\s+(-[cuxXaA])\s*(?:'.+?')(@\d+)?/";
      $command = preg_replace($pattern, ' \1 ***\2', $command);
    }
    print_message('CMD[%y'.$command.'%n]', 'color');
    print_message('EXITCODE['.($exec_status['exitcode'] > 0 ? '%r' : '%g').$exec_status['exitcode'].'%n]', 'color');
    print_message('RUNTIME['.($runtime > 59 ? '%r' : '%g').round($runtime, 4).'s%n], ', 'color');
    print_message("STDOUT[\n".$stdout."\n]", NULL, FALSE);
  }

  return $stdout;
}

// DOCME needs phpdoc block
// TESTME needs unit testing
function is_cli()
{
  global $cache;

  if (isset($cache['is_cli']))
  {
    return $cache['is_cli'];
  }

  if (php_sapi_name() == 'cli' && empty($_SERVER['REMOTE_ADDR']))
  {
    $cache['is_cli'] = TRUE;
  } else {
    $cache['is_cli'] = FALSE;
  }

  return $cache['is_cli'];
}

// Detect if script runned from crontab
// DOCME needs phpdoc block
// TESTME needs unit testing
function is_cron()
{
  $cron = is_cli() && !isset($_SERVER['TERM']);
  // For more accurate check if STDOUT exist (but this requires posix extension)
  if ($cron && check_extension_exists('posix'))
  {
    $cron = $cron && !posix_isatty(STDOUT);
  }
  return $cron;
}

// DOCME needs phpdoc block
// TESTME needs unit testing
function print_prompt($text, $default_yes = FALSE)
{
  if (is_cli())
  {
    if (check_extension_exists('posix') && !posix_isatty(STDOUT))
    {
      // If now not have interactive TTY skip any prompts, return default
      $return = TRUE && $default_yes;
    }

    $question = ($default_yes ? 'Y/n' : 'y/N');
    echo trim($text), " [$question]: ";
    $handle = fopen ('php://stdin', 'r');
    $line   = strtolower(trim(fgets($handle, 3)));
    fclose($handle);
    if ($default_yes)
    {
      $return = ($line === 'no' || $line === 'n');
    } else {
      $return = ($line === 'yes' || $line === 'y');
    }
  } else {
    // Here placeholder for web prompt
    $return = TRUE && $default_yes;
  }

  return $return;
}

// DOCME needs phpdoc block
// TESTME needs unit testing
function print_debug($text)
{
  if ($GLOBALS['debug'])
  {
    print_message($text, 'debug');
  }
}

// DOCME needs phpdoc block
// TESTME needs unit testing
function print_error($text)
{
  print_message($text, 'error');
}

// DOCME needs phpdoc block
// TESTME needs unit testing
function print_warning($text)
{
  print_message($text, 'warning');
}

// DOCME needs phpdoc block
// TESTME needs unit testing
function print_success($text)
{
  print_message($text, 'success');
}

// DOCME needs phpdoc block
// TESTME needs unit testing
function print_message($text, $type='', $strip = TRUE)
{
  global $config;

  $type = trim(strtolower($type));
  switch ($type)
  {
    case 'success':
      $color = array('cli'       => '%g',                   // green
                     'cli_color' => FALSE,                  // by default cli coloring disabled
                     'class'     => 'alert alert-success'); // green
      $icon  = 'oicon-tick-circle';
      break;
    case 'warning':
      $color = array('cli'       => '%b',                   // blue
                     'cli_color' => FALSE,                  // by default cli coloring disabled
                     'class'     => 'alert');               // yellow
      $icon  = 'oicon-bell';
      break;
    case 'error':
      $color = array('cli'       => '%r',                   // red
                     'cli_color' => FALSE,                  // by default cli coloring disabled
                     'class'     => 'alert alert-error');   // red
      $icon  = 'oicon-exclamation-red';
      break;
    case 'debug':
      $color = array('cli'       => '%r',                   // red
                     'cli_color' => FALSE,                  // by default cli coloring disabled
                     'class'     => 'alert alert-error');   // red
      $icon  = 'oicon-exclamation-red';
      break;
    case 'color':
      $color = array('cli'       => '',                     // none
                     'cli_color' => TRUE,                   // allow using coloring
                     'class'     => 'alert alert-info');    // blue
      $icon  = 'oicon-information';
      break;
    default:
      $color = array('cli'       => '%W',                   // bold
                     'cli_color' => FALSE,                  // by default cli coloring disabled
                     'class'     => 'alert alert-info');    // blue
      $icon  = 'oicon-information';
      break;
  }

  if (is_cli())
  {
    include_once($config['install_dir'] . "/includes/pear/Console/Color2.php");

    if ($strip) { $text = strip_tags($text); }
    $msg  = new Console_Color2();
    print $msg->convert($color['cli'].$text."%n\n", $color['cli_color']);
  } else {
    if ($text === '') { return NULL; } // Do not web output if the string is empty
    $msg = '<div class="'.$color['class'].'">';
    if ($type != 'warning' && $type != 'error')
    {
      $msg .= '<button type="button" class="close" data-dismiss="alert">&times;</button>';
    }
    $msg .= '
      <div class="pull-left" style="padding:0 5px 0 0"><i class="'.$icon.'"></i></div>
      <div>'.nl2br($text).'</div>
    </div>';
    echo($msg);
  }
}

// Check if php extension exist, than warn or fail
// DOCME needs phpdoc block
// TESTME needs unit testing
function check_extension_exists($extension, $text = FALSE, $fatal = FALSE)
{
  $exist = FALSE;
  $extension = strtolower($extension);
  $extension_functions = array(
    'ldap'     => 'ldap_connect',
    'mbstring' => 'mb_detect_encoding',
    'mcrypt'   => 'mcrypt_encrypt',
    'posix'    => 'posix_isatty',
    'session'  => 'session_name',
    'svn'      => 'svn_log'
  );

  if (isset($extension_functions[$extension]))
  {
    $exist = @function_exists($extension_functions[$extension]);
  } else {
    $exist = @extension_loaded($extension);
  }

  if (!$exist)
  {
    // Print error (only if $text not equals to FALSE)
    if ($text === '' || $text === TRUE)
    {
      // Generic message
      print_error("The extension '$extension' is missing. Please check your PHP configuration.");
    }
    elseif ($text !== FALSE)
    {
      // Custom message
      print_error("The extension '$extension' is missing. $text");
    } else {
      // Debug message
      print_debug("The extension '$extension' is missing. Please check your PHP configuration.");
    }

    // Exit if $fatal set to TRUE
    if ($fatal) { exit; }
  }

  return $exist;
}

// TESTME needs unit testing
/**
 * Sign function
 *
 * This function extracts the sign of the number.
 * Returns -1 (negative), 0 (zero), 1 (positive)
 *
 * @param integer $int
 * @return integer
 */
function sgn($int)
{
  if ($int < 0)
  {
    return -1;
  } elseif ($int == 0) {
    return 0;
  } else {
    return 1;
  }
}

// DOCME needs phpdoc block
// TESTME needs unit testing
// MOVEME to includes/functions.inc.php
function get_sensor_rrd($device, $sensor)
{
  global $config;

  # For IPMI, sensors tend to change order, and there is no index, so we prefer to use the description as key here.
  if ($config['os'][$device['os']]['sensor_descr'] || $sensor['poller_type'] == "ipmi")
  {
    $rrd_file = "sensor-".$sensor['sensor_class']."-".$sensor['sensor_type']."-".$sensor['sensor_descr'] . ".rrd";
  } else {
    $rrd_file = "sensor-".$sensor['sensor_class']."-".$sensor['sensor_type']."-".$sensor['sensor_index'] . ".rrd";
  }

  return($rrd_file);
}

// Get port array by ID (using cache)
// DOCME needs phpdoc block
// TESTME needs unit testing
// MOVEME to includes/functions.inc.php
function get_port_by_id_cache($port_id)
{
  return get_entity_by_id_cache('port', $port_id);
}

// Get port array by ID (with port state)
// NOTE get_port_by_id(ID) != get_port_by_id_cache(ID)
// DOCME needs phpdoc block
// TESTME needs unit testing
// MOVEME to includes/functions.inc.php
function get_port_by_id($port_id)
{
  if (is_numeric($port_id))
  {
    $port = dbFetchRow("SELECT * FROM `ports` LEFT JOIN `ports-state` ON `ports`.`port_id` = `ports-state`.`port_id`  WHERE `ports`.`port_id` = ?", array($port_id));
  }

  if (is_array($port))
  {
    $port['port_id'] = $port_id; // It corrects the situation, when `ports-state` is empty
    humanize_port($port);
    return $port;
  }

  return FALSE;
}

// Get port array by ifIndex (using cache)
// DOCME needs phpdoc block
// TESTME needs unit testing
// MOVEME to includes/functions.inc.php
function get_port_by_index_cache($device_id, $ifIndex)
{
  global $cache;

  if (isset($cache['port_index'][$device_id][$ifIndex]) && is_numeric($cache['port_index'][$device_id][$ifIndex]))
  {
    $id = $cache['port_index'][$device_id][$ifIndex];
  } else {
    $id = dbFetchCell("SELECT `port_id` FROM `ports` WHERE `device_id` = ? AND `ifIndex` = ? LIMIT 1", array($device_id, $ifIndex));
    if (is_numeric($id)) { $cache['port_index'][$device_id][$ifIndex] = $id; }
  }

  $port = get_port_by_id_cache($id);
  if (is_array($port)) { return $port; }

  return FALSE;
}

// Get port array by ifIndex
// DOCME needs phpdoc block
// TESTME needs unit testing
// MOVEME to includes/functions.inc.php
function get_port_by_ifIndex($device_id, $ifIndex)
{
  $port = dbFetchRow("SELECT * FROM `ports` WHERE `device_id` = ? AND `ifIndex` = ? LIMIT 1", array($device_id, $ifIndex));

  if (is_array($port))
  {
    humanize_port($port);
    return $port;
  }

  return FALSE;
}

// Get port ID by ifDescr (i.e. 'TenGigabitEthernet1/1') or ifName (i.e. 'Te1/1')
// DOCME needs phpdoc block
// TESTME needs unit testing
// MOVEME to includes/functions.inc.php
function get_port_id_by_ifDescr($device_id, $ifDescr)
{
  $port_id = dbFetchCell("SELECT `port_id` FROM `ports` WHERE `device_id` = ? AND (`ifDescr` = ? OR `ifName` = ?) LIMIT 1", array($device_id, $ifDescr, $ifDescr));

  if (is_numeric($port_id))
  {
    return $port_id;
  } else {
    return FALSE;
  }
}

// Get port ID by ifAlias (interface description)
// DOCME needs phpdoc block
// TESTME needs unit testing
// MOVEME to includes/functions.inc.php
function get_port_id_by_ifAlias($device_id, $ifAlias)
{
  $port_id = dbFetchCell("SELECT `port_id` FROM `ports` WHERE `device_id` = ? AND `ifAlias` = ? LIMIT 1", array($device_id, $ifAlias));

  if (is_numeric($port_id))
  {
    return $port_id;
  } else {
    return FALSE;
  }
}

// DOCME needs phpdoc block
// TESTME needs unit testing
// MOVEME to includes/functions.inc.php
function get_bill_by_id($bill_id)
{
  $bill = dbFetchRow("SELECT * FROM `bills` WHERE `bill_id` = ?", array($bill_id));

  if (is_array($bill))
  {
    return $bill;
  } else {
    return FALSE;
  }

}

// Get port ID by customer params (see http://www.observium.org/wiki/Interface_Description_Parsing)
// DOCME needs phpdoc block
// TESTME needs unit testing
// MOVEME to includes/functions.inc.php
function get_port_id_by_customer($customer)
{
  $where = ' WHERE 1';
  if (is_array($customer))
  {
    foreach ($customer as $var => $value)
    {
      if ($value != '')
      {
        switch ($var)
        {
          case 'device':
          case 'device_id':
            $where .= ' AND `device_id` = ?';
            $param[] = $value;
            break;
          case 'type':
          case 'descr':
          case 'circuit':
          case 'speed':
          case 'notes':
            $where .= ' AND `port_descr_'.$var.'` = ?';
            $param[] = $value;
            break;
        }
      }
    }
  } else {
    return FALSE;
  }

  $query = 'SELECT `port_id` FROM `ports` '.$where.' LIMIT 1';
  $port_id = dbFetchCell($query, $param);

  if (is_numeric($port_id))
  {
    return $port_id;
  } else {
    return FALSE;
  }
}

// DOCME needs phpdoc block
// TESTME needs unit testing
// MOVEME to includes/functions.inc.php
function get_all_devices($device, $type = "")
{
  global $cache;

  // FIXME needs access control checks!
  // FIXME respect $type (server, network, etc) -- needs an array fill in topnav.

  if (isset($cache['devices']['hostname']))
  {
    $devices = array_keys($cache['devices']['hostname']);
  }
  else
  {
    foreach (dbFetchRows("SELECT `hostname` FROM `devices`") as $data)
    {
      $devices[] = $data['hostname'];
    }
  }

  return $devices;
}

// DOCME needs phpdoc block
// TESTME needs unit testing
// RENAME to get_device_icon()
// MOVEME to html/includes/functions.inc.php
function getImage($device)
{
  global $config;

  $device['os'] = strtolower($device['os']);

  if ($device['icon'] && file_exists($config['html_dir'] . "/images/os/" . $device['icon'] . ".png"))
  {
    $image = '<img src="' . $config['base_url'] . '/images/os/' . $device['icon'] . '.png" alt="" />';
  }
  elseif ($config['os'][$device['os']]['icon'] && file_exists($config['html_dir'] . "/images/os/" . $config['os'][$device['os']]['icon'] . ".png"))
  {
    $image = '<img src="' . $config['base_url'] . '/images/os/' . $config['os'][$device['os']]['icon'] . '.png" alt="" />';
  } else {
    if (file_exists($config['html_dir'] . '/images/os/' . $device['os'] . '.png'))
    {
      $image = '<img src="' . $config['base_url'] . '/images/os/' . $device['os'] . '.png" alt="" />';
    }
    if ($device['os'] == "linux")
    {
      $distro = strtolower(trim($device['distro']));
      if (file_exists($config['html_dir'] . "/images/os/".safename($distro) . ".png"))
      {
        $image = '<img src="' . $config['base_url'] . '/images/os/' . htmlentities($distro) . '.png" alt="" />';
      }
    }
  }

  return $image;
}

// DOCME needs phpdoc block
// TESTME needs unit testing
// MOVEME to includes/functions.inc.php
function get_application_by_id($application_id)
{
  if (is_numeric($application_id))
  {
    $application = dbFetchRow("SELECT * FROM `applications` WHERE `app_id` = ?", array($application_id));
  }
  if (is_array($application))
  {
    return $application;
  } else {
    return FALSE;
  }
}

// DOCME needs phpdoc block
// TESTME needs unit testing
// MOVEME to includes/functions.inc.php
function get_sensor_by_id($sensor_id)
{
  if (is_numeric($sensor_id))
  {
    $sensor = dbFetchRow("SELECT * FROM `sensors` WHERE `sensor_id` = ?", array($sensor_id));
  }
  if (is_array($sensor))
  {
    return $sensor;
  } else {
    return FALSE;
  }
}

// DOCME needs phpdoc block
// TESTME needs unit testing
// MOVEME to includes/functions.inc.php
function get_device_id_by_port_id($port_id)
{
  if (is_numeric($port_id))
  {
    $device_id = dbFetchCell("SELECT `device_id` FROM `ports` WHERE `port_id` = ?", array($port_id));
  }
  if (is_numeric($device_id))
  {
    return $device_id;
  } else {
    return FALSE;
  }
}

// DOCME needs phpdoc block
// TESTME needs unit testing
// MOVEME to includes/functions.inc.php
function get_device_id_by_app_id($app_id)
{
  if (is_numeric($app_id))
  {
    $device_id = dbFetchCell("SELECT `device_id` FROM `applications` WHERE `app_id` = ?", array($app_id));
  }
  if (is_numeric($device_id))
  {
    return $device_id;
  } else {
    return FALSE;
  }
}

// DOCME needs phpdoc block
// TESTME needs unit testing
function ifclass($ifOperStatus, $ifAdminStatus)
{
  $ifclass = "interface-upup";
  if ($ifAdminStatus == "down") { $ifclass = "gray"; }
  if ($ifAdminStatus == "up")
  {
    if ($ifOperStatus == "down") { $ifclass = "red"; }
    if ($ifOperStatus == "lowerLayerDown") { $ifclass = "orange"; }
    if ($ifOperStatus == "monitoring") { $ifclass = "green"; }
    if ($ifOperStatus == "up") { $ifclass = ""; }
  }

  return $ifclass;
}

// DOCME needs phpdoc block
// TESTME needs unit testing
// MOVEME to includes/functions.inc.php
function device_by_name($name, $refresh = 0)
{
  // FIXME - cache name > id too.
  return device_by_id_cache(getidbyname($name), $refresh);
}

// DOCME needs phpdoc block
// TESTME needs unit testing
// MOVEME to includes/functions.inc.php
function accesspoint_by_id($ap_id, $refresh = '0')
{
  $ap = dbFetchRow("SELECT * FROM `accesspoints` WHERE `accesspoint_id` = ?", array($ap_id));

  return $ap;
}

// DOCME needs phpdoc block
// TESTME needs unit testing
// MOVEME to includes/functions.inc.php
function device_by_id_cache($device_id, $refresh = '0')
{
  global $cache;

  if (!$refresh && isset($cache['devices']['id'][$device_id]) && is_array($cache['devices']['id'][$device_id]))
  {
    $device = $cache['devices']['id'][$device_id];
  } else {
    $device = dbFetchRow("SELECT * FROM `devices` WHERE `device_id` = ?", array($device_id));
    humanize_device($device);
    $cache['devices']['id'][$device_id] = $device;
  }

  return $device;
}

// DOCME needs phpdoc block
// TESTME needs unit testing
function truncate($substring, $max = 50, $rep = '...')
{
  if (strlen($substring) < 1) { $string = $rep; } else { $string = $substring; }
  $leave = $max - strlen ($rep);
  if (strlen($string) > $max) { return substr_replace($string, $rep, $leave); } else { return $string; }
}

// DOCME needs phpdoc block
// TESTME needs unit testing
// FIXME mysqli instead? this is in all our required versions right?
function mres($string)
{ // short function wrapper because the real one is stupidly long and ugly. aesthetics.
  return mysql_real_escape_string($string);
}

// DOCME needs phpdoc block
// TESTME needs unit testing
// MOVEME to includes/functions.inc.php
function getifhost($id)
{
  return dbFetchCell("SELECT `device_id` from `ports` WHERE `port_id` = ?", array($id));
}

// DOCME needs phpdoc block
// TESTME needs unit testing
// MOVEME to includes/functions.inc.php
function gethostbyid($id)
{
  global $cache;

  if (isset($cache['devices']['id'][$id]['hostname']))
  {
    $hostname = $cache['devices']['id'][$id]['hostname'];
  }
  else
  {
    $hostname = dbFetchCell("SELECT `hostname` FROM `devices` WHERE `device_id` = ?", array($id));
  }

  return $hostname;
}

// Return random string with optional character list
// DOCME needs phpdoc block
// TESTME needs unit testing
function generate_random_string($max = 16, $characters = NULL)
{
  if (!$characters || !is_string($characters))
  {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
  }

  $randstring = '';
  $length = strlen($characters) - 1;

  for ($i = 0; $i < $max; $i++)
  {
    $randstring .= $characters[mt_rand(0, $length)];
  }

  return $randstring;
}

// Backward compatible random string generator
// DOCME needs phpdoc block
// TESTME needs unit testing
function strgen($length = 16)
{
  return generate_random_string($length);
}

// DOCME needs phpdoc block
// TESTME needs unit testing
// MOVEME to includes/functions.inc.php
function getpeerhost($id)
{
  return dbFetchCell("SELECT `device_id` from `bgpPeers` WHERE `bgpPeer_id` = ?", array($id));
}

// DOCME needs phpdoc block
// TESTME needs unit testing
// MOVEME to includes/functions.inc.php
function getifindexbyid($id)
{
  return dbFetchCell("SELECT `ifIndex` FROM `ports` WHERE `port_id` = ?", array($id));
}

// DOCME needs phpdoc block
// TESTME needs unit testing
// MOVEME to includes/functions.inc.php
function getifbyid($id)
{
  return dbFetchRow("SELECT * FROM `ports` WHERE `port_id` = ?", array($id));
}

// DOCME needs phpdoc block
// TESTME needs unit testing
// MOVEME to includes/functions.inc.php
function getifdescrbyid($id)
{
  return dbFetchCell("SELECT `ifDescr` FROM `ports` WHERE `port_id` = ?", array($id));
}

// DOCME needs phpdoc block
// TESTME needs unit testing
// MOVEME to includes/functions.inc.php
function getidbyname($hostname)
{
  global $cache;

  if (isset($cache['devices']['hostname'][$hostname]))
  {
    $id = $cache['devices']['hostname'][$hostname];
  } else
  {
    $id = dbFetchCell("SELECT `device_id` FROM `devices` WHERE `hostname` = ?", array($hostname));
  }

  return $id;
}

// DOCME needs phpdoc block
// TESTME needs unit testing
// MOVEME to includes/functions.inc.php
function gethostosbyid($id)
{
  global $cache;

  if (isset($cache['devices']['id'][$id]['os']))
  {
    $os = $cache['devices']['id'][$id]['os'];
  }
  else
  {
    $os = dbFetchCell("SELECT `os` FROM `devices` WHERE `device_id` = ?", array($id));
  }

  return $os;
}

// DOCME needs phpdoc block
// TESTME needs unit testing
function safename($name)
{
  return preg_replace('/[^a-zA-Z0-9,._\-]/', '_', $name);
}

// DOCME needs phpdoc block
// TESTME needs unit testing
function zeropad($num, $length = 2)
{
  return str_pad($num, $length, '0', STR_PAD_LEFT);
}

// DOCME needs phpdoc block
// TESTME needs unit testing
// MOVEME to includes/functions.inc.php
function set_dev_attrib($device, $attrib_type, $attrib_value)
{
  if (dbFetchCell("SELECT COUNT(*) FROM devices_attribs WHERE `device_id` = ? AND `attrib_type` = ?", array($device['device_id'],$attrib_type)))
  {
    $return = dbUpdate(array('attrib_value' => $attrib_value), 'devices_attribs', 'device_id=? and attrib_type=?', array($device['device_id'], $attrib_type));
  }
  else
  {
    $return = dbInsert(array('device_id' => $device['device_id'], 'attrib_type' => $attrib_type, 'attrib_value' => $attrib_value), 'devices_attribs');
  }
  return $return;
}

// DOCME needs phpdoc block
// TESTME needs unit testing
// MOVEME to includes/functions.inc.php
function get_dev_attribs($device_id)
{
  $attribs = array();
  foreach (dbFetchRows("SELECT * FROM devices_attribs WHERE `device_id` = ?", array($device_id)) as $entry)
  {
    $attribs[$entry['attrib_type']] = $entry['attrib_value'];
  }
  return $attribs;
}

// DOCME needs phpdoc block
// TESTME needs unit testing
// RENAME to get_device_entphysical_state
// MOVEME to includes/functions.inc.php
function get_dev_entity_state($device)
{
  $state = array();
  foreach (dbFetchRows("SELECT * FROM `entPhysical-state` WHERE `device_id` = ?", array($device)) as $entity)
  {
    $state['group'][$entity['group']][$entity['entPhysicalIndex']][$entity['subindex']][$entity['key']] = $entity['value'];
    $state['index'][$entity['entPhysicalIndex']][$entity['subindex']][$entity['group']][$entity['key']] = $entity['value'];
  }

  return $state;
}

// DOCME needs phpdoc block
// TESTME needs unit testing
// MOVEME to includes/functions.inc.php
function get_dev_attrib($device, $attrib_type)
{
  if ($row = dbFetchRow("SELECT attrib_value FROM devices_attribs WHERE `device_id` = ? AND `attrib_type` = ?", array($device['device_id'], $attrib_type)))
  {
    return $row['attrib_value'];
  }
  else
  {
    return NULL;
  }
}

// DOCME needs phpdoc block
// Return cached MIBs array available for device (from os definitions)
// TESTME needs unit testing
// MOVEME to includes/functions.inc.php
function get_device_mibs($device)
{
  global $config, $cache;

  if (is_numeric($device))
  {
    $device_id = $device;
    $device    = device_by_id_cache($device_id);
  } else {
    $device_id = $device['device_id'];
  }

  if (!(isset($cache['devices']['mibs'][$device_id]) && is_array($cache['devices']['mibs'][$device_id])))
  {
    $mibs = array_unique(array_merge((array)$config['os_group'][$config['os'][$device['os']]['group']]['mibs'],
                                     (array)$config['os'][$device['os']]['mibs']));
    $cache['devices']['mibs'][$device_id] = $mibs;
  }

  return $cache['devices']['mibs'][$device_id];
}

// Check if MIB available and permitted for device
// if $check_permissions is TRUE, check permissions by config option $config['mibs'][$mib]
// and from the enable/disable panel in the device configuration in the web interface
// DOCME needs phpdoc block
// TESTME needs unit testing
// MOVEME to includes/functions.inc.php
function is_device_mib($device, $mib, $check_permissions = TRUE)
{
  global $config;

  $mib_permitted = in_array($mib, get_device_mibs($device)); // Check if mib available for device
  if ($check_permissions && $mib_permitted && (!isset($config['mibs'][$mib]) || $config['mibs'][$mib]))
  {
    // Check if MIB permitted by config
    $mib_permitted = $mib_permitted && (!isset($config['mibs'][$mib]) || $config['mibs'][$mib]);

    // Check if MIB disabled by web interface or polling process
    $attribs = get_dev_attribs($device['device_id']);
    $mib_permitted = $mib_permitted && (!isset($attribs['mib_'.$mib]) || $attribs['mib_'.$mib] != 0);
  }

  return $mib_permitted;
}

// DOCME needs phpdoc block
// TESTME needs unit testing
// MOVEME to includes/functions.inc.php
function del_dev_attrib($device, $attrib_type)
{
  return dbDelete('devices_attribs', "`device_id` = ? AND `attrib_type` = ?", array($device['device_id'], $attrib_type));
}

// DOCME needs phpdoc block
// TESTME needs unit testing
function formatRates($value, $round = '2', $sf = '3')
{
   $value = format_si($value, $round, $sf) . "bps";
   return $value;
}

// DOCME needs phpdoc block
// TESTME needs unit testing
function formatStorage($value, $round = '2', $sf = '3')
{
   $value = format_bi($value, $round, $sf) . 'B';
   return $value;
}

// DOCME needs phpdoc block
// TESTME needs unit testing
function format_si($value, $round = '2', $sf = '3')
{
  if ($value < "0")
  {
    $neg = 1;
    $value = $value * -1;
  }

  if ($value >= "0.1")
  {
    $sizes = Array('', 'k', 'M', 'G', 'T', 'P', 'E');
    $ext = $sizes[0];
    for ($i = 1; (($i < count($sizes)) && ($value >= 1000)); $i++) { $value = $value / 1000; $ext  = $sizes[$i]; }
  }
  else
  {
    $sizes = Array('', 'm', 'u', 'n');
    $ext = $sizes[0];
    for ($i = 1; (($i < count($sizes)) && ($value != 0) && ($value <= 0.1)); $i++) { $value = $value * 1000; $ext  = $sizes[$i]; }
  }

  if ($neg) { $value = $value * -1; }

  return format_number_short(round($value, $round),$sf).$ext;
}

// DOCME needs phpdoc block
// TESTME needs unit testing
function format_bi($value, $round = '2', $sf = '3')
{
  if ($value < "0")
  {
    $neg = 1;
    $value = $value * -1;
  }
  $sizes = Array('', 'k', 'M', 'G', 'T', 'P', 'E');
  $ext = $sizes[0];
  for ($i = 1; (($i < count($sizes)) && ($value >= 1024)); $i++) { $value = $value / 1024; $ext  = $sizes[$i]; }

  if ($neg) { $value = $value * -1; }

  return format_number_short(round($value, $round), $sf).$ext;
}

// DOCME needs phpdoc block
// TESTME needs unit testing
function format_number($value, $base = '1000', $round=2, $sf=3)
{
  if ($base == '1000')
  {
    return format_si($value, $round, $sf);
  } else {
    return format_bi($value, $round, $sf);
  }
}

/**
 * Is Valid Hostname
 *
 * The Internet standards (Request for Comments) for protocols mandate that
 * component hostname labels may contain only the ASCII letters 'a' through 'z'
 * (in a case-insensitive manner), the digits '0' through '9', and the hyphen
 * ('-'). The original specification of hostnames in RFC 952, mandated that
 * labels could not start with a digit or with a hyphen, and must not end with
 * a hyphen. However, a subsequent specification (RFC 1123) permitted hostname
 * labels to start with digits. No other symbols, punctuation characters, or
 * white space are permitted. While a hostname may not contain other characters,
 * such as the underscore character (_), other DNS names may contain the underscore
 *
 * @param string $hostname
 * @return bool
 */
function is_valid_hostname($hostname)
{
  // check for invalid starting characters
  if (preg_match('/^[_.-]/', $hostname))
  {
    return FALSE;
  } else {
    return ctype_alnum(str_replace('_','',str_replace('-','',str_replace('.','',$hostname))));
  }
}

// get $host record from /etc/hosts
// DOCME needs phpdoc block
// TESTME needs unit testing
function ipFromEtcHosts($host)
{
  try {
    foreach (new SplFileObject('/etc/hosts') as $line)
    {
      $d = preg_split('/\s/', $line, -1, PREG_SPLIT_NO_EMPTY);
      if (empty($d) || substr(reset($d), 0, 1) == '#') { continue; }
      $ip = array_shift($d);
      $hosts = array_map('strtolower', $d);
      if (in_array(strtolower($host), $hosts)) { return $ip; }
    }
  }
  catch (Exception $e)
  {
    print_warning("Could not open the file /etc/hosts! This file should be world readable, also check if not enabled SELinux in enforcing mode.");
  }

  return FALSE;
}

// Same as gethostbyname(), but work with both IPv4 and IPv6
// Get the IPv4 or IPv6 address corresponding to a given Internet hostname
// By default return IPv4 address (A record) if exist,
// else IPv6 address (AAAA record) if exist.
// For get only IPv6 record use gethostbyname6($hostname, FALSE)
// DOCME needs phpdoc block
// TESTME needs unit testing
function gethostbyname6($host, $try_a = TRUE)
{
  // get AAAA record for $host
  // if $try_a is true, if AAAA fails, it tries for A
  // the first match found is returned
  // otherwise returns FALSE

  $dns = gethostbynamel6($host, $try_a);
  if ($dns == FALSE)
  {
    return FALSE;
  } else {
    return $dns[0];
  }
}

// Same as gethostbynamel(), but work with both IPv4 and IPv6
// By default returns both IPv4/6 addresses (A and AAAA records),
// for get only IPv6 addresses use gethostbynamel6($hostname, FALSE)
// DOCME needs phpdoc block
// TESTME needs unit testing
function gethostbynamel6($host, $try_a = TRUE)
{
  // get AAAA records for $host,
  // if $try_a is true, if AAAA fails, it tries for A
  // results are returned in an array of ips found matching type
  // otherwise returns FALSE

  $ip6 = array();
  $ip4 = array();

  // First try /etc/hosts
  $etc = ipFromEtcHosts($host);

  if ($try_a == TRUE)
  {
    if ($etc && strstr($etc, '.')) { $ip4[] = $etc; }
    $dns = dns_get_record($host, DNS_A + DNS_AAAA);
  } else {
    if ($etc && strstr($etc, ':')) { $ip6[] = $etc; }
    $dns = dns_get_record($host, DNS_AAAA);
  }

  foreach ($dns as $record)
  {
    switch ($record['type'])
    {
      case 'A':
        $ip4[] = $record['ip'];
        break;
      case 'AAAA':
        $ip6[] = $record['ipv6'];
        break;
    }
  }

  if ($try_a && count($ip4))
  {
    // Merge ipv4 & ipv6
    $ip6 = array_merge($ip4, $ip6);
  }

  if (count($ip6))
  {
    return $ip6;
  }

  return FALSE;
}

// Get hostname by IP (both IPv4/IPv6)
// Return PTR or FALSE
// DOCME needs phpdoc block
// TESTME needs unit testing
function gethostbyaddr6($ip)
{
  include_once('Net/DNS2.php');
  include_once('Net/DNS2/RR/PTR.php');

  $ptr = FALSE;
  $resolver = new Net_DNS2_Resolver();
  try
  {
    $response = $resolver->query($ip, 'PTR');
    if ($response)
    {
      $ptr = $response->answer[0]->ptrdname;
    }
  } catch (Net_DNS2_Exception $e) {}

  return $ptr;
}

// DOCME needs phpdoc block
// TESTME needs unit testing
// DEPRECATED
function add_service($device, $service, $descr)
{
  $insert = array('device_id' => $device['device_id'], 'service_ip' => $device['hostname'], 'service_type' => $service,
                  'service_changed' => array('UNIX_TIMESTAMP(NOW())'), 'service_desc' => $descr, 'service_param' => "", 'service_ignore' => "0");

  echo dbInsert($insert, 'services');
}

// DOCME needs phpdoc block
// TESTME needs unit testing
// MOVEME to includes/functions.inc.php
function get_port_rrdfilename($port, $suffix = NULL, $fullpath = FALSE)
{
  global $config;

  $device = device_by_id_cache($port['device_id']);

  $device_identifier = strtolower($config['os'][$device['os']]['port_rrd_identifier']);

  // default to ifIndex
  $this_port_identifier = $port['ifIndex'];

  if ($device_identifier == "ifname" && $port['ifName'] != "")
  {
    $this_port_identifier = strtolower(str_replace("/", "-", $port['ifName']));
  }

  if ($suffix == "")
  {
    $filename = "port-" . $this_port_identifier . ".rrd";
  }
  else
  {
    $filename = "port-" . $this_port_identifier . "-" . $suffix . ".rrd";
  }

  return ($fullpath ? get_rrd_path($device, $filename) : $filename);
}

// DOCME needs phpdoc block
// TESTME needs unit testing
function get_http_request($request)
{
  global $config, $debug;

  $response = '';

  $opts = array('http' => array('timeout' => '20'));
  if (isset($config['http_proxy']) && $config['http_proxy'])
  {
    $opts['http']['proxy'] = 'tcp://' . $config['http_proxy'];
    $opts['http']['request_fulluri'] = TRUE;
  }

  // Basic proxy auth
  if (isset($config['proxy_user']) && $config['proxy_user'] && isset($config['proxy_password']))
  {
    $auth = base64_encode($config['proxy_user'].':'.$config['proxy_password']);
    $opts['http']['header'] = 'Proxy-Authorization: Basic '.$auth;
  }

  $context = stream_context_create($opts);
  $response = file_get_contents($request, FALSE, $context);

  return $response;
}

/**
 * Format date string.
 *
 * This function convert date/time string to format from
 * config option $config['timestamp_format'].
 * If date/time not detected in string, function return original string.
 * Example conversions to format 'd-m-Y H:i':
 * '2012-04-18 14:25:01' -> '18-04-2012 14:25'
 * 'Star wars' -> 'Star wars'
 *
 * @param string $str
 * @return string
 */
// TESTME needs unit testing
function format_timestamp($str)
{
  global $config;

  if (($timestamp = strtotime($str)) === FALSE)
  {
    return $str;
  } else {
    return date($config['timestamp_format'], $timestamp);
  }
}

/**
 * Format unixtime.
 *
 * This function convert date/time string to format from
 * config option $config['timestamp_format'].
 * Can take an optional format parameter, which is passed to date();
 *
 * @param string $time
 * @param string $format
 * @return string
 */
// TESTME needs unit testing
function format_unixtime($time, $format = NULL)
{
  global $config;

  if ($format != NULL)
  {
    return date($format, $time);
  } else {
    return date($config['timestamp_format'], $time);
  }
}

/**
 * Convert age string to seconds.
 *
 * This function convert age string to seconds.
 * If age is numeric than it in seconds.
 * The supplied age accepts values such as 31d, 240h, 1.5d etc.
 * Accepted age scales are:
 * y (years), M (months), w (weeks), d (days), h (hours), m (minutes), s (seconds).
 * NOTE, for month use CAPITAL 'M'
 * With wrong and negative returns 0
 *
 * '3y 4M 6w 5d 3h 1m 3s' -> 109191663
 * '3y4M6w5d3h1m3s'       -> 109191663
 * '1.5w'                 -> 907200
 * -886732     -> 0
 * 'Star wars' -> 0
 *
 * @param string $age
 * @return int
 */
// TESTME needs unit testing
function age_to_seconds($age)
{
  $age = trim($age);

  if (is_numeric($age))
  {
    $age = (int)$age;
    if ($age > 0)
    {
      return $age;
    } else {
      return 0;
    }
  }

  $pattern = '/^';
  $pattern .= '(?:(?P<years>\d+(?:\.\d)*)y\ *)*';   // y (years)
  $pattern .= '(?:(?P<months>\d+(?:\.\d)*)M\ *)*';  // M (months)
  $pattern .= '(?:(?P<weeks>\d+(?:\.\d)*)w\ *)*';   // w (weeks)
  $pattern .= '(?:(?P<days>\d+(?:\.\d)*)d\ *)*';    // d (days)
  $pattern .= '(?:(?P<hours>\d+(?:\.\d)*)h\ *)*';   // h (hours)
  $pattern .= '(?:(?P<minutes>\d+(?:\.\d)*)m\ *)*'; // m (minutes)
  $pattern .= '(?:(?P<seconds>\d+(?:\.\d)*)s)*';    // s (seconds)
  $pattern .= '$/';

  if (!empty($age) && preg_match($pattern, $age, $matches))
  {
    $seconds  = $matches['seconds'];
    $seconds += $matches['years'] * 31536000; // year  = 365 * 24 * 60 * 60
    $seconds += $matches['months'] * 2628000; // month = year / 12
    $seconds += $matches['weeks']   * 604800; // week  = 7 days
    $seconds += $matches['days']     * 86400; // day   = 24 * 60 * 60
    $seconds += $matches['hours']     * 3600; // hour  = 60 * 60
    $seconds += $matches['minutes']     * 60; // minute = 60
    $age = (int)$seconds;

    return $age;
  }

  return 0;
}

/**
 * Convert age string to unixtime.
 *
 * This function convert age string to unixtime.
 *
 * Description and notes same as for age_to_seconds()
 *
 * Additional check if $age more than minimal age in seconds
 *
 * '3y 4M 6w 5d 3h 1m 3s' -> time() - 109191663
 * '3y4M6w5d3h1m3s'       -> time() - 109191663
 * '1.5w'                 -> time() - 907200
 * -886732     -> 0
 * 'Star wars' -> 0
 *
 * @param string $age
 * @return int
 */
// TESTME needs unit testing
function age_to_unixtime($age, $min_age = 1)
{
  $age = age_to_seconds($age);
  if ($age >= $min_age)
  {
    return time() - $age;
  }
  return 0;
}

/**
 * Parse number with units to numeric.
 *
 * This function converts numbers with units (e.g. 100MB) to their value
 * in bytes (e.g. 104857600).
 *
 * @param string $str
 * @return int
 */
function unit_string_to_numeric($str)
{
  // If it's already a number, return original string
  if (is_numeric($str)) { return (float)$str; }

  preg_match('/(\d+\.?\d*)\ ?(\w+)/', $str, $matches);

  // Error, return original string
  if (!is_numeric($matches[1])) { return $str; }

  switch ($matches[2])
  {
    case '':
    case 'B':
    case 'b':
    case 'bit':
    case 'bps':
    case 'Bps':
    case 'byte':
    case 'Byte':
      $multiplier = 1;
      break;
    case 'k':
    case 'kB':
    case 'kByte':
    case 'kbyte':
      $multiplier = 1024;
      break;
    case 'kb':
    case 'kBps':
    case 'kbit':
    case 'kbps':
      $multiplier = 1000;
      break;
    case 'M':
    case 'MB':
    case 'MByte':
    case 'Mbyte':
      $multiplier = 1024*1024;
      break;
    case 'Mb':
    case 'MBps':
    case 'Mbit':
    case 'Mbps':
      $multiplier = 1000*1000;
      break;
    case 'G':
    case 'GB':
    case 'GByte':
    case 'Gbyte':
      $multiplier = 1024*1024*1024;
      break;
    case 'Gb':
    case 'GBps':
    case 'Gbit':
    case 'Gbps':
      $multiplier = 1000*1000*1000;
      break;
    case 'T':
    case 'TB':
    case 'TByte':
    case 'Tbyte':
      $multiplier = 1024*1024*1024*1024;
      break;
    case 'Tb':
    case 'TBps':
    case 'Tbit':
    case 'Tbps':
      $multiplier = 1000*1000*1000*1000;
      break;
    default:
      $multiplier = 1;
      break;
  }

  return (float)($matches[1] * $multiplier);
}

// DOCME needs phpdoc block
// TESTME needs unit testing
function get_rrd_path($device, $filename)
{
  global $config;

  return trim($config['rrd_dir']) . "/" . $device['hostname'] . "/" . safename($filename);
}

/**
 * Microtime
 *
 * This function returns the current Unix timestamp seconds, accurate to the
 * nearest microsecond.
 *
 * @return float
 */
function utime()
{
  return microtime(TRUE);
}

// EOF
