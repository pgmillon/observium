ALTER TABLE `vminfo` ADD  `vm_source` VARCHAR(32) NULL DEFAULT NULL
ALTER TABLE `vminfo` DROP `vmwVmVMID`
ALTER TABLE `vminfo` DROP `vmwVmConfigFile`
ALTER TABLE `vminfo` DROP `vmwVmGuestState`
ALTER TABLE `vminfo` CHANGE `vmwVmDisplayName` `vm_name` VARCHAR(128)
ALTER TABLE `vminfo` CHANGE `vmwVmCpus` `vm_cpucount` INT(11)
ALTER TABLE `vminfo` CHANGE `vmwVmMemSize` `vm_memory` INT(11)
ALTER TABLE `vminfo` CHANGE `vmwVmState` `vm_state` VARCHAR(128)
ALTER TABLE `vminfo` CHANGE `vmwVmGuestOS` `vm_guestos` VARCHAR(128)
ALTER TABLE `vminfo` CHANGE `vmwVmUUID` `vm_uuid` VARCHAR(64)
