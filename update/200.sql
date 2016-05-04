ALTER TABLE  `ports` ADD  `port_label_base` VARCHAR( 128 ) NOT NULL AFTER  `port_label` ;
ALTER TABLE  `ports` ADD  `port_label_num` VARCHAR( 16 ) NOT NULL AFTER  `port_label_base` ;
ALTER TABLE  `ports` CHANGE  `port_label`  `port_label` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ;
ALTER TABLE  `ports` CHANGE  `port_label_base`  `port_label_base` VARCHAR( 128 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ;
ALTER TABLE  `ports` CHANGE  `port_label_num`  `port_label_num` VARCHAR( 16 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ;
ALTER TABLE  `ports` CHANGE  `port_label_short`  `port_label_short` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL ;
