<?php

class Nxc_Changelog_Model extends Cgn_Data_Model {
	var $tableName = 'nxc_changelog';

	var $ownerIdField = 'user_id';
	var $groupIdField = 'account_id';

	var $parentIdField = 'nxc_project_id';
	var $parentTable   = 'nxc_project';

	var $sharingModeRead   = 'parent-owner';
	var $sharingModeCreate = 'parent-owner';
	var $sharingModeEdit   = 'parent-owner';

}

class Nxc_Changelog_Model_List extends Cgn_Data_Model_List {

	var $tableName = 'nxc_changelog';

	var $ownerIdField = 'user_id';
	var $groupIdField = 'account_id';

	var $parentIdField = 'nxc_project_id';
	var $parentTable   = 'nxc_project';

	var $sharingModeRead   = 'parent-owner';
	var $sharingModeCreate = 'parent-owner';
	var $sharingModeEdit   = 'parent-owner';
}
?>
