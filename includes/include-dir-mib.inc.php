<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage common
 * @author     Adam Armstrong <adama@memetic.org>
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

global $debug;

/* not finished yet --mike
if (isset($config['os'][$device['os']]['detect']) && $config['os'][$device['os']]['detect'])
{
  $detect_mibs = array();
  foreach (array('os', 'os_group') as $e)
  {
    foreach ($config[$e] as $entry)
    {
      if (is_array($entry['mibs'])) { $detect_mibs = array_merge($detect_mibs, $entry['mibs']); }
    }
  }
  $config['os'][$device['os']]['mibs'] = array_unique($detect_mibs);
  var_dump($config['os'][$device['os']]['mibs']);
}
*/

// This is an include so that we don't lose variable scope.

foreach (get_device_mibs($device) as $mib)
{
  $inc_file = $config['install_dir'] . '/' . $include_dir . '/' . strtolower($mib) . '.inc.php';
  $inc_dir  = $config['install_dir'] . '/' . $include_dir . '/' . strtolower($mib);

  if (is_device_mib($device, $mib))
  {
    if (is_file($inc_file))
    {
      if ($debug) { echo("[[$mib]]"); }

      include($inc_file);
    }
    else if (is_dir($inc_dir))
    {
      if ($debug) { echo("[[$mib]]"); }
      foreach (glob($inc_dir.'/*.inc.php') as $dir_file)
      {
        if (is_file($dir_file))
        {
          include($dir_file);
        }
      }
    }
  }
}

unset($include_dir, $inc_file, $inc_dir, $dir_file, $mib);

// EOF
