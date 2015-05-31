<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage graphs
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

// FIXME - wtfbbq

if ($_SESSION['userlevel'] >= "5" || $auth)
{
  $id = mres($vars['id']);
  $title = "Customer :: ".mres($vars['id']);
  $auth = TRUE;
}

?>
