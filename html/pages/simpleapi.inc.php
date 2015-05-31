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

switch ($vars['api'])
{
  case "errorcodes":
    include("pages/api/errorcodes.inc.php");
    break;
  default:

    include("pages/api/manual.inc.php");
}

// EOF
