<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage snmp
 * @author     Adam Armstrong <adama@observium.org>
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

## If anybody has again the idea to implement the PHP internal library calls,
## be aware that it was tried and banned by lead dev Adam
##
## TRUE STORY. THAT SHIT IS WHACK. -- adama.

/**
 * Generates a list of mibdirs in the correct format for net-snmp
 *
 * @return string
 * @global config
 * @param $mib1, $mib2, ...
 */
function mib_dirs()
{
  global $config;

  $dirs = array($config['mib_dir']."/rfc", $config['mib_dir']."/net-snmp");

  foreach (func_get_args() as $mibs)
  {
    if (!is_array($mibs)) { $mibs = array($mibs); }

    foreach ($mibs as $mib)
    {
      if (ctype_alnum(str_replace(array('-', '_'), '', $mib)))
      {
        // If mib name equals 'mibs' just add root mib_dir to list
        $dirs[] = ($mib == 'mibs' ? $config['mib_dir'] : $config['mib_dir']."/".$mib);
      }
    }
  }

  return implode(":", array_unique($dirs));
}

/**
 * De-wrap 32bit counters
 * Crappy function to get workaround 32bit counter wrapping in HOST-RESOURCES-MIB
 * See: http://blog.logicmonitor.com/2011/06/11/linux-monitoring-net-snmp-and-terabyte-file-systems/
 *
 * @param integer $value
 * @return integer
 */
function snmp_dewrap32bit($value)
{
  if (is_numeric($value) && $value < 0)
  {
    return ($value + 4294967296);
  } else {
    return $value;
  }
}

/**
 * Clean returned numeric data from snmp output
 * Supports only non-scientific numbers
 * Examples: "  20,4" -> 20.4
 *
 * @param string $value
 * @return mixed $numeric
 */
function snmp_fix_numeric($value)
{
  if (is_numeric($value)) { return $value + 0; } // If already numeric just return value

  $numeric = trim($value, " \t\n\r\0\x0B\"");
  list($numeric) = explode(' ', $numeric);
  $numeric = preg_replace('/[^0-9a-z\-,\.]/i', '', $numeric);
  // Some retarded devices report data with spaces and commas: STRING: "  20,4"
  $numeric = str_replace(',', '.', $numeric);
  if (is_numeric($numeric))
  {
    // If cleaned data is numeric return number
    return $numeric + 0;
  } else {
    // Else return original value
    return $value;
  }
}

/**
 * Fixed ascii coded chars in snmp string as correct UTF-8 chars
 *
 * NOTE, currently support only one-byte unicode
 *
 * Examples: "This is a &#269;&#x5d0; test&#39; &#250;" -> "This is a čא test' ú"
 *           "P<FA>lt stj<F3>rnst<F6><F0>"              -> "Púlt stjórnstöð"
 *
 * @param string $string
 * @return string $string
 */
function snmp_fix_string($string)
{
  if (!preg_match('/^[[:print:]\p{L}]*$/mu', $string))
  {
    // find unprintable and all unicode chars, because old pcre library not always detect orb
    $debug_msg = '>>> Founded unprintable chars in string:' . PHP_EOL . $string;
    $string = preg_replace_callback('/[^[:print:]\x00-\x1F\x80-\x9F]/m', 'convert_ord_char', $string);
    print_debug($debug_msg . PHP_EOL . '>>> Converted to:' . PHP_EOL . $string . PHP_EOL);
  }
  return $string;
}

/**
 * Convert an SNMP hex string to regular string
 *
 * @param string $string
 * @return string
 */
function snmp_hexstring($string)
{
  if (isHexString($string))
  {
    return hex2str(str_replace(' 00', '', $string)); // 00 is EOL
  } else {
    return $string;
  }
}

/**
 * Check if returned snmp value is valid
 *
 * @param string $value
 * @return bool
 */
function is_valid_snmp_value($value)
{
  $valid = strpos($value, 'at this OID') === FALSE &&
           strpos($value, 'No more variables left') === FALSE &&
           $value != 'NULL';

  return $valid;
}

// Translate OID string to numeric:
//'BGP4-V2-MIB-JUNIPER::jnxBgpM2PeerRemoteAs' -> '.1.3.6.1.4.1.2636.5.1.1.2.1.1.1.13'
// or numeric OID to string:
// '.1.3.6.1.4.1.9.1.685' -> 'ciscoAIRAP1240'
// DOCME needs phpdoc block
// TESTME needs unit testing
function snmp_translate($oid, $mib = NULL, $mibdir = NULL)
{
  // $rewrite_oids set in rewrites.inc.php
  global $rewrite_oids, $config;

  if (is_numeric(str_replace('.', '', $oid)))
  {
    $options = '-Os';
  }
  else if ($mib)
  {
    // If $mib::$oid known in $rewrite_oids use this value instead shell command snmptranslate.
    if (isset($rewrite_oids[$mib][$oid]))
    {
      print_debug("SNMP TRANSLATE (REWRITE): '$mib::$oid' -> '".$rewrite_oids[$mib][$oid]."'");
      return $rewrite_oids[$mib][$oid];
    }
    $oid = $mib . '::' . $oid;
  }

  $cmd  = $config['snmptranslate'];
  if ($options) { $cmd .= ' ' . $options; } else { $cmd .= ' -On'; }
  if ($mib) { $cmd .= ' -m ' . $mib; }

  // Set correct MIB directories based on passed dirs and OS definition
  if ($mibdir)
  {
    $cmd .= " -M " . $mibdir;
  }
  else if (is_array($config['os'][$device['os']]['mib_dirs']))
  {
    // Add device-OS-specific MIB dirs
    $cmd .= ' -M ' . mib_dirs($config['os'][$device['os']]['mib_dirs']);
  }
  else if (isset($config['mibs'][$mib]['mib_dir'][0]))
  {
    // Add definition based MIB dir
    $cmd .= ' -M ' . mib_dirs($config['mibs'][$mib]['mib_dir']);
  } else {
    // Set default Observium MIB dir
    $cmd .= " -M " . $config['mib_dir'];
  }

  $cmd .= ' \'' . $oid . '\'';
  if (!OBS_DEBUG) { $cmd .= ' 2>/dev/null'; }

  $data = trim(external_exec($cmd));

  if ($data && !strstr($data, 'Unknown'))
  {
    print_debug("SNMP TRANSLATE (CMD): '$oid' -> '".$data."'");
    return $data;
  } else {
    return '';
  }
}

