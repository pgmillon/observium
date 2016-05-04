<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage ajax
 * @author     Adam Armstrong <adama@memetic.org>
 * @copyright  (C) 2006-2015 Adam Armstrong
 *
 */

include_once("../../includes/defaults.inc.php");
include_once("../../config.php");
include_once("../../includes/definitions.inc.php");

include($config['install_dir'] . "/includes/common.inc.php");
include($config['install_dir'] . "/includes/dbFacile.php");
include($config['install_dir'] . "/includes/rewrites.inc.php");
include($config['install_dir'] . "/includes/entities.inc.php");
include($config['html_dir'] . "/includes/functions.inc.php");
include($config['html_dir'] . "/includes/authenticate.inc.php");

if (!$_SESSION['authenticated']) { print_error('Session expired, please log in again!'); exit; }

$vars = get_vars();

if(preg_match('/^[a-zA-Z0-9\-]+$/', $vars['form']) == 1)
{
  $form_file = $config['html_dir'] . '/ajax/forms/'.$vars['form'].'.inc.php';
  if(file_exists($form_file))
  {
    include($form_file);
  } else {
    json_output("error", "Unknown form file (".$form_file.").");
  }
} else {

  json_output("error", "Invalid form type.");

}

// EOF
