ALTER TABLE `munin_plugins` ADD UNIQUE `dev_mplug` (  `device_id` ,  `mplug_type` );
ALTER TABLE `applications` ADD UNIQUE `dev_type_inst` (  `device_id` ,  `app_type` ,  `app_instance` );