/**
 * Take -OXqs output and parse it into an array containing OID array and the value
 * Hopefully this is the beginning of more intelligent OID parsing!
 * Thanks to David Farrell <DavidPFarrell@gmail.com> for the parser solution.
 * This function is free for use by all with attribution to David.
 *
 * @return array
 * @param $string
 */
// TESTME needs unit testing
function parse_oid2($string)
{
  $result = array();
  $matches = array();

  // Match OID - If wrapped in double-quotes ('"'), must escape '"', else must escape ' ' (space) or '[' - Other escaping is optional
  $match_count = preg_match('/^(?:((?!")(?:[^\\\\\\[ ]|(?:\\\\.))+)|(?:"((?:[^\\\\\"]|(?:\\\\.))+)"))/', $string, $matches);
  if (null !== $match_count && $match_count > 0)
  {
    // [1] = unquoted, [2] = quoted
    $value = strlen($matches[1]) > 0 ? $matches[1] : $matches[2];
    $result[] = stripslashes($value);

    // I do this (vs keeping track of offset) to use ^ in regex
    $string = substr($string, strlen($matches[0]));

    // Match indexes (optional) - If wrapped in double-quotes ('"'), must escape '"', else must escape ']' - Other escaping is optional
    while (true)
    {
      $match_count = preg_match('/^\\[(?:((?!")(?:[^\\\\\\]]|(?:\\\\.))+)|(?:"((?:[^\\\\\"]|(?:\\\\.))+)"))\\]/', $string, $matches);
      if (null !== $match_count && $match_count > 0)
      {
        // [1] = unquoted, [2] = quoted
        $value = strlen($matches[1]) > 0 ? $matches[1] : $matches[2];
        $result[] = stripslashes($value);

        // I do this (vs keeping track of offset) to use ^ in regex
        $string = substr($string, strlen($matches[0]));
      }
      else
      {
        break;
      }
    } // while

    // Match value - Skips leading ' ' characters - If remainder is wrapped in double-quotes ('"'), must escape '"', other escaping is optional
    $match_count = preg_match('/^\\s+(?:((?!")(?:[^\\\\]|(?:\\\\.))+)|(?:"((?:[^\\\\\"]|(?:\\\\.))+)"))$/', $string, $matches);
    if (null !== $match_count && $match_count > 0)
    {
      // [1] = unquoted, [2] = quoted
      $value = strlen($matches[1]) > 0 ? $matches[1] : $matches[2];

      $result[] = stripslashes($value);

      if (strlen($string) != strlen($matches[0])) { echo "Length error!"; return null; }

      return $result;
    }
  }

  // All or nothing
  return null;
}

/**
 * Take -Oqs output and parse it into an array containing OID array and the value
 * Hopefully this is the beginning of more intelligent OID parsing!
 * Thanks to David Farrell <DavidPFarrell@gmail.com> for the parser solution.
 * This function is free for use by all with attribution to David.
 *
 * @return array
 * @param $string
 */
// TESTME needs unit testing
function parse_oid($string)
{
  $result = array();
  while (true)
  {
    $matches = array();
    $match_count = preg_match('/^(?:((?:[^\\\\\\. "]|(?:\\\\.))+)|(?:"((?:[^\\\\"]|(?:\\\\.))+)"))((?:[\\. ])|$)/', $string, $matches);
    if (null !== $match_count && $match_count > 0)
    {
      // [1] = unquoted, [2] = quoted
      $value = strlen($matches[1]) > 0 ? $matches[1] : $matches[2];
      $result[] = stripslashes($value);

      // Are we expecting any more parts?
      if (strlen($matches[3]) > 0)
      {
        // I do this (vs keeping track of offset) to use ^ in regex
        $string = substr($string, strlen($matches[0]));
      }
      else
      {
        $ret['value'] = array_pop($result);
        $ret['oid']   = $result;
        return $ret;
      }
    }
    else
    {
      // All or nothing
      return null;
    }
  } // while
}

// DOCME needs phpdoc block
// TESTME needs unit testing
function string_to_oid($string)
{
  $oid = strlen($string);

  for($i = 0; $i != strlen($string); $i++)
  {
     $oid .= ".".ord($string[$i]);
  }

  return $oid;
}

// Dirty attempt to parse snmp stuff. YUCK.

// DOCME needs phpdoc block
// TESTME needs unit testing
function snmp_parser_quote($m)
{
  return str_replace(array('.',' '),
    array('PLACEHOLDER-DOT', 'PLACEHOLDER-SPACE'), $m[1]);
}

// DOCME needs phpdoc block
// TESTME needs unit testing
function snmp_parser_unquote($str)
{
  return str_replace(array('PLACEHOLDER-DOT', 'PLACEHOLDER-SPACE', 'PLACEHOLDER-ESCAPED-QUOTE'),
    array('.',' ','"'), $str);
}

/**
 * Build a commandline string for net-snmp commands.
 *
 * @param  string $command
 * @param  array  $device
 * @param  string $oids
 * @param  string $options
 * @param  string $mib
 * @param  string $mibdir
 * @global config
 * @global debug
 * @return string
 */
