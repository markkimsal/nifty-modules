DROP TABLE IF EXISTS `nxc_changelog`;
CREATE TABLE `nxc_changelog` (
	`nxc_changelog_id` integer (11) NOT NULL auto_increment, 
	`nxc_project_id` integer (11) NOT NULL default '0', 
	`title` varchar (255) NOT NULL default '', 
	`description` text NOT NULL default '', 

	`trunk_url` tinytext NOT NULL default '', 
	`last_tag_url` tinytext NOT NULL default '', 

	`trunk_rev` int NOT NULL default '0', 
	`last_tag_rev` int NOT NULL default '0', 

	`edited_on` integer (11) NOT NULL default 0,
	`created_on` integer (11) NOT NULL default 0,
	PRIMARY KEY (nxc_changelog_id) 
);

CREATE INDEX edited_on_idx ON nxc_changelog (`edited_on`);
CREATE INDEX created_on_idx ON nxc_changelog (`created_on`);

ALTER TABLE `nxc_changelog` COLLATE utf8_general_ci;
