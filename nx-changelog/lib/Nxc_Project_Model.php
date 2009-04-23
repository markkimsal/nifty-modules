<?php

class Nxc_Project_Model extends Cgn_Data_Model {
	var $tableName = 'nxc_project';

	var $ownerIdField = 'user_id';
	var $groupIdField = 'account_id';

	var $sharingModeRead   = 'same-owner';
	var $sharingModeCreate = 'same-owner';

}

class Nxc_Project_Model_List extends Cgn_Data_Model_List {

	var $tableName = 'nxc_project';

	var $ownerIdField = 'user_id';
	var $groupIdField = 'account_id';

	var $sharingModeRead   = 'same-owner';
	var $sharingModeCreate = 'same-owner';
}
?>
