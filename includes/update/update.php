<?php

/* Observium Network Management and Monitoring System
 *
 * @package    observium
 * @subpackage updater
 * @author     Adam Armstrong <adama@memetic.org>
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

if (!isset($debug))
{
  # Not called from within discovery, let's load up the necessary stuff.

  include("includes/defaults.inc.php");
  include("config.php");
  include("includes/definitions.inc.php");
  include("includes/functions.inc.php");

  $options = getopt("d");
  if (isset($options['d']))
  {
    $debug = TRUE;
  }
  else
  {
    $debug = FALSE;
  }
}

$insert = 0;

if ($db_rev = @dbFetchCell("SELECT version FROM `dbSchema` ORDER BY version DESC LIMIT 1")) {} else
{
  $db_rev = 0;
  $insert = 1;
}

$updating = 0;

$sql_regexp = "/\.sql$/";
$php_regexp = "/\.php$/";

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

asort($filelist);

foreach ($filelist as $file)
{
  list($filename,$extension) = explode('.',$file,2);
  if ($filename > $db_rev)
  {
    if (!$updating)
    {
      echo "-- Updating database/file schema\n";
    }

    if ($extension == "php")
    {
      echo sprintf("%03d",$db_rev) . " -> " . sprintf("%03d",$filename) . " ... (file)";

      include_wrapper($config['install_dir'] . '/update/' . $file);
    } elseif ($extension == "sql") {
      echo sprintf("%03d",$db_rev) . " -> " . sprintf("%03d",$filename) . " ... (db)";

      $err = 0;

      if ($fd = @fopen($config['install_dir'] . '/update/' . $file,'r'))
      {
        $data = fread($fd,4096);
        while (!feof($fd))
        {
          $data .= fread($fd,4096);
        }

        foreach (explode("\n", $data) as $line)
        {
          if (trim($line))
          {
            if ($debug) { echo("$line \n"); }
            if ($line[0] != "#")
            {
              $update = dbQuery($line);
              if (!$update)
              {
                $err++;
                $errors[] = array('query' => $line, 'error' => mysql_error());
                if ($debug) { echo(mysql_error() . "\n"); }
              }
            }
          }
        }

        if ($db_rev < 5)
        {
          echo(" done.\n");
        }
        elseif($err)
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
        }
        else
        {
          echo(" done.\n");
        }
      }
      else
      {
        echo(" Could not open file!\n");
      }
    }

    $updating++;
    $db_rev = $filename;
  }
}

if ($updating)
{
  if ($insert)
  {
    dbInsert(array('version' => $db_rev), 'dbSchema');
  } else {
    dbUpdate(array('version' => $db_rev), 'dbSchema');
  }
  echo "-- Done\n";
}

// EOF
