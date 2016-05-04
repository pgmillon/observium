<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage graphs
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

// FIXME - wtfbbq

if ($_SESSION['userlevel'] >= "5" || $auth)
{
  $id = $vars['id'];
  $title = "Customer :: ". escape_html($vars['id']);
  $auth = TRUE;
}

// EOF
