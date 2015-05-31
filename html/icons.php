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
  if (preg_match('/\.(oicon[\w\-]+)\s*{/', $line, $matches)) { $oicons[] = $matches[1]; }
}

$icons_exclude = array('icon-large', 'icon-fixed-width', 'icon-li', 'icon-muted', 'icon-light', 'icon-dark',
                       'icon-border', 'icon-2x', 'icon-3x', 'icon-4x', 'icon-5x', 'icon-white', 'icon-spin',
                       'icon-stack', 'icon-rotate-90', 'icon-rotate-180', 'icon-rotate-270', 'icon-flip-horizontal',
                       'icon-flip-vertical', 'icon-bar');
foreach (new SplFileObject('css/bootstrap.css') as $line)
{
  if (preg_match('/\.(icon(?:-[^:\ {\.,+]+)+)/', $line, $matches))
  {
    if (!in_array($matches[1], $icons_exclude)) { $icons[$matches[1]] = TRUE; }
  }
}

foreach ($oicons as $icon)
{
  echo('<div style="margin: 2px; background-color: #f5f5f5; width: 260px; padding: 2px; float: left; height:16px; ">');
  echo('<a alt="'.$icon.'" class="'.$icon.'"><a> '.$icon);
  echo('</div>');
}

foreach ($icons as $icon => $bool)
{
  echo('<div style="margin: 2px; background-color: #f5f5f5; width: 260px; padding: 2px; float: left; height:16px; ">');
  echo('<a alt="'.$icon.'" class="'.$icon.'"><a> '.$icon);
  echo('</div>');
}

?>
</body>
</html>
