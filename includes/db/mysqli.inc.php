<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage db
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

/* Specific mysqli function calls, uses procedural style. */

/**
 * Get MySQL client info
 *
 * @return string $info
 */
function dbClientInfo()
{
  return mysqli_get_client_info();
}

/**
 * Returns a string representing the type of connection used
 *
 * @return string $info
 */
function dbHostInfo()
{
  return mysqli_get_host_info($GLOBALS[OBS_DB_LINK]);
}

/**
 * Open connection to mysql server
 *
 * @param string $database Database name
 * @param string $user     Username for connect to mysql server
 * @param string $password Username password
 * @param string $host     Hostname for mysql server, default 'localhost'
 * @param string $charset  Charset used for mysql connection, default 'utf8'
 *
 * @return object $connection
 */
function dbOpen($host = 'localhost', $user, $password, $database, $charset = 'utf8')
{
  // Check host params
  $host_array = explode(':', $host);
  if (count($host_array) > 1)
  {
    if ($host_array[0] === 'p')
    {
      // p:example.com
      // p:::1
      array_shift($host_array);
      $host = implode(':', $host_array);
      $GLOBALS['config']['db_persistent'] = TRUE;
    }
    else if (count($host_array) === 2)
    {
      // This is for compatability with old style host option (from mysql extension)
      // IPv6 host not possible here
      $host = $host_array[0];
      if (is_numeric($host_array[1]))
      {
        // example.com:3306
        $port   = $host_array[1];
      } else {
        // example.com:/tmp/mysql.sock
        $socket = $host_array[1];
      }
    }
  }

  // Server port
  if (is_numeric($GLOBALS['config']['db_port']))
  {
    $port = $GLOBALS['config']['db_port'];
  }
  else if (!isset($port))
  {
    $port = ini_get("mysqli.default_port");
  }

  // Server socket
  if (strlen($GLOBALS['config']['db_socket']))
  {
    $socket = $GLOBALS['config']['db_socket'];
  }
  else if (!isset($socket))
  {
    $socket = ini_get("mysqli.default_socket");
  }

  // Prepending host by p: for open a persistent connection.
  if ($GLOBALS['config']['db_persistent'] && ini_get('mysqli.allow_persistent'))
  {
    $host = 'p:' . $host;
  }

  // Init new connection
  $connection = mysqli_init();
  if ($connection === (object)$connection)
  {
    $client_flags = 0;
    // Optionally compress connection
    if ($GLOBALS['config']['db_compress'] && defined('MYSQLI_CLIENT_COMPRESS'))
    {
      $client_flags |= MYSQLI_CLIENT_COMPRESS;
    }

    if (!mysqli_real_connect($connection, $host, $user, $password, $database, (int)$port, $socket, $client_flags))
    {
      if (OBS_DEBUG)
      {
        echo('MySQLi connection error ' . mysqli_connect_errno($connection) . ': ' . mysqli_connect_error($connection) . PHP_EOL);
      }
      return NULL;
    }
    if ($charset)
    {
      mysqli_set_charset($connection, $charset);
      /*
      if (version_compare(PHP_VERSION, '5.3.6', '<') && $charset == 'utf8')
      {
        // Seem as problem to set default charset on php < 5.3.6
        mysqli_query($connection, "SET NAMES 'utf8' COLLATE 'utf8_general_ci';");
      }
      */
    }
  }

  return $connection;
}

/**
 * Closes a previously opened database connection
 *
 * @param object $connection Link to resource with mysql connection, default last used connection
 *
 * @return bool Returns TRUE on success or FALSE on failure.
 */
function dbClose($connection = NULL)
{
  // Observium uses $observium_link global variable name for db link
  if      ($connection === (object)$connection) {}
  else if ($GLOBALS[OBS_DB_LINK] === (object)$GLOBALS[OBS_DB_LINK])
  {
    $connection = $GLOBALS[OBS_DB_LINK];
  } else {
    print_error("Call to function mysqli_close() without link identifier.");
    return;
  }

  return mysqli_close($connection);
}

