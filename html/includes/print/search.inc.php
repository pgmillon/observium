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
 * Generate search form
 *
 * generates a search form.
 * types allowed: select, multiselect, text (or input), datetime, newline
 *
 * Example of use:
 *  - array for 'select' item type
 *  $search[] = array('type'    => 'select',          // Type
 *                    'name'    => 'Search By',       // Displayed title for item
 *                    'id'      => 'searchby',        // Item id and name
 *                    'width'   => '120px',           // (Optional) Item width
 *                    'size'    => '15',              // (Optional) Maximum number of items to show in the menu (default 15)
 *                    'value'   => $vars['searchby'], // (Optional) Current value(-s) for item
 *                    'values'  => array('mac' => 'MAC Address',
 *                                       'ip'  => 'IP Address'));  // Array with option items
 *  - array for 'multiselect' item type (array keys same as above)
 *  $search[] = array('type'    => 'multiselect',
 *                    'name'    => 'Priorities',
 *                    'id'      => 'priority',
 *                    'width'   => '150px',
 *                    'subtext' => TRUE,              // (Optional) Display items value right of the item name
 *                    'encode'  => FALSE,             // (Optional) Use var_encode for values, use when values contains commas or empty string
 *                    'value'   => $vars['priority'],
 *                    'values'  => $priorities);
 *  - array for 'text' or 'input' item type
 *  $search[] = array('type'  => 'text',
 *                    'name'  => 'Address',
 *                    'id'    => 'address',
 *                    'width' => '120px',
 *                    'placeholder' => FALSE,         // (Optional) Display item name as pleseholder or left relatively input
 *                    'value' => $vars['address']);
 *  - array for 'datetime' item type
 *  $search[] = array('type'  => 'datetime',
 *                    'id'    => 'timestamp',
 *                    'presets' => TRUE,                  // (optional) Show select field with timerange presets
 *                    'min'   => dbFetchCell('SELECT MIN(`timestamp`) FROM `syslog`'), // (optional) Minimum allowed date/time
 *                    'max'   => dbFetchCell('SELECT MAX(`timestamp`) FROM `syslog`'), // (optional) Maximum allowed date/time
 *                    'from'  => $vars['timestamp_from'], // (optional) Current 'from' value
 *                    'to'    => $vars['timestamp_to']);  // (optional) Current 'to' value
 *  - array for 'sort' item pseudo type
 *  $search[] = array('type'   => 'sort',
 *                    'value'  => $vars['sort'],
 *                    'values' => $sorts);
 *  - array for 'newline' item pseudo type
 *  $search[] = array('type' => 'newline',
 *                    'hr'   => FALSE);                   // (optional) show or not horizontal line
 *  print_search($search, 'Title here', 'search', url);
 *
 * @param array $data, string $title
 * @return none
 *
 */
function print_search($data, $title = NULL, $button = 'search', $url = NULL)
{
  // Cache permissions to session var
  permissions_cache_session();
  //r($_SESSION['cache']);

  $submit_by_key = FALSE;
  $string_items = '';
  foreach ($data as $item)
  {
    if ($url && isset($item['id']))
    {
      // Remove old vars from url
      $url = preg_replace('/'.$item['id'].'=[^\/]+\/?/', '', $url);
    }
    if ($item['type'] == 'sort')
    {
      $sort = $item;
      continue;
    }
    else if (isset($item['submit_by_key']) && $item['submit_by_key'])
    {
      $submit_by_key = TRUE;
    }
    $string_items .= generate_form_element($item);
  }

  $form_id = 'search-'.strgen('4');

  if ($submit_by_key)
  {
    $action = '';
    if ($url)
    {
      $action .= 'this.form.prop(\'action\', form_to_path(\'' . $form_id . '\'));';
    }
    $GLOBALS['cache_html']['script'][] = '$(function(){$(\'form#' . $form_id . '\').each(function(){$(this).find(\'input\').keypress(function(e){if(e.which==10||e.which==13){'.$action.'this.form.submit();}});});});';
  }

  // Form header
  $string = PHP_EOL . '<!-- START search form -->' . PHP_EOL;
  $string .= '<form method="POST" action="'.$url.'" class="form-inline" id="'.$form_id.'">' . PHP_EOL;
  $string .= '<div class="navbar">' . PHP_EOL;
  $string .= '<div class="navbar-inner">';
  $string .= '<div class="container">';
  if (isset($title)) { $string .= '  <a class="brand">' . $title . '</a>' . PHP_EOL; }

  $string .= '<div class="nav" style="margin: 5px 0 5px 0;">';

  // Main
  $string .= $string_items;

  $string .= '</div>';

  // Form footer
  /// FIXME. I don't know how to put this buttons to middle or bottom..
  $string .= '    <div class="nav pull-right"';

  $button_style = 'line-height: 20px;';
  // Add sort switcher if present
  if (isset($sort))
  {
    $string .= ' style="margin: 5px 0 5px 0;">' . PHP_EOL;
    $string .= '      <select name="sort" id="sort" class="selectpicker pull-right" title="Sort Order" style="width: 150px;" data-width="150px">' . PHP_EOL;
    foreach ($sort['values'] as $item => $name)
    {
      if (!$sort['value']) { $sort['value'] = $item; }
      $string .= '        <option value="'.$item.'"';
      if ($sort['value'] == $item)
      {
        $string .= ' data-icon="oicon-sort-alphabet-column" selected';
      }
      $string .= '>'.$name.'</option>';
    }
    $string .= '      </select><br />' . PHP_EOL;
    $button_style .= ' margin-top: 7px;';
  } else {
    $string .= '>' . PHP_EOL;
  }

  // Note, script submitURL() stored in js/observium.js
  $button_type    = 'submit';
  $button_onclick = '';
  if ($url)
  {
    $button_type    = 'button';
    $button_onclick = " onclick=\"form_to_path('".$form_id."');\"";
  }

  $string .= '      <button type="'.$button_type.'" class="btn pull-right" style="'.$button_style.'"'.$button_onclick.'>';
  switch($button)
  {
    // Note. 'update' - use POST request, all other - use GET with generate url from js.
    case 'update':
      $string .= '<i class="icon-refresh"></i> Update</button>' . PHP_EOL;
      break;
    default:
      $string .= '<i class="icon-search"></i> Search</button>' . PHP_EOL;
  }
  $string .= '    </div>' . PHP_EOL;
  $string .= '</div></div></div></form>' . PHP_EOL;
  $string .= '<!-- END search form -->' . PHP_EOL . PHP_EOL;

  // Print search form
  echo($string);
}

