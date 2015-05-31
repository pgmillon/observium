<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage web
 * @copyright  (C) 2006-2014 Adam Armstrong
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
 *                    'json'    => FALSE,             // (Optional) Use base64+json for values, use when values contains commas or empty string
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
    $string_items .= get_form_element($item);
  }

  $form_id = 'search-'.strgen('4');

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
  if ($url && $button != 'update')
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

// For now basically similar to print_search(), exept using bootstrap grid system
function print_form($data)
{
  $form_id    = 'form-'.strgen();
  $form_class = ($data['type'] == 'rows' ? 'form-inline' : 'form');
  $base_class = ($data['class'] ? $data['class'] : 'well');
  $base_space = '5px';
  $used_vars  = array();

  // Form elements
  if ($data['type'] == 'rows')
  {
    $row_style = '';
    $string_elements = '';
    foreach ($data['row'] as $k => $row)
    {
      $string_elements .= '  <div class="row" '.$row_style.'> <!-- START row-'.$k.' -->' . PHP_EOL;
      foreach ($row as $id => $element)
      {
        $used_vars[]      = $id;
        $element['id']    = $id;
        $element['class'] = 'col-lg-2';
        if ($element['right'])
        {
          $element['class'] .= ' pull-right';
        }
        if ($id == 'search' && $data['url'])
        {
          // Add form_id here, for generate onclick action in submit button
          $element['form_id'] = $form_id;
        }
        $string_elements .= '    <div class="'.$element['class'].'">' . PHP_EOL;
        $string_elements .= get_form_element($element);
        $string_elements .= '    </div>' . PHP_EOL;
      }
      $string_elements .= '  </div> <!-- END row-'.$k.' -->' . PHP_EOL;
      $row_style = 'style="margin-top: '.$base_space.';"'; // Add space between rows
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
  $string .= '<div class="'.$base_class.'" style="padding: '.$base_space.';">' . PHP_EOL;
  $string .= '<form method="POST" id="'.$form_id.'" action="'.$data['url'].'" class="'.$form_class.'" style="margin-bottom:0;">' . PHP_EOL;
  if ($data['brand']) { $string .= '  <a class="brand">' . $data['brand'] . '</a>' . PHP_EOL; }

  // Form elements
  $string .= $string_elements;

  // Form footer
  $string .= '</form>' . PHP_EOL;
  $string .= '</div>' . PHP_EOL;
  $string .= "<!-- END $form_id -->" . PHP_EOL;

  // Print form
  echo($string);
}

// Generates form elements. The main use for print_search(), see examples of that function.
// DOCME needs phpdoc block
function get_form_element($item, $type = '')
{
  if (!isset($item['value'])) { $item['value'] = ''; }
  if (!isset($item['type']))  { $item['type'] = $type; }
  $string = '';
  switch($item['type'])
  {
    case 'text':
    case 'input':
      if ($item['placeholder'])
      {
        $string .= PHP_EOL;
        $string .= '    <input type="'.$item['type'].'" placeholder="'.$item['name'].'" ';
      } else {
        $string .= '  <div class="input-prepend">' . PHP_EOL;
        if (!$item['name']) { $item['name'] = '<i class="icon-list"></i>'; }
        $string .= '    <span class="add-on">'.$item['name'].'</span>' . PHP_EOL;
        $string .= '    <input type="'.$item['type'].'" ';
      }
      $string .= (isset($item['width'])) ? 'style="width:'.$item['width'].'" ' : '';
      $string .= 'name="'.$item['id'].'" id="'.$item['id'].'" class="input" value="'.$item['value'].'"/>' . PHP_EOL;
      $string .= ($item['placeholder'] ? PHP_EOL : '  </div>' . PHP_EOL);
      // End 'text' & 'input'
      break;
    case 'datetime':
      $id_from = $item['id'].'_from';
      $id_to = $item['id'].'_to';
      // Presets
      if ($item['from'] === FALSE || $item['to'] === FALSE) { $item['presets'] = FALSE; }
      if ($item['presets'])
      {
        $presets = array('sixhours'  => 'Last 6 hours',
                         'today'     => 'Today',
                         'yesterday' => 'Yesterday',
                         'tweek'     => 'This week',
                         'lweek'     => 'Last week',
                         'tmonth'    => 'This month',
                         'lmonth'    => 'Last month',
                         'tyear'     => 'This year',
                         'lyear'     => 'Last year');
        $string .= '    <select id="'.$item['id'].'" class="selectpicker show-tick" data-size="false" data-width="auto">' . PHP_EOL . '      ';
        $string .= '<option value="" selected>Date/Time presets</option>';
        foreach ($presets as $k => $v)
        {
          $string .= '<option value="'.$k.'">'.$v.'</option> ';
        }
        $string .= PHP_EOL . '    </select>' . PHP_EOL;
      }
      // Date/Time input fields
      if ($item['from'] !== FALSE)
      {
        $string .= '  <div class="input-prepend" id="'.$id_from.'">' . PHP_EOL;
        $string .= '    <span class="add-on btn"><i data-time-icon="icon-time" data-date-icon="icon-calendar"></i> From</span>' . PHP_EOL;
        $string .= '    <input type="text" class="input-medium" data-format="yyyy-MM-dd hh:mm:ss" ';
        $string .= 'name="'.$id_from.'" id="'.$id_from.'" value="'.$item['from'].'"/>' . PHP_EOL;
        $string .= '  </div>' . PHP_EOL;
      }
      if ($item['to'] !== FALSE)
      {
        $string .= '  <div class="input-prepend" id="'.$id_to.'">' . PHP_EOL;
        $string .= '    <span class="add-on btn"><i data-time-icon="icon-time" data-date-icon="icon-calendar"></i> To</span>' . PHP_EOL;
        $string .= '    <input type="text" class="input-medium" data-format="yyyy-MM-dd hh:mm:ss" ';
        $string .= 'name="'.$id_to.'" id="'.$id_to.'" value="'.$item['to'].'"/>' . PHP_EOL;
        $string .= '  </div>' . PHP_EOL;
      }
      // JS
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
      }
      if (!empty($item['max']))
      {
        if (preg_match($pattern, $item['max'], $matches))
        {
          $matches[2] = $matches[2] - 1;
          array_shift($matches);
          $max = 'new Date(' . implode(',', $matches) . ')';
        }
      }
      $string .= '
    <script type="text/javascript">
      var startDate = '.$min.';
      var endDate   = '.$max.';
      $(document).ready(function() {
        $(\'#'.$id_from.'\').datetimepicker({
          //pickSeconds: false,
          weekStart: 1,
          startDate: startDate,
          endDate: endDate
        });
        $(\'#'.$id_to.'\').datetimepicker({
          //pickSeconds: false,
          weekStart: 1,
          startDate: startDate,
          endDate: endDate
        });
      });' . PHP_EOL;
      if ($item['presets'])
      {
        $string .= '
      $(\'select#'.$item['id'].'\').change(function() {
        var input_from = $(\'input#'.$id_from.'\');
        var input_to   = $(\'input#'.$id_to.'\');
        switch ($(this).val()) {' . PHP_EOL;
          foreach ($presets as $k => $v)
          {
            $preset = datetime_preset($k);
            $string .= "          case '$k':\n";
            $string .= "            input_from.val('".$preset['from']."');\n";
            $string .= "            input_to.val('".$preset['to']."');\n";
            $string .= "            break;\n";
          }
          $string .= '
          default:
            input_from.val("");
            input_to.val("");
            break;
        }
      });' . PHP_EOL;
      }
      $string .= '</script>' . PHP_EOL;
      // End 'datetime'
      break;
    case 'multiselect':
      unset($item['icon']); // For now not used icons in multiselect
    case 'select':
      if (empty($item['values'])) { $item['values'] = array(0 => '[there is no data]'); }
      if ($item['type'] == 'multiselect')
      {
        $title = (isset($item['name'])) ? 'title="'.$item['name'].'" ' : '';
        $string .= '    <select multiple name="'.$item['id'].'[]" ' . $title;
      } else {
        $string .= '    <select name="'.$item['id'].'" ';
        if ($item['name'] && !isset($item['values']['']))
        {
          $item['values'] = array('' => $item['name']) + $item['values'];
        }
      }
      $string .= 'id="'.$item['id'].'" ';
      $data_width = ($item['width']) ? ' data-width="'.$item['width'].'"' : ' data-width="auto"';
      $data_size = (is_numeric($item['size'])) ? ' data-size="'.$item['size'].'"' : ' data-size="15"';
      $string .= 'class="selectpicker show-tick';
      if ($item['right']) { $string .= ' pull-right'; }
      $string .= '" data-selected-text-format="count>2"';
      $string .= $data_width . $data_size . '>' . PHP_EOL . '      ';
      if (!is_array($item['value'])) { $item['value'] = array($item['value']); }
      foreach ($item['values'] as $k => $name)
      {
        $k = (string)$k;
        $value = ($item['json'] ? base64_encode(json_encode(array($k))) : $k); // Use base64+json encoding
        $subtext = ($item['subtext']) ? ' data-subtext="('.$k.')"' : '';
        $string .= '<option value="'.$value.'"' . $subtext;
        if ($name == '[there is no data]') { $string .= ' disabled'; }
        if ($item['icon'] && $item['value'] === array(''))
        {
          $string .= ' data-icon="'.$item['icon'].'"';
          unset($item['icon']);
        }
        if ($k !== '' && in_array($k, $item['value']))
        {
          if ($item['icon']) { $string .= ' data-icon="'.$item['icon'].'"'; }
          $string .= ' selected';
        }

        $string .= '>'.$name.'</option> ';
      }
      $string .= PHP_EOL . '    </select>' . PHP_EOL;
      // End 'select' & 'multiselect'
      break;
    case 'submit':
      $button_type    = 'submit';
      $button_onclick = '';
      $button_class = ($item['right'] ? 'btn pull-right' : 'btn');
      if ($item['form_id'] && $item['id'] == 'search')
      {
        // Note, used script form_to_path() stored in js/observium.js
        $button_type    = 'button';
        $button_onclick = " onclick=\"form_to_path('".$item['form_id']."');\"";
      }

      $string .= '      <button type="'.$button_type.'" class="'.$button_class.'" style="line-height: 20px;"'.$button_onclick.'>';
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
      if ($item['icon']) { $button_icon = $item['icon']; }
      if ($item['name']) { $button_name = $item['name']; }
      $string .= '<i class="'.$button_icon.'"></i> '.$button_name.'</button>' . PHP_EOL;
      // End 'submit'
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
