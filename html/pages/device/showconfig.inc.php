<?php

/**
 * Observium Network Management and Monitoring System
 * Copyright (C) 2006-2014, Adam Armstrong - http://www.observium.org
 *
 * @package    observium
 * @subpackage webui
 * @author     Adam Armstrong <adama@memetic.org>
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

if ($_SESSION['userlevel'] >= 5)
{
  // Print device config navbar
  $navbar = array();
  $navbar['brand'] = "Config";
  $navbar['class'] = "navbar-narrow";

  $rev = array('count' => 0);
  if (is_executable($config['svn']))
  {
    //$svnlogs = external_exec($config['svn'] . ' log -q -l 8 ' . $device_config_file); // Last 8 entries
    $svnlogs = external_exec($config['svn'] . ' log -q ' . $device_config_file);
    foreach (explode("\n", $svnlogs) as $line)
    {
      // r1884 | rancid | 2014-09-19 19:50:12 +0400 (Fri, 19 Sep 2014)
      // ------------------------------------------------------------------------
      if (preg_match('/r(?<rev>\d+) \| .+? \| (?<date>[\d\-]+ [\d:]+ [\d\+]+)/', $line, $matches))
      {
        $rev['list'][] = array('rev' => $matches['rev'], 'date' => format_timestamp(trim($matches['date'])));
        $rev['count']++;
      }
    }
    if ($rev['count']) { $rev['type'] = 'svn'; }
  }
  if (!$rev['count'] && is_executable($config['git']))
  {
    $gitlogs = external_exec($config['git'].' -C '.dirname($device_config_file).' log --pretty=format:"%h %ci" '.$device_config_file);
    foreach (explode("\n", $gitlogs) as $line)
    {
      // b6989b9 2014-11-10 00:16:53 +0100
      // 66840ee 2014-11-02 23:34:18 +0100
      if (preg_match('/(?<rev>\w+) (?<date>[\d\-]+ [\d:]+ [\d\+]+)/', $line, $matches))
      {
        $rev['list'][] = array('rev' => $matches['rev'], 'date' => format_timestamp($matches['date']));
        $rev['count']++;
      }
    }
    if ($rev['count']) { $rev['type'] = 'git'; }
  }

  $navbar['options']['latest']['url']   = generate_url(array('page'=>'device','device'=>$device['device_id'],'tab'=>'showconfig'));
  $navbar['options']['latest']['class'] = 'active';
  if ($rev['count'])
  {
    $rev_active_index = 0;
    foreach($rev['list'] as $i => $entry)
    {
      $rev_name = ($rev['type'] == 'svn' ? 'r'.$entry['rev'] : $entry['rev']);
      if ($i > 9)
      {
        break; // Show only last 10 revisions
      }
      else if ($i > 0)
      {
        $navbar['options'][$rev_name]['text'] = '['.$rev_name.', '.$entry['date'].']';
        $navbar['options'][$rev_name]['url']  = generate_url(array('page'=>'device','device'=>$device['device_id'],'tab'=>'showconfig','rev'=>$entry['rev']));
        if ($vars['rev'] == $entry['rev'])
        {
          unset($navbar['options']['latest']['class']);
          $navbar['options'][$rev_name]['class'] = 'active';
          $rev_active_index = $i;
        }
        else if ($rev['count'] > 4)
        {
          // Simplify too long revisions list
          $navbar['options'][$rev_name]['alt'] = $navbar['options'][$rev_name]['text'];
          $navbar['options'][$rev_name]['text'] = '['.$rev_name.']';
        }
      } else {
        // Latest revision
        $navbar['options']['latest']['text'] = 'Latest ['.$rev_name.', '.$entry['date'].']';
      }
    }
  } else {
    $navbar['options']['latest']['text'] = 'Latest';
  }

  // Print out the navbar defined above
  print_navbar($navbar);
  unset($navbar);

  if ($rev['count'])
  {
    $rev['curr'] = $rev['list'][$rev_active_index]['rev'];
    if (isset($rev['list'][$rev_active_index + 1]))
    {
      $rev['prev'] = $rev['list'][$rev_active_index + 1]['rev'];
    }
    switch ($rev['type'])
    {
      case 'svn':
        $cmd_cat   = $config['svn'] . ' cat -r'.$rev['curr'].' '.$device_config_file;
        $cmd_diff  = $config['svn'] . ' diff --ignore-properties -r'.$rev['prev'].':'.$rev['curr'].' '.$device_config_file;
        $prev_name = 'r'.$rev['prev'];
        break;
      case 'git':
        $cmd_cat   = $config['git'].' -C '.dirname($device_config_file).' show '.$rev['curr'].':'.basename($device_config_file);
        $cmd_diff  = $config['git'].' -C '.dirname($device_config_file).' diff '.$rev['prev'].' '.$rev['curr'].' '.$device_config_file;
        $prev_name = $rev['prev'];
    }
    $device_config = external_exec($cmd_cat);
    if (!isset($rev['prev']))
    {
      $diff = '';
      if (empty($device_config))
      {
        $device_config = '# Initial device added.';
      }
    } else {
      $diff = external_exec($cmd_diff);
      if (!$diff)
      {
        $diff = 'No Difference';
      }
    }
  } else {
    $fh = fopen($device_config_file, 'r') or die("Can't open file");
    $device_config = fread($fh, filesize($device_config_file));
    fclose($fh);
  }

  if ($config['rancid_ignorecomments'])
  {
    // FIXME. Cisco used '!' as comment
    $lines = explode('\n', $device_config);
    for ($i = 0;$i < count($lines);$i++)
    {
      if ($lines[$i][0] == '#') { unset($lines[$i]); }
    }
    $device_config = join('\n', $lines);
  }

  if ($rev['count'])
  {
    $text = '';
    ?>
<div class="panel-group" id="accordion">
    <?php
    if (isset($rev['prev']))
    {
    ?>
  <div class="panel panel-default">
    <div class="panel-heading" data-toggle="collapse" data-parent="#accordion" data-target="#diff">
      <h4 class="panel-title">
        <a class="accordion-toggle">
          Show difference with previous revision (<?php echo $prev_name; ?>):
        </a>
      </h4>
    </div>
    <div id="diff" class="panel-collapse collapse">
      <div class="panel-body">
        <pre class="prettyprint lang-sh">
          <?php echo htmlspecialchars($diff); ?>
        </pre>
      </div>
    </div>
  </div>
  <hr />
    <?php
    } // End if (isset($rev['prev']))
    ?>
  <div class="panel panel-default">
    <div class="panel-heading">
      <h4 class="panel-title"  data-toggle="collapse" data-parent="#accordion" data-target="#device_config">
        <a class="accordion-toggle">
          Device configuration:
        </a>
      </h4>
    </div>
    <div id="device_config" class="panel-collapse collapse in">
      <div class="panel-body">
        <pre class="prettyprint linenums lang-sh">
          <?php echo htmlspecialchars($device_config); ?>
        </pre>
      </div>
    </div>
  </div>
</div>
    <?php
  } else {
    $text = '<pre class="prettyprint linenums lang-sh">' . PHP_EOL . htmlspecialchars($device_config) . '</pre>' . PHP_EOL;
  }
  $text .= '<script type="text/javascript">window.prettyPrint && prettyPrint();</script>' . PHP_EOL;
  echo($text);
}

$pagetitle[] = 'Config';

// Clean
unset($text, $device_config, $diff, $rev, $rev_active_index);

// EOF
