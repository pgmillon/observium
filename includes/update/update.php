<?php

/* Observium Network Management and Monitoring System
 *
 * @package    observium
 * @subpackage updater
 * @author     Adam Armstrong <adama@memetic.org>
 * @copyright  (C) 2006-2015 Adam Armstrong
 *
 */

if (!defined('OBS_DEBUG'))
{
  # Not called from within discovery, let's load up the necessary stuff.

  include_once("includes/defaults.inc.php");
  include_once("config.php");

  $options = getopt("d");

  include_once("includes/definitions.inc.php");
  include("includes/functions.inc.php");
}

$insert = 0;

if ($db_rev = @dbFetchCell("SELECT `version` FROM `dbSchema` ORDER BY `version` DESC LIMIT 1")) {} else
{
  $db_rev = 0;
  $insert = 1;
}

$updating = 0;

// Only numeric filenames (001.sql, 013.php)
$sql_regexp = "/^\d+\.sql$/";
$php_regexp = "/^\d+\.php$/";

if ($handle = opendir($config['install_dir'] . '/update'))
{
  while (false !== ($file = readdir($handle)))
  {
    if (filetype($config['install_dir'] . '/update/' . $file) == 'file' && (preg_match($sql_regexp, $file) || preg_match($php_regexp, $file)))
    {
      $filelist[] = $file;
    }
  }
  closedir($handle);
}

sort($filelist);
//print_vars($filelist);

foreach ($filelist as $file)
{
  $filepath = $config['install_dir'] . '/update/' . $file;
  list($filename, $extension) = explode('.', $file, 2);
  if ($filename > $db_rev)
  {
    if (!$updating)
    {
      echo "-- Updating database/file schema\n";
    }

    if ($extension == "php")
    {
      echo sprintf("%03d",$db_rev) . " -> " . sprintf("%03d",$filename) . " ... (php)";

      include_wrapper($filepath);
    } else if ($extension == "sql") {
      echo sprintf("%03d",$db_rev) . " -> " . sprintf("%03d",$filename) . " ... (db)";

      $err = 0;

      if ($fd = @fopen($filepath, 'r'))
      {
        $data = fread($fd,4096);
        while (!feof($fd))
        {
          $data .= fread($fd, 4096);
        }

        foreach (explode("\n", $data) as $line)
        {
          if (trim($line))
          {
            print_debug($line);
            if ($line[0] != "#")
            {
              $update = dbQuery($line);
              if (!$update)
              {
                $error_no  = mysql_errno();
                $error_msg = "($error_no) " . mysql_error();
                if ($error_no >= 2000)
                {
                  // Critical errors, stop update
                  // http://dev.mysql.com/doc/refman/5.6/en/error-messages-client.html
                  logfile('update-errors.log', "====== Schema update " . sprintf("%03d", $db_rev) . " -> " . sprintf("%03d", $filename) . " ==============");
                  logfile('update-errors.log', "Query: " . $line);
                  logfile('update-errors.log', "Error: " . $error_msg);
                  exit(1);
                } else {
                  $err++;
                  $errors[] = array('query' => $line, 'error' => $error_msg);
                  print_debug($error_msg);
                }
              }
            }
          }
        }

        if ($db_rev < 5)
        {
          echo(" done.\n");
        }
        else if($err)
        {
          echo(" done ($err errors).\n");
          logfile('update-errors.log', "====== Schema update " . sprintf("%03d", $db_rev) . " -> " . sprintf("%03d", $filename) . " ==============");
          foreach ($errors as $error)
          {
            logfile('update-errors.log', "Query: " . $error['query']);
            logfile('update-errors.log', "Error: " . $error['error']);
          }
          fclose($fd);
          unset($errors);
        } else {
          echo(" done.\n");
        }
      } else {
        echo(" Could not open file!\n");
        // Critical errors, stop update
        logfile('update-errors.log', "====== Schema update " . sprintf("%03d", $db_rev) . " -> " . sprintf("%03d", $filename) . " ==============");
        logfile('update-errors.log', "Error: Could not open file $filepath!");
        exit(1);
      }
    }

    $updating++;
    $db_rev = $filename;
  }
}

if ($updating)
{
  $GLOBALS['cache']['db_version'] = $db_rev; // Cache new db version
  if ($insert)
  {
    dbInsert(array('version' => $db_rev), 'dbSchema');
  } else {
    dbUpdate(array('version' => $db_rev), 'dbSchema');
  }
  echo "-- Done\n";
}

// EOF
