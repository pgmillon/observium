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
    } else {
      $uri = $method.'://' . $device['hostname'];
    }

    if (isset($config['libvirt_socket']) && $config['libvirt_socket'] != "")
    {
      // Allow setting of socket, used to force use of read only socket
      // $config['libvirt_socket'] = "/var/run/libvirt/libvirt-sock-ro";

      $uri = $uri."?socket=".$config['libvirt_socket'];
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
        $vm_DisplayName = $dom_split[1];
        $vm_State = implode(' ',array_slice($dom_split,2)); // Convert split words back into one

        if (is_numeric($dom_id) || $dom_id == '-') // - when domain is not running
        {
          // Fetch the Virtual Machine information.
          unset($vm_info_array);
          exec($config['virsh'] . ' -c '.$uri.' dumpxml ' . $vm_DisplayName, $vm_info_array);

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

          // Parse XML, add xml header in front as this is required by the parser but not supplied by libvirt
          $xml = simplexml_load_string('<?xml version="1.0"?> ' . $vm_info_xml);
          if (OBS_DEBUG && $xml) { print_vars($xml); }

          // Call VM discovery
          discover_virtual_machine($valid, $device, array('id' => $xml->uuid, 'name' => $vm_DisplayName, 'cpucount' => $xml->vcpu,
                'memory' => $xml->currentMemory * 1024, 'status' => $vm_State, 'type' => 'libvirt', 'source' => 'libvirt'));

          // Save the discovered Virtual Machine.
          $libvirt_vmlist[] = $vm_DisplayName;
        }
      }
    }

    // If we found VMs, don't cycle the other protocols anymore.
    if (count($libvirt_vmlist)) { break; }
  }

  unset($libvirt_vmlist);

  // Clean up removed VMs (our type - libvirt - only, so we don't clean up other modules' VMs)
  check_valid_virtual_machines($device, $valid, 'libvirt');
  echo(PHP_EOL);
}

// EOF
