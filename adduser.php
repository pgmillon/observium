#!/usr/bin/env php
<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage cli
 * @author     Adam Armstrong <adama@memetic.org>
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

chdir(dirname($argv[0]));

include("includes/defaults.inc.php");
include("config.php");
include("includes/definitions.inc.php");
include("includes/functions.inc.php");

$scriptname = basename($argv[0]);

print_message("%g".OBSERVIUM_PRODUCT." ".OBSERVIUM_VERSION."\n%WAdd User%n\n", 'color');

$auth_file = $config['html_dir'].'/includes/authentication/' . $config['auth_mechanism'] . '.inc.php';
if (is_file($auth_file))
{
  include($auth_file);

  // Include base auth functions calls
  include($config['html_dir'].'/includes/authenticate-functions.inc.php');
} else {
  print_error("ERROR: no valid auth_mechanism defined.");
  exit();
}

if (auth_usermanagement())
{
  if (isset($argv[1]) && isset($argv[2]) && isset($argv[3]))
  {
    if (!auth_user_exists($argv[1]))
    {
      if (adduser($argv[1], $argv[2], $argv[3], @$argv[4]))
      {
        print_success("User ".$argv[1]." added successfully.");
      } else {
        print_error("User ".$argv[1]." creation failed!");
      }
    } else {
      print_warning("User ".$argv[1]." already exists!");
    }
  } else {
    print_message("%n
USAGE:
$scriptname <username> <password> <level 1-10> [email]

EXAMPLE:
%WADMIN%n:   $scriptname <username> <password> 10 [email]
%WRW user%n: $scriptname <username> <password> 7  [email]
%WRO user%n: $scriptname <username> <password> 1  [email]

%rInvalid arguments!%n", 'color');
  }
} else {
  print_error("Auth module does not allow adding users!");
}

// EOF