/**
 * Pretty form generator
 *
 * Form options:
 *   id     - form id, default is auto generated
 *   type   - rows (multiple elements with small amount of rows), horizontal (mostly single element per row), simple (raw form without any grid/divs)
 *   brand  - only for rows, adds "other" form title (I think not work and obsolete)
 *   title  - displayed form title (only for rows and horizontal)
 *   icon   - adds icon to title
 *   class  - adds div with class (default box box-solid) in horizontal
 *   space  - adds style for base div in rows type and horizontal with box box-solid class (padding: xx) and horizontal type with box class (padding-top: xx)
 *   style  - adds style for base form element, default (margin-bottom:0;)
 *   url    - form action url, if url set and submit element with id "search" used (or submit_by_key), than form send params as GET query
 *   submit_by_key - send form query by press enter key in text/input forms
 *   fieldset - horizontal specific, array with fieldset names and descriptions, in form element should be add fieldset option with same key name
 *
 * Elements options see in generate_form_element() description
 *
 * @param array $data Form options and form elements
 * @return NULL
 */
function print_form($data)
{
  $form_id    = (isset($data['id']) ? $data['id'] : 'form-'.strgen());
  $form_class = 'form form-inline'; // default for rows and simple
  if (isset($data['style']))
  {
    $form_style = ' style="'.$data['style'].'"';
  } else {
    $form_style = ' style="margin-bottom: 0px;"';
  }
  $base_class = (array_key_exists('class', $data) ? $data['class'] : OBS_CLASS_BOX);
  $base_space = ($data['space'] ? $data['space'] : '5px');
  $used_vars  = array();

  // Cache permissions to session var
  permissions_cache_session();
  //r($_SESSION['cache']);

  if ($data['submit_by_key'])
  {
    $action = '';
    if ($data['url'])
    {
      $action .= 'this.form.prop(\'action\', form_to_path(\'' . $form_id . '\'));';
    }
    $GLOBALS['cache_html']['script'][] = '$(function(){$(\'form#' . $form_id . '\').each(function(){$(this).find(\'input\').keypress(function(e){if(e.which==10||e.which==13){'.$action.'this.form.submit();}});});});';
  }

  // Form elements
  if ($data['type'] == 'rows')
  {
    // Rows form, see example in html/pages/devices.inc.php
    if (strpos($base_class, 'box') !== FALSE)
    {
      $base_space = ($data['space'] ? $data['space'] : '10px');

      // Box horizontal style
      $box_args  = array('header-border' => TRUE,
                         'body-style' => 'padding: '.$base_space.' !important;'); // Override top padding
      if (isset($data['title'])) { $box_args['title'] = $data['title']; }
      $div_begin = generate_box_open($box_args);
      $div_end   = generate_box_close();
      unset($box_args);
    } else {
      $div_begin = '<div class="'.$base_class.'" style="padding: '.$base_space.';">' . PHP_EOL;
      $div_end   = '</div>' . PHP_EOL;
    }
    $row_style = '';
    $string_elements = '';

    //$max_count = 0;
    //foreach ($data['row'] as $row)
    //{
    //  // Search max row
    //  if (count($row) > $max_count) { $max_count = count($row); }
    //}
    //// Calculate grid system
    //$grid = intval(12 / $max_count);
    //if ($grid < 2) { $grid = 2; } // minimum 2 for auto
    //$div_class = 'col-lg-' . $grid . ' col-md-' . $grid . ' col-sm-' . $grid;

    foreach ($data['row'] as $k => $row)
    {
      // Calculate grid system for current row
      $grid = intval(12 / count($row));
      if ($grid < 2) { $grid = 2; } // minimum 2 for auto
      $div_class = 'col-lg-' . $grid . ' col-md-' . $grid . ' col-sm-' . $grid;

      $string_elements .= '  <div class="row" '.$row_style.'> <!-- START row-'.$k.' -->' . PHP_EOL;
      foreach ($row as $id => $element)
      {
        $used_vars[]      = $id;
        $element['id']    = $id;
        if (empty($element['div_class']))
        {
          $element['div_class'] = $div_class;
        }
        if ($element['right'])
        {
          $element['div_class'] .= ' col-lg-push-0 col-md-push-0 col-sm-push-0';
        }
        if ($id == 'search' && $data['url'])
        {
          // Add form_id here, for generate onclick action in submit button
          $element['form_id'] = $form_id;
        }
        $string_elements .= '    <div class="'.$element['div_class'].'">' . PHP_EOL;
        $string_elements .= generate_form_element($element);
        $string_elements .= '    </div>' . PHP_EOL;
      }
      $string_elements .= '  </div> <!-- END row-'.$k.' -->' . PHP_EOL;
      $row_style = 'style="margin-top: '.$base_space.';"'; // Add space between rows
    }
  } // end rows type
  else if ($data['type'] == 'horizontal')
  {
    // Horizontal form, see example in html/pages/edituser.inc.php
    if (strpos($base_class, 'widget') !== FALSE || strpos($base_class, 'box') !== FALSE)
    {
      $base_space = ($data['space'] ? $data['space'] : '10px');

      // Box horizontal style
      $box_args  = array('header-border' => TRUE,
                         'body-style' => 'padding-top: '.$base_space.' !important;'); // Override top padding
      if (isset($data['title'])) { $box_args['title'] = $data['title']; }
      $div_begin = generate_box_open($box_args);
      $div_end   = generate_box_close();
      unset($box_args);
    }
    else if (empty($base_class))
    {
      // Clean class
      // Example in html/pages.logon.inc.php
      $div_begin = PHP_EOL;
      $div_end   = PHP_EOL;
    } else {
      // Old box box-solid style (or any custom style)
      $div_begin = '<div class="'.$base_class.'" style="padding: '.$base_space.';">' . PHP_EOL;
      if (isset($data['title']))
      {
        $div_begin .= '  <div class="title">';
        if ($data['icon'])
        {
           $div_begin .= '<i class="'.$data['icon'].'"></i>';
        }
        $div_begin .= '&nbsp;'.$data['title'].'</div>' . PHP_EOL;
      }
      $div_end   = '</div>' . PHP_EOL;
    }
    $form_class = 'form form-horizontal';
    $row_style = '';
    $fieldset  = array();

    foreach ($data['row'] as $k => $row)
    {
      $row_group = $k;
      $row_elements = '';
      $row_label    = '';
      $row_control_group = FALSE;
      $i = 0;
      foreach ($row as $id => $element)
      {
        $used_vars[]      = $id;
        $element['id']    = $id;
        if ($element['fieldset'])
        {
          $row_group = $element['fieldset']; // Add this element to group
        }

        // Additional element options for horizontal specific form
        $div_style = '';
        switch ($element['type'])
        {
          case 'hidden':
            $div_class = '';
            break;
          case 'submit':
            $div_class = 'form-actions';
            break;
          case 'text':
          case 'input':
          case 'password':
          case 'textarea':
          default:
            $row_control_group = TRUE;
            // In horizontal, first element name always placed at left
            if (!isset($element['placeholder'])) { $element['placeholder'] = TRUE; }
            if ($i < 1)
            {
              // Add laber for first element in row
              $row_label = '    <label class="control-label" for="'.$element['id'].'">'.$element['name'].'</label>' . PHP_EOL;
              $row_control_id = $element['id'] . '_div';
              if ($element['type'] == 'datetime')
              {
                $element['name'] = '';
              }
            }
            $div_class = 'controls';
            break;
        }

        if (empty($element['div_class']))
        {
          $element['div_class'] = $div_class;
        }
        if ($element['div_class'] == 'form-actions')
        {
          // Remove margins only for form-actions elements
          $div_style = 'margin: 0px;';
        }
        //if ($element['right'])
        //{
        //  $element['div_class'] .= ' pull-right';
        //}
        if (isset($element['div_style']))
        {
          $div_style .= ' ' . $element['div_style'];
        }
        if ($id == 'search' && $data['url'])
        {
          // Add form_id here, for generate onclick action in submit button
          $element['form_id'] = $form_id;
        }

        $row_elements .= generate_form_element($element);
        $i++;
      }
      if ($element['div_class'])
      {
        // no additional divs if empty div class (hidden element for example)
        $row_begin = $row_label . PHP_EOL . '    <div class="'.$element['div_class'].'"';
        if (strlen($div_style))
        {
          $row_begin .= ' style="' . $div_style . '"';
        }
        $row_elements = $row_begin . '>' . PHP_EOL . $row_elements . '    </div>' . PHP_EOL;
      } else {
        $row_elements = $row_label . PHP_EOL . $row_elements;
      }

      if ($row_control_group)
      {
        $fieldset[$row_group] .= '  <div id="'.$row_control_id.'" class="control-group" style="margin-bottom: '.$base_space.';"> <!-- START row-'.$k.' -->' . PHP_EOL;
        $fieldset[$row_group] .= $row_elements;
        $fieldset[$row_group] .= '  </div> <!-- END row-'.$k.' -->' . PHP_EOL;
      } else {
        // Do not add control group for submit/hidden
        $fieldset[$row_group] .= $row_elements;
      }
      //$row_style = 'style="margin-top: '.$base_space.';"'; // Add space between rows
    }
    foreach ($data['fieldset'] as $row_group => $name)
    {
      if (isset($fieldset[$row_group]))
      {
        $row_elements = '
          <fieldset> <!-- START fieldset-'.$row_group.' -->';
        if (!empty($name))
        {
          // fieldset title
          $row_elements .= '
          <div class="control-group">
              <div class="controls">
                  <h3>'.$name.'</h3>
              </div>
          </div>';
        }
        $row_elements .= PHP_EOL . $fieldset[$row_group] . '
          </fieldset>  <!-- END fieldset-'.$row_group.' -->
        ' . PHP_EOL;
        $fieldset[$row_group] = $row_elements;
      }
    }
    $string_elements = implode('', $fieldset);
  } else {
    // Simple form, without any divs, see example in html/pages/edituser.inc.php
    $div_begin  = '';
    $div_end    = '';
    $string_elements = '';
    foreach ($data['row'] as $k => $row)
    {
      foreach ($row as $id => $element)
      {
        $used_vars[]      = $id;
        $element['id']    = $id;

        if ($id == 'search' && $data['url'])
        {
          // Add form_id here, for generate onclick action in submit button
          $element['form_id'] = $form_id;
        }
        $string_elements .= generate_form_element($element);
      }
      $string_elements .= PHP_EOL;
    }
  }

  // Remove old vars from url
  if ($data['url'])
  {
    foreach ($used_vars as $var)
    {
      $data['url'] = preg_replace('/'.$var.'=[^\/]+\/?/', '', $data['url']);
    }
  }

  // Form header
  $string = PHP_EOL . "<!-- START $form_id -->" . PHP_EOL;
  $string .= $div_begin;
  $string .= '<form method="POST" id="'.$form_id.'" name="'.$form_id.'" action="'.$data['url'].'" class="'.$form_class.'"'.$form_style.'>' . PHP_EOL;
  if ($data['brand']) { $string .= '  <a class="brand">' . $data['brand'] . '</a>' . PHP_EOL; }

  // Form elements
  $string .= $string_elements;

  // Form footer
  $string .= '</form>' . PHP_EOL;
  $string .= $div_end;
  $string .= "<!-- END $form_id -->" . PHP_EOL;

  // Print form
  echo($string);
}

