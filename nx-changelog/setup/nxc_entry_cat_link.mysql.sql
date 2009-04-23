DROP TABLE IF EXISTS `nxc_entry_cat_link`;
CREATE TABLE `nxc_entry_cat_link` (
	`nxc_entry_cat_link_id` integer (11) NOT NULL auto_increment, 
	`nxc_changelog_entry_id` integer (11) NOT NULL default 0, 
	`category` varchar(255) NOT NULL default '',
	PRIMARY KEY (nxc_entry_cat_link_id) 
);

CREATE INDEX nxc_changelog_entry_idx ON nxc_entry_cat_link (`nxc_changelog_entry_id`);

ALTER TABLE `nxc_entry_cat_link` COLLATE utf8_general_ci;
