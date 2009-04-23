<?php

class Cgn_MagentoSystemRunner extends Cgn_SystemRunner {


	function runTickets() {
		spl_autoload_unregister(array(Cgn_ObjectStore::$singleton, 'autoloadClass'));
		require 'magento/app/Mage.php';
		spl_autoload_register('__autoload');

     error_reporting(1);
		$modulePath = Cgn_ObjectStore::getConfig('path://default/cgn/module');

		//initialize the class if it has not been loaded yet (lazy loading)
		Cgn_ObjectStore::getObject('object://defaultSessionLayer');

		$mySession =& Cgn_Session::getSessionObj();
		$mySession->start();

		//initialize the class if it has not been loaded yet (lazy loading)
		//@@TODO, the new autoload function should make this unneeded
		Cgn_ObjectStore::getObject('object://defaultOutputHandler');

		$req = &$this->currentRequest;

		//start the session here
		$req->getUser()->startSession();

		//set up the template vars
		$template = array();
		Cgn_ObjectStore::setArray("template://variables/", $template);

		Cgn_ObjectStore::storeObject('request://currentRequest',$this->currentRequest);
		while(count($this->ticketList)) {
			$tk = array_shift($this->ticketList);

			//check first for magento tickets
			$includeResult = class_exists($tk->className, FALSE);
			if (!$includeResult) {
				$includeResult = $this->includeService($tk);
			}
			//chnage everything to the Magnifty Proxy service
			if (!$includeResult) {
				$tk->module  = 'magnifty';
				$tk->service = 'main';
				$tk->event   = 'main';
				$tk->filename   = 'main.php';
				$tk->className   = 'Cgn_Service_Magnifty_Main';
				$tk->isRouted   = TRUE;

				//special event for onepage checkout
				if (in_array('onepage', $req->getvars)) {
					$tk->event   = 'onepage';
				}
			} 

			$service = $this->runCogniftyTicket($tk);
			$this->ticketDoneList[] = $tk;
		}

		if (! is_object($service)) {
			return false;
		}

		//use the last service as the main one
		// OUTPUT happens here

		switch($service->presenter) {
			case 'default':
				$myTemplate =& Cgn_ObjectStore::getObject("object://defaultOutputHandler");
				if ($service->templateName != '') {
					$myTemplate->contentTpl = $service->templateName;
				}
				$myTemplate->parseTemplate($service->templateStyle);
				break;
			case 'redirect':
				$template = Cgn_ObjectStore::getArray("template://variables/");
				$myRedirector =& Cgn_ObjectStore::getObject("object://redirectOutputHandler");
				$myRedirector->redirect($req,$template);
				break;
			case 'self':
				$template = Cgn_ObjectStore::getArray("template://variables/");
				$service->output($req,$template);
				break;
			default:
				break;
		}
		Cgn_Template::cleanAll();
		$mySession->close();

	}
}
?>
