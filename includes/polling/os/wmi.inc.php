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

$os = preg_replace("/(\(R\)|®|«|Â)/", "", $wmi['os']['Caption']);
$os = str_replace("Microsoft Windows ", "", $os);
if ($wmi['os']['CSDVersion']) { $os .= " ". str_replace("Service Pack ", "SP", $wmi['os']['CSDVersion']); }
$os .= " (".$wmi['os']['Version'].")";

if ($device['os'] != $os)
{
  echo(" Windows version updated:");
}

$version = $os;

echo(" ".$os."\n");

// EOF
