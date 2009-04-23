<?php

class Cgn_Slot_Magento {

	function bindMagentoSession($signal) {
		$source = $signal->getSource();
		$user = $source->user;

	//	include('magento/app/Mage.php');
		Mage::app('default');
		$customer = Mage::getModel('customer/customer');
		if (empty($user->email)) {
			$email = $user->username;
		} else {
			$email = $user->email;
		}
		$customer->loadByEmail($email);
		$session = Mage::getSingleton('customer/session');
		$session->start();
		$session->setCustomer($customer);
		//var_dump($customer);
		//var_dump($email);exit();
	}
}
