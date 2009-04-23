DROP TABLE IF EXISTS `nxc_project`;
CREATE TABLE `nxc_project` (
	`nxc_project_id` integer (11) NOT NULL auto_increment, 
	`title` varchar (255) NOT NULL default '', 
	`description` text NOT NULL default '', 
	`trunk_url` tinytext NOT NULL default '', 
	`edited_on` integer (11) NOT NULL default 0,
	`created_on` integer (11) NOT NULL default 0,
	`user_id` integer (11) NOT NULL default 0,
	`account_id` integer (11) NOT NULL default 0,
	PRIMARY KEY (nxc_project_id) 
);

CREATE INDEX edited_on_idx ON nxc_project (`edited_on`);
CREATE INDEX created_on_idx ON nxc_project (`created_on`);
CREATE INDEX user_idx ON nxc_project (`user_id`);
CREATE INDEX account_idx ON nxc_project (`account_id`);

ALTER TABLE `nxc_project` COLLATE utf8_general_ci;