// TESTME needs unit testing
function snmp_command($command, $device, $oids, $options, $mib = NULL, $mibdir = NULL)
{
  global $config;

  // This is compatibility code after refactor in r6306, for keep devices up before DB updated
  if (isset($device['snmpver']) && !isset($device['snmp_version'])) // get_db_version() < 189)
  {
    // FIXME. Remove this in r7000
    $device['snmp_version'] = $device['snmpver'];
    foreach (array('transport', 'port', 'timeout', 'retries', 'community',
                   'authlevel', 'authname', 'authpass', 'authalgo', 'cryptopass', 'cryptoalgo') as $old_key)
    {
      // Convert to new device snmp keys
      $device['snmp_'.$old_key] = $device[$old_key];
    }
  }

  $nobulk = $device['snmp_version'] == 'v1' || (isset($device['snmp_nobulk']) && $device['snmp_nobulk']) ||
            (isset($config['os'][$device['os']]['nobulk']) && $config['os'][$device['os']]['nobulk']);
  // Get the full command path from the config. Chose between bulkwalk and walk. Add max-reps if needed.
  switch($command)
  {
    case "snmpwalk":
      if ($nobulk)
      {
        $cmd = $config['snmpwalk'];
      } else {
        $cmd = $config['snmpbulkwalk'];
        if ($config['snmp']['max-rep'] && is_numeric($config['os'][$device['os']]['snmp']['max-rep']))
        {
          $cmd .= ' -Cr'.$config['os'][$device['os']]['snmp']['max-rep'];
        }
      }
      break;
    case "snmpget":
      $cmd = $config[$command];
      break;
    case "snmpbulkget":
      if ($nobulk)
      {
        $cmd = $config['snmpget'];
      } else {
        $cmd = $config['snmpbulkget'];
        if ($config['snmp']['max-rep'] && is_numeric($config['os'][$device['os']]['snmp']['max-rep']))
        {
          $cmd .= ' -Cr'.$config['os'][$device['os']]['snmp']['max-rep'];
        }
      }
      break;
    default:
      print_error("THIS SHOULD NOT HAPPEN. PLEASE REPORT TO DEVELOPERS.");
      return FALSE;
  }

  // Set timeout values if set in the database
  if (is_numeric($device['snmp_timeout']) && $device['snmp_timeout'] > 0)
  {
    $snmp_timeout = $device['snmp_timeout'];
  }
  else if (isset($config['snmp']['timeout']))
  {
    $snmp_timeout = $config['snmp']['timeout'];
  }
  if (isset($snmp_timeout)) { $cmd .= " -t " . escapeshellarg($snmp_timeout); }

  // Set retries if set in the database
  if (is_numeric($device['snmp_retries']) && $device['snmp_retries'] >= 0)
  {
    $snmp_retries = $device['snmp_retries'];
  }
  else if (isset($config['snmp']['retries']))
  {
    $snmp_retries = $config['snmp']['retries'];
  }
  if (isset($snmp_retries)) { $cmd .= " -r " . escapeshellarg($snmp_retries); }

  // If no transport is set in the database, default to UDP.
  if (empty($device['snmp_transport']))
  {
    $device['snmp_transport'] = 'udp';
  }

  if (!$device['snmp_port'])
  {
    $device['snmp_port'] = 161;
  }

  // Add the SNMP authentication settings for the device
  $cmd .= snmp_gen_auth($device);

  // Hardcode ignoring underscore parsing errors because net-snmp is dumb as a bag of rocks
  $cmd .= " -Pu ";

  if ($options) { $cmd .= " " . $options; }
  if ($mib) { $cmd .= " -m " . $mib; }

  // Set correct MIB directories based on passed dirs and OS definition
  if ($mibdir)
  {
    $cmd .= " -M " . $mibdir;
  }
  else if (is_array($config['os'][$device['os']]['mib_dirs']))
  {
    // Add device-OS-specific MIB dirs
    $cmd .= ' -M ' . mib_dirs($config['os'][$device['os']]['mib_dirs']);
  }
  else if (isset($config['mibs'][$mib]['mib_dir'][0])) // Array or non-empty string
  {
    // Add definition based MIB dir
    $cmd .= ' -M ' . mib_dirs($config['mibs'][$mib]['mib_dir']);
  } else {
    // Set default Observium MIB dir
    $cmd .= " -M " . $config['mib_dir'];
  }

  // Add the device URI to the string
  $cmd .= " ".escapeshellarg($device['snmp_transport']).":".escapeshellarg($device['hostname']).":".escapeshellarg($device['snmp_port']);

  // Add the OID(s) to the string
  $cmd .= " ".$oids;

  // If we're not debugging, direct errors to /dev/null.
  if (!OBS_DEBUG) { $cmd .= " 2>/dev/null"; }

  return $cmd;
}

/**
 * Detect SNMP errors and log it in DB.
 * Error logged in poller modules only, all other just return error code
 *
 * @param string  $command  Used snmp command (ie: snmpget, snmpwalk)
 * @param array   $device   Device array (device_id not allowed)
 * @param string  $oid      SNMP oid string
 * @param string  $options  SNMP options
 * @param string  $mib      SNMP MIBs list
 * @param string  $mibdir   SNMP MIB dirs list
 * @return int              Numeric error code. Full list error codes see in definitions: $config['snmp']['errorcodes']
 */
