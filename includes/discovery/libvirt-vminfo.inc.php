<?php

/**
 * Observium
 *
 *   This file is part of Observium.
 *
 * @package    observium
 * @subpackage discovery
 * @copyright  (C) 2006-2014 Adam Armstrong
 *
 */

// FIXME should do the deletion etc in a common file perhaps? like for the sensors

// Try to discover Libvirt Virtual Machines.

if ($config['enable_libvirt'] == '1' && $device['os'] == "linux" )
{
  $libvirt_vmlist = array();

  echo("Libvirt VM: ");

  $ssh_ok = 0;

  foreach ($config['libvirt_protocols'] as $method)
  {
    if (strstr($method,'qemu'))
    {
      $uri = $method.'://' . $device['hostname'] . '/system';
    }
    else
    {
      $uri = $method.'://' . $device['hostname'];
    }

    if (strstr($method,'ssh') && !$ssh_ok)
    {
      // Check if we are using SSH if we can log in without password - without blocking the discovery
      // Also automatically add the host key so discovery doesn't block on the yes/no question, and run echo so we don't get stuck in a remote shell ;-)
      exec('ssh -p '.$device['ssh_port'].' -o "StrictHostKeyChecking no" -o "PreferredAuthentications publickey" -o "IdentitiesOnly yes" ' . $device['hostname'] . ' echo -e', $out, $ret);
      if ($ret != 255) { $ssh_ok = 1; }
    }

    if ($ssh_ok || !strstr($method,'ssh'))
    {
      // Fetch virtual machine list
      unset($domlist);
      exec($config['virsh'] . ' -c '.$uri.' list --all',$domlist);

      foreach ($domlist as $dom)
      {
        $dom_split = str_word_count($dom, 1, '1234567890-.');

        $dom_id = $dom_split[0];
        $vmwVmDisplayName = $dom_split[1];
        $vmwVmState = implode(' ',array_slice($dom_split,2)); // Convert split words back into one

        if (is_numeric($dom_id) || $dom_id == '-')
        {
          // Fetch the Virtual Machine information.
          unset($vm_info_array);
          exec($config['virsh'] . ' -c '.$uri.' dumpxml ' . $vmwVmDisplayName, $vm_info_array);

          // <domain type='kvm' id='3'>
          //   <name>moo.example.com</name>
          //   <uuid>48cf6378-6fd5-4610-0611-63dd4b31cfd6</uuid>
          //   <memory>1048576</memory>
          //   <currentMemory>1048576</currentMemory>
          //   <vcpu>8</vcpu>
          //   <os>
          //     <type arch='x86_64' machine='pc-0.12'>hvm</type>
          //     <boot dev='hd'/>
          //   </os>
          //   <features>
          //     <acpi/>
          //   (...)

          // Convert array to string
          $vm_info_xml = implode('', $vm_info_array);

          $xml = simplexml_load_string('<?xml version="1.0"?> ' . $vm_info_xml);
          if ($debug) { print_vars($xml); }

          $vmwVmGuestOS   = ''; // libvirt does not supply this
          $vmwVmMemSize   = $xml->currentMemory / 1024;

          $vmwVmState = ucfirst($vmwVmState);

          $vmwVmCpus = $xml->vcpu;

          // Check whether the Virtual Machine is already known for this host.
          if (dbFetchCell("SELECT COUNT(`id`) FROM `vminfo` WHERE `device_id` = ? AND `vmwVmDisplayName` = ? AND `vm_type` = 'libvirt'", array($device['device_id'], $vmwVmDisplayName)) == '0')
          {
            $libvirt_insert = array('device_id' => $device['device_id'], 'vm_type' => 'libvirt', 'vmwVmVMID' => $dom_id, 'vmwVmDisplayName' => $vmwVmDisplayName, 'vmwVmGuestOS' => $vmwVmGuestOS, 'vmwVmMemSize' => $vmwVmMemSize, 'vmwVmCpus' => $vmwVmCpus, 'vmwVmState' => $vmwVmState);
            $inserted = dbInsert($libvirt_insert, 'vminfo');
            echo("+");
            log_event("Virtual Machine added: $vmwVmDisplayName ($vmwVmMemSize MB)", $device, 'vm', $inserted);
            if (is_valid_hostname($vmwVmDisplayName) && $vmwVmState == 'Running') { discover_new_device($vmwVmDisplayName, 'libvirt'); }
          } else {
            $libvirt = dbFetchRow("SELECT * FROM `vminfo` WHERE `device_id` = ? AND `vmwVmVMID` = ? AND `vm_type` = 'libvirt'", array($device['device_id'], $dom_id));
            if ($libvirt['vmwVmState'] != $vmwVmState || $libvirt['vmwVmDisplayName'] != $vmwVmDisplayName || $libvirt['vmwVmCpus'] != $vmwVmCpus || $libvirt['vmwVmGuestOS'] != $vmwVmGuestOS || $libvirt['vmwVmMemSize'] != $vmwVmMemSize)
            {
              $update = array('vmwVmState' => $vmwVmState, 'vmwVmGuestOS' => $vmwVmGuestOS, 'vmwVmDisplayName' => $vmwVmDisplayName,'vmwVmMemSize' => $vmwVmMemSize,'vmwVmCpus' => $vmwVmCpus);
              dbUpdate($update, 'vminfo', "device_id = ? AND vm_type = 'libvirt' AND vmwVmVMID = ?", array($device['device_id'], $dom_id));
              echo("U");
              /// FIXME eventlog changed fields
            }
            else
            {
              echo(".");
            }
          }

          // Save the discovered Virtual Machine.
          $libvirt_vmlist[] = $vmwVmDisplayName;
        }
      }
    }

    // If we found VMs, don't cycle the other protocols anymore.
    if (count($libvirt_vmlist)) { break; }
  }

  // Get a list of all the known Virtual Machines for this host.
  foreach (dbFetchRows("SELECT id, vmwVmVMID, vmwVmDisplayName FROM `vminfo` WHERE `device_id` = ? AND `vm_type` = 'libvirt'", array($device['device_id'])) as $db_vm)
  {
    // Delete the Virtual Machines that are removed from the host.

    if (!in_array($db_vm['vmwVmDisplayName'], $libvirt_vmlist))
    {
      dbDelete('vminfo', '`id` = ?', array($db_vm['id']));
      echo("-");
      log_event("Virtual Machine removed: " . $db_vm['vmwVmDisplayName'], $device, 'vm', $db_vm['id']);
    }
  }

  echo(PHP_EOL);
}

// EOF