/**
 * Returns the text of the error message from last MySQL operation
 *
 * @param object $connection Link to resource with mysql connection, default last used connection
 *
 * @return string $return
 */
function dbError($connection = NULL)
{
  // Observium uses $observium_link global variable name for db link
  if      ($connection === (object)$connection) {}
  else if ($GLOBALS[OBS_DB_LINK] === (object)$GLOBALS[OBS_DB_LINK])
  {
    $connection = $GLOBALS[OBS_DB_LINK];
  } else {
    //print_error("Call to function mysqli_error() without link identifier.");
    return mysqli_connect_error();
  }

  return mysqli_error($connection);
}

/**
 * Returns the numerical value of the error message from last MySQL operation
 *
 * @param object $connection Link to resource with mysql connection, default last used connection
 *
 * @return string $return
 */
function dbErrorNo($connection = NULL)
{
  // Observium uses $observium_link global variable name for db link
  if      ($connection === (object)$connection) {}
  else if ($GLOBALS[OBS_DB_LINK] === (object)$GLOBALS[OBS_DB_LINK])
  {
    $connection = $GLOBALS[OBS_DB_LINK];
  } else {
    //print_error("Call to function mysqli_errno() without link identifier.");
    return mysqli_connect_errno();
  }

  return mysqli_errno($connection);
}

function dbAffectedRows($connection = NULL)
{
  // Observium uses $observium_link global variable name for db link
  if      ($connection === (object)$connection) {}
  else if ($GLOBALS[OBS_DB_LINK] === (object)$GLOBALS[OBS_DB_LINK])
  {
    $connection = $GLOBALS[OBS_DB_LINK];
  } else {
    print_error("Call to function mysqli_affected_rows() without link identifier.");
    return;
  }

  return mysqli_affected_rows($connection);
}

function dbCallQuery($fullSql, $connection = NULL)
{
  // Observium uses $observium_link global variable name for db link
  if      ($connection === (object)$connection) {}
  else if ($GLOBALS[OBS_DB_LINK] === (object)$GLOBALS[OBS_DB_LINK])
  {
    $connection = $GLOBALS[OBS_DB_LINK];
  } else {
    print_error("Call to function mysqli_query() without link identifier.");
    return;
  }

  return mysqli_query($connection, $fullSql);
  //return mysqli_query($connection, $fullSql, MYSQLI_USE_RESULT); // Unbuffered results, for speedup!
}

/**
 * Returns escaped string
 *
 * @param string $string Input string for escape in mysql query
 * @param object $connection Link to resource with mysql connection, default last used connection
 *
 * @return string
 */
function dbEscape($string, $connection = NULL)
{
  // Observium uses $observium_link global variable name for db link
  if      ($connection === (object)$connection) {}
  else if ($GLOBALS[OBS_DB_LINK] === (object)$GLOBALS[OBS_DB_LINK])
  {
    $connection = $GLOBALS[OBS_DB_LINK];
  } else {
    print_error("Call to function mysqli_real_escape_string() without link identifier.");
    return;
  }

  $return = mysqli_real_escape_string($connection, $string);
  if (!isset($return[0]) && isset($string[0]))
  {
    // If character set empty, use escape alternative
    // FIXME. I really not know why, but in unittests $connection object is lost!
    print_debug("Mysql connection lost, in dbEscape() used escape alternative!");
    $search = array("\\", "\x00", "\n", "\r", "'", '"', "\x1a");
    $replace = array("\\\\","\\0","\\n", "\\r", "\'", '\"', "\\Z");
    $return = str_replace($search, $replace, $string);
  }
  return $return;
}

/**
 * Returns the auto generated id used in the last query
 *
 * @param object $connection Link to resource with mysql connection, default last used connection
 *
 * @return string
 */