// Box specific form
function print_form_widget($data)
{
  print_form_box($data);
}

// Box specific form (mostly same as in print_form, but support only box style and fieldset options)
// FIXME should likely not be in this file? As it's used throughout the software now...
function print_form_box($data)
{
  $form_id    = (isset($data['id']) ? $data['id'] : 'form-'.strgen());
  $form_class = 'form form-horizontal';
  if (isset($data['style']))
  {
    $form_style = ' style="'.$data['style'].'"';
  } else {
    $form_style = ' style="margin-bottom:0;"';
  }
  $base_class = (array_key_exists('class', $data) ? $data['class'] : 'box');
  $base_space = ($data['space'] ? $data['space'] : '15px');
  $used_vars  = array();

  // Cache permissions to session var
  permissions_cache_session();
  //r($_SESSION['cache']);

  if ($data['submit_by_key'])
  {
    $action = '';
    if ($data['url'])
    {
      $action .= 'this.form.prop(\'action\', form_to_path(\'' . $form_id . '\'));';
    }
    $GLOBALS['cache_html']['script'][] = '$(function(){$(\'form#' . $form_id . '\').each(function(){$(this).find(\'input\').keypress(function(e){if(e.which==10||e.which==13){'.$action.'this.form.submit();}});});});';
  }

  $header = '';
  if (isset($data['title']))
  {
    $header .= '  <h2>' . $data['title'] . '</h2>' . PHP_EOL;
  }

  // Form elements
  $div_begin = '<div class="row">' . PHP_EOL;
  $div_end   = '</div>' . PHP_EOL;
  if ($data['type'] == 'horizontal')
  {
    $row_style = '';
    $fieldset  = array();

    foreach ($data['row'] as $k => $row)
    {
      $row_group = $k;
      $row_elements = '';
      $row_label    = '';
      $row_control_group = FALSE;
      $i = 0;
      foreach ($row as $id => $element)
      {
        $used_vars[]      = $id;
        $element['id']    = $id;
        if ($element['fieldset'])
        {
          $row_group = $element['fieldset']; // Add this element to group
        }

        // Additional element options for horizontal specific form
        switch ($element['type'])
        {
          case 'hidden':
            $div_class = '';
            $div_style = '';
            break;
          case 'submit':
            $div_class = 'form-actions';
            $div_style = ' style="margin: 0px;"';
            break;
          case 'text':
          case 'input':
          case 'password':
          case 'textarea':
          default:
            $row_control_group = TRUE;
            // In horizontal, name always placed at left
            if (!isset($element['placeholder'])) { $element['placeholder'] = TRUE; }
            if ($i < 1)
            {
              // Add laber for first element in row
              $row_label = '    <label class="control-label" for="'.$element['id'].'">'.$element['name'].'</label>' . PHP_EOL;
              $row_control_id = $element['id'] . '_div';
            }
            $div_class = 'controls';
            $div_style = '';
            break;
        }

        if (!isset($element['div_class']))
        {
          $element['div_class'] = $div_class;
        }
        //if ($element['right'])
        //{
        //  $element['div_class'] .= ' pull-right';
        //}
        if ($id == 'search' && $data['url'])
        {
          // Add form_id here, for generate onclick action in submit button
          $element['form_id'] = $form_id;
        }

        $row_elements .= generate_form_element($element);
        $i++;
      }
      if ($element['div_class'])
      {
        // no additional divs if empty div class (hidden element for example)
        $row_elements = $row_label . PHP_EOL .
                        '    <div class="'.$element['div_class'].'"'.$div_style.'>' . PHP_EOL .
                        $row_elements .
                        '    </div>' . PHP_EOL;
      } else {
        $row_label = str_replace(' class="control-label"', '', $row_label);
        $row_elements = $row_label . PHP_EOL . $row_elements;
      }

      if ($row_control_group)
      {
        $fieldset[$row_group] .= '  <div id="'.$row_control_id.'" class="control-group"> <!-- START row-'.$k.' -->' . PHP_EOL;
        $fieldset[$row_group] .= $row_elements;
        $fieldset[$row_group] .= '  </div> <!-- END row-'.$k.' -->' . PHP_EOL;
      } else {
        // Do not add control group for submit/hidden
        $fieldset[$row_group] .= $row_elements;
      }
      //$row_style = 'style="margin-top: '.$base_space.';"'; // Add space between rows
    }

    $divs = array();
    $fieldset_tooltip = '';
    foreach ($data['fieldset'] as $group => $entry)
    {
      if (isset($fieldset[$group]))
      {
        if (!is_array($entry))
        {
          $entry = array('title' => $entry);
        }
        // Custom style
        if (!isset($entry['style']))
        {
          $entry['style'] = 'padding-bottom: 0px !important;'; // Remove last additional padding space
        }
        // Combinate fieldsets into common rows
        if ($entry['div'])
        {
          $divs[$entry['div']][] = $group;
        } else {
          $divs['row'][] = $group;
        }

        $box_args = array('header-border' => TRUE,
                          'padding' => TRUE,
                          'id' => $group,
                         );
        if (isset($entry['style']))
        {
          $box_args['body-style'] = $entry['style'];
        }
        if (isset($entry['title']))
        {
          $box_args['title'] = $entry['title'];
          if ($entry['icon'])
          {
            // $box_args['icon'] => $entry['icon'];
          }
        }

        if(isset($entry['tooltip']))
        {
          $box_args['header-controls'] = array('controls' => array('tooltip'   => array('icon'   => 'icon-info text-primary',
                                                                                        'anchor' => TRUE,
                                                                                        //'url'    => '#',
                                                                                        'class'  => 'tooltip-from-element',
                                                                                        'data'   => 'data-tooltip-id="tooltip-'.$group.'"')));

          $fieldset_tooltip .= '<div id="tooltip-'.$group.'" style="display: none;">' . PHP_EOL;
          $fieldset_tooltip .= $entry['tooltip'] . '</div>' . PHP_EOL;
        }

        if(isset($entry['tooltip'])) { $box_args['style'] = $entry['style']; }

        $fieldset_begin = generate_box_open($box_args);

        $fieldset_end   = generate_box_close();

        // Additional div class if set
        if (isset($entry['class']))
        {
          $fieldset_begin = '<div class="'.$entry['class'].'">' . PHP_EOL . $fieldset_begin;
          $fieldset_end  .= '</div>' . PHP_EOL;
        }

        $row_elements = $fieldset_begin . '
          <fieldset> <!-- START fieldset-'.$group.' -->';
        $row_elements .= PHP_EOL . $fieldset[$group] . '
          </fieldset> <!-- END fieldset-'.$group.' -->' . PHP_EOL;
        $fieldset[$group] = $row_elements . $fieldset_end;
      }
    }
    // Combinate fieldsets into common rows
    foreach ($divs as $entry)
    {
      $row_elements = $div_begin;
      foreach ($entry as $i => $group)
      {
        $row_elements .= $fieldset[$group];
        if ($i > 0)
        {
          // unset all fieldsets instead first for replace later
          unset($fieldset[$group]);
        }
      }
      $row_elements .= $div_end;
      // now replace first fieldset in group
      $fieldset[array_shift($entry)] = $row_elements;
    }
    // Final combining elements
    $string_elements = implode('', $fieldset);
  }

  // Remove old vars from url
  if ($data['url'])
  {
    foreach ($used_vars as $var)
    {
      $data['url'] = preg_replace('/'.$var.'=[^\/]+\/?/', '', $data['url']);
    }
  }

  // Form header
  $string = PHP_EOL . "<!-- START $form_id -->" . PHP_EOL;
  $string .= $header;
  $string .= '<form method="POST" id="'.$form_id.'" name="'.$form_id.'" action="'.$data['url'].'" class="'.$form_class.'"'.$form_style.'>' . PHP_EOL;

  // Form elements
  $string .= $string_elements;

  // Form footer
  $string .= '</form>' . PHP_EOL;
  $string .= $fieldset_tooltip;
  $string .= "<!-- END $form_id -->" . PHP_EOL;

  // Print form
  echo($string);
}

