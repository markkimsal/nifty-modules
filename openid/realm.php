<?php

class Cgn_Service_Openid_Realm extends Cgn_Service {

	var $presenter = 'self';

	/**
	 * This event is a proxy for "openid.main.finishAuth".
	 * This proxy is required for some OPs which require the 
	 * realm to match the return_to variable.
	 */
	public function mainEvent($req, &$t) {

		if (array_key_exists('finishAuth', $req->getvars)) {
			$myHandler =& Cgn_ObjectStore::getObject("object://defaultSystemHandler");
			$newTicket = new Cgn_SystemTicket('openid', 'main', 'finishAuth');
			array_push($myHandler->ticketList, $newTicket);
		}
	}

	public function gFinishAuthEvent($req, &$t) {

		$myHandler =& Cgn_ObjectStore::getObject("object://defaultSystemHandler");
		$newTicket = new Cgn_SystemTicket('openid', 'main', 'gFinishAuth');
		array_push($myHandler->ticketList, $newTicket);
	}


	/**
	 * Push out an XRDS file proclaimin that this service is the return to url.
	 */
	public function xrdsEvent($req, &$t) {
		$this->presenter = 'none';
		header('Content-Type: application/xrds+xml');
echo '<?xml version="1.0" encoding="UTF-8"?>
<xrds:XRDS
    xmlns:xrds="xri://$xrds"
    xmlns:openid="http://openid.net/xmlns/1.0"
    xmlns="xri://$xrd*($v*2.0)">
    <XRD>
        <Service priority="1">
            <Type>http://specs.openid.net/auth/2.0/return_to</Type>
            <URI>'.cgn_appurl('openid', 'realm').'?finishAuth'.'</URI>
        </Service>
    </XRD>
</xrds:XRDS>
';
	}

	public function output($req, $t){ 
		header('X-XRDS-Location: '.cgn_appurl('openid', 'realm', 'xrds'));
		echo '<html><head><meta http-equiv="X-XRDS-Location" content="'.cgn_appurl('openid', 'realm', 'xrds').'"/></head><body></body></html>';
	}
}
?>
