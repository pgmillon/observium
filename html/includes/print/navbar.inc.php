<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage web
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

/**
 * Generate Bootstrap-format Navbar
 *
 *   A little messy, but it works and lets us move to having no navbar markup on pages :)
 *   Examples:
 *   print_navbar(array('brand' => "Apps", 'class' => "navbar-narrow", 'options' => array('mysql' => array('text' => "MySQL", 'url' => generate_url($vars, 'app' => "mysql")))))
 *
 * @param array $vars
 * @return none
 *
 */
function print_tabbar($tabbar)
{
  $output = '<ul class="nav nav-tabs">';

  foreach ($tabbar['options'] as $option => $array)
  {
    if ($array['right'] == TRUE) { $array['class'] .= ' pull-right'; }
    $output .= '<li class="' . $array['class'] . '">';
    $output .= '<a href="'.$array['url'].'">';
    if (isset($array['icon']))
    {
      $output .= '<i class="'.$array['icon'].'"></i> ';
    }

    $output .= $array['text'].'</a></li>';
  }
  $output .= '</ul>';

  echo $output;
}

/**
 * Generate Bootstrap-format navigation bar
 *
 *   A little messy, but it works and lets us move to having no navbar markup on pages :)
 *   Examples:
 *   print_navbar(array('brand' => "Apps", 'class' => "navbar-narrow", 'options' => array('mysql' => array('text' => "MySQL", 'url' => generate_url($vars, 'app' => "mysql")))))
 *
 * @param array $vars
 * @return none
 *
 */
function print_navbar($navbar)
{
  global $config;

  if (OBSERVIUM_EDITION == 'community' && isset($navbar['community']) && $navbar['community'] === FALSE)
  {
    // Skip not exist features on community
    return;
  }

  $id = strgen();

  ?>

  <div class="navbar <?php echo $navbar['class']; ?>" style="<?php echo $navbar['style']; ?>">
    <div class="navbar-inner">
      <div class="container">
        <button type="button" class="btn btn-navbar" data-toggle="collapse" data-target="#nav-<?php echo $id; ?>">
          <span class="oicon-bar"></span>
        </button>

  <?php

  if (isset($navbar['brand'])) { echo ' <a class="brand">'.$navbar['brand'].'</a>'; }
  echo('<div class="nav-collapse" id="nav-'.$id.'">');

  //rewrite navbar (for class pull-right)
  $newbar = array();
  foreach (array('options', 'options_right') as $array_name)
  {
    if (isset($navbar[$array_name]))
    {
      foreach ($navbar[$array_name] as $option => $array)
      {
        if (isset($array['userlevel']) && isset($_SESSION['userlevel']) && $_SESSION['userlevel'] < $array['userlevel'])
        {
          // skip not permitted menu items
          continue;
        }
        if (OBSERVIUM_EDITION == 'community' && isset($array['community']) && $array['community'] === FALSE)
        {
          // Skip not exist features on community
          continue;
        }

        if (strstr($array['class'], 'pull-right') || $array_name == 'options_right' || $array['right'] == TRUE)
        {
          $array['class'] = str_replace('pull-right', '', $array['class']);
          $newbar['options_right'][$option] = $array;
        } else {
          $newbar['options'][$option] = $array;
        }
      }
    }
  }

  foreach (array('options', 'options_right') as $array_name)
  {
    if ($array_name == 'options_right')
    {
      if (!$newbar[$array_name]) { break; }
      echo('<ul class="nav pull-right">');
    } else {
      echo('<ul class="nav">');
    }
    foreach ($newbar[$array_name] as $option => $array)
    {

      // if($array['divider']) { echo '<li class="divider"></li>'; break;}

      if (!is_array($array['suboptions']))
      {
        echo('<li class="'.$array['class'].'">');

        $link_opts = '';
        if (isset($array['link_opts'])) { $link_opts .= ' ' . $array['link_opts']; }
        if (isset($array['alt']))       { $link_opts .= ' data-rel="tooltip" data-tooltip="'.$array['alt'].'"'; }
        if (isset($array['id']))        { $link_opts .= ' id="'.$array['id'].'"'; }

        if (empty($array['url']) || $array['url'] == '#') { $array['url'] = 'javascript:void(0)'; }
        echo('<a href="'.$array['url'].'" '.$link_opts.'>');

        if (isset($array['icon']))
        {
          echo('<i class="'.$array['icon'].'"></i>&nbsp;');
          $array['text'] = '<span>'.$array['text'].'</span>'; // Added span for allow hide by class 'icon'
        }
        if (isset($array['image'])) { echo('<img src="' . $array['image'] . '" alt="" /> '); }
        echo($array['text'].'</a>');
        echo('</li>');
      } else {
        echo('  <li class="dropdown '.$array['class'].'">');

        $link_opts = '';
        if (isset($array['link_opts'])) { $link_opts .= ' ' . $array['link_opts']; }
        if (isset($array['alt']))       { $link_opts .= ' data-rel="tooltip" data-tooltip="'.$array['alt'].'"'; }
        if (isset($array['id']))        { $link_opts .= ' id="'.$array['id'].'"'; }

        if (empty($array['url']) || $array['url'] == '#') { $array['url'] = 'javascript:void(0)'; }
        echo('    <a class="dropdown-toggle" data-hover="dropdown" data-toggle="dropdown" href="'.$array['url'].'" '.$link_opts.'>');
        if (isset($array['icon'])) { echo('<i class="'.$array['icon'].'"></i> '); }
        echo($array['text'].'
            <strong class="caret"></strong>
          </a>
        <ul class="dropdown-menu">');
        foreach ($array['suboptions'] as $suboption => $subarray)
        {

          if(isset($subarray['divider']) && $subarray['divider'])
          {
            echo '<li class="divider"></li>';
          } else {
            echo('<li class="'.$subarray['class'].'">');
            $link_opts = '';
            if (isset($subarray['link_opts'])) { $link_opts .= ' ' . $subarray['link_opts']; }
            if (isset($subarray['alt']))       { $link_opts .= ' data-rel="tooltip" data-tooltip="'.$subarray['alt'].'"'; }
            if (isset($subarray['id']))        { $link_opts .= ' id="'.$subarray['id'].'"'; }

            if (empty($subarray['url']) || $subarray['url'] == '#') { $subarray['url'] = 'javascript:void(0)'; }
            echo('<a href="'.$subarray['url'].'" '.$link_opts.'>');
            if (isset($subarray['icon']))
            {
              echo('<i class="'.$subarray['icon'].'"></i>&nbsp;');
              $subarray['text'] = '<span>'.$subarray['text'].'</span>'; // Added span for allow hide by class 'icon'
            }
            echo($subarray['text'].'</a>');
            echo('</li>');
          }
        }
        echo('    </ul>
      </li>');
      }
    }
    echo('</ul>');
  }

  ?>
        </div>
      </div>
    </div>
  </div>

 <?php

}

// EOF
