DROP TABLE IF EXISTS `nxc_changelog_entry`;
CREATE TABLE `nxc_changelog_entry` (
	`nxc_changelog_entry_id` integer (11) NOT NULL auto_increment, 
	`nxc_changelog_id` integer (11) NOT NULL default 0, 
	`revision` int (11) NOT NULL default 0, 
	`author` varchar (255) NOT NULL default '', 
	`message` text NOT NULL default '', 
	`entry_date` int NOT NULL default 0, 
	`edited_on` integer (11) NOT NULL default 0,
	`created_on` integer (11) NOT NULL default 0,
	PRIMARY KEY (nxc_changelog_entry_id) 
);

CREATE INDEX edited_on_idx ON nxc_changelog_entry (`edited_on`);
CREATE INDEX created_on_idx ON nxc_changelog_entry (`created_on`);

ALTER TABLE `nxc_changelog_entry` COLLATE utf8_general_ci;
