<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage graphs
 * @copyright  (C) 2006-2015 Adam Armstrong
 *
 */

if ($_SESSION['userlevel'] > 5 || $auth)
{
  $auth = 1;
} else {
  // error?
}

// EOF
