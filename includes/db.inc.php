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

/* Here common DB functions which uses calls to specific api functions */

// Initial variables
$db_stats = array('insert'    => 0, 'insert_sec'    => 0,
                  'update'    => 0, 'update_sec'    => 0,
                  'delete'    => 0, 'delete_sec'    => 0,
                  'fetchcell' => 0, 'fetchcell_sec' => 0,
                  'fetchrow'  => 0, 'fetchrow_sec'  => 0,
                  'fetchrows' => 0, 'fetchrows_sec' => 0,
                  'fetchcol'  => 0, 'fetchcol_sec'  => 0);

// Include DB api. Default mysql, recommended mysqli
switch (OBS_DB_EXTENSION)
{
  case 'mysqli':
    if (@function_exists('mysqli_connect'))
    {
      require($config['install_dir'] . '/includes/db/mysqli.inc.php');
    } else {
      print_error('ERROR. PHP mysqli extension not exist. Execution is stopped.');
      exit(2);
    }
    break;
  case 'mysql':
  default:
    if (@function_exists('mysql_connect'))
    {
      require($config['install_dir'] . '/includes/db/mysql.inc.php');
    } else {
      print_error('ERROR. PHP mysql extension not exist. Execution is stopped.');
      exit(2);
    }
}

/**
 * Provides server status information
 *
 * @param string $scope GLOBAL or SESSION variable scope modifier
 * @return array Array with server status variables
 */
function dbShowStatus($scope = 'SESSION')
{
  switch ($scope)
  {
    case 'GLOBAL':
      $sql = 'SHOW GLOBAL STATUS;';
      break;
    default:
      $sql = 'SHOW STATUS;';
  }

  $rows = array();
  foreach (dbFetchRows($sql) as $row)
  {
    $rows[$row['Variable_name']] = $row['Value'];
  }

  return $rows;
}

/**
 * Shows the values of MySQL system variables
 *
 * @param string $scope GLOBAL or SESSION variable scope modifier
 * @param string $where WHERE or LIKE clause
 * @return array Array with variables
 */
function dbShowVariables($scope = 'SESSION', $where = '')
{
  switch ($scope)
  {
    case 'GLOBAL':
      $sql = 'SHOW GLOBAL VARIABLES';
      break;
    default:
      $sql = 'SHOW VARIABLES';
  }
  if (strlen($where))
  {
    $sql .= ' ' . $where;
  }

  $rows = array();
  foreach (dbFetchRows($sql) as $row)
  {
    $rows[$row['Variable_name']] = $row['Value'];
  }

  return $rows;
}

/*
 * Performs a query using the given string.
 * Used by the other _query functions.
 * */
function dbQuery($sql, $parameters = array())
{
  global $fullSql;

  $fullSql = dbMakeQuery($sql, $parameters);

  if (OBS_DEBUG > 0)
  {
    // Pre query debug output
    if (is_cli())
    {
      $debug_sql = explode(PHP_EOL, $fullSql);
      print_message(PHP_EOL.'SQL[%y' . implode('%n'.PHP_EOL.'%y', $debug_sql) . '%n]', 'console', FALSE);
    } else {
      print_sql($fullSql);
    }
  }

  if (OBS_DEBUG > 0 || $GLOBALS['config']['profile_sql'])
  {
    $time_start = microtime(true);
  }

  $result = dbCallQuery($fullSql); // sets $this->result

  if (OBS_DEBUG > 0 || $GLOBALS['config']['profile_sql'])
  {
    $runtime = number_format(microtime(true) - $time_start, 8);
    $debug_msg .= 'SQL RUNTIME['.($runtime > 0.05 ? '%r' : '%g').$runtime.'s%n]';
    if ($GLOBALS['config']['profile_sql'])
    {
      #fwrite($this->logFile, date('Y-m-d H:i:s') . "\n" . $fullSql . "\n" . number_format($time_end - $time_start, 8) . " seconds\n\n");
      $GLOBALS['sql_profile'][] = array('sql' => $fullSql, 'time' => $runtime);
    }
  }

  if (OBS_DEBUG > 0)
  {
    if ($result === FALSE && (error_reporting() & 1))
    {
      $error_msg = 'Error in query: (' . dbError() . ') ' . dbErrorNo();
      $debug_msg .= PHP_EOL . 'ERROR[%r'.$error_msg.'%n]';
    }

    if (is_cli())
    {
      if (OBS_DEBUG > 1)
      {
        $rows = dbAffectedRows();
        $debug_msg = 'ROWS['.($rows < 1 ? '%r' : '%g').$rows.'%n]'.PHP_EOL.$debug_msg;
      }
      // After query debug output for cli
      print_message($debug_msg, 'console', FALSE);
    } else {
      print_error($error_msg);
    }
  }

  if ($result === FALSE && isset($GLOBALS['config']['db']['debug']) && $GLOBALS['config']['db']['debug'])
  {
    logfile('db.log', 'Failed dbQuery (#' . dbErrorNo() . ' - ' . dbError() . '), Query: ' . $fullSql);
  }

  return $result;
}

