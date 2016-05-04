<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage rrdtool
 * @author     Adam Armstrong <adama@observium.org>
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

/**
 * Get full path for rrd file.
 *
 * @param array $device Device arrary
 * @param string $filename Base filename for rrd file
 * @return string Full rrd file path
 */
// TESTME needs unit testing
function get_rrd_path($device, $filename)
{
  $filename = safename($filename);
  $ext = pathinfo($filename, PATHINFO_EXTENSION);
  if ($ext != 'rrd') { $filename .= '.rrd'; } // Add rrd extension if not already set

  return trim($GLOBALS['config']['rrd_dir']) . "/" . $device['hostname'] . "/" . safename($filename);
}

/**
 * Rename rrd file for device is some schema changes.
 *
 * @param array $device
 * @param string $old_rrd Base filename for old rrd file
 * @param string $new_rrd Base filename for new rrd file
 * @return bool TRUE if renamed
 */
function rename_rrd($device, $old_rrd, $new_rrd)
{
  $old_rrd = get_rrd_path($device, $old_rrd);
  $new_rrd = get_rrd_path($device, $new_rrd);
  if (is_file($old_rrd))
  {
    $renamed = rename($old_rrd, $new_rrd);
    print_warning('Moved RRD');
    if (OBS_DEBUG > 1)
    {
      print_message("OLD RRD: $old_rrd\nNEW RRD: $new_rrd");
    }
  } else {
    $renamed = FALSE;
  }
  return $renamed;
}

/**
 * Opens up a pipe to RRDTool using handles provided
 *
 * @return boolean
 * @global array $config
 * @param &rrd_process
 * @param &rrd_pipes
 */
// TESTME needs unit testing
function rrdtool_pipe_open(&$rrd_process, &$rrd_pipes)
{
  global $config;

  $command = $config['rrdtool'] . " -"; // Waits for input via standard input (STDIN)

  $descriptorspec = array(
     0 => array("pipe", "r"),  // stdin
     1 => array("pipe", "w"),  // stdout
     2 => array("pipe", "w")   // stderr
  );

  $cwd = $config['rrd_dir'];
  $env = array();

  $rrd_process = proc_open($command, $descriptorspec, $rrd_pipes, $cwd, $env);

  stream_set_blocking($rrd_pipes[1], 0);
  stream_set_blocking($rrd_pipes[2], 0);

  if (is_resource($rrd_process))
  {
    // $pipes now looks like this:
    // 0 => writeable handle connected to child stdin
    // 1 => readable handle connected to child stdout
    // 2 => readable handle connected to child stderr
    if (OBS_DEBUG > 1)
    {
      print_message('RRD PIPE OPEN[%gTRUE%n]', 'console');
    }

    return TRUE;
  } else {
    if (isset($config['rrd']['debug']) && $config['rrd']['debug'])
    {
      logfile('rrd.log', "RRD pipe process not opened '$command'.");
    }
    if (OBS_DEBUG > 1)
    {
      print_message('RRD PIPE OPEN[%rFALSE%n]', 'console');
    }
    return FALSE;
  }
}

/**
 * Closes the pipe to RRDTool
 *
 * @return integer
 * @param resource rrd_process
 * @param array rrd_pipes
 */
// TESTME needs unit testing
function rrdtool_pipe_close($rrd_process, &$rrd_pipes)
{
  if (OBS_DEBUG > 1)
  {
    $rrd_status['stdout'] = stream_get_contents($rrd_pipes[1]);
    $rrd_status['stderr'] = stream_get_contents($rrd_pipes[2]);
  }

  if (is_resource($rrd_pipes[0]))
  {
    fclose($rrd_pipes[0]);
  }
  fclose($rrd_pipes[1]);
  fclose($rrd_pipes[2]);

  // It is important that you close any pipes before calling
  // proc_close in order to avoid a deadlock

  $rrd_status['exitcode'] = proc_close($rrd_process);
  if (OBS_DEBUG > 1)
  {
    print_message('RRD PIPE CLOSE['.($rrd_status['exitcode'] !== 0 ? '%rFALSE' : '%gTRUE').'%n]', 'console');
    if ($rrd_status['stdout'])
    {
      print_message("RRD PIPE STDOUT[\n".$rrd_status['stdout']."\n]", 'console', FALSE);
    }
    if ($rrd_status['exitcode'] && $rrd_status['stderr'])
    {
      // Show stderr if exitcode not 0
      print_message("RRD PIPE STDERR[\n".$rrd_status['stderr']."\n]", 'console', FALSE);
    }
  }

  return $rrd_status['exitcode'];
}

