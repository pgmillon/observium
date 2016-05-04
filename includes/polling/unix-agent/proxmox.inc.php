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

$qemu = json_decode($agent_data['proxmox']['qemu']);
unset($agent_data['proxmox']['qemu']);

echo("Proxmox VE QEMU: ");

foreach ($qemu as $vm)
{
  // [cpu] => 0
  // [cpus] => 2
  // [disk] => 0
  // [diskread] => 0
  // [diskwrite] => 0
  // [maxdisk] => 53687091200
  // [maxmem] => 1610612736
  // [mem] => 1158195243
  // [name] => testvm.example.com
  // [netin] => 32192265743
  // [netout] => 1538266557
  // [pid] => 760125
  // [status] => running
  // [template] =>
  // [uptime] => 1135198
  // [vmid] => 100

  discover_virtual_machine($valid['vm'], $device, array('id' => $vm->vmid, 'name' => $vm->name, 'cpucount' => $vm->cpus,
    'memory' => $vm->maxmem, 'status' => $vm->status, 'type' => 'proxmox', 'source' => 'agent'));
}

echo(PHP_EOL);

unset($qemu);

// EOF
