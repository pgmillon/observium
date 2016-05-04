<?php
/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package        observium
 * @subpackage     functions
 * @copyright  (C) 2006-2015 Adam Armstrong
 *
 */

function generate_entity_popup_graphs($entity, $entity_type)
{

  global $config;

  if (is_array($config['entities'][$entity_type]['graph']))
  {

    if(isset($config['entities'][$entity_type]['graph']['type']))
    {
      $graphs[] = $config['entities'][$entity_type]['graph'];
    } else {
      $graphs = $config['entities'][$entity_type]['graph'];
    }

    foreach ($graphs as $graph_array)
    {
      $graph_array = $config['entities'][$entity_type]['graph'];
      // We can draw a graph for this type/metric pair!

      foreach ($graph_array as $key => $val)
      {
        // Check to see if we need to do any substitution
        if (substr($val, 0, 1) == "@")
        {
          $nval = substr($val, 1);
          $graph_array[$key] = $entity[$nval];
        }
      }
      $graph_array['height'] = "100";
      $graph_array['width'] = "323";

      $content .= "<h4>" . ucwords(str_replace("_", " ", $graph_array['type'])) . "</h4>";

      foreach (array('day', 'month') as $period)
      {
        $graph_array['from'] = $config['time'][$period];
        $content .= generate_graph_tag($graph_array);
      }
    }

    return $content;
  }
}

function generate_entity_popup_header($entity, $entity_type)
{

  $translate = entity_type_translate_array($entity_type);

  $contents = '
      <table style="margin-top: 10px; margin-bottom: 10px;" class="table table-striped table-bordered table-rounded table-condensed">
        <tr class="' . $entity['row_class'] . '" style="font-size: 10pt;">
          <td class="state-marker"></td>
          <td style="width: 10px;"></td>
          <td width="400"><i class="'.$translate['icon'].'" style="margin-right: 10px;"></i> <a class="entity" style="font-size: 15px; font-weight: bold;">'.$entity[$translate['name_field']].'</a></td>
          <td width="100"></td>
          <td></td>
        </tr>
          </table>';

  return $contents;

}

function generate_entity_popup($entity, $entity_type)
{

  global $config;

  if(is_numeric($entity)) { $entity = get_entity_by_id_cache($entity, $entity_type); }
  $device = device_by_id_cache($entity['device_id']);

  switch($entity_type)
  {
    default:
      $content  = generate_device_popup_header($device);
      $content .= generate_entity_popup_header($entity, $entity_type);
      $content .= generate_entity_popup_graphs($entity, $entity_type);
  }

  return $content;

}