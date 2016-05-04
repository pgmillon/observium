<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package        observium
 * @subpackage     webui
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

$link_array = array('page'    => 'p2pradios');

$navbar = array('brand' => "P2P Radios", 'class' => "navbar-narrow");

$navbar['options']['overview']['text'] = 'Overview';
$navbar['options']['graphs']['text']   = 'Graphs';

foreach ($navbar['options'] as $option => $array)
{
  if (!isset($vars['view'])) { $vars['view'] = "overview"; }
  if ($vars['view'] == $option) { $navbar['options'][$option]['class'] .= " active"; }
  $navbar['options'][$option]['url'] = generate_url($link_array,array('view' => $option));
}

print_navbar($navbar);
unset($navbar);

print_p2pradio_table($vars);

// EOF