function snmp_log_errors($command, $device, $oid, $options, $mib, $mibdir)
{
  $error_timestamp = time(); // current timestamp
  $error_codes = $GLOBALS['config']['snmp']['errorcodes'];
  $error_code = 0; // By default - OK

  if ($GLOBALS['snmp_status'] === FALSE)
  {
    if (is_array($oid))
    {
      $oid = implode(' ', $oid);
    }
    if ($mib == 'SNMPv2-MIB')
    {
      if ($oid == 'sysObjectID.0 sysUpTime.0') { return 900; } // this is isSNMPable test, ignore
      else if (isset($GLOBALS['config']['os'][$device['os']]['snmpable']) && in_array($oid, isset($GLOBALS['config']['os'][$device['os']]['snmpable'])))
      {
        return 900; // This is also isSNMPable, ignore
      }
    }

    $error_code = 999;

    if (strlen(trim($GLOBALS['exec_status']['stdout'])) === 0)
    {
      $error_code = 1;  // Empty output non critical
      if ($GLOBALS['exec_status']['exitcode'] === 1 || $GLOBALS['exec_status']['exitcode'] === -1)
      {
        $error_code = 1002;
      }
      else if ($GLOBALS['exec_status']['exitcode'] === 2)
      {
        // Reason: (noSuchName) There is no such variable name in this MIB.
        $error_code = 1000;
      }
    } else {
      if (preg_match('/(?:No Such Instance|No Such Object|There is no such variable|No more variables left|Wrong Type)/i', $GLOBALS['exec_status']['stdout']) ||
          $GLOBALS['exec_status']['stdout'] == 'NULL')
      {
        $error_code = 1000;
      }
      else if (stripos($GLOBALS['exec_status']['stdout'], 'Authentication failure') !== FALSE)
      {
        $error_code = 1001;
      }
      else if ($GLOBALS['exec_status']['exitcode'] === 2 || stripos($GLOBALS['exec_status']['stderr'], 'Timeout') !== FALSE)
      {
        // non critical
        $error_code = 2;
      }
      else if ($GLOBALS['exec_status']['exitcode'] === 1)
      {
        if      (strpos($GLOBALS['exec_status']['stdout'], ' NULL') !== FALSE) { $error_code = 1000; }
        else if ($GLOBALS['exec_status']['runtime'] > 10)                      { $error_code = 3; }
      }
    }

    $GLOBALS['snmp_error_code'] = $error_code; // Set global variable $snmp_error_code

    $msg = 'device: ' . $device['device_id'] . ', cmd: ' . $command . ', options: ' . $options;
    $msg .= ', mib: \'' . $mib . '\', oid: \'' . $oid . '\'';
    $msg .= ', cmd exitcode: ' . $GLOBALS['exec_status']['exitcode'] . ',' . PHP_EOL;
    $msg .= '             snmp error code: #' . $error_code . ', reason: \'' . $error_codes[$error_code]['reason'] . '\', runtime: ' . $GLOBALS['exec_status']['runtime'];

    if (OBS_DEBUG > 1)
    {
      print_debug('SNMP error - '. $msg);
    }

    // CLEANME. Compatibility, remove in r7500, but not before CE 0.16.1 (Oct 18, 2015)
    if (get_db_version() < 242)
    {
      return $error_code;
    }

    // Log error into DB, but only in poller modules, all other just return error code
    if (isset($GLOBALS['argv'][0]) && in_array(basename($GLOBALS['argv'][0]), array('poller.php')))
    {
      if ($error_code > 999 || $error_code < 900)
      {
        //$poll_period = 300;
        $poll_period = $GLOBALS['config']['rrd']['step'];
        // Count critical errors into DB (only for poller)
        $sql  = 'SELECT * FROM `snmp_errors` ';
        $sql .= 'WHERE `device_id` = ? AND `error_code` = ? AND `snmp_cmd` = ? AND `snmp_options` = ? AND `mib` = ? AND `oid` = ?;';
        $error_db = dbFetchRow($sql, array($device['device_id'], $error_code, $command, $options, $mib, $oid));
        if (isset($error_db['error_id']))
        {
          $error_db['error_count']++;

          // DEBUG, error rate, if error rate >= 0.95, than error appears in each poll run
          //$poll_count = round(($error_timestamp - $error_db['added']) / $poll_period) + 1;
          //$error_db['error_rate'] = $error_db['error_count'] / $poll_count;
          //$msg .= ', rate: ' . $error_db['error_rate'] . ' err/poll';
          //logfile('snmp.log', $msg);

          // Update count
          $update_array = array('error_count' => $error_db['error_count'],
                                'updated'     => $error_timestamp);
          if ($error_db['mib_dir'] != $mibdir)
          {
            $update_array['mib_dir'] = $mibdir;
          }
          dbUpdate($update_array, 'snmp_errors', '`error_id` = ?', array($error_db['error_id']));
        } else {
          dbInsert(array('device_id'          => $device['device_id'],
                         'error_count'        => 1,
                         'error_code'         => $error_code,
                         'error_reason'       => $error_codes[$error_code]['reason'],
                         'snmp_cmd_exitcode'  => $GLOBALS['exec_status']['exitcode'],
                         'snmp_cmd'           => $command,
                         'snmp_options'       => $options,
                         'mib'                => $mib,
                         'mib_dir'            => $mibdir,
                         'oid'                => $oid,
                         'added'              => $error_timestamp,
                         'updated'            => $error_timestamp), 'snmp_errors');
        }
      } else {
        // DEBUG
        //logfile('snmp.log', $msg);
      }
    }
  }

  return $error_code;
}

/**
 * Uses snmpget to fetch multiple OIDs and returns a parsed array.
 *
 * @param  array  $device
 * @param  array  $oids
 * @param  string $options
 * @param  string $mib
 * @param  string $mibdir
 * @global debug
 * @return array
 */
// TESTME needs unit testing
function snmp_get_multi($device, $oids, $options = "-OQUs", $mib = NULL, $mibdir = NULL, $flags = OBS_QUOTES_TRIM)
{
  global $runtime_stats, $mibs_loaded;

  if (is_array($oids))
  {
    $data = '';
    $oid_chunks = array_chunk($oids, 16);
    $GLOBALS['snmp_status'] = FALSE;
    foreach ($oid_chunks as $oid_chunk)
    {
      $oid_text = implode($oid_chunk, ' ');
      $cmd   = snmp_command('snmpget', $device, $oid_text, $options, $mib, $mibdir);
      $this_data = trim(external_exec($cmd));
      $GLOBALS['snmp_status'] = ($GLOBALS['exec_status']['exitcode'] === 0 ? TRUE : $GLOBALS['snmp_status']);
      snmp_log_errors('snmpget', $device, $oid_text, $options, $mib, $mibdir);
      $data .= $this_data."\n";
      $runtime_stats['snmpget']++;
    }
  } else {
    $cmd  = snmp_command('snmpget', $device, $oids, $options, $mib, $mibdir);
    $data = trim(external_exec($cmd));
    $GLOBALS['snmp_status'] = ($GLOBALS['exec_status']['exitcode'] === 0 ? TRUE : FALSE);
    snmp_log_errors('snmpget', $device, $oids, $options, $mib, $mibdir);
    $runtime_stats['snmpget']++;
  }

  foreach (explode("\n", $data) as $entry)
  {
    list($oid,$value) = explode("=", $entry, 2);
    $oid   = trim($oid);
    $value = trim_quotes($value, $flags);
    list($oid, $index) = explode(".", $oid);
    if (strpos($value, 'Wrong Type') === 0)
    {
      // Remove Wrong Type string
      $value = preg_replace('/Wrong Type .*?: (.*)/s', '\1', $value);
    }
    if (isset($oid[0]) && is_valid_snmp_value($value))
    {
      $array[$index][$oid] = $value;
    }
  }
  if (empty($array))
  {
    $GLOBALS['snmp_status'] = FALSE;
    snmp_log_errors('snmpget', $device, $oids, $options, $mib, $mibdir);
  }

  if (OBS_DEBUG)
  {
    print_message('SNMP_STATUS['.($GLOBALS['snmp_status'] ? '%gTRUE': '%rFALSE').'%n]', 'color');
  }

  return $array;
}

