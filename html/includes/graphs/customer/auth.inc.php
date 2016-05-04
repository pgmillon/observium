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

// FIXME - wtfbbq

if ($_SESSION['userlevel'] >= "5" || $auth)
{
  $id = $vars['id'];
  $title = "Customer :: ". escape_html($vars['id']);
  $auth = TRUE;
}

?>
