<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage common
 * @author     Adam Armstrong <adama@observium.org>
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

// Common Functions
/// FIXME. There should be functions that use only standard php (and self) functions.

/**
 * Autoloader for Classes used in Observium
 *
 */
function __autoload($class_name)
{

  $base_dir    = $GLOBALS['config']['install_dir'] . '/libs/';

  $class_array = explode('\\', $class_name);
  $class_file  = str_replace('_', '/', implode($class_array, '/')) . '.php';
  switch ($class_array[0])
  {
    case 'cli':
      include_once $base_dir . 'cli/cli.php'; // Cli classes required base functions
      break;
    default:
      if (is_file($base_dir . 'pear/' . $class_file))
      {
        // By default try Pear file
        $class_file = 'pear/' . $class_file;
      }
      else if (is_dir($base_dir . 'pear/' . $class_name))
      {
        // And Pear dir
        $class_file = 'pear/' . $class_name . '/' . $class_file;
      }
      //else if (!is_cli() && is_file($GLOBALS['config']['html_dir'] . '/includes/' . $class_file))
      //{
      //  // For WUI check class files in html_dir
      //  $base_dir   = $GLOBALS['config']['html_dir'] . '/includes/';
      //}
  }
  $full_path = $base_dir . $class_file;

  $status = is_file($full_path);
  if ($status)
  {
    $status = include_once $full_path;
  }
  if (OBS_DEBUG > 1)
  {
    print_message("%WLoad class '$class_name' from '$full_path': " . ($status ? '%gOK' : '%rFAIL'), 'console');
  }
  return $status;
}

// DOCME needs phpdoc block
// TESTME needs unit testing
// MOVEME to includes/functions.inc.php
function del_obs_attrib($attrib_type)
{
  if (isset($GLOBALS['cache']['attribs'])) { unset($GLOBALS['cache']['attribs']); } // Reset attribs cache

  return dbDelete('observium_attribs', "`attrib_type` = ?", array($attrib_type));
}

// DOCME needs phpdoc block
// TESTME needs unit testing
// MOVEME to includes/functions.inc.php
function set_obs_attrib($attrib_type, $attrib_value)
{
  if (isset($GLOBALS['cache']['attribs'])) { unset($GLOBALS['cache']['attribs']); } // Reset attribs cache

  if (dbFetchCell("SELECT COUNT(*) FROM `observium_attribs` WHERE `attrib_type` = ?;", array($attrib_type)))
  {
    $status = dbUpdate(array('attrib_value' => $attrib_value), 'observium_attribs', "`attrib_type` = ?", array($attrib_type));
  } else {
    $status = dbInsert(array('attrib_type' => $attrib_type, 'attrib_value' => $attrib_value), 'observium_attribs');
    if ($status !== FALSE) { $status = TRUE; } // Note dbInsert return IDs if exist or 0 for not indexed tables
  }
  return $status;
}

// DOCME needs phpdoc block
// TESTME needs unit testing
// MOVEME to includes/functions.inc.php
function get_obs_attribs($type_filter)
{
  if (!isset($GLOBALS['cache']['attribs']))
  {
    $attribs = array();
    foreach (dbFetchRows("SELECT * FROM `observium_attribs`") as $entry)
    {
      $attribs[$entry['attrib_type']] = $entry['attrib_value'];
    }
    $GLOBALS['cache']['attribs'] = $attribs;
  }

  if (strlen($type_filter))
  {
    $attribs = array();
    foreach ($GLOBALS['cache']['attribs'] as $type => $value)
    {
      if (strpos($type, $type_filter) !== FALSE)
      {
        $attribs[$type] = $value;
      }
    }
    return $attribs; // Return filtered attribs
  }

  return $GLOBALS['cache']['attribs']; // All cached attribs
}

// DOCME needs phpdoc block
// TESTME needs unit testing
// MOVEME to includes/functions.inc.php
function get_obs_attrib($attrib_type)
{
  if (isset($GLOBALS['cache']['attribs'][$attrib_type]))
  {
    return $GLOBALS['cache']['attribs'][$attrib_type];
  }

  if ($row = dbFetchRow("SELECT `attrib_value` FROM `observium_attribs` WHERE `attrib_type` = ?;", array($attrib_type)))
  {
    return $row['attrib_value'];
  } else {
    return NULL;
  }
}

function get_unique_id()
{
  if (!isset($GLOBALS['cache']['unique_id']))
  {
    $unique_id = get_obs_attrib('unique_id');
    //$GLOBALS['cache']['attribs']['unique_id'] = dbFetchCell("SELECT `attrib_value` FROM `observium_attribs` WHERE `attrib_type` = 'unique_id';");

    if (!strlen($unique_id))
    {
      $unique_id = str_replace('.', '', uniqid(NULL, TRUE));
      dbInsert(array('attrib_type' => 'unique_id', 'attrib_value' => $unique_id), 'observium_attribs');
    }
    $GLOBALS['cache']['unique_id'] = $unique_id;
  }

  return $GLOBALS['cache']['unique_id'];
}

/**
 * Set new DB Schema version
 *
 * @param integer $db_rev New DB schema revision
 * @param boolean $schema_insert Update (by default) or insert by first install db schema
 * @return boolean Status of DB schema update
 */
function set_db_version($db_rev, $schema_insert = FALSE)
{
  if ($db_rev >= 211) // Do not remove this check, since before this revision observium_attribs table not exist!
  {
    $status = set_obs_attrib('dbSchema', $db_rev);
  } else {
    if ($schema_insert)
    {
      $status = dbInsert(array('version' => $db_rev), 'dbSchema');
      if ($status !== FALSE) { $status = TRUE; } // Note dbInsert return IDs if exist or 0 for not indexed tables
    } else {
      $status = dbUpdate(array('version' => $db_rev), 'dbSchema');
    }
  }

  if ($status)
  {
    $GLOBALS['cache']['db_version'] = $db_rev; // Cache new db version
  }

  return $status;
}

/**
 * Get current DB Schema version
 *
 * @return string DB schema version
 */
// TESTME needs unit testing
function get_db_version()
{
  if (!isset($GLOBALS['cache']['db_version']))
  {
    if ($db_rev = @get_obs_attrib('dbSchema')) {} else
    {
      // CLEANME remove fallback at r7000
      // not r7000, but after one next CE release!
      if ($db_rev = @dbFetchCell("SELECT `version` FROM `dbSchema` ORDER BY `version` DESC LIMIT 1")) {} else
      {
        $db_rev = 0;
      }
    }
    $db_rev = (int)$db_rev;
    if ($db_rev > 0)
    {
      $GLOBALS['cache']['db_version'] = $db_rev;
    } else {
      // Do not cache zero value
      return $db_rev;
    }
  }
  return $GLOBALS['cache']['db_version'];
}

/**
 * Get current DB Size
 *
 * @return string DB size in bytes
 */
// TESTME needs unit testing
function get_db_size()
{
  $db_size = dbFetchCell('SELECT SUM(`data_length` + `index_length`) AS `size` FROM `information_schema`.`tables` WHERE `table_schema` = ?;', array($GLOBALS['config']['db_name']));
  return $db_size;
}

/**
 * Get local hostname
 *
 * @return string FQDN local hostname
 */
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

/**
 * Get the directory size
 *
 * @param string $directory
 * @return integer Directory size in bytes
 */
