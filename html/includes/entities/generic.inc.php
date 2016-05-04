<?php
/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package        observium
 * @subpackage     functions
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

function get_customoid_by_id($oid_id)
{

  if (is_numeric($oid_id))
  {
    $oid = dbFetchRow('SELECT * FROM `oids` WHERE `oid_id` = ?', array($oid_id));
  }
  if (count($oid))
  {
    return $oid;
  } else {
    return FALSE;
  }

} // end function get_customoid_by_id()


function generate_entity_popup_graphs($entity, $vars)
{
  global $config;

  $entity_type = $vars['entity_type'];

  if (is_array($config['entities'][$entity_type]['graph']))
  {
    if (isset($config['entities'][$entity_type]['graph']['type']))
    {
      $graphs[] = $config['entities'][$entity_type]['graph'];
    } else {
      $graphs = $config['entities'][$entity_type]['graph'];
    }

    foreach($graphs as $graph_array)
    {
      //$graph_array = $config['entities'][$entity_type]['graph'];
      // We can draw a graph for this type/metric pair!

      foreach($graph_array as $key => $val)
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

      $content = '<div style="white-space: nowrap;">';
      $content .= "<div class=entity-title><h4>" . nicecase(str_replace("_", " ", $graph_array['type'])) . "</h4></div>";
      /*
      $content = generate_box_open(array('title' => nicecase(str_replace("_", " ", $graph_array['type'])),
                                         'body-style' => 'white-space: nowrap;'));
      */
      foreach(array('day', 'month') as $period)
      {
        $graph_array['from'] = $config['time'][$period];
        $content .= generate_graph_tag($graph_array);
      }
      $content .= "</div>";
      //$content .= generate_box_close();
    }

    //r($content);
    return $content;
  }
}

function generate_entity_popup_header($entity, $vars)
{
  $translate = entity_type_translate_array($vars['entity_type']);

  $vars['popup']       = TRUE;
  $vars['entity_icon'] = TRUE;

  switch($vars['entity_type'])
  {
    case "sensor":

      $contents .= generate_box_open();
      $contents .= '<table class="'. OBS_CLASS_TABLE .'">';
      $contents .= generate_sensor_row($entity, $vars);
      $contents .= '</table>';
      $contents .= generate_box_close();

      break;

    case "toner":

      $contents .= generate_box_open();
      $contents .= '<table class="'. OBS_CLASS_TABLE .'">';
      $contents .= generate_toner_row($entity, $vars);
      $contents .= '</table>';
      $contents .= generate_box_close();

      break;

    case "bgp_peer":
      if ($entity['peer_device_id'])
      {
        $peer_dev = device_by_id_cache($entity['peer_device_id']);
        $peer_name = '<br /><a class="entity" style="font-weight: bold;">'.$peer_dev['hostname'].'</a>';
      }
      else if ($entity['reverse_dns'])
      {
        $peer_name = '<br /><span style="font-weight: bold;">' . $entity['reverse_dns'] . '</span>';
      }
      $astext = '<span>AS'.$entity['bgpPeerRemoteAs'];
      if ($entity['astext'])
      {
        $astext .= '<br />' . $entity['astext'] . '</span>';
      }
      $astext .= '</span>';
      $contents .= generate_box_open();
      $contents .= '
      <table class="'. OBS_CLASS_TABLE .'">
        <tr class="' . $entity['row_class'] . '" style="font-size: 10pt;">
          <td class="state-marker"></td>
          <td style="width: 10px;"></td>
          <td style="width: 10px; vertical-align: middle;"><i class="'.$translate['icon'].'"></i></td>
          <td style="vertical-align: middle;"><a class="entity-popup" style="font-size: 15px; font-weight: bold;">'.$entity['entity_shortname'].'</a>'.$peer_name.'</td>
          <td style="width: 20%; white-space: nowrap;">'.$astext.'</td>
          <td></td>
        </tr>
      </table>';
      $contents .= generate_box_close();
      break;

    case "sla":

      $contents .= generate_box_open();
      $contents .= '<table class="'. OBS_CLASS_TABLE .'">';
      $contents .= generate_sla_row($entity, $vars);
      $contents .= '</table>';
      $contents .= generate_box_close();

      break;

    case "processor":

      $contents .= generate_box_open();
      $contents .= '<table class="'. OBS_CLASS_TABLE .'">';
      $contents .= generate_processor_row($entity, $vars);
      $contents .= '</table>';
      $contents .= generate_box_close();

      break;

    case "mempool":

      $contents .= generate_box_open();
      $contents .= '<table class="'. OBS_CLASS_TABLE .'">';
      $contents .= generate_mempool_row($entity, $vars);
      $contents .= '</table>';
      $contents .= generate_box_close();

      break;

    case "p2pradio":

      $contents .= generate_box_open();
      $contents .= '<table class="'. OBS_CLASS_TABLE .'">';
      $contents .= generate_p2pradio_row($entity, $vars);
      $contents .= '</table>';
      $contents .= generate_box_close();

      break;

    case "status":

      $contents .= generate_box_open();
      $contents .= '<table class="'. OBS_CLASS_TABLE .'">';
      $contents .= generate_status_row($entity, $vars);
      $contents .= '</table>';
      $contents .= generate_box_close();

      break;

    case "storage":

      $contents .= generate_box_open();
      $contents .= '<table class="'. OBS_CLASS_TABLE .'">';
      $contents .= generate_storage_row($entity, $vars);
      $contents .= '</table>';
      $contents .= generate_box_close();

      break;

    default:
      entity_rewrite($vars['entity_type'], $entity);
      $contents = generate_box_open(). '
      <table class="' . OBS_CLASS_TABLE_STRIPED . '">
        <tr class="' . $entity['row_class'] . '" style="font-size: 10pt;">
          <td class="state-marker"></td>
          <td style="width: 10px;"></td>
          <td width="400"><i class="'.$translate['icon'].'" style="margin-right: 10px;"></i> <a class="entity-popup" style="font-size: 15px; font-weight: bold;">'.$entity['entity_name'].'</a></td>
          <td width="100"></td>
          <td></td>
        </tr>
      </table>'.generate_box_close();
  }

  return $contents;
}

function generate_entity_popup($entity, $vars)
{
  if (is_numeric($entity)) { $entity = get_entity_by_id_cache($entity, $vars['entity_type']); }
  $device = device_by_id_cache($entity['device_id']);

  $content  = generate_device_popup_header($device);
  $content .= generate_entity_popup_header($entity, $vars);
  $content .= generate_entity_popup_graphs($entity, $vars);

  return $content;
}

// EOF