/**
 * Generates a graph file at $graph_file using $options
 * Opens its own rrdtool pipe.
 *
 * @return integer
 * @param string graph_file
 * @param string options
 */
// TESTME needs unit testing
function rrdtool_graph($graph_file, $options)
{
  // Note, always use pipes, because standart comman line have limits!
  if ($GLOBALS['config']['rrdcached'])
  {
    $cmd = "graph --daemon " . $GLOBALS['config']['rrdcached'] . " $graph_file $options";
  } else {
    $cmd = "graph $graph_file $options";
  }
  $GLOBALS['rrd_status']  = FALSE;
  $GLOBALS['exec_status'] = array('command'  => $GLOBALS['config']['rrdtool'] . ' ' . $cmd,
                                  'stdout'   => '',
                                  'exitcode' => -1);

  $start = microtime(TRUE);
  rrdtool_pipe_open($rrd_process, $rrd_pipes);
  if (is_resource($rrd_process))
  {
    // $pipes now looks like this:
    // 0 => writeable handle connected to child stdin
    // 1 => readable handle connected to child stdout
    // Any error output will be appended to /tmp/error-output.txt

    fwrite($rrd_pipes[0], $cmd);
    fclose($rrd_pipes[0]);

    $iter = 0;
    while (strlen($line) < 1 && $iter < 1000)
    {
      // wait for 10 milliseconds to loosen loop
      usleep(10000);
      $line = fgets($rrd_pipes[1], 1024);
      $stdout .= $line;
      $iter++;
    }
    $stdout = preg_replace('/(?:\n|\r\n|\r)$/D', '', $stdout); // remove last (only) eol
    unset($iter);

    $runtime  = microtime(TRUE) - $start;

    // Check rrdtool's output for the command.
    if (preg_match('/\d+x\d+/', $stdout))
    {
      $GLOBALS['rrd_status'] = TRUE;
    } else {
      $stderr = trim(stream_get_contents($rrd_pipes[2]));
      if (isset($config['rrd']['debug']) && $config['rrd']['debug'])
      {
        logfile('rrd.log', "RRD $stderr, CMD: " . $GLOBALS['exec_status']['command']);
      }
    }
    $exitcode = rrdtool_pipe_close($rrd_process, $rrd_pipes);

    $GLOBALS['exec_status']['exitcode'] = $exitcode;
    $GLOBALS['exec_status']['stdout']   = $stdout;
    $GLOBALS['exec_status']['stderr']   = $stderr;
  } else {
    $runtime = microtime(TRUE) - $start;
    $stdout  = NULL;
  }
  $GLOBALS['exec_status']['runtime']  = $runtime;

  if (OBS_DEBUG)
  {
    print_message(PHP_EOL . 'RRD CMD[%y' . $cmd . '%n]', 'console', FALSE);
    $debug_msg  = 'RRD RUNTIME['.($runtime > 0.1 ? '%r' : '%g').round($runtime, 4).'s%n]' . PHP_EOL;
    $debug_msg .= 'RRD STDOUT['.($GLOBALS['rrd_status'] ? '%g': '%r').$stdout.'%n]' . PHP_EOL;
    if ($stderr)
    {
      $debug_msg .= 'RRD STDERR[%r'.$stderr.'%n]' . PHP_EOL;
    }
    $debug_msg .= 'RRD_STATUS['.($GLOBALS['rrd_status'] ? '%gTRUE': '%rFALSE').'%n]';

    print_message($debug_msg . PHP_EOL, 'console');
  }
  return $stdout;
}

/**
 * Generates and pipes a command to rrdtool
 *
 * @param string command
 * @param string filename
 * @param string options
 * @global config
 * @global debug
 * @global rrd_pipes
 */