/*
 * This is intended to be the method used for large result sets.
 * It is intended to return an iterator, and act upon buffered data.
 * */
function dbFetch($sql, $parameters = array())
{
  return dbFetchRows($sql, $parameters);
}

/*
 * This method is quite different from fetchCell(), actually
 * It fetches one cell from each row and places all the values in 1 array
 * */
function dbFetchColumn($sql, $parameters = array())
{
  $time_start = microtime(true);
  $cells = array();
  foreach (dbFetchRows($sql, $parameters) as $row)
  {
    $cells[] = array_shift($row);
  }
  $time_end = microtime(true);

  $GLOBALS['db_stats']['fetchcol_sec'] += number_format($time_end - $time_start, 8);
  $GLOBALS['db_stats']['fetchcol']++;

  return $cells;
}

/*
 * Should be passed a query that fetches two fields
 * The first will become the array key
 * The second the key's value
 */
function dbFetchKeyValue($sql, $parameters = array())
{
  $data = array();
  foreach (dbFetchRows($sql, $parameters) as $row)
  {
    $key = array_shift($row);
    if (sizeof($row) == 1)
    { // if there were only 2 fields in the result
      // use the second for the value
      $data[$key] = array_shift($row);
    } else { // if more than 2 fields were fetched
      // use the array of the rest as the value
      $data[$key] = $row;
    }
  }

  return $data;
}

/*
 * Passed an array and a table name, it attempts to insert the data into the table.
 * Check for boolean false to determine whether insert failed
 * */
function dbInsert($data, $table)
{
  global $fullSql;

  // the following block swaps the parameters if they were given in the wrong order.
  // it allows the method to work for those that would rather it (or expect it to)
  // follow closer with SQL convention:
  // insert into the TABLE this DATA
  if (is_string($data) && is_array($table))
  {
    $tmp = $data;
    $data = $table;
    $table = $tmp;

    print_debug('Parameters passed to dbInsert() were in reverse order.');
  }

  $sql = 'INSERT INTO `' . $table . '` (`' . implode('`,`', array_keys($data)) . '`)  VALUES (' . implode(',', dbPlaceHolders($data)) . ')';

  $time_start = microtime(true);
  //dbBeginTransaction();
  $result = dbQuery($sql, $data);
  if ($result)
  {
    // This should return true if insert succeeded, but no ID was generated
    $id = dbLastID();
    //dbCommitTransaction();
  } else {
    //dbRollbackTransaction();
    $id = FALSE;
  }

  $time_end = microtime(true);
  $GLOBALS['db_stats']['insert_sec'] += number_format($time_end - $time_start, 8);
  $GLOBALS['db_stats']['insert']++;

  return $id;
}

/*
 * Passed an array, table name, WHERE clause, and placeholder parameters, it attempts to update a record.
 * Returns the number of affected rows
 * */
