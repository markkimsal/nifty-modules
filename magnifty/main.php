<?php

class Cgn_Service_Magnifty_Main extends Cgn_Service {

	var $templateStyle = 'store';
	function Cgn_Service_Mage_Main () {
		Cgn_Template::setPageTitle('Magnifty');
		Cgn_Template::setSiteName('magnifty');
	}

	/**
	 * Prepare a magento request by configuring magento URLs for a magnifty proxy.
	 */
	public function eventBefore($req, &$t) {
//		ob_end_clean();
		Mage::app('default');

		//update the URLs
		$config = Mage::getConfig();
		$storeNode = $config->getNode('stores/default/web');
		$unsecureNode = new Varien_SimpleXml_Element('<web><unsecure><base_url>'.cgn_url().'</base_url><base_media_url>'.cgn_url().'magento/media/</base_media_url><base_skin_url>'.cgn_url().'magento/skin/</base_skin_url><base_js_url>'.cgn_url().'magento/js/</base_js_url></unsecure></web>');
		$storeNode->extend($unsecureNode,  TRUE);

		$secureNode = new Varien_SimpleXml_Element('<web><secure><base_url>'.cgn_url(1).'</base_url><base_media_url>'.cgn_url(1).'magento/media/</base_media_url><base_skin_url>'.cgn_url(1).'magento/skin/</base_skin_url><base_js_url>'.cgn_url(1).'magento/js/</base_js_url></secure></web>');
		$storeNode->extend($secureNode,  TRUE);

		$newNode = new Varien_SimpleXml_Element('<web><seo><use_rewrites>0</use_rewrites></seo></web>');
		$storeNode->extend($newNode,  TRUE);

//$unsecureBaseUrl = Mage::getConfig()->getNode('stores/default/web/unsecure/base_url');
//var_dump($unsecureBaseUrl);exit();
	}


	/**
	 * Show a standard Magento page
	 */
	function mainEvent(&$req, &$t) {
		$controller = Mage::app()->getFrontController()->setNoRender(true)->dispatch();
//	    $controller->setNoRender(true)->dispatch();
	    $controller->setNoRender(false);

		$controller->getAction()->getLayout()->removeOutputBlock('root');
		$controller->getAction()->getLayout()->addOutputBlock('content');
		$controller->getAction()->renderLayout();

		$t['mage_output'] = $controller->getResponse()->__toString();
		//$t['mage_controller'] = $controller;
		
		Cgn_Template::setSiteName('Magnifty');
		Cgn_Template::setPageTitle('Demo Store');
		Cgn_Template::setSiteTagLine('Your Cognifty Commerce Solution');
	}

	/**
	 * Handle all the onepage-checkout strangeness here.
	 *
	 * The magento ticket runner will spot request for "onepage" and push them here.
	 */
	function onepageEvent($req, &$t) {
		//this is needed for the right-hand column
		$this->templateStyle = 'store-right';

		$controller = Mage::app()->getFrontController()->setNoRender(true)->dispatch();
	    $controller->setNoRender(false);

		//special hacks for ajax requests for onepage checkout
		if (in_array('saveBilling', $req->getvars) //ajax
			||in_array('saveShipping', $req->getvars) //ajax
			||in_array('savePayment', $req->getvars) //ajax
			||in_array('saveOrder', $req->getvars) //redirect
			) {
				header('Content-type: application/x-json', TRUE);
				exit();
			//$this->presenter = 'self';
		} else if (in_array('progress', $req->getvars)) {
			$this->presenter = 'self';
		} else {
			$controller->getAction()->getLayout()->removeOutputBlock('root');
			$controller->getAction()->getLayout()->addOutputBlock('content');
		}

		$controller->getAction()->renderLayout();
		$t['mage_output'] = $controller->getResponse()->__toString();
	}

	function output($req, $t) {
		echo $t['mage_output'];
	}
}
?>
