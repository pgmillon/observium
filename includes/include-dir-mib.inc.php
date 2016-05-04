<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage common
 * @author     Adam Armstrong <adama@observium.org>
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

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

$include_lib = isset($include_lib) && $include_lib;

foreach (get_device_mibs($device) as $mib)
{
  $inc_dir  = $config['install_dir'] . '/' . $include_dir . '/' . strtolower($mib);
  $inc_file = $inc_dir . '.inc.php';

  if (is_device_mib($device, $mib))
  {
    if (is_file($inc_file))
    {
      if (OBS_DEBUG) { echo("[[$mib]]"); }

      include($inc_file);

      if ($include_lib && is_file($inc_dir . '.lib.php'))
      {
        // separated functions include, for exclude fatal redeclare errors
        include_once($inc_dir . '.lib.php');
      }
    }
    else if (is_dir($inc_dir))
    {
      if (OBS_DEBUG) { echo("[[$mib]]"); }
      foreach (glob($inc_dir.'/*.inc.php') as $dir_file)
      {
        if (is_file($dir_file))
        {
          include($dir_file);
        }
      }
      if ($include_lib && is_file($inc_dir . '.lib.php'))
      {
        // separated functions include, for exclude fatal redeclare errors
        include_once($inc_dir . '.lib.php');
      }
    }
  }
}

unset($include_dir, $include_lib, $inc_file, $inc_dir, $dir_file, $mib);

// EOF
