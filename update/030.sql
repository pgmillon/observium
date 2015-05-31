ALTER TABLE `ports-state`  ADD `ifInOctets_perc` INT NOT NULL AFTER `ifOutOctets_rate`;
ALTER TABLE `ports-state`  ADD `ifOutOctets_perc` INT NOT NULL AFTER `ifInOctets_perc`;
