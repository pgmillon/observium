<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage discovery
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

// FIXME could do with a rewrite (MIB/tables)

if ($config['enable_printers'] && $device['os_group'] == 'printer')
{
  $valid_toner = array();

  echo("Toner: ");

  $oids = trim(snmp_walk($device, "1.3.6.1.2.1.43.11.1.1.3", "-OsqnU"));
  /* Not parsed below, so let's not walk this for now*/
  /*
  if (!$oids)
  {
    $oids = trim(snmp_walk($device, "1.3.6.1.2.1.43.12.1.1.2.1", "-OsqnU"));
  }
  */

  if ($oids) echo("Jetdirect ");
  foreach (explode("\n", $oids) as $data)
  {
    $data = trim($data);
    if ($data)
    {
      list($oid,$role) = explode(" ", $data);
      $split_oid = explode('.',$oid);
      $index = $split_oid[count($split_oid)-1];
      if (is_numeric($role))
      {
        $class_oid    = ".1.3.6.1.2.1.43.11.1.1.4.1.$index"; // prtMarkerSuppliesClass (supplyThatIsConsumed(3), receptacleThatIsFilled(4))
        $type_oid     = ".1.3.6.1.2.1.43.11.1.1.5.1.$index"; // prtMarkerSuppliesType
        $descr_oid    = ".1.3.6.1.2.1.43.11.1.1.6.1.$index"; // prtMarkerSuppliesDescription
        $capacity_oid = ".1.3.6.1.2.1.43.11.1.1.8.1.$index"; // prtMarkerSuppliesMaxCapacity
        $toner_oid    = ".1.3.6.1.2.1.43.11.1.1.9.1.$index"; // prtMarkerSuppliesLevel

        $resourcetype = snmp_get($device, $type_oid, "-Oqv");

        $descr        = snmp_hexstring(trim(str_replace("\n","",str_replace('"','',snmp_get($device, $descr_oid, "-Oqv")))));

        if ($descr != "")
        {
          switch ($resourcetype)
          {
            case 3: // Toner
            case 5: // Ink or particulate
            case 6: // InkCardridge 
            case 21: // Toner Cardridge
              /* .1.3.6.1.2.1.43.11.1.1.8 prtMarkerSuppliesMaxCapacity
               *  The value (-1) means other and specifically indicates that the sub-unit places
               *  no restrictions on this parameter. The value (-2) means unknown.
               *
               * .1.3.6.1.2.1.43.11.1.1.9 prtMarkerSuppliesLevel
               *  The value (-1) means other and specifically indicates that the sub-unit places
               *  no restrictions on this parameter. The value (-2) means unknown.
               *  A value of (-3) means that the printer knows that there is some supply/remaining space,
               *  respectively.
               */
              $level    = snmp_get($device, $toner_oid, "-Oqv");
              $capacity = snmp_get($device, $capacity_oid, "-Oqv");
              if ($level == '-1' || $capacity == '-1')
              {
                // Unlimited
                $level    = 100;
                $capacity = 100;
              }
              //else if ($level == '-3' || $level == '-2')
              //{
              //  $level    = 1; // This is wrong SuppliesLevel (1%), but better than nothing
              //  $capacity = ($capacity > 0 ? $capacity : 100);
              //}
              if ($capacity > 0 && $level >= 0)
              {
                $level = round($level / $capacity * 100);
              }
              $type  = "jetdirect";
              discover_toner($valid_toner, $device, $toner_oid, $index, $type, $descr, $capacity_oid, $capacity, $level);
            break;
            case 9:
            case 20:
            case 15:
              if (stristr($descr, 'drum') !== FALSE)
              {
                if (stristr($descr, 'cyan') !== FALSE)
                {
                  echo(" ImagingDrumC");
                  set_dev_attrib($device, "imagingdrum_c_oid", $toner_oid);
                  set_dev_attrib($device, "imagingdrum_c_cap_oid", $capacity_oid);
                }
                elseif (stristr($descr, 'magenta') !== FALSE)
                {
                  echo(" ImagingDrumM");
                  set_dev_attrib($device, "imagingdrum_m_oid", $toner_oid);
                  set_dev_attrib($device, "imagingdrum_m_cap_oid", $capacity_oid);
                }
                elseif (stristr($descr, 'yellow') !== FALSE)
                {
                  echo(" ImagingDrumY");
                  set_dev_attrib($device, "imagingdrum_y_oid", $toner_oid);
                  set_dev_attrib($device, "imagingdrum_y_cap_oid", $capacity_oid);
                }
                elseif (stristr($descr, 'black') !== FALSE)
                {
                  echo(" ImagingDrumK");
                  set_dev_attrib($device, "imagingdrum_k_oid", $toner_oid);
                  set_dev_attrib($device, "imagingdrum_k_cap_oid", $capacity_oid);
                }
                else
                {
                  echo(" ImagingDrum");
                  set_dev_attrib($device, "imagingdrum_oid", $toner_oid);
                  set_dev_attrib($device, "imagingdrum_cap_oid", $capacity_oid);
                }
              }
              elseif (stristr($descr, 'fuser') !== FALSE)
              {
                echo(" Fuser");
                set_dev_attrib($device, "fuser_oid", $toner_oid);
                set_dev_attrib($device, "fuser_cap_oid", $capacity_oid);
                }
              elseif (stristr($descr, 'transfer') !== FALSE)
              {
                echo(" TransferRoller");
                set_dev_attrib($device, "transferroller_oid", $toner_oid);
                set_dev_attrib($device, "transferroller_cap_oid", $capacity_oid);
              }
            break;
            case 4: // Waste Toner
            case 8: // Waste Ink or toner
            case 24: // Waste liquid or Ink
              if (stristr($descr, 'waste') !== FALSE || stristr($descr, 'absorber') == FALSE || $classtype == 3)
              {
                echo(" WasteTonerBox");
                set_dev_attrib($device, "wastebox_oid", $toner_oid);
              }
            break;
          }
        }
      }
    }
  }

  echo(" ");

  // Delete removed toners
  if (OBS_DEBUG && count($valid_toner)) { print_vars($valid_toner); }

  foreach (dbFetchRows("SELECT * FROM `toner` WHERE `device_id` = ?", array($device['device_id'])) as $test_toner)
  {
  $toner_index = $test_toner['toner_index'];
  $toner_type = $test_toner['toner_type'];
  if (!$valid_toner[$toner_type][$toner_index])
  {
    echo("-");
    dbDelete('toner', '`toner_id` = ?', array($test_toner['toner_id']));
    log_event("Toner removed: type $toner_type index $toner_index descr ". $test_toner['toner_descr'], $device, 'toner', $test_toner['toner_id']);
  }
  }

  unset($valid_toner);

  // Discover other counters and monitored values
  $pagecounters = array("1.3.6.1.2.1.43.10.2.1.4.1.1");

  foreach ($pagecounters as $oid)
  {
    if (snmp_get($device, $oid, "-OUqnv"))
    {
      echo(" Pagecounter");
      set_dev_attrib($device, "pagecount_oid", $oid);
      break;
    }
  }

  echo(PHP_EOL);
} # if ($config['enable_printers'] && $device['os_group'] == 'printers')

// EOF
