<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage housekeeping
 * @author     Adam Armstrong <adama@observium.org>
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

// Minimum allowed age for delete RRDs is 24h
$cutoff = age_to_unixtime($config['housekeeping']['rrd']['age'], age_to_seconds('24h'));

if ($cutoff || $config['housekeeping']['rrd']['invalid'])
{
  if ($prompt)
  {
    $msg = "RRD files:" . PHP_EOL;
    if ($config['housekeeping']['rrd']['invalid'])
    {
      $msg .= " - not valid RRD" . PHP_EOL;
    }
    if ($cutoff)
    {
      $msg .= " - not modified since " . format_unixtime($cutoff) . PHP_EOL;
    }
    $answer = print_prompt($msg . "will be deleted");
  }
} else {
  print_message("RRD housekeeping disabled in configuration or less than 24h.");
  $answer = FALSE;
}

if ($answer)
{
  $count_notvalid = 0;
  $count_notmodified = 0;
  foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($config['rrd_dir'])) as $file)
  {
    if (basename($file) != "." && basename($file) != ".." && substr($file, -4) == ".rrd")
    {
      print_debug("Found file ending in '.rrd': " . $file);

      if ($cutoff)
      {
        $file_data = stat($file);

        if ($file_data['mtime'] < $cutoff)
        {
          print_debug("File modification time is " . format_unixtime($file_data['mtime']) . " - deleting");
          logfile("housekeeping.log", "File $file modification time is " . format_unixtime($file_data['mtime']) . " - deleting");
          unlink($file);
          $count_notmodified++;
        }
      }

      // Not using finfo functions from PHP, whatever I tried always resulted in application/octet-stream. Dumb thing.
      if ($config['housekeeping']['rrd']['invalid'] && file_exists($file)) // Check if we didn't delete the file above
      {
        if (!file_exists($config['file']))
        {
          print_debug("Magic 'file' binary not found in configured path!");
        } else {
          $filetype = $this_data = trim(external_exec($config['file'] . " -b " . $file));
          if (substr($filetype,0,10) != "RRDTool DB")
          {
            print_debug("Invalid file type for $file ($filetype) - deleting");
            logfile("housekeeping.log", "File $file has invalid type: $filetype - deleting");
            unlink($file);
            $count_notvalid++;
          }
        }
      }
    }
  }

  if ($prompt && $cutoff)
  {
    if ($count_notmodified)
    {
      print_message("Deleted $count_notmodified not modified RRD files older than " . format_unixtime($cutoff));
    } else {
      print_message("No RRD files found last modified before " . format_unixtime($cutoff));
    }
  }
  if ($prompt && $config['housekeeping']['rrd']['invalid'])
  {
    if ($count_notvalid)
    {
      print_message("Deleted $count_notvalid invalid RRD files");
    } else {
      print_message("No invalid RRD files found");
    }
  }
}

// EOF