/**
 * Uses snmpget to fetch a single OID and returns a string.
 *
 * @param  array  $device
 * @param  array  $oid
 * @param  string $options
 * @param  string $mib
 * @param  string $mibdir
 * @global debug
 * @return string
 */
// TESTME needs unit testing
function snmp_get($device, $oid, $options = NULL, $mib = NULL, $mibdir = NULL, $flags = OBS_QUOTES_TRIM)
{
  global $runtime_stats,$mibs_loaded;

  if (strpos($oid, ' '))
  {
    print_debug("WARNING: snmp_get called for multiple OIDs: $oid");
  }

  $cmd = snmp_command('snmpget', $device, $oid, $options, $mib, $mibdir);
  $data = external_exec($cmd);
  $data = trim_quotes($data, $flags);
  $GLOBALS['snmp_status'] = ($GLOBALS['exec_status']['exitcode'] === 0 ? TRUE : FALSE);

  $runtime_stats['snmpget']++;

  if (isset($data[0])) // same as strlen($data) > 0
  {
    if (strpos($data, 'Wrong Type') === 0)
    {
      // Remove Wrong Type string
      $data = preg_replace('/Wrong Type .*?: (.*)/s', '\1', $data);
    }
    if (preg_match('/(?:No Such Instance|No Such Object|There is no such variable|No more variables left|Authentication failure)/i', $data) ||
        $data == 'NULL')
    {
      $data = '';
      $GLOBALS['snmp_status'] = FALSE;
    }
  } else {
    $GLOBALS['snmp_status'] = FALSE;
  }
  if (OBS_DEBUG)
  {
    print_message('SNMP_STATUS['.($GLOBALS['snmp_status'] ? '%gTRUE': '%rFALSE').'%n]', 'color');
  }
  snmp_log_errors('snmpget', $device, $oid, $options, $mib, $mibdir);

  return $data;
}

// DOCME needs phpdoc block
// TESTME needs unit testing
function snmp_walk_parser2($device, $oid, $oid_elements, $mib = NULL, $mibdir = NULL)
{
  $data = snmp_walk($device, $oid, "-Oqs", $mib, $mibdir, FALSE);
  foreach (explode("\n", $data) as $text)
  {
    $ret = parse_oid2($text);
    if (!empty($ret['value']))
    {
      // this seems retarded. need a way to just build this automatically.
      switch ($oid_elements)
      {
        case "1":
          $array[$ret['oid'][0]] = $ret['value'];
          break;
        case "2":
          $array[$ret['oid'][1]][$ret['oid'][0]] = $ret['value'];
          break;
        case "3":
          $array[$ret['oid'][1]][$ret['oid'][2]][$ret['oid'][0]] = $ret['value'];
          break;
        case "4":
          $array[$ret['oid'][1]][$ret['oid'][2]][$ret['oid'][3]][$ret['oid'][0]] = $ret['value'];
          break;
      }
    }
  }
  return $array;
}

// DOCME needs phpdoc block
// TESTME needs unit testing
function snmp_walk_parser($device, $oid, $oid_elements, $mib = NULL, $mibdir = NULL)
{
  $data = snmp_walk($device, $oid, "-Oqs", $mib, $mibdir, FALSE);
  foreach (explode("\n", $data) as $text)
  {
    $ret = parse_oid($text);
    if (!empty($ret['value']))
    {
      // this seems retarded. need a way to just build this automatically.
      switch ($oid_elements)
      {
        case "1":
          $array[$ret['oid'][0]] = $ret['value'];
          break;
        case "2":
          $array[$ret['oid'][1]][$ret['oid'][0]] = $ret['value'];
          break;
        case "3":
          $array[$ret['oid'][1]][$ret['oid'][2]][$ret['oid'][0]] = $ret['value'];
          break;
        case "4":
          $array[$ret['oid'][1]][$ret['oid'][2]][$ret['oid'][3]][$ret['oid'][0]] = $ret['value'];
          break;
      }
    }
  }

  return $array;
}

// DOCME needs phpdoc block
// TESTME needs unit testing
function snmp_walk($device, $oid, $options = NULL, $mib = NULL, $mibdir = NULL, $flags = OBS_QUOTES_STRIP) // FIXME, why strip quotes is default? this removes all quotes also in index
{
  global $runtime_stats;

  $cmd = snmp_command('snmpwalk', $device, $oid, $options, $mib, $mibdir);
  $data = trim(external_exec($cmd));

  $GLOBALS['snmp_status'] = ($GLOBALS['exec_status']['exitcode'] === 0 ? TRUE : FALSE);

  if (is_flag_set(OBS_QUOTES_STRIP, $flags))
  {
    if (OBS_DEBUG > 1)
    {
      print_warning("All quotes striped from snmp output, also for indexes and values!");
    }
    $data = str_replace("\"", "", $data); }

  if (is_string($data) && (preg_match("/No Such (Object|Instance)/i", $data)))
  {
    $data = '';
    $GLOBALS['snmp_status'] = FALSE;
  } else {
    if (preg_match('/No more variables left in this MIB View \(It is past the end of the MIB tree\)$/', $data)
     || preg_match('/End of MIB$/', $data))
    {
      # Bit ugly :-(
      $d_ex = explode("\n",$data);
      $d_ex_count = count($d_ex);
      if ($d_ex_count > 1)
      {
        // Remove last line
        unset($d_ex[$d_ex_count-1]);
        $data = implode("\n",$d_ex);
      } else {
        $data = '';
        $GLOBALS['snmp_status'] = FALSE;
      }
    }

    // Concatinate multiline values if not set option -Oq
    if (is_flag_set(OBS_SNMP_CONCAT, $flags) && $data && strpos($options, 'q') === FALSE)
    {
      $old_data = $data;
      $data = array();
      foreach (explode("\n", $old_data) as $line)
      {
        if (strpos($line, '=') !== FALSE)
        {
          $data[] = $line;
        } else {
          //$data[count($data)-1] .= '\n' . $line; // here NOT newline char, but two chars!
          $data[count($data)-1] .= ' ' . $line;
        }
      }
      unset($old_data);
      $data = implode("\n", $data);
    }
  }
  $runtime_stats['snmpwalk']++;
  if (OBS_DEBUG)
  {
    print_message('SNMP_STATUS['.($GLOBALS['snmp_status'] ? '%gTRUE': '%rFALSE').'%n]', 'color');
  }
  snmp_log_errors('snmpwalk', $device, $oid, $options, $mib, $mibdir);

  return $data;
}