/**
 * Generates form elements. The main use for print_search() and print_form(), see examples of this functions.
 *
 * Options tree:
 * textarea -\
 *     (string)id, (string)name, (bool)readonly, (bool)disabled, (string)width, (string)class,
 *     (int)rows, (int)cols,
 *     (string)value, (bool,string)placeholder, (bool)ajax, (array)ajax_vars
 * text, input, password -\
 *     (string)id, (string)name, (bool)readonly, (bool)disabled, (string)width, (string)class,
 *     (string)value, (bool,string)placeholder, (bool)ajax, (array)ajax_vars,
 *     (bool)show_password
 * hidden -\
 *     (string)id, (string)value
 * select, multiselect -\
 *     (string)id, (string)name, (bool)readonly, (bool)disabled, (string)onchange, (string)width,
 *     (string)title, (int)size, (bool)right, (bool)live-search, (bool)encode, (bool)subtext
 *     (string)value, (array)values, (string)icon,
 *     values can be as array('name' => string, 'icon' => string)
 * datetime -\
 *     (string)id, (string)name, (bool)readonly, (bool)disabled,
 *     (string|FALSE)from, (string|FALSE)to, (bool)presets, (string)min, (string)max
 *     (string)value (use it for single input)
 * checkbox, switch -\
 *     (string)id, (string)name, (bool)readonly, (bool)disabled, (string)onchange,
 *     (bool)revert, (int)width, (string)size, (string)off-color, (string)on-color, (string)off-text, (string)on-text
 *     (string)value, (string)placeholder, (string)title
 * submit -\
 *     (string)id, (string)name, (bool)readonly, (bool)disabled,
 *     (string)class, (bool)right,
 *     (string)value, (string)form_id, (string)icon
 * html, raw -\
 *     (string)id,
 *     (string)html
 * newline -\
 *     (string)id,
 *     (bool)hr
 *
 * @param array $item Options for current form element
 * @param string $type Type of form element, also can passed as $item['type']
 * @return string Generated form element
 */
