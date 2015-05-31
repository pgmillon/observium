<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage poller
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

$os = preg_replace("/(\(R\)|®|«|Â)/", "", $wmi['os']['Caption']);
$os = str_replace("Microsoft Windows ", "", $os);
$os .= " ". str_replace("Service Pack ", "SP", $wmi['os']['CSDVersion']);
$os .= " (".$wmi['os']['Version'].")";

if ($device['os'] != $os)
{
  echo(" Windows version updated:");
}

$version = $os;

echo(" ".$os."\n");

// EOF