// DOCME needs phpdoc block
// TESTME needs unit testing
function snmpwalk_cache_cip($device, $oid, $array, $mib = NULL, $mibdir = NULL)
{
  $data = snmp_walk($device, $oid, "-OsnQ", $mib, $mibdir);

  foreach (explode("\n", $data) as $entry)
  {
    list ($this_oid, $this_value) = preg_split("/=/", $entry);
    $this_oid = trim($this_oid);
    $this_value = trim($this_value);
    $this_oid = substr($this_oid, 30);
    list($ifIndex,$dir,$a,$b,$c,$d,$e,$f) = explode(".", $this_oid);
    $h_a = zeropad(dechex($a));
    $h_b = zeropad(dechex($b));
    $h_c = zeropad(dechex($c));
    $h_d = zeropad(dechex($d));
    $h_e = zeropad(dechex($e));
    $h_f = zeropad(dechex($f));
    $mac = "$h_a$h_b$h_c$h_d$h_e$h_f";
    if ($dir == "1") { $dir = "input"; } elseif ($dir == "2") { $dir = "output"; }
    if ($mac && $dir)
    {
      $array[$ifIndex][$mac][$oid][$dir] = $this_value;
    }
  }
  return $array;
}

// Cache snmpEngineID
// DOCME needs phpdoc block
// TESTME needs unit testing
function snmp_cache_snmpEngineID($device)
{
  global $cache;

  if ($device['snmp_version'] == 'v1') { return; } // snmpEngineID allowed only in v2c/v3

  if (!isset($cache['snmp'][$device['device_id']]['snmpEngineID']))
  {
    $snmpEngineID = snmp_get($device, "snmpEngineID.0", "-Ovqn", "SNMP-FRAMEWORK-MIB", mib_dirs());
    $snmpEngineID = str_replace(array(' ', '"', "'", "\n", "\r"), '', $snmpEngineID);

    $cache['snmp'][$device['device_id']]['snmpEngineID'] = $snmpEngineID;
  }

  return $cache['snmp'][$device['device_id']]['snmpEngineID'];
}

// Return just an array of values without oids.
// DOCME needs phpdoc block
// TESTME needs unit testing
function snmpwalk_values($device, $oid, $array, $mib = NULL, $mibdir = NULL)
{
  $data = snmp_walk($device, $oid, "-OQUs", $mib, $mibdir);
  foreach (explode("\n", $data) as $entry)
  {
    list($oid,$value) = explode("=", $entry, 2);
    $oid = trim($oid); $value = trim($value);
    list($oid, $index) = explode(".", $oid, 2);
    if (isset($oid) && isset($index) && is_valid_snmp_value($value))
    {
      $array[] = $value;
    }
  }

  return $array;
}

// Return an array of values with numeric oids as keys
// DOCME needs phpdoc block
// TESTME needs unit testing
function snmpwalk_numericoids($device, $oid, $array, $mib = NULL, $mibdir = NULL)
{
  $data = snmp_walk($device, $oid, "-OQUn", $mib, $mibdir);
  foreach (explode("\n", $data) as $entry)
  {
    list($oid,$value) = explode("=", $entry, 2);
    $oid = trim($oid); $value = trim($value);
    if (isset($oid) && is_valid_snmp_value($value))
    {
      $array[$oid] = $value;
    }
  }

  return $array;
}

/**
 * Uses snmpget to fetch a single OID and returns a string.
 *
 * @param  array  $device
 * @param  string $oid
 * @param  string $mib
 * @param  string $mibdir
 * @return array
 */
function snmpwalk_cache_oid($device, $oid, $array, $mib = NULL, $mibdir = NULL, $flags = OBS_SNMP_ALL)
{
  // CLEANME, remove in r7500 (Oct 20, 2015)
  if (is_bool($flags))
  {
    $flags = ($flags ? OBS_SNMP_ALL : OBS_SNMP_ALL ^ OBS_QUOTES_STRIP);
  }

  $data = snmp_walk($device, $oid, "-OQUs", $mib, $mibdir, $flags);

  foreach (explode("\n", $data) as $entry)
  {
    list($oid,$value) = explode("=", $entry, 2);
    $oid = trim($oid); $value = trim_quotes($value, $flags);
    list($oid, $index) = explode(".", $oid, 2);
    if (isset($oid) && isset($index) && is_valid_snmp_value($value))
    {
      $array[$index][$oid] = $value;
    }
  }

  return $array;
}

// just like snmpwalk_cache_oid except that it returns the numerical oid as the index
// this is useful when the oid is indexed by the mac address and snmpwalk would
// return periods (.) for non-printable numbers, thus making many different indexes appear
// to be the same.
// DOCME needs phpdoc block
// TESTME needs unit testing
function snmpwalk_cache_oid_num($device, $oid, $array, $mib = NULL, $mibdir = NULL)
{
  $data = snmp_walk($device, $oid, "-OQUn", $mib, $mibdir);
  foreach (explode("\n", $data) as $entry)
  {
    list($oid,$value) = explode("=", $entry, 2);
    $oid = trim($oid); $value = trim($value);
    list($oid, $index) = explode(".", $oid, 2);
    if (isset($oid) && isset($index) && is_valid_snmp_value($value))
    {
      $array[$index][$oid] = $value;
    }
  }

  return $array;
}

