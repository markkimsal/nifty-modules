<?php

class Cgn_Magento_Layout {

	/**
	 * Print the layout XML for block "head" in Magento's layout config.
	 */
	public function showMageHeader() {
		$controller = Mage::app()->getFrontController();
	    $controller->setNoRender(true);
		$action = $controller->getAction();
		if (!empty($action)) {
			$head = $action->getLayout()->getBlock('head');

			echo $head->getCssJsHtml();
			echo $head->getChildHtml();
		}
	}

	/**
	 * Print the layout XML for block "right" in Magento's layout config.
	 */
	public function showMageRight() {
		$controller = Mage::app()->getFrontController();
	    $controller->setNoRender(true);
		$action = $controller->getAction();
		if (!empty($action)) {
			$right = $action->getLayout()->getBlock('right');

			echo $right->getChildHtml();
		}
	}

}