// TESTME needs unit testing
function rrdtool($command, $filename, $options)
{
  global $config, $rrd_pipes;

  $cmd = "$command $filename $options";
  if ($command != "create" && $config['rrdcached'])
  {
    $cmd .= " --daemon " . $config['rrdcached'];
  }

  $GLOBALS['rrd_status'] = FALSE;
  $GLOBALS['exec_status'] = array('command' => $config['rrdtool'] . ' ' . $cmd,
                                  'exitcode' => 1);

  if ($config['norrd'])
  {
    print_message("[%rRRD Disabled - $cmd%n]", 'color');
    return NULL;
  } else {
    $start = microtime(TRUE);
    fwrite($rrd_pipes[0], $cmd."\n");
    usleep(1000);
  }

  $stdout = trim(stream_get_contents($rrd_pipes[1]));
  $stderr = trim(stream_get_contents($rrd_pipes[2]));
  $runtime = microtime(TRUE) - $start;

  // Check rrdtool's output for the command.
  if (strpos($stdout, 'ERROR') !== FALSE)
  {
    if (isset($config['rrd']['debug']) && $config['rrd']['debug'])
    {
      logfile('rrd.log', "RRD $stdout, CMD: $cmd");
    }
  } else {
    $GLOBALS['rrd_status'] = TRUE;
    $GLOBALS['exec_status']['exitcode'] = 0;
  }
  $GLOBALS['exec_status']['stdout']  = $stdout;
  $GLOBALS['exec_status']['stdin']   = $stdin;
  $GLOBALS['exec_status']['runtime'] = $runtime;

  $GLOBALS['rrdtool'][$command]['time'] += $runtime;
  $GLOBALS['rrdtool'][$command]['count']++;

  if (OBS_DEBUG)
  {
    print_message(PHP_EOL . 'RRD CMD[%y' . $cmd . '%n]', 'console', FALSE);
    $debug_msg  = 'RRD RUNTIME['.($runtime > 1 ? '%r' : '%g').round($runtime, 4).'s%n]' . PHP_EOL;
    $debug_msg .= 'RRD STDOUT['.($GLOBALS['rrd_status'] ? '%g': '%r').$stdout.'%n]' . PHP_EOL;
    if ($stderr)
    {
      $debug_msg .= 'RRD STDERR[%r'.$stderr.'%n]' . PHP_EOL;
    }
    $debug_msg .= 'RRD_STATUS['.($GLOBALS['rrd_status'] ? '%gTRUE': '%rFALSE').'%n]';

    print_message($debug_msg . PHP_EOL, 'console');
  }
}

/**
 * Generates an rrd database at $filename using $options
 * Creates the file if it does not exist yet.
 *
 * @param array  device
 * @param string filename
 * @param string ds
 * @param string options
 */
// TESTME needs unit testing
function rrdtool_create($device, $filename, $ds, $options = '')
{
  global $config;

  if ($filename[0] == '/')
  {
    print_debug("You should pass the filename only (not the full path) to this function! Passed filename: ".$filename);
    $filename = basename($filename);
  }

  $fsfilename = get_rrd_path($device, $filename);

  if (is_file($fsfilename))
  {
    if (OBS_DEBUG > 1)
    {
      print_message("RRD $fsfilename already exists - no need to create.");
    }
    return FALSE; // Bail out if the file exists already
  }

  if (!$options)
  {
    $options = preg_replace('/\s+/', ' ', $config['rrd']['rra']);
  }

  $step = "--step ".$config['rrd']['step'];

  if ($config['norrd'])
  {
    print_message("[%rRRD Disabled - create $fsfilename%n]", 'color');
    return NULL;
  } else {
    $command = $config['rrdtool'] . " create $fsfilename $ds $step $options";
    // FIXME not possible to run this through rrdtool() ?
  }

  return external_exec($command);
}

/**
 * Updates an rrd database at $filename using $options
 * Where $options is an array, each entry which is not a number is replaced with "U"
 *
 * @param array  device
 * @param string filename
 * @param array  options
 */
// TESTME needs unit testing
function rrdtool_update($device, $filename, $options)
{
  // Do some sanitisation on the data if passed as an array.
  if (is_array($options))
  {
    $values[] = "N";
    foreach ($options as $value)
    {
      if (!is_numeric($value)) { $value = 'U'; }
      $values[] = $value;
    }
    $options = implode(':', $values);
  }

  if ($filename[0] == '/')
  {
    $filename = basename($filename);
    print_debug("You should pass the filename only (not the full path) to this function!");
  }

  $fsfilename = get_rrd_path($device, $filename);

  return rrdtool("update", $fsfilename, $options);
}

// DOCME needs phpdoc block
// TESTME needs unit testing
function rrdtool_fetch($filename, $options)
{
  return rrdtool("fetch", $filename, $options);
}

// DOCME needs phpdoc block
// TESTME needs unit testing
function rrdtool_last($filename, $options)
{
  return rrdtool("last", $filename, $options);
}

