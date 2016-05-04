<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage icons
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

?>
<html>
<head>
  <link rel="stylesheet" href="css/bootstrap.css" />
  <link rel="stylesheet" href="css/sprite.css" />
</head>
<body>
<?php

$icons = '';
foreach (new SplFileObject('css/sprite.css') as $line)
{
  if (preg_match('/\.(oicon[\w\-]+)\s*{/', $line, $matches)) { $oicons[$matches[1]] = TRUE; }
}

$icons_exclude = array('icon-large', 'icon-fixed-width', 'icon-li', 'icon-muted', 'icon-light', 'icon-dark',
                       'icon-border', 'icon-2x', 'icon-3x', 'icon-4x', 'icon-5x', 'icon-white', 'icon-spin',
                       'icon-stack', 'icon-rotate-90', 'icon-rotate-180', 'icon-rotate-270', 'icon-flip-horizontal',
                       'icon-flip-vertical', 'icon-bar', 'icon-stack-base');
foreach (new SplFileObject('css/bootstrap.css') as $line)
{
  if (preg_match_all('/\.(icon(?:-[^:\ {\.,+]+)+)/', $line, $matches))
  {
    foreach ($matches as $match)
    {
      foreach ($match as $icon)
      {
        if (!in_array($icon, $icons_exclude) && $icon[0] !== '.') { $icons[$icon] = TRUE; }
      }
    }
  }
  if (preg_match_all('/\.(glyphicon(?:-[^:\ {\.,+]+)+)/', $line, $matches))
  {
    foreach ($matches as $match)
    {
      foreach ($match as $icon)
      {
        if (!in_array($icon, $icons_exclude) && $icon[0] !== '.') { $glyphicons[$icon] = TRUE; }
      }
    }
  }
}

$types = array(
  'oicons'     => '',
  'icons'      => '',
  'glyphicons' => 'glyphicon ',
);
foreach ($types as $type => $base)
{
  echo('<hr /><h3>' . strtoupper($type) . '</h3>');
  foreach ($$type as $icon => $bool)
  {
    echo('<div style="margin: 2px; background-color: #f5f5f5; width: 280px; padding: 2px; height:16px; float: left;">');
    echo('<a alt="'.$icon.'"><i class="' . $base . $icon . '"></i>&nbsp;' . $icon . '</a>');
    echo('</div>');
  }
  echo('<div style="clear: both;"><br /></div>');
}

?>

</body>
</html>
