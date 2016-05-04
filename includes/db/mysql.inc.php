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

/*
This code based on original non-OO version of dbFacile version 0.4.3
written by Alan Szlosek from http://www.greaterscope.net/projects/dbFacile
and licensed by the MIT license http://en.wikipedia.org/wiki/MIT_License

Many part of code rewritten by Observium developers.

It's a bit simplistic, but gives you the really useful bits in non-class form.
*/

/* Specific mysql function calls. */

/**
 * Get MySQL client info
 *
 * @return string $info
 */
function dbClientInfo()
{
  return mysql_get_client_info();
}

/**
 * Returns a string representing the type of connection used
 *
 * @return string $info
 */
function dbHostInfo()
{
  return mysql_get_host_info();
}

/**
 * Open connection to mysql server
 *
 * @param string $host     Hostname for mysql server, default 'localhost'
 * @param string $user     Username for connect to mysql server
 * @param string $password Username password
 * @param string $database Database name
 * @param string $charset  Charset used for mysql connection, default 'utf8'
 *
 * @return resource $connection
 */
function dbOpen($host = 'localhost', $user, $password, $database, $charset = 'utf8')
{
  // Check host params
  $host_array = explode(':', $host);
  if (count($host_array) > 1)
  {
    if ($host_array[0] === 'p')
    {
      // This is for compatability with new style host option (from mysqli extension)
      // p:example.com
      // p:::1
      array_shift($host_array);
      $host = implode(':', $host_array);
      $GLOBALS['config']['db_persistent'] = TRUE;
    }
    else if (count($host_array) === 2)
    {
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

  switch ($host)
  {
    case 'localhost':
    case '127.0.0.1':
    case '::1':
    case '':
      // For localhost socket prefer
      if (strlen($GLOBALS['config']['db_socket']))
      {
        $host .= ':' . $GLOBALS['config']['db_socket'];
      }
      else if (strlen($socket))
      {
        $host .= ':' . $socket;
      }
      else if (is_numeric($GLOBALS['config']['db_port']))
      {
        $host .= ':' . $GLOBALS['config']['db_port'];
      }
      else if (is_numeric($port))
      {
        $host .= ':' . $port;
      }
      break;
    default:
      // All other host uses only port
      if (is_numeric($GLOBALS['config']['db_port']))
      {
        $host .= ':' . $GLOBALS['config']['db_port'];
      }
      else if (is_numeric($port))
      {
        $host .= ':' . $port;
      }
  }

  $client_flags = 0;
  // Optionally compress connection
  if ($GLOBALS['config']['db_compress'] && defined('MYSQL_CLIENT_COMPRESS'))
  {
    $client_flags |= MYSQL_CLIENT_COMPRESS;
  }

  if ($GLOBALS['config']['db_persistent'] && ini_get('mysql.allow_persistent'))
  {
    // Open a persistent connection
    $connection = mysql_pconnect($host, $user, $password, $client_flags);
  } else {
    // force opening a new link because we might be selecting a different database
    $connection = mysql_connect($host, $user, $password, TRUE, $client_flags);
  }

  if ($connection)
  {
    if (mysql_select_db($database, $connection))
    {
      // Connected to DB
      if ($charset)
      {
        mysql_set_charset($charset, $connection);
      }
    } else {
      // DB not exist, reset $connection
      if (OBS_DEBUG)
      {
        echo('MySQL connection error ' . mysql_errno() . ': ' . mysql_error($connection) . PHP_EOL);
      }
      return NULL;
    }
  }

  return $connection;
}

/**
 * Closes a previously opened database connection
 *
 * @param resource $connection Link to resource with mysql connection, default last used connection
 *
 * @return bool Returns TRUE on success or FALSE on failure.
 */
function dbClose($connection = NULL)
{
  if (is_resource($connection))
  {
    return mysql_close($connection);
  } else {
    return mysql_close();
  }
}

/**
 * Returns the text of the error message from last MySQL operation
 *
 * @param resource $connection Link to resource with mysql connection, default last used connection
 *
 * @return string $return
 */
function dbError($connection = NULL)
{
  if (is_resource($connection))
  {
    return mysql_error($connection);
  } else {
    return mysql_error();
  }
}

/**
 * Returns the numerical value of the error message from last MySQL operation
 *
 * @param resource $connection Link to resource with mysql connection, default last used connection
 *
 * @return string $return
 */
function dbErrorNo($connection = NULL)
{
  if (is_resource($connection))
  {
    return mysql_errno($connection);
  } else {
    return mysql_errno();
  }
}

function dbAffectedRows($connection = NULL)
{
  if (is_resource($connection))
  {
    return mysql_affected_rows($connection);
  } else {
    return mysql_affected_rows();
  }
}

function dbCallQuery($fullSql, $connection = NULL)
{
  if (is_resource($connection))
  {
    return mysql_query($fullSql, $connection);
  } else {
    return mysql_query($fullSql);
  }
}

/**
 * Returns escaped string
 *
 * @param string $string Input string for escape in mysql query
 *
 * @return string
 */
function dbEscape($string, $connection = NULL)
{
  if (is_resource($connection))
  {
    return mysql_real_escape_string($string, $connection);
  } else {
    return mysql_real_escape_string($string);
  }
}

/**
 * Returns the auto generated id used in the last query
 *
 * @param resource $connection Link to resource with mysql connection, default last used connection
 *
 * @return string
 */
function dbLastID($connection = NULL)
{
  if (is_resource($connection))
  {
    return mysql_insert_id($connection);
  } else {
    return mysql_insert_id();
  }
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
  if (mysql_num_rows($result) > 0)
  {
    while ($row = mysql_fetch_assoc($result))
    {
      $rows[] = $row;
    }
    mysql_free_result($result);

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
function dbFetchRow($sql = null, $parameters = array())
{
  $time_start = microtime(true);
  $result = dbQuery($sql, $parameters);
  if ($result)
  {
    $row = mysql_fetch_assoc($result);
    mysql_free_result($result);
    $time_end = microtime(true);

    $GLOBALS['db_stats']['fetchrow_sec'] += number_format($time_end - $time_start, 8);
    $GLOBALS['db_stats']['fetchrow']++;

    return $row;
  } else {
    return null;
  }

  //$time_start = microtime(true);
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
    $row = mysql_fetch_assoc($result);
    mysql_free_result($result);
    $time_end = microtime(true);

    $GLOBALS['db_stats']['fetchcell_sec'] += number_format($time_end - $time_start, 8);
    $GLOBALS['db_stats']['fetchcell']++;

    return array_shift($row); // shift first field off first row
  }

  return null;
}

function dbBeginTransaction()
{
  mysql_query('begin');
}

function dbCommitTransaction()
{
  mysql_query('commit');
}

function dbRollbackTransaction()
{
  mysql_query('rollback');
}

/*
class dbIterator implements Iterator {
  private $result;
  private $i;

  public function __construct($r) {
    $this->result = $r;
    $this->i = 0;
  }
  public function rewind() {
    mysql_data_seek($this->result, 0);
    $this->i = 0;
  }
  public function current() {
    $a = mysql_fetch_assoc($this->result);
    return $a;
  }
  public function key() {
    return $this->i;
  }
  public function next() {
    $this->i++;
    $a = mysql_data_seek($this->result, $this->i);
    if ($a === false) {
      $this->i = 0;
    }
    return $a;
  }
  public function valid() {
    return ($this->current() !== false);
  }
}
*/

// EOF