// DOCME needs phpdoc block
// TESTME needs unit testing
function rrdtool_lastupdate($filename, $options)
{
  return rrdtool("lastupdate", $filename, $options);
}

// TESTME needs unit testing
/**
 * Renames a DS inside an RRD file
 *
 * @param array Device
 * @param string Filename
 * @param string Current DS name
 * @param string New DS name
 */
function rrdtool_rename_ds($device, $filename, $oldname, $newname)
{
  global $config;

  if ($config['norrd'])
  {
    print_message("[%gRRD Disabled%n] ");
  } else {
    $fsfilename = get_rrd_path($device, $filename);

    rrdtool("tune", $fsfilename, "--data-source-rename $oldname:$newname");
  }
}

// TESTME needs unit testing
/**
 * Adds one or more RRAs to an RRD file; space-separated if you want to add more than one.
 *
 * @param array  Device
 * @param string Filename
 * @param array  RRA(s) to be added to the RRD file
 */
function rrdtool_add_rra($device, $filename, $options)
{
  global $config;

  if ($config['norrd'])
  {
    print_message("[%gRRD Disabled%n] ");
  } else {
    $fsfilename = get_rrd_path($device, $filename);

    external_exec($config['install_dir'] . "/scripts/rrdtoolx.py addrra $fsfilename $fsfilename.new $options");
    rename("$fsfilename.new", $fsfilename);
  }
}

/**
 * Escapes strings for RRDtool
 *
 * @param string String to escape
 * @param integer if passed, string will be padded and trimmed to exactly this length (after rrdtool unescapes it)
 *
 * @return string Escaped string
 */
// TESTME needs unit testing
function rrdtool_escape($string, $maxlength = NULL)
{
  if ($maxlength != NULL)
  {
    $string = substr(str_pad($string, $maxlength),0,$maxlength);
  }

  $string = str_replace(array(':', "'", '%'), array('\:', '`', '%%'), $string);

  // FIXME: should maybe also probably escape these? # \ ? [ ^ ] ( $ ) '

  return $string;
}

/**
 * Helper function to strip quotes from RRD output
 *
 * @str RRD-Info generated string
 * @return String with one surrounding pair of quotes stripped
 */
// TESTME needs unit testing
function rrd_strip_quotes($str)
{
  if ($str[0] == '"' && $str[strlen($str)-1] == '"')
  {
    return substr($str, 1, strlen($str)-2);
  }

  return $str;
}

/**
 * Determine useful information about RRD file
 *
 * Copyright (C) 2009  Bruno Prémont <bonbons AT linux-vserver.org>
 *
 * @file Name of RRD file to analyse
 *
 * @return Array describing the RRD file
 *
 */
// TESTME needs unit testing
function rrdtool_file_info($file)
{
  $info = array('filename'=>$file);

  $rrd = array_filter(explode(PHP_EOL, external_exec($GLOBALS['config']['rrdtool'] . " info " . $file)), 'strlen');
  if ($rrd)
  {
    foreach ($rrd as $s)
    {
      $p = strpos($s, '=');
      if ($p === false)
      {
        continue;
      }

      $key = trim(substr($s, 0, $p));
      $value = trim(substr($s, $p+1));
      if (strncmp($key,'ds[', 3) == 0)
      {
        /* DS definition */
        $p = strpos($key, ']');
        $ds = substr($key, 3, $p-3);
        if (!isset($info['DS']))
        {
          $info['DS'] = array();
        }

        $ds_key = substr($key, $p+2);

        if (strpos($ds_key, '[') === false)
        {
          if (!isset($info['DS']["$ds"]))
          {
            $info['DS']["$ds"] = array();
          }
          $info['DS']["$ds"]["$ds_key"] = rrd_strip_quotes($value);
        }
      }
      else if (strncmp($key, 'rra[', 4) == 0)
      {
        /* RRD definition */
        $p = strpos($key, ']');
        $rra = substr($key, 4, $p-4);
        if (!isset($info['RRA']))
        {
          $info['RRA'] = array();
        }
        $rra_key = substr($key, $p+2);

        if (strpos($rra_key, '[') === false)
        {
          if (!isset($info['RRA']["$rra"]))
          {
            $info['RRA']["$rra"] = array();
          }
          $info['RRA']["$rra"]["$rra_key"] = rrd_strip_quotes($value);
        }
      } else if (strpos($key, '[') === false) {
        $info[$key] = rrd_strip_quotes($value);
      }
    }
  }

  return $info;
}

// EOF