function dbUpdate($data, $table, $where = NULL, $parameters = array())
{
  global $fullSql;

  // the following block swaps the parameters if they were given in the wrong order.
  // it allows the method to work for those that would rather it (or expect it to)
  // follow closer with SQL convention:
  // update the TABLE with this DATA
  if (is_string($data) && is_array($table))
  {
    $tmp = $data;
    $data = $table;
    $table = $tmp;
    //trigger_error('QDB - The first two parameters passed to update() were in reverse order, but it has been allowed', E_USER_NOTICE);
  }

  // need field name and placeholder value
  // but how merge these field placeholders with actual $parameters array for the WHERE clause
  $sql = 'UPDATE `' . $table . '` set ';
  foreach ($data as $key => $value)
  {
    $sql .= "`".$key."` ". '=:' . $key . ',';
  }
  $sql = substr($sql, 0, -1); // strip off last comma

  if ($where)
  {
    $sql .= ' WHERE ' . $where;
    $data = array_merge($data, $parameters);
  }

  $time_start = microtime(true);
  if (dbQuery($sql, $data))
  {
    $return = dbAffectedRows();
  } else {
    $return = FALSE;
  }
  $time_end = microtime(true);
  $GLOBALS['db_stats']['update_sec'] += number_format($time_end - $time_start, 8);
  $GLOBALS['db_stats']['update']++;

  return $return;
}

function dbDelete($table, $where = NULL, $parameters = array())
{
  $sql = 'DELETE FROM `' . $table.'`';
  if ($where)
  {
    $sql .= ' WHERE ' . $where;
  }

  $time_start = microtime(true);
  if (dbQuery($sql, $parameters))
  {
    $return = dbAffectedRows();
  } else {
    $return = FALSE;
  }
  $time_end = microtime(true);
  $GLOBALS['db_stats']['delete_sec'] += number_format($time_end - $time_start, 8);
  $GLOBALS['db_stats']['delete']++;

  return $return;
}

/*
 * This combines a query and parameter array into a final query string for execution
 * PDO drivers don't need to use this
 */
function dbMakeQuery($sql, $parameters)
{
  // bypass extra logic if we have no parameters

  if (sizeof($parameters) == 0)
  {
    return $sql;
  }

  $parameters = dbPrepareData($parameters);
  // separate the two types of parameters for easier handling
  $questionParams = array();
  $namedParams = array();
  foreach ($parameters as $key => $value)
  {
    if (is_numeric($key))
    {
      $questionParams[] = $value;
    } else {
      $namedParams[':' . $key] = $value;
    }
  }

  if (count($namedParams) == 0)
  {
    // use simple pattern if named params not used (this broke some queries)
    $pattern = '/(\?)/';
  } else {
    // sort namedParams in reverse to stop substring squashing
    krsort($namedParams);
    // full pattern
    $pattern = '/(\?|:[a-zA-Z0-9_-]+)/';
  }

  // split on question-mark and named placeholders
  $result = preg_split($pattern, $sql, -1, PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE);

  // every-other item in $result will be the placeholder that was found

  $query = '';
  for ($i = 0; $i < sizeof($result); $i+=2)
  {
    $query .= $result[$i];

    $j = $i+1;
    if (array_key_exists($j, $result))
    {
      $test = $result[$j];
      if ($test == '?')
      {
        $query .= array_shift($questionParams);
      } else {
        $query .= $namedParams[$test];
      }
    }
  }

  return $query;
}

function dbPrepareData($data)
{
  $values = array();

  foreach ($data as $key=>$value)
  {
    $escape = true;
    // don't quote or esc if value is an array, we treat it
    // as a "decorator" that tells us not to escape the
    // value contained in the array IF there is one item in the array
    if (is_array($value) && !is_object($value))
    {
      if (count($value) == 1)
      {
        $escape = false;
        $value = array_shift($value);
      } else {
        // if this is a multi-value array, implode this as it's probably
        // (hopefully) used in an IN statement.
        $escape = false; // we'll escape on our own, thanks.
        // escape each entry by itself, unfortunately requires an extra array
        // but implode() can't first escape each string, of course.
        foreach ($value as $entry)
        {
          $escaped[] = "'" . dbEscape($entry) . "'";
        }
        $value = implode(",", $escaped);
      }
    }

    // it's not right to worry about invalid fields in this method because we may be operating on fields
    // that are aliases, or part of other tables through joins
    //if (!in_array($key, $columns)) // skip invalid fields
    //  continue;
    if ($escape)
    {
      $values[$key] = "'" . dbEscape($value) . "'";
    } else {
      $values[$key] = $value;
    }
  }

  return $values;
}