// just like snmpwalk_cache_oid_num (it returns the numerical oid as the index),
// but use snmptranslate for cut mib part from index
/// FIXME. maybe override function snmpwalk_cache_oid_num()?
// DOCME needs phpdoc block
// TESTME needs unit testing
function snmpwalk_cache_oid_num2($device, $oid, $array, $mib = NULL, $mibdir = NULL)
{
  $data = snmp_walk($device, $oid, '-OQUn', $mib, $mibdir);

  $translate = snmp_translate($oid, $mib, $mibdir);
  $pattern = '/^' . str_replace('.', '\.', $translate) . '\./';

  foreach (explode("\n", $data) as $entry)
  {
    list($oid_num, $value) = explode("=", $entry, 2);
    $oid_num = trim($oid_num); $value = trim($value);
    $index = preg_replace($pattern, '', $oid_num);

    if (isset($oid) && isset($index) && is_valid_snmp_value($value))
    {
      $array[$index][$oid] = $value;
    }
  }

  return $array;
}

// DOCME needs phpdoc block
// TESTME needs unit testing
function snmpwalk_cache_multi_oid($device, $oid, $array, $mib = NULL, $mibdir = NULL, $flags = OBS_SNMP_ALL)
{
  // CLEANME, remove in r7500 (Oct 20, 2015), grep by code not found using of variable $numericindex
  if (is_bool($flags))
  {
    $flags = ($flags ? OBS_SNMP_ALL_NUMERIC : OBS_SNMP_ALL);
  }
  $output = "QUs";
  if (is_flag_set(OBS_SNMP_NUMERIC, $flags)) { $output .= "b"; }
  if (is_flag_set(OBS_SNMP_ENUM, $flags))    { $output .= "e"; }
  if (is_flag_set(OBS_SNMP_HEX, $flags))     { $output .= "x"; }

  $data = snmp_walk($device, $oid, "-O$output", $mib, $mibdir, $flags);
  foreach (explode("\n", $data) as $entry)
  {
    list($r_oid,$value) = explode("=", $entry, 2);
    $r_oid = trim($r_oid);
    $oid_parts = explode(".", $r_oid);
    if (count($oid_parts) < 2) { continue; }
    $r_oid = $oid_parts['0'];
    $index = implode('.', array_slice($oid_parts,1));
    $value = trim_quotes($value, $flags);
    if (isset($r_oid) && count($index) && is_valid_snmp_value($value))
    {
      $array[$index][$r_oid] = $value;
    }
  }

  return $array;
}

// DOCME needs phpdoc block
// TESTME needs unit testing
// used only in discovery/processors/juniper-system-mib.inc.php
function snmpwalk_cache_double_oid($device, $oid, $array, $mib = NULL, $mibdir = NULL, $flags = OBS_SNMP_ALL)
{
  // CLEANME, remove in r7500 (Oct 20, 2015), grep by code not found using of variable $numericindex
  if (is_bool($flags))
  {
    $flags = ($flags ? OBS_SNMP_ALL_NUMERIC : OBS_SNMP_ALL);
  }
  $output = "QUs";
  if (is_flag_set(OBS_SNMP_NUMERIC, $flags)) { $output .= "b"; }

  $data = snmp_walk($device, $oid, "-O$output", $mib, $mibdir, $flags);

  foreach (explode("\n", $data) as $entry)
  {
    list($oid,$value) = explode("=", $entry, 2);
    $oid = trim($oid); $value = trim_quotes($value, $flags);
    list($oid, $first, $second) = explode(".", $oid);
    if (isset($oid) && isset($first) && isset($second) && is_valid_snmp_value($value))
    {
      $double = $first.".".$second;
      $array[$double][$oid] = $value;
    }
  }

  return $array;
}

// DOCME needs phpdoc block
// TESTME needs unit testing
function snmpwalk_cache_triple_oid($device, $oid, $array, $mib = NULL, $mibdir = NULL)
{
  $data = snmp_walk($device, $oid, "-OQUs", $mib, $mibdir);

  foreach (explode("\n", $data) as $entry)
  {
    list($oid, $value) = explode("=", $entry, 2);
    $oid = trim($oid); $value = trim($value);
    list($oid, $first, $second, $third) = explode(".", $oid);
    if (isset($oid) && isset($first) && isset($second) && is_valid_snmp_value($value))
    {
      $index = $first.".".$second.".".$third;
      $array[$index][$oid] = $value;
    }
  }

  return $array;
}

// DOCME needs phpdoc block
// TESTME needs unit testing
function snmpwalk_cache_twopart_oid($device, $oid, $array, $mib = NULL, $mibdir = NULL, $flags = OBS_SNMP_ALL)
{
  // CLEANME, remove in r7500 (Oct 20, 2015), grep by code not found using of variable $numericindex
  if (is_bool($flags))
  {
    $flags = ($flags ? OBS_SNMP_ALL_NUMERIC : OBS_SNMP_ALL);
  }
  if (is_flag_set(OBS_SNMP_NUMERIC, $flags)) { $switches = "-OQUsb"; } else { $switches = "-OQUs"; }

  $data = snmp_walk($device, $oid, $switches, $mib, $mibdir, $flags);

  foreach (explode("\n", $data) as $entry)
  {
    list($oid,$value) = explode("=", $entry, 2);
    $oid = trim($oid); $value = trim_quotes($value, $flags);

    if (strpos($oid, '"') !== FALSE)
    {
      $oid_part = $oid;
      $oid_parts = array();
      do
      {
        if (preg_match('/^"([^"]*)"(?:\.(.+))?/', $oid_part, $matches))
        {
          // Part with stripes
          $oid_parts[] = $matches[1];
          $oid_part    = $matches[2]; // Next part
        } else {
          $matches = explode('.', $oid_part, 2);
          $oid_parts[] = $matches[0];
          $oid_part    = $matches[1]; // Next part
        }
        // print_vars($matches);
      } while (strlen($oid_part) > 0);
      // print_vars($oid_parts);
      $oid    = $oid_parts[0];
      $first  = $oid_parts[1];
      $second = $oid_parts[2];
    } else {
      // Simple, not always correct
      list($oid, $first, $second) = explode(".", $oid);
    }

    if (isset($oid) && isset($first) && isset($second) && is_valid_snmp_value($value))
    {
      $array[$first][$second][$oid] = $value;
    }
  }

  return $array;
}

