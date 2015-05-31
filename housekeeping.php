#!/usr/bin/env php
<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage housekeeping
 * @author     Adam Armstrong <adama@memetic.org>
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

chdir(dirname($argv[0]));

$options = getopt("A:Vyaselrptd");

if (isset($options['d']))
{
  echo("DEBUG!\n");
  $debug = TRUE;
  ini_set('display_errors', 1);
  ini_set('display_startup_errors', 1);
  ini_set('log_errors', 1);
#  ini_set('error_reporting', E_ALL ^ E_NOTICE);
} else {
  $debug = FALSE;
#  ini_set('display_errors', 0);
  ini_set('display_startup_errors', 0);
  ini_set('log_errors', 0);
#  ini_set('error_reporting', 0);
}

include("includes/defaults.inc.php");
include("config.php");
include("includes/definitions.inc.php");
include("includes/functions.inc.php");

$scriptname = basename($argv[0]);

$cli = is_cli();

if (isset($options['V']))
{
  print_message(OBSERVIUM_PRODUCT." ".OBSERVIUM_VERSION);
  exit;
}

print_message("%g".OBSERVIUM_PRODUCT." ".OBSERVIUM_VERSION."\n%WHouseKeeping%n\n", 'color');

// For interactive prompt/answer checks
// if it is started from crontab - prompt disabled and answer always 'yes'
if (is_cron())
{
  $prompt = FALSE;
} else {
  $prompt = !isset($options['y']);
}
$answer = TRUE;

$modules = array();

if (isset($options['a']) || isset($options['s'])) { $modules[] = 'syslog'; }
if (isset($options['a']) || isset($options['e'])) { $modules[] = 'eventlog'; }
if (isset($options['a']) || isset($options['l'])) { $modules[] = 'alertlog'; }
if (isset($options['a']) || isset($options['r'])) { $modules[] = 'rrd'; }
if (isset($options['a']) || isset($options['p'])) { $modules[] = 'ports'; }
if (isset($options['a']) || isset($options['t'])) { $modules[] = 'timing'; }

// Get age from command line
if (isset($options['A']))
{
  $age = age_to_seconds($options['A']);
  if ($age)
  {
    foreach ($modules as $module)
    {
      if ($module == 'ports') { $module = 'deleted_ports'; }
      $config['housekeeping'][$module]['age'] = $age;
    }
  } else {
    print_debug("Invalid age specified '" . $options['A'] . "', skipped.");
  }
  unset($age, $module);
}

if (!count($modules))
{
  print_message("%n
USAGE:
$scriptname [-Vyaserptd] [-A <age>]

NOTE, by default $scriptname asks 'Are you sure want to delete (y/N)?'.
      To assume 'yes' as answer to all prompts and run non-interactively,
      add '-y' in command line.
      Not necessary when run from cron (determined automatically).

OPTIONS:
 -V                                          Show version and exit.
 -y                                          Automatically answer 'yes' to prompts
 -a                                          Maintain all modules as specified below.
 -s                                          Clean up syslog
 -e                                          Clean up event log
 -l                                          Clean up alert log
 -r                                          Clean up unused RRD files
 -p                                          Clean up deleted ports
 -t                                          Clean up timing data (discovery and poll times)
 -A <age>                                    Specifies maximum age for all modules (overrides configuration)

DEBUGGING OPTIONS:
 -d                                          Enable debugging output.

EXAMPLES:
  $scriptname -a                        Clean up by all modules interactively (with prompts!)
  $scriptname -ya                       Clean up by all modules without prompts

%rInvalid arguments!%n", 'color', FALSE);
  exit;
} else {
  foreach ($modules as $module)
  {
    if (is_file($config['install_dir'] . "/includes/housekeeping/$module.inc.php"))
    {
      include($config['install_dir'] . "/includes/housekeeping/$module.inc.php");
    } else {
      print_warning("Housekeeping module not found: $module");
    }
  }
}

// EOF