/*
 * Given a data array, this returns an array of placeholders
 * These may be question marks, or ":email" type
 */
function dbPlaceHolders($values)
{
  $data = array();
  foreach ($values as $key => $value)
  {
    if (is_numeric($key))
    {
      $data[] = '?';
    } else {
      $data[] = ':' . $key;
    }
  }
  return $data;
}

/**
 * This function generates WHERE condition string from array with values
 * NOTE, value should be exploded by comma before use generate_query_values(), for example in get_vars()
 *
 * @param mixed $value
 * @param string $column
 * @param string $condition
 * @return string
 */
function generate_query_values($value, $column, $condition = NULL)
{
  //if (!is_array($value)) { $value = explode(',', $value); }
  if (!is_array($value)) { $value = array((string)$value); }
  $column = '`' . str_replace(array('`', '.'), array('', '`.`'), $column) . '`'; // I.column -> `I`.`column`
  $condition = ($condition === TRUE ? 'LIKE' : strtoupper(trim($condition)));
  if (strpos($condition, 'NOT') === 0 || strpos($condition, '!=') === 0)
  {
    $negative  = TRUE;
    $condition = str_replace(array('NOT', '!=', ' '), '', $condition);
  } else {
    $negative  = FALSE;
  }

  $search  = array('%', '_');
  $replace = array('\%', '\_');
  $values  = array();
  switch ($condition)
  {
    // Use LIKE condition
    case 'LIKE':
      // Replace stars by % only for LIKE condition
      $search[]  = '*';
      $replace[] = '%';
    case '%LIKE%':
    case '%LIKE':
    case 'LIKE%':
      if ($negative) { $implode = ' AND '; $like = ' NOT LIKE '; }
      else           { $implode = ' OR ';  $like = ' LIKE '; }
      foreach ($value as $v)
      {
        if ($v === '*')
        {
          $values = array("ISNULL($column, 1)" . $like . "'%'");
          break;
        }
        else if ($v === '')
        {
          $values[] = "ISNULL($column, '')" . $like . "''";
        } else {
          $v = dbEscape($v); // Escape BEFORE replace!
          $v = str_replace($search, $replace, $v);
          $v = str_replace('LIKE', $v, $condition);
          $values[] = $column . $like . "'" . $v . "'";
        }
      }
      $values = array_unique($values); // Removes duplicate values
      $where = ' AND (' . implode($implode, $values) . ')';
      break;
    // Use IN condition
    default:
      $where = '';
      foreach ($value as $v)
      {
        if ($v == OBS_VAR_UNSET || $v === '')
        {
          $add_null = TRUE; // Add check NULL values at end
          $values[] = "''";
        } else {
          $values[] = "'" . dbEscape($v) . "'";
        }
      }
      $count = count($values);
      if ($count == 1)
      {
        $where .= $column . ($negative ? ' != ' : ' = ') . $values[0];
      }
      else if ($count)
      {
        $values = array_unique($values); // Removes duplicate values
        $where .= $column . ($negative ? ' NOT IN (' : ' IN (') . implode(',', $values) . ')';
      }
      if ($add_null)
      {
        // Add search for empty values
        if ($negative)
        {
          $where .= " AND $column IS NOT NULL";
        } else {
          $where .= " OR $column IS NULL";
        }
        $where = " AND ($where)";
      } else {
        $where = " AND " . $where;
      }
      break;
  }

  return $where;
}

// EOF
