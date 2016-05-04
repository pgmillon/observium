<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage update
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

echo(" Adding MIN RRA to very old RRD files:" . PHP_EOL);

$graphdata['hr_processes'] = 'hr_processes.rrd';
$graphdata['hr_users'] = 'hr_users.rrd';
$graphdata['ucd_contexts'] = 'ucd_ssRawContexts.rrd';
$graphdata['ucd_load' ] = 'ucd_load.rrd';
$graphdata['ucd_interrupts'] = 'ucd_ssRawInterrupts.rrd';

foreach ($graphdata as $graph => $graphfile)
{
  echo("  * $graph: ");

  foreach (dbFetchRows("SELECT DISTINCT hostname FROM `device_graphs`,`devices` WHERE `devices`.device_id=`device_graphs`.device_id AND graph='$graph'") as $entry)
  {
    // get_rrd_path expects a $device array, but it only uses the 'hostname' index, so we get away with this.
    $filename = get_rrd_path($entry, $graphfile);

    if (file_exists($filename))
    {
      $info = rrdtool_file_info($filename);

      $has_min = FALSE;

      foreach ($info['RRA'] as $rra)
      {
        if ($rra['cf'] == 'MIN')
        {
          $has_min = TRUE;
          break;
        }
      }

      if (!$has_min)
      {
        shell_exec("scripts/rrdtoolx.py addrra $filename $filename.new RRA:MIN:0.5:6:1440 RRA:MIN:0.5:96:360 RRA:MIN:0.5:288:1440");
        rename("$filename.new", $filename);
        echo("U");
      } else {
        echo('.');
      }
    }
  }

  echo(PHP_EOL);
}

// EOF
