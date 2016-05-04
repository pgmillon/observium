<?php
/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage ajax
 * @author     Adam Armstrong <adama@observium.org>
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */
$config['install_dir'] = "../..";

include_once("../../includes/sql-config.inc.php");

include($config['html_dir'] . "/includes/functions.inc.php");
include($config['html_dir'] . "/includes/authenticate.inc.php");

if (!$_SESSION['authenticated']) { echo('<li class="nav-header">Session expired, please log in again!</li>'); exit; }

include($config['html_dir'] . "/includes/cache-data.inc.php");

$query_limit = 8; // Limit per query

// Is there a POST/GET query string?
if (isset($_REQUEST['queryString']))
{
  $queryString = trim($_REQUEST['queryString']);

  // Is the string length greater than 0?
  if (strlen($queryString) > 0)
  {
    $query_param = "%$queryString%";

    // Start out with a clean slate
    $search_results = array();

    // Increase query_limit by one, so we can show "+" on result display if there are more than $query_limit entries
    $query_limit++;

    // Prepare user permission SQL query for use in search module queries
    $query_permitted_device = $cache['where']['devices_permitted'];
    $query_permitted_port   = $cache['where']['ports_permitted'];

    // Run search modules
    foreach ($config['wui']['search_modules'] as $module)
    {
      if (is_file($config['html_dir'] . "/includes/search/$module.inc.php"))
      {
        include($config['html_dir'] . "/includes/search/$module.inc.php");
      }
    }

    // Reset query_limit
    $query_limit--;

    foreach ($search_results as $results)
    {
      $display_count = count($results['results']);

      // If there are more results than query_limit (can happen, as we ++'d above), cut array to desired size and add + to counter
      if (count($results['results']) > $query_limit)
      {
        $results['results'] = array_slice($results['results'], 0, $query_limit);
        $display_count = count($results['results']) . '+';
      }

      echo('<li class="nav-header">' . $results['descr'] . ': '. $display_count . '</li>' . PHP_EOL);

      foreach ($results['results'] as $result)
      {
        echo('<li class="divider" style="margin: 0px;"></li>' . PHP_EOL);
        echo('<li style="margin: 0px;">' . PHP_EOL . '  <a href="'.$result['url'].'">' . PHP_EOL);
        echo('    <dl style="border-left: 10px solid '.$result['colour'].'; " class="dl-horizontal dl-search">' . PHP_EOL);
        echo('  <dt style="padding-left: 10px; text-align: center;">' . $result['icon'] . '</dt>' . PHP_EOL);
        echo('    <dd>' . PHP_EOL);
        echo('      <strong>'.highlight_search(escape_html($result['name'])) . PHP_EOL);
        echo('        <small>'.  implode($result['data'], '<br />') . '</small>' . PHP_EOL);
        echo('      </strong>' . PHP_EOL);
        echo('    </dd>' . PHP_EOL);
        echo('</dl>' . PHP_EOL);
        echo('  </a>' . PHP_EOL);
        echo('</li>' . PHP_EOL);
      }
    }

    if (!count($search_results))
    {
      echo('<li class="nav-header">No search results.</li>');
    }
  }
} else {
  // There is no queryString, we shouldn't get here.
  echo('<li class="nav-header">There should be no direct access to this script! Please reload the page.</li>');
}

// DOCME needs phpdoc block
// TESTME needs unit testing
function highlight_search($text)
{
  return preg_replace("/".preg_quote($GLOBALS['queryString'], "/")."/i", "<em class='text-danger'>$0</em>", $text);
}

// EOF
