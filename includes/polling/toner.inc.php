<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage poller
 * @copyright  (C) 2006-2013 Adam Armstrong, (C) 2013-2016 Observium Limited
 *
 */

// FIXME could do with a rewrite (MIB/tables)

if ($config['enable_printers'])
{
  $toner_data = dbFetchRows("SELECT * FROM `toner` WHERE `device_id` = ?", array($device['device_id']));

  foreach ($toner_data as $toner)
  {
    echo("Checking toner " . $toner['toner_descr'] . "... ");

    $level = snmp_get($device, $toner['toner_oid'], "-OUqnv");
    if ($level == '-1')
    {
      // Unlimited
      $level = 100;
    }
    //else if ($level == '-3')
    //{
    //  $level = 1; // This is wrong SuppliesLevel (1%), but better than nothing
    //}
    if ($level >= 0)
    {
      $tonerperc = round($level / $toner['toner_capacity'] * 100);
    } else {
      $tonerperc = $level;
    }

    $tonerrrd = "toner-" . $toner['toner_index'] . ".rrd";

    rrdtool_create($device, $tonerrrd," \
      DS:toner:GAUGE:600:0:20000 ");

    echo($tonerperc . " %\n");

    rrdtool_update($device, $tonerrrd,"N:$tonerperc");

    if ($tonerperc > $toner['toner_current'])
    {
      log_event('Toner ' . $toner['toner_descr'] . ' was replaced (new level: ' . $tonerperc . '%)', $device, 'toner', $toner['toner_id']);
    }

    dbUpdate(array('toner_current' => $tonerperc, 'toner_capacity' => $toner['toner_capacity']), 'toner', '`toner_id` = ?', array($toner['toner_id']));

    check_entity('toner', $toner, array('toner_current' => $tonerperc));

    $graphs['toner'] = TRUE;
  }

  if ($device['type'] == 'printer')
  {
    $oid = get_dev_attrib($device, 'pagecount_oid');

    if ($oid)
    {
      echo("Checking page count... ");
      $pages = snmp_get($device, $oid, "-OUqnv");

      $pagecountrrd = "pagecount.rrd";

      rrdtool_create($device, $pagecountrrd," \
        DS:pagecount:GAUGE:600:0:U ");

      set_dev_attrib($device, "pagecounter", $pages);
      rrdtool_update($device, $pagecountrrd,"N:$pages");

      echo("$pages\n");
    }

    $oid = get_dev_attrib($device, 'imagingdrum_oid');

    if ($oid)
    {
      echo("Checking Imaging Drum... ");
      $capacity = snmp_get($device, get_dev_attrib($device, 'imagingdrum_cap_oid'), '-OUqnv');
      $level    = snmp_get($device, $oid, "-OUqnv");
      if ($level == '-1' || $capacity == '-1')
      {
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
        echo("$level%\n");
      }
      $drumrrd = "drum.rrd";

      rrdtool_create($device, $drumrrd," \
        DS:drum:GAUGE:600:0:100 ");

      set_dev_attrib($device, "drum", $level);
      rrdtool_update($device, $drumrrd,"N:$level");
    }

    $drums = array(
      'Cyan' => 'c',
      'Magenta' => 'm',
      'Yellow' => 'y',
      'Black' => 'k'
    );

    foreach ($drums as $drum => $letter)
    {
      $oid = get_dev_attrib($device, 'imagingdrum_' . $letter . '_oid');

      if ($oid)
      {
        echo("Checking $drum Imaging Drum... ");
        $capacity = snmp_get($device, get_dev_attrib($device, 'imagingdrum_' . $letter . '_cap_oid'), '-OUqnv');
        $level    = snmp_get($device, $oid, "-OUqnv");
        if ($level == '-1' || $capacity == '-1')
        {
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
          echo("$level%\n");
        }
        $drumrrd = "drum-" . $letter . ".rrd";

        rrdtool_create($device, $drumrrd, " \
          DS:drum:GAUGE:600:0:100 ");

        set_dev_attrib($device, "drum-" . $letter, $level);
        rrdtool_update($device, $drumrrd,"N:$level");
      }
    }

    $levels = array(
      'Waste Toner Box' => 'wastebox',
      'Fuser' => 'fuser',
      'Transfer roller' => 'transferroller',
    );

    foreach ($levels as $key => $value)
    {
      $oid = get_dev_attrib($device, $value.'_oid');

      if ($oid)
      {
        echo("Checking $key... ");
        $capacity = snmp_get($device, get_dev_attrib($device, $value.'_cap_oid'), '-OUqnv');
        $level    = snmp_get($device, $oid, "-OUqnv");
        if ($level == '-1' || $capacity == '-1')
        {
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
          echo("$level%\n");
        }
        $rrd = "$value.rrd";
        rrdtool_create($device, $rrd," \
          DS:level:GAUGE:600:0:100 ");

        set_dev_attrib($device, $value, $level);
        rrdtool_update($device, $rrd,"N:$level");
      }
    }
  }
}

// EOF