function generate_form_element($item, $type = '')
{
  $value_isset = isset($item['value']);
  if (!$value_isset) { $item['value'] = ''; }
  if (!isset($item['type']))  { $item['type'] = $type; }
  $string = '';
  switch ($item['type'])
  {
    case 'hidden':
      if (!$item['readonly'] && !$item['disabled']) // If item readonly or disabled, just skip item
      {
        $string .= '    <input type="'.$item['type'].'" name="'.$item['id'] . '" id="' .$item['id'] . '" value="'.$item['value'].'" />' . PHP_EOL;
      }
      break;
    case 'password':
    case 'textarea':
    case 'text':
    case 'input':
      if ($item['type'] != 'textarea')
      {
        $item_begin = '    <input type="'.$item['type'].'" ';
        // password specific options
        if ($item['type'] == 'password')
        {
          // disable autocomplete for passwords
          $item_begin .= ' autocomplete="off" ';
          // mask password field for disabled/readonly by bullet
          if (strlen($item['value']) && ($item['disabled'] || $item['readonly']))
          {
            if (!($item['show_password'] && $_SESSION['userlevel'] > 7)) // For admin, do not replace, required for show password
            {
              $item['value'] = '&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;&#8226;';
            }
          }
          // add icon for show/hide password
          if ($item['show_password'])
          {
            $item_begin .= ' data-toggle="password" ';
            $GLOBALS['cache_html']['js'][] = 'js/bootstrap-show-password.min.js';
            $GLOBALS['cache_html']['javascript'][] = "$('[data-toggle=\"password\"]').password();";
          }
        }
        $item_end   = ' value="'.$item['value'].'" />';
        $item_class = 'input';
      } else {
        $item_begin = '    <textarea ';
        // textarea specific options
        if (is_numeric($item['rows']))
        {
          $item_begin .= 'rows="' . $item['rows'] . '" ';
        }
        if (is_numeric($item['cols']))
        {
          $item_begin .= 'cols="' . $item['cols'] . '" ';
        }
        $item_end   = '>' . $item['value'] . '</textarea>';
        $item_class = 'form-control';
      }
      if ($item['disabled'])
      {
        $item_end = ' disabled="1"' . $item_end;
      }
      else if ($item['readonly'])
      {
        $item_end = ' readonly="1"' . $item_end;
      }

      if (isset($item['placeholder']) && $item['placeholder'] !== FALSE)
      {
        if ($item['placeholder'] === TRUE)
        {
          $item['placeholder'] = $item['name'];
        }
        $string .= PHP_EOL;
        $string .= $item_begin . 'placeholder="'.$item['placeholder'].'" ';
        $item['placeholder'] = TRUE; // Set to true for check at end
      } else {
        $string .= '  <div class="input-prepend">' . PHP_EOL;
        if (!$item['name']) { $item['name'] = '<i class="icon-list"></i>'; }
        $string .= '    <span class="add-on">'.$item['name'].'</span>' . PHP_EOL;
        $string .= $item_begin;
      }
      if ($item['class'])
      {
        $item_class .= ' ' . $item['class'];
      }

      $string .= (isset($item['width'])) ? 'style="width:' . $item['width'] . '" ' : '';
      $string .= 'name="'.$item['id'] . '" id="' .$item['id'] . '" class="' . $item_class;

      if ($item['ajax'] === TRUE && is_array($item['ajax_vars']))
      {
        $ajax_vars = array();
        if (!isset($item['ajax_vars']['field']))
        {
          // If query field not specified use item id as field
          $item['ajax_vars']['field'] = $item['id'];
        }
        foreach ($item['ajax_vars'] as $k => $v)
        {
          $ajax_vars[] = urlencode($k) . '=' . var_encode($v);
        }
        $string .= ' ajax-typeahead" autocomplete="off" data-link="/ajax/input.php?' . implode('&amp;', $ajax_vars);
      }

      $string .= '" ' . $item_end . PHP_EOL;
      $string .= ($item['placeholder'] ? PHP_EOL : '  </div>' . PHP_EOL);
      // End 'text' & 'input'
      break;
    case 'switch':
      // switch specific options
      if ($item['revert'])
      {
        $item_switch = ' data-toggle="switch-revert"';
      } else {
        $item_switch = ' data-toggle="switch"';
      }
      if ($item['size'])      { $item_switch .= ' data-size="' . $item['size'] . '"'; }
      if ($item['on-color'])  { $item_switch .= ' data-on-color="' . $item['on-color'] . '"'; }
      if ($item['off-color']) { $item_switch .= ' data-off-color="' . $item['off-color'] . '"'; }
      if ($item['on-text'])   { $item_switch .= ' data-on-text="' . $item['on-text'] . '"'; }
      if ($item['off-text'])  { $item_switch .= ' data-off-text="' . $item['off-text'] . '"'; }
      if (is_numeric($item['width']) && $item['width'] > 10)
      {
        $item_switch .= ' data-handle-width="' . intval($item['width'] / 2) . '"';
      }
    case 'checkbox':
      $string = '    <input type="checkbox" ';
      $string .= ' name="'.$item['id'] . '" id="' .$item['id'] . '" ' . $item_switch;
      if ($item['title'])
      {
        $string .= ' data-rel="tooltip" data-tooltip="'.escape_html($item['title']).'"';
      }
      if ($item['value'] == '1' || $item['value'] === 'on' || $item['value'] === 'yes' || $item['value'] === TRUE)
      {
        $string .= ' checked';
      }
      if ($item['disabled'])
      {
        $string .= ' disabled="1"';
      }
      else if ($item['readonly'])
      {
        $string .= ' readonly="1" onclick="return false"';
      }
      else if ($item['onchange'])
      {
        $string .= ' onchange="'.$item['onchange'].'"';
      }
      $string .= ' value="1" />';
      if (is_string($item['placeholder']))
      {
        // add placeholder text at right of the element
        $string .= '      <span class="help-inline" style="margin-top: 4px;">' .
                   $item['placeholder'] . '</span>' . PHP_EOL;
      }
      // End 'switch' & 'checkbox'
      break;
    case 'datetime':
      $GLOBALS['cache_html']['js'][] = 'js/bootstrap-datetimepicker.min.js'; // Enable DateTime JS
      $id_from = $item['id'].'_from';
      $id_to = $item['id'].'_to';
      if ($value_isset && !$item['from'] && !$item['to'])
      {
        // Single datetime input
        $item['from']    = $item['value'];
        $item['to']      = FALSE;
        $item['presets'] = FALSE;
        $id_from      = $item['id'];
        $name_from    = $item['name'];
      } else {
        $name_from = 'From';
      }
      // Presets
      if ($item['from'] === FALSE || $item['to'] === FALSE) { $item['presets'] = FALSE; }

      if (is_numeric($item['from'])) { $item['from'] = strftime("%F %T", $item['from']); }
      if (is_numeric($item['to']))   { $item['to']   = strftime("%F %T", $item['to']); }

      if ($item['presets'])
      {
        $presets = array('sixhours'  => 'Last 6 hours',
                         'today'     => 'Today',
                         'yesterday' => 'Yesterday',
                         'tweek'     => 'This week',
                         'lweek'     => 'Last week',
                         'tmonth'    => 'This month',
                         'lmonth'    => 'Last month',
                         'tquarter'  => 'This quarter',
                         'lquarter'  => 'Last quarter',
                         'tyear'     => 'This year',
                         'lyear'     => 'Last year');
        $string .= '    <select id="'.$item['id'].'_preset" class="selectpicker show-tick" data-size="false" data-width="120px">' . PHP_EOL . '      ';
        $string .= '<option value="" selected>Date presets</option>';
        foreach ($presets as $k => $v)
        {
          $string .= '<option value="'.$k.'">'.$v.'</option> ';
        }
        $string .= PHP_EOL . '    </select>' . PHP_EOL;
      }
      // Date/Time input fields
      if ($item['from'] !== FALSE)
      {
        $string .= '  <div id="'.$id_from.'_div" class="input-prepend" style="margin-bottom: 0;">' . PHP_EOL;
        $string .= '    <span class="add-on btn"><i data-time-icon="icon-time" data-date-icon="icon-calendar"></i> '.$name_from.'</span>' . PHP_EOL;
        $string .= '    <input type="text" class="input-medium" data-format="yyyy-MM-dd hh:mm:ss" ';
        if ($item['disabled'])
        {
          $string .= 'disabled="1" ';
        }
        else if ($item['readonly'])
        {
          $item['disabled'] = TRUE; // for js
          $string .= 'readonly="1" ';
        }
        $string .= 'name="'.$id_from.'" id="'.$id_from.'" value="'.$item['from'].'"/>' . PHP_EOL;
        $string .= '  </div>' . PHP_EOL;
      }
      if ($item['to'] !== FALSE)
      {
        $string .= '  <div id="'.$id_to.'_div" class="input-prepend" style="margin-bottom: 0;">' . PHP_EOL;
        $string .= '    <span class="add-on btn"><i data-time-icon="icon-time" data-date-icon="icon-calendar"></i> To</span>' . PHP_EOL;
        $string .= '    <input type="text" class="input-medium" data-format="yyyy-MM-dd hh:mm:ss" ';
        $string .= 'name="'.$id_to.'" id="'.$id_to.'" value="'.$item['to'].'"/>' . PHP_EOL;
        $string .= '  </div>' . PHP_EOL;
      }
      // JS SCRIPT
      $min = '-Infinity';
      $max = 'Infinity';
      $pattern = '/^(\d{4})-(\d{2})-(\d{2}) ([01][0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])$/';
      if (!empty($item['min']))
      {
        if (preg_match($pattern, $item['min'], $matches))
        {
          $matches[2] = $matches[2] - 1;
          array_shift($matches);
          $min = 'new Date(' . implode(',', $matches) . ')';
        }
        else if ($item['min'] == 'now' || $item['min'] == 'current')
        {
          $min = 'new Date()';
        }
      }
      if (!empty($item['max']))
      {
        if (preg_match($pattern, $item['max'], $matches))
        {
          $matches[2] = $matches[2] - 1;
          array_shift($matches);
          $max = 'new Date(' . implode(',', $matches) . ')';
        }
        else if ($item['max'] == 'now' || $item['max'] == 'current')
        {
          $max = 'new Date()';
        }
      }

      $script = '
      var startDate = '.$min.';
      var endDate   = '.$max.';
      $(document).ready(function() {
        $(\'#'.$id_from.'_div\').datetimepicker({
          //pickSeconds: false,
          weekStart: 1,
          startDate: startDate,
          endDate: endDate
        });';
      if ($item['disabled'])
      {
        $script .= '
        $(\'#'.$id_from.'_div\').datetimepicker(\'disable\');';
      }
      if ($item['to'] !== FALSE)
      {
        $script .= '
        $(\'#'.$id_to.'_div\').datetimepicker({
          //pickSeconds: false,
          weekStart: 1,
          startDate: startDate,
          endDate: endDate
        });';
      }
      $script .= '
      });' . PHP_EOL;

      if ($item['presets'])
      {
        $script .= '
      $(\'select#'.$item['id'].'_preset\').change(function() {
        var input_from = $(\'input#'.$id_from.'\');
        var input_to   = $(\'input#'.$id_to.'\');
        switch ($(this).val()) {' . PHP_EOL;
          foreach ($presets as $k => $v)
          {
            $preset = datetime_preset($k);
            $script .= "          case '$k':\n";
            $script .= "            input_from.val('".$preset['from']."');\n";
            $script .= "            input_to.val('".$preset['to']."');\n";
            $script .= "            break;\n";
          }
          $script .= '
          default:
            input_from.val("");
            input_to.val("");
            break;
        }
      });';
      }
      $GLOBALS['cache_html']['script'][] = $script;
      // End 'datetime'
      break;
    case 'multiselect':
      unset($item['icon']); // For now not used icons in multiselect
    case 'select':
      if (empty($item['values'])) { $item['values'] = array(0 => '[there is no data]'); }
      if ($item['type'] == 'multiselect')
      {
        $string .= '    <select multiple name="'.$item['id'].'[]" ' . $title;
      } else {
        $string .= '    <select name="'.$item['id'].'" ';
      }
      $string .= 'id="'.$item['id'].'" ';

      if      ($item['title'])       { $string .= 'title="' . $item['title'] . '" '; }
      else if (isset($item['name'])) { $string .= 'title="' . $item['name']  . '" '; }

      $data_width = ($item['width']) ? ' data-width="'.$item['width'].'"' : ' data-width="auto"';
      $data_size = (is_numeric($item['size'])) ? ' data-size="'.$item['size'].'"' : ' data-size="15"';
      $string .= 'class="selectpicker show-tick';
      if ($item['right']) { $string .= ' pull-right'; }
      $string .= '" data-selected-text-format="count>2"';
      if (count($item['values']) > 12 && $item['live-search'] !== FALSE) { $string .= ' data-live-search="true"'; }

      if ($item['disabled'])
      {
        $string .= ' disabled="1"';
      }
      else if ($item['readonly'])
      {
        $string .= ' disabled="1" readonly="1"'; // Bootstrap select not support readonly attribute, currently use disable
      }
      if ($item['onchange'])
      {
        $string .= ' onchange="'.$item['onchange'].'"';
      }

      $string .= $data_width . $data_size . '>' . PHP_EOL . '      '; // end <select>
      if (!is_array($item['value'])) { $item['value'] = array($item['value']); }
      foreach ($item['values'] as $k => $name)
      {
        $k = (string)$k;
        $value = ($item['encode'] ? var_encode($k) : $k); // Use base64+serialize encoding
        $subtext = ($item['subtext']) ? ' data-subtext="'.$k.'"' : '';
        $string .= '<option value="'.$value.'"';

        // Allow more complex values list (with icons for example)
        if (is_array($name))
        {
          $icon = $name['icon'];
          if (isset($name['subtext']))
          {
            $subtext = ' data-subtext="' . $name['subtext'] . '"';
          }
          if (isset($name['class']))
          {
            $string .= ' class="' . $name['class'] . '"';
          }
          else if (isset($name['color']))
          {
            $string .= ' data-content="<span style=\'color: ' . $name['color'] . '\'>' . $name['name'] . '</span>"';
          }
          $name = $name['name']; // Rewrite name to string
        }
        else if ($name == '[there is no data]')
        {
          $string .= ' disabled="1"';
        }
        $string .= $subtext;

        // Icons
        if ($icon)
        {
          // For each value
          $string .= ' data-icon="'.$icon.'"';
        }
        else if ($item['icon'] && $item['value'] === array(''))
        {
          // Only one main icon
          $string .= ' data-icon="'.$item['icon'].'"';
          unset($item['icon']);
        }

        if (in_array($k, $item['value']))
        {
          if (!($k === '' && $name === '')) // additionaly skip if value and name empty
          {
            if ($item['icon']) { $string .= ' data-icon="'.$item['icon'].'"'; }
            $string .= ' selected';
          }
        }

        if (!isset($name[0]) && $k !== '') { $name = $k; } // if name still empty set it as value
        $string .= '>'.escape_html($name).'</option> ';
      }
      $string .= PHP_EOL . '    </select>' . PHP_EOL;
      // End 'select' & 'multiselect'
      break;
    case 'submit':
      $button_type    = 'submit';
      $button_onclick = '';
      $button_class = ($item['right'] ? 'btn pull-right' : 'btn');
      if ($item['class'])
      {
        $button_class .= ' ' . $item['class'];
      }
      if ($item['form_id'] && $item['id'] == 'search')
      {
        // Note, used script form_to_path() stored in js/observium.js
        $button_type    = 'button';
        $button_onclick = " onclick=\"form_to_path('".$item['form_id']."');\"";
      }

      $button_disabled = $item['disabled'] || $item['readonly'];
      if ($button_disabled)
      {
        $button_class .= ' disabled';
      }

      $string .= '      <button id="' . $item['id'] . '" name="' . $item['id'] . '" type="'.$button_type.'" class="'.$button_class.'" style="line-height: 20px;"'.$button_onclick;
      if ($button_disabled)
      {
        $string .= ' disabled="1"';
      }

      if ($item['value'])
      {
        $string .= ' value="' . $item['value'] . '"';
      }
      $string .= '>';
      switch($item['id'])
      {
        // Note. 'update' - use POST request, all other - use GET with generate url from js.
        case 'update':
          $button_icon = 'icon-refresh';
          $button_name = 'Update';
          break;
        default:
          $button_icon = 'icon-search';
          $button_name = 'Search';
      }
      $nbsp = 0;
      if (array_key_exists('icon', $item)) { $button_icon = trim($item['icon']); }
      if (strlen($button_icon))
      {
        $string .= '<i class="'.$button_icon.'"></i>';
        $nbsp++;
      }

      if (array_key_exists('name', $item)) { $button_name = trim($item['name']); }
      if (strlen($button_name))
      {
        $nbsp++;
      }

      if ($nbsp == 2)
      {
        $string .= '&nbsp;';
      }
      $string .= $button_name.'</button>' . PHP_EOL;
      // End 'submit'
      break;
    case 'raw':
    case 'html':
      // Just add custom (raw) html element
      if (isset($item['html']))
      {
        $string .= $item['html'];
      } else {
        $string .= '<span';
        if (isset($item['class']))
        {
          $string .= ' class="' . $item['class'] . '"';
        }
        $string .= '>' . $item['value'] . '</span>';
      }
      break;
    case 'newline': // Deprecated
      $string .= '<div class="clearfix" id="'.$item['id'].'">';
      $string .= ($item['hr'] ? '<hr />' : '<hr style="border-width: 0px;" />');
      $string .= '</div>' . PHP_EOL;
      // End 'newline'
      break;
  }

  return($string);
}

// EOF