function get_dir_size($directory)
{
  $size = 0;

  foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory)) as $file)
  {
    if ($file->getFileName() != '..') { $size += $file->getSize(); }
  }
  return $size;
}

// DOCME needs phpdoc block
// TESTME needs unit testing
// MOVEME to includes/alerts.inc.php
function get_alert_entry_by_id($id)
{
  return dbFetchRow("SELECT * FROM `alert_table`".
                    //" LEFT JOIN `alert_table-state` ON  `alert_table`.`alert_table_id` =  `alert_table-state`.`alert_table_id`".
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

/**
 * Convert sequence of numbers in an array to range of numbers.
 * Example:
 *  array(1,2,3,4,5,6,7,8,9,10)    -> '1-10'
 *  array(1,2,3,5,7,9,10,11,12,14) -> '1-3,5,7,9-12,14'
 *
 * @param array $arr Array with sequence of numbers
 * @param string $separator Use this separator for list
 * @param bool $sort Sort input array or not
 * @return string
 */
function range_to_list($arr, $separator = ',', $sort = TRUE)
{
  if ($sort) { sort($arr, SORT_NUMERIC); }

  for ($i = 0; $i < count($arr); $i++)
  {
    $rstart = $arr[$i];
    $rend  = $rstart;
    while (isset($arr[$i+1]) && $arr[$i+1] - $arr[$i] == 1)
    {
      $rend = $arr[$i+1];
      $i++;
    }
    if (is_numeric($rstart) && is_numeric($rend))
    {
      $ranges[] = ($rstart == $rend) ? $rstart : $rstart.'-'.$rend;
    } else {
      return ''; // Not numeric value(s)
    }
  }
  $list = implode($separator, $ranges);

  return $list;
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
  if (is_file($filename))
  {
    if (!is_writable($filename))
    {
      print_debug("Log file '$filename' is not writable, check file permissions.");
      return FALSE;
    }
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

/**
 * Print version information about used Observium and additional softwares.
 *
 * @return NULL
 */
function print_versions()
{
  if (is_executable($GLOBALS['config']['install_dir'].'/scripts/distro'))
  {
    $os = explode('|', external_exec($GLOBALS['config']['install_dir'].'/scripts/distro'), 5);
    $os_version = $os[0].' '.$os[1].' ['.$os[2].'] ('.$os[3].' '.$os[4].')';
  }
  $php_version     = phpversion();
  $python_version  = str_replace("Python ", "", external_exec('/usr/bin/env python --version 2>&1'));
  $mysql_client    = dbClientInfo();
  if (preg_match('/(\d+\.[\d\w\.\-]+)/', $mysql_client, $matches))
  {
    $mysql_client  = $matches[1];
  }
  $mysql_version   = dbFetchCell("SELECT version();");
  $mysql_version  .= ' (extension: ' . OBS_DB_EXTENSION . ' ' . $mysql_client . ')';
  if (is_executable($GLOBALS['config']['snmpget']))
  {
    $snmp_version  = str_replace(" version:", "", external_exec($GLOBALS['config']['snmpget'] . " --version 2>&1"));
  } else {
    $snmp_version  = 'not found';
  }
  if (is_executable($GLOBALS['config']['rrdtool']))
  {
    $rrdtool_version = implode(" ",array_slice(explode(" ",external_exec($GLOBALS['config']['rrdtool'] . " --version |head -n1")), 1, 1));
    if (strlen($GLOBALS['config']['rrdcached']))
    {
      $rrdtool_version .= ' (rrdcached: ' . $GLOBALS['config']['rrdcached'] . ')';
    }
  } else {
    $rrdtool_version = 'not found';
  }

  if (is_cli())
  {
    $timezone      = get_timezone();
    //print_vars($timezone);
    
    $http_version  = external_exec('/usr/bin/env apache2 -v | awk \'/Server version:/ {print $3}\'');
    if (!$http_version)
    {
      $http_version = external_exec('/usr/bin/env httpd -v | awk \'/Server version:/ {print $3}\'');
    }
    if ($http_version)
    {
      $http_version  = str_replace("Apache/", "", $http_version);
    } else {
      $http_version  = 'not found';
    }

    $mysql_mode    = dbFetchCell("SELECT @@sql_mode;");
    $mysql_charset = dbShowVariables('SESSION', "LIKE 'character_set_connection'");

    echo(PHP_EOL);
    print_cli_heading("Software versions");
    print_cli_data("OS",      $os_version);
    print_cli_data("Apache",  $http_version);
    print_cli_data("PHP",     $php_version);
    print_cli_data("Python",  $python_version);
    print_cli_data("MySQL",   $mysql_version);
    print_cli_data("SNMP",    $snmp_version);
    print_cli_data("RRDtool", $rrdtool_version);

    // In CLI always display mode and charset info
    echo(PHP_EOL);
    print_cli_heading("MySQL mode", 3);
    print_cli_data("MySQL",   $mysql_mode, 3);

    echo(PHP_EOL);
    print_cli_heading("Charset info", 3);
    print_cli_data("PHP",     ini_get("default_charset"), 3);
    print_cli_data("MySQL",   $mysql_charset['character_set_connection'], 3);

    echo(PHP_EOL);
    print_cli_heading("Timezones info", 3);
    print_cli_data("Date",    date("l, d-M-y H:i:s T"), 3);
    print_cli_data("PHP",     $timezone['php'], 3);
    print_cli_data("MySQL",   ($timezone['diff'] !== 0 ? '%r' : '') . $timezone['mysql'], 3);
    echo(PHP_EOL);

  } else {
    $http_version    = str_replace("Apache/", "", $_SERVER['SERVER_SOFTWARE']);
    $observium_date  = format_unixtime(strtotime(OBSERVIUM_DATE), 'jS F Y');

    echo('
  <div class="box box-solid">
    <div class="box-header">
      <h3 class="box-title">Version Information</h3>
    </div>
    <div class="box-content">
        <table class="table table-striped table-condensed-more">
          <tbody>
            <tr><td><b>'.escape_html(OBSERVIUM_PRODUCT).'</b></td><td>'.escape_html(OBSERVIUM_VERSION).' ('.escape_html($observium_date).')</td></tr>
            <tr><td><b>OS</b></td><td>'.escape_html($os_version).'</td></tr>
            <tr><td><b>Apache</b></td><td>'.escape_html($http_version).'</td></tr>
            <tr><td><b>PHP</b></td><td>'.escape_html($php_version).'</td></tr>
            <tr><td><b>Python</b></td><td>'.escape_html($python_version).'</td></tr>
            <tr><td><b>MySQL</b></td><td>'.escape_html($mysql_version).'</td></tr>
            <tr><td><b>SNMP</b></td><td>'.escape_html($snmp_version).'</td></tr>
            <tr><td><b>RRDtool</b></td><td>'.escape_html($rrdtool_version).'</td></tr>
          </tbody>
        </table>
    </div>
  </div>'.PHP_EOL);
  }
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
      $query = SqlFormatter::compress($query);
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
      ref::config('shortcutFunc', array('print_vars'));
      ref::config('showUrls', FALSE);
      if (OBS_DEBUG > 0)
      {
        $trace = defined('DEBUG_BACKTRACE_IGNORE_ARGS') ? debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS) : debug_backtrace();
        ref::config('Backtrace', $trace); // pass original backtrace
      } else {
        ref::config('showBacktrace',      FALSE);
        ref::config('showResourceInfo',   FALSE);
        ref::config('showStringMatches',  FALSE);
        ref::config('showMethods',        FALSE);
      }
      rt($vars);
    } else {
      print_r($vars);
    }
  } else {
    if (function_exists('r'))
    {
      ref::config('shortcutFunc', array('print_vars'));
      ref::config('showUrls',   FALSE);
      if (OBS_DEBUG > 0)
      {
        $trace = defined('DEBUG_BACKTRACE_IGNORE_ARGS') ? debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS) : debug_backtrace();
        ref::config('Backtrace', $trace); // pass original backtrace
      } else {
        ref::config('showBacktrace',      FALSE);
        ref::config('showResourceInfo',   FALSE);
        ref::config('showStringMatches',  FALSE);
        ref::config('showMethods',        FALSE);
      }
      //ref::config('stylePath',  $GLOBALS['config']['html_dir'] . '/css/ref.css');
      //ref::config('scriptPath', $GLOBALS['config']['html_dir'] . '/js/ref.js');
      r($vars);
    } else {
      print_r($vars);
    }
  }
}

/**
 * Convert SNMP timeticks string into seconds
 *
 * SNMP timeticks can be in two different normal formats:
 *  - "(2105)"       == 21.05 sec
 *  - "0:0:00:21.05" == 21.05 sec
 * Sometime devices return wrong type or numeric instead timetick:
 *  - "Wrong Type (should be Timeticks): 1632295600" == 16322956 sec
 *  - "1546241903" == 15462419.03 sec
 * Parse the timeticks string and convert it to seconds.
 *
 * @param string $timetick
 * @param bool $float - Return a float with microseconds
 *
 * @return int|float
 */
function timeticks_to_sec($timetick, $float = FALSE)
{
  if (strpos($timetick, 'Wrong Type') !== FALSE)
  {
    // Wrong Type (should be Timeticks): 1632295600
    list(, $timetick) = explode(': ', $timetick, 2);
  }

  $timetick = trim($timetick, " \t\n\r\0\x0B\"()"); // Clean string
  if (is_numeric($timetick))
  {
    // When "Wrong Type" or timetick as an integer, than time with count of ten millisecond ticks
    $time = $timetick / 100;
    return ($float ? (float)$time : (int)$time);
  }
  if (!preg_match('/^[\d\.: ]+$/', $timetick)) { return FALSE; }

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
  $time  = ($float ? (float)$secs + $microsecs/100 : (int)$secs);

  return $time;
}

/**
 * Convert SNMP DateAndTime string into unixtime
 *
 * field octets contents range
 * ----- ------ -------- -----
 * 1 1-2 year 0..65536
 * 2 3 month 1..12
 * 3 4 day 1..31
 * 4 5 hour 0..23
 * 5 6 minutes 0..59
 * 6 7 seconds 0..60
 * (use 60 for leap-second)
 * 7 8 deci-seconds 0..9
 * 8 9 direction from UTC '+' / '-'
 * 9 10 hours from UTC 0..11
 * 10 11 minutes from UTC 0..59
 *
 * For example, Tuesday May 26, 1992 at 1:30:15 PM EDT would be displayed as:
 * 1992-5-26,13:30:15.0,-4:0
 *
 * Note that if only local time is known, then timezone information (fields 8-10) is not present.
 *
 * @param string $datetime DateAndTime string
 * @param boolean $use_gmt Return unixtime converted to GMT or Local (by default)
 *
 * @return integer Unixtime
 */
function datetime_to_unixtime($datetime, $use_gmt = FALSE)
{
  $timezone = get_timezone();

  $datetime = trim($datetime);
  if (preg_match('/(?<year>\d+)-(?<mon>\d+)-(?<day>\d+)(?:,(?<hour>\d+):(?<min>\d+):(?<sec>\d+)(?<millisec>\.\d+)?(?:,(?<tzs>[+\-]?)(?<tzh>\d+):(?<tzm>\d+))?)/', $datetime, $matches))
  {
    if (isset($matches['tzh']))
    {
      // Use TZ offset from datetime string
      $offset = $matches['tzs'] . ($matches['tzh'] * 3600 + $matches['tzm'] * 60); // Offset from GMT in seconds
    } else {
      // Or use system offset
      $offset = $timezone['php_offset'];
    }
    $time_tmp = mktime($matches['hour'], $matches['min'], $matches['sec'], $matches['mon'], $matches['day'], $matches['year']); // Generate unixtime

    $time_gmt   = $time_tmp + ($offset * -1);            // Unixtime from string in GMT
    $time_local = $time_gmt + $timezone['php_offset'];   // Unixtime from string in local timezone
  } else {
    $time_local = time();                                // Current unixtime with local timezone
    $time_gmt   = $time_local - $timezone['php_offset']; // Current unixtime in GMT
  }

  if (OBS_DEBUG > 1)
  {
    $debug_msg  = 'UNIXTIME from DATETIME "' . ($time_tmp ? $datetime : 'time()') . '": ';
    $debug_msg .= 'LOCAL (' . format_unixtime($time_local) . '), GMT (' . format_unixtime($time_gmt) . '), TZ (' . $timezone['php'] . ')';
    print_message($debug_msg);
  }

  if ($use_gmt)
  {
    return ($time_gmt);
  } else {
    return ($time_local);
  }
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
 * Example:
 * Array
 * (
 *  [mysql] => +03:00
 *  [php] => +03:00
 *  [php_abbr] => MSK
 *  [php_offset] => +10800
 *  [mysql_offset] => +10800
 *  [diff] => 0
 * )
 *
 * @return array Timezones info
 */
// MOVEME to includes/functions.inc.php
function get_timezone()
{
  global $cache;

  if (!isset($cache['timezone']))
  {
    $timezone = array();
    //$timezone['system'] = external_exec('date "+%:z"');                          // return '+04:00'
    $timezone['mysql'] = dbFetchCell('SELECT TIMEDIFF(NOW(), UTC_TIMESTAMP);'); // return '04:00:00'
    if ($timezone['mysql'][0] != '-')
    {
      $timezone['mysql'] = '+'.$timezone['mysql'];
    }
    $timezone['mysql']       = preg_replace('/:00$/', '', $timezone['mysql']);  // convert to '+04:00'
    $timezone['php']         = date('P');                                       // return '+03:00'
    $timezone['php_abbr']    = date('T');                                       // return 'MSK'
    foreach (array('php', 'mysql') as $entry)
    {
      $sign = $timezone[$entry][0];
      list($hours, $minutes) = explode(':', $timezone[$entry]);
      $timezone[$entry . '_offset'] = $sign . (abs($hours) * 3600 + $minutes * 60); // Offset from GMT in seconds
    }

    // Get get the difference in sec between mysql and php timezones
    $timezone['diff'] = (int)$timezone['mysql_offset'] - (int)$timezone['php_offset'];
    $cache['timezone'] = $timezone;
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
    $timeout_usec = NULL;
    $timeout = NULL;
  }

  $descriptorspec = array(
    //0 => array('pipe', 'r'), // stdin
    1 => array('pipe', 'w'), // stdout
    2 => array('pipe', 'w')  // stderr
  );

  // Debug the command *before* we run it!
  if (OBS_DEBUG > 0)
  {
    $debug_command = $command;
    if (OBS_DEBUG < 2 && $GLOBALS['config']['snmp']['hide_auth'] && preg_match("/snmp(?:bulk)?(?:get|walk)\s+(?:-(?:t|r|Cr)['\d\s]+){0,3}-v[123]c?\s+/", $command))
    {
      // Hide snmp auth params from debug cmd out,
      // for help users who want send debug output to developers
      $pattern = "/\s+(-[cuxXaA])\s*(?:'.+?')(@\d+)?/";
      $debug_command = preg_replace($pattern, ' \1 ***\2', $debug_command);
    }
    print_message(PHP_EOL . 'CMD[%y' . $debug_command . '%n]' . PHP_EOL, 'console');
  }

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
    //while (feof($pipes[1]) === FALSE || feof($pipes[2]) === FALSE)
    while (TRUE)
    {
      $read = array();
      if (!feof($pipes[1])) { $read[] = $pipes[1]; }
      if (!feof($pipes[2])) { $read[] = $pipes[2]; }
      if (empty($read)) { break; }
      $write  = NULL;
      $except = NULL;
      stream_select($read, $write, $except, $timeout, $timeout_usec);

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
        if (feof($pipes[1]) === FALSE)
        {
          // Very rare situation, seems as next proc_get_status() bug
          if (!isset($status_fix)) { $status_fix = $status; }
          if (OBS_DEBUG > 1) { print_debug("Wrong process status! Try fix.."); }
        } else {
          //var_dump($status);
          break;
        }
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
    else if (isset($status_fix))
    {
      // See fixed proc_get_status() above
      $status = $status_fix;
    }
    $exec_status['exitcode'] = (int)$status['exitcode'];
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

  $exec_status['runtime'] = $runtime;
  $exec_status['stdout']  = $stdout;

  if (OBS_DEBUG > 0)
  {
    print_message('EXITCODE['.($exec_status['exitcode'] !== 0 ? '%r' : '%g').$exec_status['exitcode'].'%n]'.PHP_EOL.
                  'CMD RUNTIME['.($runtime > 7 ? '%r' : '%g').round($runtime, 4).'s%n]', 'console');
    print_message("STDOUT[\n".$stdout."\n]", 'console', FALSE);
    if ($exec_status['exitcode'] && $exec_status['stderr'])
    {
      // Show stderr if exitcode not 0
      print_message("STDERR[\n".$exec_status['stderr']."\n]", 'console', FALSE);
    }
  }

  return $stdout;
}

/**
 * Determine array is associative or sequential?
 *
 * @param array
 * @return boolean
 */
function is_array_assoc($array)
{
  return ($array !== array_values($array));
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

function cli_is_piped()
{
  if (isset($GLOBALS['cache']['cli_is_piped']))
  {
    return $GLOBALS['cache']['cli_is_piped'];
  }

  $GLOBALS['cache']['cli_is_piped'] = check_extension_exists('posix') && !posix_isatty(STDOUT);
  return $GLOBALS['cache']['cli_is_piped'];
}

// Detect if script runned from crontab
// DOCME needs phpdoc block
// TESTME needs unit testing
function is_cron()
{
  $cron = is_cli() && !isset($_SERVER['TERM']);
  // For more accurate check if STDOUT exist (but this requires posix extension)
  if ($cron)
  {
    $cron = $cron && cli_is_piped();
  }
  return $cron;
}

// DOCME needs phpdoc block
// TESTME needs unit testing
function print_prompt($text, $default_yes = FALSE)
{
  if (is_cli())
  {
    if (cli_is_piped())
    {
      // If now not have interactive TTY skip any prompts, return default
      $return = TRUE && $default_yes;
    }

    $question = ($default_yes ? 'Y/n' : 'y/N');
    echo trim($text), " [$question]: ";
    $handle = fopen ('php://stdin', 'r');
    $line  = strtolower(trim(fgets($handle, 3)));
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

/**
 * This function echoes text with style 'debug', see print_message().
 * Here checked constant OBS_DEBUG, if OBS_DEBUG not set output - empty.
 *
 * @param string $text
 * @param boolean $strip Stripe special characters (for web) or html tags (for cli)
 */
function print_debug($text, $strip = FALSE)
{
  if (OBS_DEBUG > 0 || (defined('OBS_DEBUG_WUI') && !defined('OBS_DEBUG')))
  {
    print_message($text, 'debug', $strip);
  }
}

/**
 * This function echoes text with style 'error', see print_message().
 *
 * @param string $text
 * @param boolean $strip Stripe special characters (for web) or html tags (for cli)
 */
function print_error($text, $strip = TRUE)
{
  print_message($text, 'error', $strip);
}

/**
 * This function echoes text with style 'warning', see print_message().
 *
 * @param string $text
 * @param boolean $strip Stripe special characters (for web) or html tags (for cli)
 */
function print_warning($text, $strip = TRUE)
{
  print_message($text, 'warning', $strip);
}

/**
 * This function echoes text with style 'success', see print_message().
 *
 * @param string $text
 * @param boolean $strip Stripe special characters (for web) or html tags (for cli)
 */
function print_success($text, $strip = TRUE)
{
  print_message($text, 'success', $strip);
}

/**
 * This function echoes text with specific styles (different for cli and web output).
 *
 * @param string $text
 * @param string $type Supported types: default, success, warning, error, debug
 * @param boolean $strip Stripe special characters (for web) or html tags (for cli)
 */
function print_message($text, $type='', $strip = TRUE)
{
  global $config;

  // Do nothing if input text not any string (like NULL, array or other). (Empty string '' still printed).
  if (!is_string($text) && !is_numeric($text)) { return NULL; }

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
                     'class'     => 'alert alert-warning');               // yellow
      $icon  = 'oicon-bell';
      break;
    case 'error':
      $color = array('cli'       => '%r',                   // red
                     'cli_color' => FALSE,                  // by default cli coloring disabled
                     'class'     => 'alert alert-danger');   // red
      $icon  = 'oicon-exclamation-red';
      break;
    case 'debug':
      $color = array('cli'       => '%r',                   // red
                     'cli_color' => FALSE,                  // by default cli coloring disabled
                     'class'     => 'alert alert-danger');  // red
      $icon  = 'oicon-exclamation-red';
      break;
    case 'color':
      $color = array('cli'       => '',                     // none
                     'cli_color' => TRUE,                   // allow using coloring
                     'class'     => 'alert alert-info');    // blue
      $icon  = 'oicon-information';
      break;
    case 'console':
      // This is special type used nl2br conversion for display console messages on WUI with correct line breaks
      $color = array('cli'       => '',                     // none
                     'cli_color' => TRUE,                   // allow using coloring
                     'class'     => 'alert alert-suppressed'); // purple
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
    if ($strip)
    {
      $text = html_entity_decode($text, ENT_QUOTES, 'UTF-8'); // Convert special HTML entities back to characters
      $text = str_ireplace(array('<br />', '<br>', '<br/>'), PHP_EOL, $text); // Convert html <br> to into newline
      $text = strip_tags($text);
    }
    if ($type == 'debug' && !$color['cli_color'])
    {
      // For debug just echo message.
      echo($text . PHP_EOL);
    } else {

      print_cli($color['cli'].$text.'%n'.PHP_EOL, $color['cli_color']);

    }
  } else {
    if ($text === '') { return NULL; } // Do not web output if the string is empty
    if ($strip)
    {
      if ($text == strip_tags($text))
      {
        // Convert special characters to HTML entities only if text not have html tags
        $text = escape_html($text);
      }
      if ($color['cli_color'])
      {
        // Replace some Pear::Console_Color2 color codes with html styles
        $replace = array('%',                                  // '%%'
                         '</span>',                            // '%n'
                         '<span class="label label-warning">', // '%y'
                         '<span class="label label-success">', // '%g'
                         '<span class="label label-danger">',  // '%r'
                         '<span class="label label-primary">', // '%b'
                         '<span class="label label-info">',    // '%c'
                         '<span class="label label-default">', // '%W'
                         '<span class="label label-default" style="color:black;">', // '%k'
                         '<span style="font-weight: bold;">',  // '%_'
                         '<span style="text-decoration: underline;">', // '%U'
                         );
      } else {
        $replace = array('%', '');
      }
      $text = str_replace(array('%%', '%n', '%y', '%g', '%r', '%b', '%c', '%W', '%k', '%_', '%U'), $replace, $text);
    }

    $msg = PHP_EOL.'    <div class="'.$color['class'].'">';
    if ($type != 'warning' && $type != 'error')
    {
      $msg .= '<button type="button" class="close" data-dismiss="alert">&times;</button>';
    }
    if ($type == 'console')
    {
      $text = nl2br(trim($text)); // Convert newline to <br /> for console messages with line breaks
    }

    $msg .= '
      <div>'.$text.'</div>
    </div>'.PHP_EOL;

    echo($msg);
  }
}

function print_cli($text, $colour = TRUE)
{
  //include_once("Console/Color2.php");

  $msg = new Console_Color2();

  print $msg->convert($text, $colour);
}

// TESTME needs unit testing
/**
 * Print an discovery/poller module stats
 *
 * @global array $GLOBALS['module_stats']
 * @param array $device Device array
 * @param string $module Module name
 */
function print_module_stats($device, $module)
{
  $log_event = FALSE;
  $stats_msg = array();
  foreach (array('added', 'updated', 'deleted', 'unchanged') as $key)
  {
    if ($GLOBALS['module_stats'][$module][$key])
    {
      $stats_msg[] = (int)$GLOBALS['module_stats'][$module][$key].' '.$key;
      if ($key != 'unchanged') { $log_event = TRUE; }
    }
  }
  if (count($GLOBALS['module_stats'][$module])) { echo(PHP_EOL); }
  if (count($stats_msg)) { print_cli_data("Changes", implode(', ', $stats_msg)); }
  if ($GLOBALS['module_stats'][$module]['time'])
  {
    print_cli_data("Duration", $GLOBALS['module_stats'][$module]['time']."s");
  }
  if ($log_event) { log_event(nicecase($module).': '.implode(', ', $stats_msg).'.', $device, 'device', $device['device_id']); }
}

// DOCME needs phpdoc block
// TESTME needs unit testing
function print_obsolete_config($filter = '')
{
  global $config;

  $list = array();
  foreach ($config['obsolete_config'] as $entry)
  {
    if ($filter && strpos($entry['old'], $filter) === FALSE) { continue; }
    $old = explode('->', $entry['old']);
    switch (count($old))
    {
      case 1:
        $entry['isset'] = isset($config[$old[0]]);
        break;
      case 2:
        $entry['isset'] = isset($config[$old[0]][$old[1]]);
        break;
      case 3:
        $entry['isset'] = isset($config[$old[0]][$old[1]][$old[2]]);
        break;
      case 4:
        $entry['isset'] = isset($config[$old[0]][$old[1]][$old[2]][$old[3]]);
        break;
    }
    if ($entry['isset'])
    {
      $new  = explode('->', $entry['new']);
      $info = (isset($entry['info']) ? ' ('.$entry['info'].')' : '');
      $list[] = "  %r\$config['".implode("']['", $old)."']%n --> %g\$config['".implode("']['", $new)."']%n".$info;
    }
  }

  if ($list)
  {
    $msg = "%WWARNING.%n Found obsolete configurations in config.php, please rename respectively:\n".implode(PHP_EOL, $list);
    print_message($msg, 'color');
    return TRUE;
  } else {
    return FALSE;
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
    'mysql'    => 'mysql_connect',
    'mysqli'   => 'mysqli_connect',
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
    if ($fatal) { exit(2); }
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

// DOCME needs phpdoc block
// TESTME needs unit testing
// MOVEME to includes/functions.inc.php
function get_status_rrd($device, $status)
{
  global $config;

  # For IPMI, sensors tend to change order, and there is no index, so we prefer to use the description as key here.
  if ($config['os'][$device['os']]['status_descr'] || $sensor['poller_type'] == "ipmi")
  {
    $rrd_file = "status-".$status['status_type']."-".$status['status_descr'] . ".rrd";
  } else {
    $rrd_file = "status-".$status['status_type']."-".$status['status_index'] . ".rrd";
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
function get_port_by_index_cache($device, $ifIndex)
{
  global $cache;

  if (is_array($device) && isset($device['device_id']))
  {
    $device_id = $device['device_id'];
  }
  else if (is_numeric($device))
  {
    $device_id = $device;
  }
  if (!isset($device_id) || !is_numeric($ifIndex))
  {
    print_error("Invalid arguments passed into function get_port_by_index_cache(). Please report to developers.");
  }

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
            $where .= generate_query_values($value, 'device_id');
            break;
          case 'type':
          case 'descr':
          case 'circuit':
          case 'speed':
          case 'notes':
            $where .= generate_query_values($value, 'port_descr_'.$var);
            break;
        }
      }
    }
  } else {
    return FALSE;
  }

  $query = 'SELECT `port_id` FROM `ports` ' . $where . ' ORDER BY `ifOperStatus` DESC';
  $ids = dbFetchColumn($query);

  //print_vars($ids);
  switch (count($ids))
  {
    case 0:
      return FALSE;
    case 1:
      return $ids[0];
      break;
    default:
      foreach ($ids as $port_id)
      {
        $port = get_port_by_id_cache($port_id);
        $device = device_by_id_cache($port['device_id']);
        if ($device['disabled'] || !$device['status'])
        {
          continue; // switch to next ID
        }
        break;
      }
      return $port_id;
  }
  return FALSE;
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
// MOVEME html/includes/functions.inc.php BUT this used in includes/rewrites.inc.php
function port_html_class($ifOperStatus, $ifAdminStatus, $encrypted)
{
  $ifclass = "interface-upup";
  if      ($ifAdminStatus == "down")            { $ifclass = "gray"; }
  else if ($ifAdminStatus == "up")
  {
    if      ($ifOperStatus == "down")           { $ifclass = "red"; }
    else if ($ifOperStatus == "lowerLayerDown") { $ifclass = "orange"; }
    else if ($ifOperStatus == "monitoring")     { $ifclass = "green"; }
    else if ($encrypted === '1')                { $ifclass = "olive"; }
    else if ($ifOperStatus == "up")             { $ifclass = ""; }
    else                                        { $ifclass = "purple"; }
  }

  return $ifclass;
}

// DOCME needs phpdoc block
// TESTME needs unit testing
// MOVEME to includes/functions.inc.php
function device_by_name($name, $refresh = 0)
{
  // FIXME - cache name > id too.
  return device_by_id_cache(get_device_id_by_hostname($name), $refresh);
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
  }

  if (!empty($device))
  {
    humanize_device($device);
    if ($refresh || !isset($device['graphs']))
    {
      // Fetch device graphs
      $device['graphs'] = dbFetchRows("SELECT * FROM `device_graphs` WHERE `device_id` = ?", array($device_id));
    }
    $cache['devices']['id'][$device_id] = $device;

    return $device;
  } else {
    return FALSE;
  }
}

// DOCME needs phpdoc block
// TESTME needs unit testing
function truncate($substring, $max = 50, $rep = '...')
{
  if (strlen($substring) < 1) { $string = $rep; } else { $string = $substring; }
  $leave = $max - strlen ($rep);
  if (strlen($string) > $max) { return substr_replace($string, $rep, $leave); } else { return $string; }
}

/**
 * Wrapper to htmlspecialchars()
 *
 * @param string $string
 */
// TESTME needs unit testing
function escape_html($string, $flags = ENT_QUOTES)
{
  return htmlspecialchars($string, $flags, 'UTF-8');
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
function get_device_by_device_id($id)
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
function get_ifIndex_by_port_id($id)
{
  return dbFetchCell("SELECT `ifIndex` FROM `ports` WHERE `port_id` = ?", array($id));
}

// DOCME needs phpdoc block
// TESTME needs unit testing
// MOVEME to includes/functions.inc.php
function get_port_by_port_id($id)
{
  return dbFetchRow("SELECT * FROM `ports` WHERE `port_id` = ?", array($id));
}

// DOCME needs phpdoc block
// TESTME needs unit testing
// MOVEME to includes/functions.inc.php
function get_port_descr_by_port_id($id)
{
  return dbFetchCell("SELECT `ifDescr` FROM `ports` WHERE `port_id` = ?", array($id));
}

// DOCME needs phpdoc block
// TESTME needs unit testing
// MOVEME to includes/functions.inc.php
function get_device_id_by_hostname($hostname)
{
  global $cache;

  if (isset($cache['devices']['hostname'][$hostname]))
  {
    $id = $cache['devices']['hostname'][$hostname];
  }
  else
  {
    $id = dbFetchCell("SELECT `device_id` FROM `devices` WHERE `hostname` = ?", array($hostname));
  }

  if (is_numeric($id))
  {
    return $id;
  } else {
    return FALSE;
  }
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

// OBSOLETE, remove when all function calls will be deleted
function get_dev_attrib($device, $attrib_type)
{
  // Call to new function
  return get_entity_attrib('device', $device, $attrib_type);
}

// OBSOLETE, remove when all function calls will be deleted
function get_dev_attribs($device_id)
{
  // Call to new function
  return get_entity_attribs('device', $device_id);
}

// OBSOLETE, remove when all function calls will be deleted
function set_dev_attrib($device, $attrib_type, $attrib_value)
{
  // Call to new function
  return set_entity_attrib('device', $device, $attrib_type, $attrib_value);
}

// OBSOLETE, remove when all function calls will be deleted
function del_dev_attrib($device, $attrib_type)
{
  // Call to new function
  return del_entity_attrib('device', $device, $attrib_type);
}

// DOCME needs phpdoc block
// Return cached MIBs array available for device (from os definitions)
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
    $mibs = array();
    if (isset($config['os'][$device['os']]['model']))
    {
      $model       = $config['os'][$device['os']]['model'];
      $sysObjectID = (preg_match('/^\.\d[\d\.]+$/', $device['sysObjectID']) ? $device['sysObjectID'] : 'WRONG_ID');
      krsort($config['model'][$model]); // Resort array by key with high to low order!
      foreach ($config['model'][$model] as $key => $entry)
      {
        if (isset($entry['mibs']) && strpos($sysObjectID, $key) === 0)
        {
          $mibs = array_merge($mibs, (array)$entry['mibs']);
          break;
        }
      }
    }
    $mibs = array_unique(array_merge((array)$mibs, (array)$config['os'][$device['os']]['mibs'],
                                     (array)$config['os_group'][$config['os'][$device['os']]['group']]['mibs'],
                                     (array)$config['os']['default']['mibs']));
    // Blacklisted MIBs
    $mibs = array_diff($mibs, get_device_mibs_blacklist($device));

    $cache['devices']['mibs'][$device_id] = $mibs;
  }

  return $cache['devices']['mibs'][$device_id];
}

/**
 * Return array with blacklisted MIBs for current device
 *
 * @param array $device Device array
 * @return array Blacklisted MIBs
 */
function get_device_mibs_blacklist(array $device)
{
  global $config;
  $blacklist = array_unique(array_merge((array)$config['os'][$device['os']]['mib_blacklist'],
                                        (array)$config['os_group'][$config['os'][$device['os']]['group']]['mib_blacklist']));
  return $blacklist;
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
  if ($check_permissions && $mib_permitted && (!isset($config['mibs'][$mib]['enable']) || $config['mibs'][$mib]['enable']))
  {
    // Check if MIB permitted by config
    $mib_permitted = $mib_permitted && (!isset($config['mibs'][$mib]['enable']) || $config['mibs'][$mib]['enable']);

    // Check if MIB disabled on device by web interface or polling process
    $dev_attribs = get_dev_attribs($device['device_id']);
    $mib_permitted = $mib_permitted && (!isset($dev_attribs['mib_'.$mib]) || $dev_attribs['mib_'.$mib] != 0);

    // Check if MIB disabled globally by web interface
    $obs_attribs = get_obs_attribs('mib_');
    $mib_permitted = $mib_permitted && (!isset($obs_attribs['mib_'.$mib]) || $obs_attribs['mib_'.$mib] != 0);
  }

  return $mib_permitted;
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
    for ($i = 1; (($i < count($sizes)) && ($value >= 1000)); $i++) { $value = $value / 1000; $ext = $sizes[$i]; }
  }
  else
  {
    $sizes = Array('', 'm', 'u', 'n');
    $ext = $sizes[0];
    for ($i = 1; (($i < count($sizes)) && ($value != 0) && ($value <= 0.1)); $i++) { $value = $value * 1000; $ext = $sizes[$i]; }
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
  for ($i = 1; (($i < count($sizes)) && ($value >= 1024)); $i++) { $value = $value / 1024; $ext = $sizes[$i]; }

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
 * See: http://stackoverflow.com/a/4694816
 *      http://stackoverflow.com/a/2183140
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
  return (preg_match("/^(_?[a-z\d](-*[_a-z\d])*)(\.(_?[a-z\d](-*[_a-z\d])*))*$/i", $hostname) // valid chars check
          && preg_match("/^.{1,253}$/", $hostname)                                      // overall length check
          && preg_match("/^[^\.]{1,63}(\.[^\.]{1,63})*$/", $hostname));                 // length of each label
  /* check for invalid starting characters
  if (preg_match('/^[_.-]/', $hostname))
  {
    return FALSE;
  } else {
    return ctype_alnum(str_replace('_','',str_replace('-','',str_replace('.','',$hostname))));
  }
  */
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
// For get only IPv6 record use gethostbyname6($hostname, OBS_DNS_AAAA)
// DOCME needs phpdoc block
// TESTME needs unit testing
function gethostbyname6($host, $flags = OBS_DNS_ALL)
{
  // get AAAA record for $host
  // if flag OBS_DNS_A is set, if AAAA fails, it tries for A
  // the first match found is returned
  // otherwise returns FALSE

  $dns = gethostbynamel6($host, $flags);
  if ($dns == FALSE)
  {
    return FALSE;
  } else {
    return $dns[0];
  }
}

// Same as gethostbynamel(), but work with both IPv4 and IPv6
// By default returns both IPv4/6 addresses (A and AAAA records),
// for get only IPv6 addresses use gethostbynamel6($hostname, OBS_DNS_AAAA)
// DOCME needs phpdoc block
// TESTME needs unit testing
function gethostbynamel6($host, $flags = OBS_DNS_ALL)
{
  // get AAAA records for $host,
  // if $try_a is true, if AAAA fails, it tries for A
  // results are returned in an array of ips found matching type
  // otherwise returns FALSE

  $ip6 = array();
  $ip4 = array();

  // First try /etc/hosts
  $etc = ipFromEtcHosts($host);

  $try_a = is_flag_set(OBS_DNS_A, $flags);
  if ($try_a === TRUE)
  {
    if ($etc && strstr($etc, '.')) { $ip4[] = $etc; }
    // Separate A and AAAA queries, see: https://www.mail-archive.com/observium@observium.org/msg09239.html
    $dns = dns_get_record($host, DNS_A);
    if (!is_array($dns)) { $dns = array(); }
    $dns6 = dns_get_record($host, DNS_AAAA);
    if (is_array($dns6))
    {
      $dns = array_merge($dns, $dns6);
    }
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
  //include_once('Net/DNS2.php');
  //include_once('Net/DNS2/RR/PTR.php');

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
function build_request_url($url, $params = array(), $method = 'GET')
{
  $request = $url;
  // Build request query
  switch (strtolower($method))
  {
    case 'post':
      break; // Just return original url
    case 'get':
    default:
      $request_params = array();
      foreach ($params as $param => $value)
      {
        $request_params[] = $param . '=' . $value;
      }
      if ($request_params)
      {
        $request .= '?' . implode('&', $request_params);
      }
  }
  return $request;
}

// DOCME needs phpdoc block
// TESTME needs unit testing
function get_http_request($request, $context = array(), $rate_limit = FALSE)
{
  global $config;

  $ok = TRUE;
  if (defined('OBS_HTTP_REQUEST') && OBS_HTTP_REQUEST === FALSE)
  {
    print_debug("HTTP requests skipped since previous request exit with timeout");
    $ok = FALSE;
    $GLOBALS['response_headers'] = array('code' => '408', 'status' => 'Request Timeout');
  }

  if ($rate_limit && is_numeric($rate_limit) && $rate_limit >= 0)
  {
    // Check limit rates to this domain (per/day)
    if (preg_match('/^https?:\/\/([\w\.]+[\w\-\.]*(:\d+)?)/', $request, $matches))
    {
      $date    = format_unixtime($config['time']['now'], 'Y-m-d');
      $domain  = $matches[0]; // base domain (with http(s)): https://test-me.com/ -> https://test-me.com
      $rate_db = json_decode(get_obs_attrib('http_rate_' . $domain), TRUE);
      //print_vars($date); print_vars($rate_db);
      if (is_array($rate_db) && isset($rate_db[$date]))
      {
        $rate_count = $rate_db[$date];
      } else {
        $rate_count = 0;
      }
      $rate_count++;
      set_obs_attrib('http_rate_' . $domain, json_encode(array($date => $rate_count)));
      if ($rate_count > $rate_limit)
      {
        print_debug("HTTP requests skipped because the rate limit $rate_limit/day for domain '$domain' is exceeded (count: $rate_count)");
        $GLOBALS['response_headers'] = array('code' => '429', 'status' => 'Too Many Requests');
        $ok = FALSE;
      }
      else if (OBS_DEBUG > 1)
      {
        print_debug("HTTP rate count for domain '$domain': $rate_count ($rate_limit/day)");
      }
    } else {
      $rate_limit = FALSE;
    }
  }

  if (OBS_DEBUG > 0)
  {
    $debug_request = $request;
    if (OBS_DEBUG < 2 && strpos($request, 'update.observium.org')) { $debug_request = preg_replace('/&stats=.+/', '&stats=***', $debug_request); }
    $debug_msg = PHP_EOL . 'REQUEST[%y' . $debug_request . '%n]';
  }

  if (!$ok)
  {
    if (OBS_DEBUG > 0)
    {
      print_message($debug_msg . PHP_EOL .
                    'REQUEST STATUS[' . $GLOBALS['response_headers']['code'] . ' ' . $GLOBALS['response_headers']['status'] . ']', 'console');
    }
    return FALSE;
  }

  $response = '';

  if (!is_array($context)) { $context = array(); } // Fix context if not array passed
  $opts = array('http' => $context);
  $opts['http']['timeout'] = '15';

  // User agent (required for some type of queries, ie geocoding)
  if (!isset($opts['http']['header'])) { $opts['http']['header'] = ''; } // Avoid 'undefined index' when concatting below
  $opts['http']['header'] .= 'User-Agent: ' . OBSERVIUM_PRODUCT . '/' . OBSERVIUM_VERSION . '\r\n';

  if (isset($config['http_proxy']) && $config['http_proxy'])
  {
    $opts['http']['proxy'] = 'tcp://' . $config['http_proxy'];
    $opts['http']['request_fulluri'] = TRUE;
  }

  // Basic proxy auth
  if (isset($config['proxy_user']) && $config['proxy_user'] && isset($config['proxy_password']))
  {
    $auth = base64_encode($config['proxy_user'].':'.$config['proxy_password']);
    $opts['http']['header'] .= 'Proxy-Authorization: Basic ' . $auth . '\r\n';
  }

  $start = utime();
  $context = stream_context_create($opts);
  $response = file_get_contents($request, FALSE, $context);
  $runtime = utime() - $start;

  // Parse response headers
  $head = array();
  foreach ($http_response_header as $k => $v)
  {
    $t = explode(':', $v, 2);
    if (isset($t[1]))
    {
      $head[trim($t[0])] = trim($t[1]);
    } else {
      if (preg_match("!HTTP/([\d\.]+)\s+(\d+)(.*)!", $v, $matches))
      {
        $head['http']   = $matches[1];
        $head['code']   = intval($matches[2]);
        $head['status'] = trim($matches[3]);
      } else {
        $head[] = $v;
      }
    }
  }
  $GLOBALS['response_headers'] = $head;

  if (OBS_DEBUG > 0)
  {
    if (OBS_DEBUG < 2 && strpos($request, 'update.observium.org')) { $request = preg_replace('/&stats=.+/', '&stats=***', $request); }
    print_message($debug_msg . PHP_EOL .
                  'REQUEST STATUS[' . $head['code'] . ' ' . $head['status'] . ']' . PHP_EOL .
                  'REQUEST RUNTIME['.($runtime > 3 ? '%r' : '%g').round($runtime, 4).'s%n]', 'console');
    if (OBS_DEBUG > 1)
    {
      print_message("RESPONSE[\n".$response."\n]", 'console', FALSE);
      print_vars($http_response_header);
      print_vars($opts);
    }
  }

  // Set OBS_HTTP_REQUEST for skip all other requests
  if (!defined('OBS_HTTP_REQUEST'))
  {
    if ($response === FALSE && empty($http_response_header))
    {
      $GLOBALS['response_headers'] = array('code' => '408', 'status' => 'Request Timeout');
      // Timeout error, only if not received responce headers
      define('OBS_HTTP_REQUEST', FALSE);
      print_debug(__FUNCTION__.'() exit with timeout. Access to outside localnet is blocked by firewall or network problems. Check proxy settings.');
      logfile(__FUNCTION__.'() exit with timeout. Access to outside localnet is blocked by firewall or network problems. Check proxy settings.');
    } else {
      define('OBS_HTTP_REQUEST', TRUE);
    }
  }
  // FIXME. what if first request fine, but second broken?
  //else if ($response === FALSE)
  //{
  //  if (function_exists('runkit_constant_redefine')) { runkit_constant_redefine('OBS_HTTP_REQUEST', FALSE); }
  //}

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
    $seconds += $matches['years'] * 31536000; // year   = 365 * 24 * 60 * 60
    $seconds += $matches['months'] * 2628000; // month  = year / 12
    $seconds += $matches['weeks']   * 604800; // week   = 7 days
    $seconds += $matches['days']     * 86400; // day    = 24 * 60 * 60
    $seconds += $matches['hours']     * 3600; // hour   = 60 * 60
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
 * Convert an variable to base64 encoded string
 *
 * This function converts any array or other variable to encoded string
 * which can be used in urls.
 * Can use serialize(default) and json methods.
 *
 * NOTE. In PHP < 5.4 json converts UTF-8 characters to Unicode escape sequences
 * also json rounds float numbers (98172397.1234567890 ==> 98172397.123457)
 *
 * @param mixed $var
 * @param string $method
 * @return string
 */
function var_encode($var, $method = 'serialize')
{
  switch ($method)
  {
    case 'json':
      if (defined('JSON_UNESCAPED_UNICODE'))
      {
        $string = base64_encode(json_encode($var, JSON_UNESCAPED_UNICODE));
      } else {
        // In pre 5.4 used escaped UTF8 (this broke not ASCII texts)
        $string = base64_encode(json_encode($var));
      }
      break;
    default:
      $string = base64_encode(serialize($var));
      break;
  }
  return $string;
}

/**
 * Decode an previously encoded string by var_encode() to original variable
 *
 * This function converts base64 encoded string to original variable.
 * Can use serialize(default) and json methods.
 * If json/serialize not detected returns original var
 *
 * NOTE. In PHP < 5.4 json converts UTF-8 characters to Unicode escape sequences,
 * also json rounds float numbers (98172397.1234567890 ==> 98172397.123457)
 *
 * @param string $string
 * @return mixed
 */
function var_decode($string, $method = 'serialize')
{
  $value = base64_decode($string, TRUE);
  if ($value === FALSE)
  {
    // This is not base64 string, return original var
    return $string;
  }

  switch ($method)
  {
    case 'json':
      if ($string === 'bnVsbA==') { return NULL; };
      $decoded = @json_decode($value, TRUE);
      if ($decoded !== NULL)
      {
        // JSON encoded string detected
        return $decoded;
      }
      break;
    default:
      if ($value === 'b:0;') { return FALSE; };
      $decoded = @unserialize($value);
      if ($decoded !== FALSE)
      {
        // Serialized encoded string detected
        return $decoded;
      }
  }

  // In all other cases return original var
  return $string;
}

/**
 * Parse number with units to numeric.
 *
 * This function converts numbers with units (e.g. 100MB) to their value
 * in bytes (e.g. 104857600).
 *
 * @param string $str
 * @param int Use custom rigid unit base (1000 or 1024)
 * @return int
 */
function unit_string_to_numeric($str, $unit_base = NULL)
{
  // If it's already a number, return original string
  if (is_numeric($str)) { return (float)$str; }

  preg_match('/(\d+\.?\d*)\ ?(\w+)/', $str, $matches);

  // Error, return original string
  if (!is_numeric($matches[1])) { return $str; }

  if (is_numeric($unit_base) && ($unit_base == 1000 || $unit_base == 1024))
  {
    // Use rigid unit base, this interprets any units with hard multiplier base
    $base = $unit_base;
  }

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
      $power = 0;
      $base = isset($base) ? $base : 1024;
      break;
    case 'K':
    case 'k':
    case 'kB':
    case 'kByte':
    case 'kbyte':
      $power = 1;
      $base = isset($base) ? $base : 1024;
      break;
    case 'kb':
    case 'kBps':
    case 'kbit':
    case 'kbps':
      $power = 1;
      $base = isset($base) ? $base : 1000;
      break;
    case 'M':
    case 'MB':
    case 'MByte':
    case 'Mbyte':
      $power = 2;
      $base = isset($base) ? $base : 1024;
      break;
    case 'Mb':
    case 'MBps':
    case 'Mbit':
    case 'Mbps':
      $power = 2;
      $base = isset($base) ? $base : 1000;
      break;
    case 'G':
    case 'GB':
    case 'GByte':
    case 'Gbyte':
      $power = 3;
      $base = isset($base) ? $base : 1024;
      break;
    case 'Gb':
    case 'GBps':
    case 'Gbit':
    case 'Gbps':
      $power = 3;
      $base = isset($base) ? $base : 1000;
      break;
    case 'T':
    case 'TB':
    case 'TByte':
    case 'Tbyte':
      $power = 4;
      $base = isset($base) ? $base : 1024;
      break;
    case 'Tb':
    case 'TBps':
    case 'Tbit':
    case 'Tbps':
      $power = 4;
      $base = isset($base) ? $base : 1000;
      break;
    default:
      $power = 0;
      $base = isset($base) ? $base : 1024;
      break;
  }
  $multiplier = pow($base, $power);

  return (float)($matches[1] * $multiplier);
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

/**
 * Reformat US-based dates to display based on $config['date_format']
 *
 * Supported input formats:
 *   DD/MM/YYYY
 *   DD/MM/YY
 *
 * Handling of YY -> YYYY years is passed on to PHP's strtotime, which
 * is currently cut off at 1970/2069.
 *
 * @param string $date Erroneous date format
 * @return string $date
 */
function reformat_us_date($date)
{
  global $config;

  list($month,$day,$year) = explode('/', $date);

  if (!is_numeric($year)) { return $date; }

  return date($config['date_format'], strtotime($date));
}

/**
 * Bitwise checking if flags set
 *
 * Examples:
 *  if (is_flag_set(FLAG_A, some_var)) // eg: some_var = 0b01100000000010
 *  if (is_flag_set(FLAG_A | FLAG_F | FLAG_L, some_var)) // to check if at least one flag is set
 *  if (is_flag_set(FLAG_A | FLAG_J | FLAG_M | FLAG_D, some_var, TRUE)) // to check if all flags are set
 *
 * @param int $flag Checked flags
 * @param int $param Parameter for checking
 * @param bool $all Check all flags
 * @return bool
 */
function is_flag_set($flag, $param, $all = FALSE)
{
  $set = $flag & $param;

  if                ($set and !$all) { return TRUE; } // at least one of the flags passed is set
  else if ($all and ($set == $flag)) { return TRUE; } // to check that all flags are set

  return FALSE;
}

// DOCME needs phpdoc block
// TESTME needs unit testing
function is_ssl()
{
  if (isset($_SERVER['HTTPS']))
  {
    if ('on' == strtolower($_SERVER['HTTPS'])) { return TRUE; }
    if ('1' == $_SERVER['HTTPS']) { return TRUE; }
  }
  else if (isset($_SERVER['SERVER_PORT']) && ('443' == $_SERVER['SERVER_PORT']))
  {
    return TRUE;
  }
  else if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) == 'https')
  {
    return TRUE;
  }

  return FALSE;
}

// EOF
