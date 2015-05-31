<?php

/**
 * Observium Network Management and Monitoring System
 * Copyright (C) 2006-2014, Adam Armstrong - http://www.observium.org
 *
 * @package    observium
 * @subpackage webui
 * @author     Adam Armstrong <adama@memetic.org>
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

if ($_SESSION['userlevel'] == '10')
{
  print_warning("This is a dump of your Observium configuration. To adjust it, please modify your <strong>config.php</strong> file.");
  echo("<pre>");
  print_vars($config);
  echo("</pre>");
} else {
  include("includes/error-no-perm.inc.php");
}

// EOF
