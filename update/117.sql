UPDATE `devices_attribs` SET `attrib_type`='discover_inventory' WHERE `attrib_type`='discover_entity-physical';
DELETE FROM `devices_attribs` WHERE `attrib_type`='discover_hr-device';
