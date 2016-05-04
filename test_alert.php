#!/usr/bin/env php
<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage cli
 * @author     Adam Armstrong <adama@observium.org>
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

chdir(dirname($argv[0]));

$options = getopt("a:d");

include("includes/sql-config.inc.php");
include($config['html_dir']."/includes/functions.inc.php");

//var_dump(cli_is_piped());

$scriptname = basename($argv[0]);

$cli = TRUE;

$localhost = get_localhost();

print_message("%g".OBSERVIUM_PRODUCT." ".OBSERVIUM_VERSION."\n%WTest Alert Notification%n\n", 'color');

print_versions();

// Allow the URL building code to build URLs with proper links.
$_SESSION['userlevel'] = 10;

if ($options['a'])
{

  if ($config['alerts']['disable']['all'])
  {
    print_warning("All alert notifications disabled in config \$config['alerts']['disable']['all'], ignore it for testing!");
    $config['alerts']['disable']['all'] = FALSE;
  }
  $alert_rules = cache_alert_rules();
  $alert_assoc = cache_alert_assoc();

  $sql  = "SELECT * FROM `alert_table`";
  $sql .= " LEFT JOIN `alert_table-state` ON `alert_table`.`alert_table_id` = `alert_table-state`.`alert_table_id`";
  $sql .= " WHERE `alert_table`.`alert_table_id` = ?";

  $entry = dbFetchRow($sql, array($options['a']));

  alert_notifier($entry);

} else {

  print_cli("
USAGE:
$scriptname -a alert_entry [-d debug]
", 'color');

  $arguments = new \cli\Arguments();
  $arguments->addFlag('d',  'Turn on debug output');
  $arguments->addFlag('dd', 'More verbose debug output');
  $arguments->addOption('a', array(
    'default'     => '<alert entry id>',
    'description' => 'Send test notification to for an alert entry'));
  echo $arguments->getHelpScreen();
  echo PHP_EOL . PHP_EOL;
}

// EOF