function dbLastID($connection = NULL)
{
  // Observium uses $observium_link global variable name for db link
  if      ($connection === (object)$connection) {}
  else if ($GLOBALS[OBS_DB_LINK] === (object)$GLOBALS[OBS_DB_LINK])
  {
    $connection = $GLOBALS[OBS_DB_LINK];
  } else {
    print_error("Call to function mysqli_insert_id() without link identifier.");
    return;
  }

  return mysqli_insert_id($connection);
}

/*
 * Fetches all of the rows (associatively) from the last performed query.
 * Most other retrieval functions build off this
 * */
function dbFetchRows($sql, $parameters = array())
{
  $time_start = microtime(true);
  $result = dbQuery($sql, $parameters);

  $rows = array();
  if ($result instanceof mysqli_result)
  {
    while ($row = mysqli_fetch_assoc($result))
    {
      $rows[] = $row;
    }
    mysqli_free_result($result);

    $time_end = microtime(true);
    $GLOBALS['db_stats']['fetchrows_sec'] += number_format($time_end - $time_start, 8);
    $GLOBALS['db_stats']['fetchrows']++;
  }

  // no records, thus return empty array
  // which should evaluate to false, and will prevent foreach notices/warnings
  return $rows;
}

/*
 * Like fetch(), accepts any number of arguments
 * The first argument is an sprintf-ready query stringTypes
 * */
function dbFetchRow($sql = NULL, $parameters = array())
{
  $time_start = microtime(true);
  $result = dbQuery($sql, $parameters);
  if ($result)
  {
    $row = mysqli_fetch_assoc($result);
    mysqli_free_result($result);
    $time_end = microtime(true);

    $GLOBALS['db_stats']['fetchrow_sec'] += number_format($time_end - $time_start, 8);
    $GLOBALS['db_stats']['fetchrow']++;

    return $row;
  } else {
    return NULL;
  }
}

/*
 * Fetches the first call from the first row returned by the query
 * */
function dbFetchCell($sql, $parameters = array())
{
  $time_start = microtime(true);
  //$row = dbFetchRow($sql, $parameters);
  $result = dbQuery($sql, $parameters);
  if ($result)
  {
    $row = mysqli_fetch_assoc($result);
    mysqli_free_result($result);
    $time_end = microtime(true);

    $GLOBALS['db_stats']['fetchcell_sec'] += number_format($time_end - $time_start, 8);
    $GLOBALS['db_stats']['fetchcell']++;

    return array_shift($row); // shift first field off first row
  }

  return NULL;
}

function dbBeginTransaction($connection = NULL)
{
  // Observium uses $observium_link global variable name for db link
  if      ($connection === (object)$connection) {}
  else if ($GLOBALS[OBS_DB_LINK] === (object)$GLOBALS[OBS_DB_LINK])
  {
    $connection = $GLOBALS[OBS_DB_LINK];
  } else {
    print_error("Call to begin db transaction without link identifier.");
    return;
  }

  mysqli_autocommit($connection, FALSE); // Set autocommit to off
}

function dbCommitTransaction($connection = NULL)
{
  // Observium uses $observium_link global variable name for db link
  if      ($connection === (object)$connection) {}
  else if ($GLOBALS[OBS_DB_LINK] === (object)$GLOBALS[OBS_DB_LINK])
  {
    $connection = $GLOBALS[OBS_DB_LINK];
  } else {
    print_error("Call to commmit db transaction without link identifier.");
    return;
  }

  mysqli_commit($connection);
  mysqli_autocommit($connection, TRUE); // Restore autocommit to on
}

function dbRollbackTransaction($connection = NULL)
{
  // Observium uses $observium_link global variable name for db link
  if      ($connection === (object)$connection) {}
  else if ($GLOBALS[OBS_DB_LINK] === (object)$GLOBALS[OBS_DB_LINK])
  {
    $connection = $GLOBALS[OBS_DB_LINK];
  } else {
    print_error("Call to rollback db transaction without link identifier.");
    return;
  }

  mysqli_rollback($connection);
  mysqli_autocommit($connection, TRUE); // Restore autocommit to on
}

// EOF
