<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage poller
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

// Very basic parser to parse classic Observium-type schemes.
// Parser should populate $port_ifAlias array with type, descr, circuit, speed and notes

function custom_port_parser($port)
{
  global $config;

  print_debug($port['ifAlias']);

  // Pull out Type and Description or abort
  if (!preg_match('/^([^:]+):([^\[\]\(\)\{\}]+)/', $port['ifAlias'], $matches))
  {
    return array();
  }

  // Munge and Validate type
  $types = array('core', 'peering', 'transit', 'cust', 'server', 'l2tp');
  foreach ($config['int_groups'] as $custom_type)
  {
    $types[] = strtolower(trim($custom_type));
  }
  $type  = strtolower(trim($matches[1], " \t\n\r\0\x0B\\/\"'"));
  if (!in_array($type, $types)) { return array(); }

  # Munge and Validate description
  $descr = trim($matches[2]);
  if ($descr == '') { return array(); }

  if (preg_match('/\{(.*)\}/', $port['ifAlias'], $matches)) { $circuit = $matches[1]; }
  if (preg_match('/\[(.*)\]/', $port['ifAlias'], $matches)) { $speed   = $matches[1]; }
  if (preg_match('/\((.*)\)/', $port['ifAlias'], $matches)) { $notes   = $matches[1]; }

  $port_ifAlias = array();
  $port_ifAlias['type']    = $type;
  $port_ifAlias['descr']   = $descr;
  $port_ifAlias['circuit'] = $circuit;
  $port_ifAlias['speed']   = $speed;
  $port_ifAlias['notes']   = $notes;

  if (OBS_DEBUG > 1) { print_vars($port_ifAlias); }

  return $port_ifAlias;
}

// EOF