// DOCME needs phpdoc block
// TESTME needs unit testing
function snmpwalk_cache_threepart_oid($device, $oid, $array, $mib = NULL, $mibdir = NULL, $flags = OBS_SNMP_ALL)
{
  // CLEANME, remove in r7500 (Oct 20, 2015), grep by code not found using of variable $numericindex
  if (is_bool($flags))
  {
    $flags = ($flags ? OBS_SNMP_ALL_NUMERIC : OBS_SNMP_ALL);
  }

  $output = "QUs";
  if (is_flag_set(OBS_SNMP_NUMERIC, $flags)) { $output .= "b"; }

  $data = snmp_walk($device, $oid, "-O$output", $mib, $mibdir, $flags);

  foreach (explode("\n", $data) as $entry)
  {
    list($oid,$value) = explode("=", $entry, 2);
    $oid = trim($oid); $value = trim_quotes($value, $flags);

    if (strpos($oid, '"') !== FALSE)
    {
      // Example: jnxVpnPwLocalSiteId.l2Circuit."ge-0/1/1.0".621
      $oid_part = $oid;
      $oid_parts = array();
      do
      {
        if (preg_match('/^"([^"]*)"(?:\.(.+))?/', $oid_part, $matches))
        {
          // Part with stripes
          $oid_parts[] = $matches[1];
          $oid_part    = $matches[2]; // Next part
        } else {
          $matches = explode('.', $oid_part, 2);
          $oid_parts[] = $matches[0];
          $oid_part    = $matches[1]; // Next part
        }
        // print_vars($matches);
      } while (strlen($oid_part) > 0);
      // print_vars($oid_parts);
      $oid    = $oid_parts[0];
      $first  = $oid_parts[1];
      $second = $oid_parts[2];
      $third  = $oid_parts[3];
    } else {
      // Simple, not always correct
      list($oid, $first, $second, $third) = explode(".", $oid);
    }
    print_debug("$entry || $oid || $first || $second || $third");
    if (isset($oid) && isset($first) && isset($second) && isset($third) && is_valid_snmp_value($value))
    {
      $array[$first][$second][$third][$oid] = $value;
    }
  }

  return $array;
}

// DOCME needs phpdoc block
// TESTME needs unit testing
function snmp_cache_slotport_oid($oid, $device, $array, $mib = NULL)
{
  ## FIXME -- convert to snmp_*

  $data = snmp_walk($device, $oid, "-OQUs", $mib, mib_dirs());

  $device_id = $device['device_id'];

  foreach (explode("\n", $data) as $entry)
  {
    $entry = str_replace($oid.".", "", $entry);
    list($slotport, $value) = explode("=", $entry, 2);
    $slotport = trim($slotport); $value = trim($value);
    if ($array[$slotport]['ifIndex'] && is_valid_snmp_value($value))
    {
      $ifIndex = $array[$slotport]['ifIndex'];
      $array[$ifIndex][$oid] = $value;
    }
  }

  return $array;
}

// DOCME needs phpdoc block
// TESTME needs unit testing
function snmp_cache_oid($oid, $device, $array, $mib = NULL, $mibdir = NULL)
{
  return snmpwalk_cache_oid($device, $oid, $array, $mib, $mibdir);
}

// DOCME needs phpdoc block
// TESTME needs unit testing
function snmpget_entity_oids($oids, $index, $device, $array, $mib = NULL)
{
  foreach ($oids as $oid)
  {
    $oid_string .= " $oid.$index";
  }

  return snmp_get_multi($device, $oids, "-Ovq", $mib, mib_dirs());
}

/**
 * Build authentication for net-snmp commands using device array
 *
 * @return array
 * @param array $device
 */
// TESTME needs unit testing
function snmp_gen_auth(&$device)
{

  $cmd = '';
  $vlan = FALSE;

  if (isset($device['snmp_context']))
  {
    if (is_numeric($device['snmp_context']) && $device['snmp_context'] > 0 && $device['snmp_context'] < 4096 )
    {
      $vlan = $device['snmp_context'];
    }
  }

  switch ($device['snmp_version'])
  {
    case 'v3':
      $cmd = ' -v3 -l ' . escapeshellarg($device['snmp_authlevel']);
      /* NOTE.
       * For proper work of 'vlan-' context on cisco, it is necessary to add 'match prefix' in snmp-server config --mike
       * example: snmp-server group MONITOR v3 auth match prefix access SNMP-MONITOR
       */
      $cmd .= ($vlan) ? ' -n "vlan-' . $vlan . '"' : ' -n ""'; // Some devices, like HP, always require option '-n'

      switch ($device['snmp_authlevel'])
      {
        case 'authPriv':
          $cmd .= ' -x ' . escapeshellarg($device['snmp_cryptoalgo']);
          $cmd .= ' -X ' . escapeshellarg($device['snmp_cryptopass']);
          // no break here
        case 'authNoPriv':
          $cmd .= ' -a ' . escapeshellarg($device['snmp_authalgo']);
          $cmd .= ' -A ' . escapeshellarg($device['snmp_authpass']);
          $cmd .= ' -u ' . escapeshellarg($device['snmp_authname']);
          break;
        case 'noAuthNoPriv':
          // We have to provide a username anyway (see Net-SNMP doc)
          $cmd .= ' -u observium';
          break;
        default:
          print_error('ERROR: Unsupported SNMPv3 snmp_authlevel (' . $device['snmp_authlevel'] . ')');
      }
      break;

    case 'v2c':
    case 'v1':
      $cmd  = ' -' . $device['snmp_version'];
      $cmd .= ' -c ' . escapeshellarg($device['snmp_community']);
      if ($vlan) { $cmd .= '@' . $vlan; }
      break;
    default:
      print_error('ERROR: ' . $device['snmp_version'] . ' : Unsupported SNMP Version.');
  }

  if (OBS_DEBUG === 1 && !$GLOBALS['config']['snmp']['hide_auth'])
  {
    $debug_auth = "DEBUG: SNMP Auth options = $cmd";
    print_debug($debug_auth);
  }

  return $cmd;
}

// EOF
