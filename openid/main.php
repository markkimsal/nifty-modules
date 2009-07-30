<?php

class Cgn_Service_Openid_Main extends Cgn_Service {


	public function mainEvent($req, &$t) {

		if(!Cgn::loadModLibrary('Openid::Openid_Common')) {
			trigger_error("Problem with OpenID engine right now.");
			return;
		}
		Cgn::loadModLibrary( 'Openid::Openid_OpenID');
		Cgn::loadModLibrary( 'Openid::Openid_Interface');
		Cgn::loadModLibrary( 'Openid::Openid_HMAC');
		Cgn::loadModLibrary( 'Openid::Openid_Nonce');

	}

	protected function _loadOpenId() {
		if(!Cgn::loadModLibrary('Openid::Openid_Common')) {
			trigger_error("Problem with OpenID engine right now.");
			return FALSE;
		}
		Cgn::loadModLibrary( 'Openid::Openid_OpenID');
		Cgn::loadModLibrary( 'Openid::Openid_Interface');
		Cgn::loadModLibrary( 'Openid::Openid_HMAC');
		Cgn::loadModLibrary( 'Openid::Openid_Nonce');
		return TRUE;
	}

	public function gAuthEvent($req, &$t) {

		if (!$this->_loadOpenId()) {
			return FALSE;
		}
		//extended attribute exchange
		Cgn::loadModLibrary( 'Openid::Openid_AX');

		$openid = $this->_getOpenIDURL($req);
//		var_dump($openid);exit();
		$consumer = Openid_Common_getConsumer();

		// Begin the OpenID authentication process.
		$auth_request = $consumer->begin($openid);

		// No auth request means we can't begin OpenID.
		if (!$auth_request) {
			echo ("Authentication error; not a valid OpenID.");
		}

		$ax_request = new Auth_Openid_Ax_FetchRequest();
		$email_attr = new Auth_OpenID_AX_AttrInfo('http://schema.openid.net/contact/email', 1, TRUE, 'email');
		$uname_attr = new Auth_OpenID_AX_AttrInfo('http://axschema.org/namePerson/friendly', 1, TRUE, 'nickname');
		$homep_attr = new Auth_OpenID_AX_AttrInfo('http://axschema.org/web/default', 1, TRUE, 'sites');
		$count_attr = new Auth_OpenID_AX_AttrInfo('http://axschema.org/contact/country/home', 1, TRUE, 'country');
		$langu_attr = new Auth_OpenID_AX_AttrInfo('http://axschema.org/pref/language', 1, TRUE, 'language');

		$namef_attr = new Auth_OpenID_AX_AttrInfo('http://axschema.org/namePerson/first', 1, TRUE, 'first');
		$namel_attr = new Auth_OpenID_AX_AttrInfo('http://axschema.org/namePerson/last',  1, TRUE, 'last');

		$ax_request->add($email_attr);
		$ax_request->add($uname_attr);
		$ax_request->add($homep_attr);
		$ax_request->add($namef_attr);
		$ax_request->add($namel_attr);
		$ax_request->add($langu_attr);
		$ax_request->add($count_attr);
		$auth_request->addExtension($ax_request);


		$trustRoot = $this->_getTrustRoot('google');
		$returnTo = $this->_getReturnTo('google');


		// Redirect the user to the OpenID server for authentication.
		// Store the token for this authentication so we can verify the
		// response.

		// For OpenID 1, send a redirect.  For OpenID 2, use a Javascript
		// form to send a POST request to the server.
		if ($auth_request->shouldSendRedirect()) {
			$redirect_url = $auth_request->redirectURL($trustRoot,
													   $returnTo);

			// If the redirect URL can't be built, display an error
			// message.
			if (Auth_OpenID::isFailure($redirect_url)) {
				displayError("Could not redirect to server: " . $redirect_url->message);
			} else {
				// Send redirect.
				header("Location: ".$redirect_url);
			}
		} else {
			// Generate form markup and render it.
			$form_id = 'openid_message';
			$form = $auth_request->formMarkup(
				$trustRoot, 
				$returnTo, 
				false,
				array('id' => $form_id));


			// Display an error if the form markup couldn't be generated;
			// otherwise, render the HTML.
			if (Auth_OpenID::isFailure($form)) {
				displayError("Could not redirect to server: " . $form->message);
			} else {
				$this->presenter = 'self';
				$t['form'] = $form;
				$t['js'] = 
				"<script>".
				"var elements = document.getElementById('openid_message').elements;".
				"for (var i = 0; i < elements.length; i++) {".
				"  elements[i].style.display = \"none\";".
				"}".
				" document.getElementById('".$form_id."').submit(); //*/".
				"</script>";
			}
		}
	}

	public function yAuthEvent($req, &$t) {
		if (!$this->_loadOpenId()) {
			return FALSE;
		}

		$username = $req->cleanString('username');
		if (!$username) {
			$this->presenter = 'redirect';
			$t['url'] = cgn_appurl('openid', 'main', 'yLogin');
			return TRUE;
		}
		$openid = 'https://me.yahoo.com/'.$username;
		$consumer = Openid_Common_getConsumer();

		// Begin the OpenID authentication process.
		$auth_request = $consumer->begin($openid);

		// No auth request means we can't begin OpenID.
		if (!$auth_request) {
			trigger_error ("Authentication error; not a valid OpenID.");
			return FALSE;
		}

		//yahoo doesn't support any extensions ATM
		if ($auth_request->shouldSendRedirect()) {
			$redirect_url = $auth_request->redirectURL(Openid_Common_getTrustRoot(),
													   Openid_Common_getReturnTo());

			// If the redirect URL can't be built, display an error
			// message.
			if (Auth_OpenID::isFailure($redirect_url)) {
				displayError("Could not redirect to server: " . $redirect_url->message);
			} else {
				// Send redirect.
				header("Location: ".$redirect_url);
			}
		} else {
			// Generate form markup and render it.
			$form_id = 'openid_message';
			$form = $auth_request->formMarkup(Openid_Common_getTrustRoot(), 
				Openid_Common_getReturnTo(), 
				false,
				array('id' => $form_id));


			// Display an error if the form markup couldn't be generated;
			// otherwise, render the HTML.
			if (Auth_OpenID::isFailure($form)) {
				displayError("Could not redirect to server: " . $form->message);
			} else {
				$this->presenter = 'self';
				$t['form'] = $form;
				$t['js'] = 
				"<script>".
				"var elements = document.getElementById('openid_message').elements;".
				"for (var i = 0; i < elements.length; i++) {".
				"  elements[i].style.display = \"none\";".
				"}".
				" document.getElementById('".$form_id."').submit(); //*/".
				"</script>";
			}
		}
	}

	/**
	 * Just show the HTML page
	 */
	public function yLoginEvent($req, &$t) {
	}

	public function tryAuthEvent($req, &$t) {
		if (!$this->_loadOpenId()) {
			return FALSE;
		}
		//extended attribute exchange
		Cgn::loadModLibrary( 'Openid::Openid_AX');

		$openid = $this->_getOpenIDURL($req);
		$consumer = Openid_Common_getConsumer();

		// Begin the OpenID authentication process.
		$auth_request = $consumer->begin($openid);

		// No auth request means we can't begin OpenID.
		if (!$auth_request) {
			echo ("Authentication error; not a valid OpenID.");
		}

		$sreg_request = Auth_OpenID_SRegRequest::build(
							 // Required
							 array('nickname'),
							 // Optional
							 array('fullname', 'email'));

		if ($sreg_request) {
			$auth_request->addExtension($sreg_request);
		}

		$ax_request = new Auth_Openid_Ax_FetchRequest();
		$email_attr = new Auth_OpenID_AX_AttrInfo('http://schema.openid.net/contact/email', 1, TRUE, 'email');
		$uname_attr = new Auth_OpenID_AX_AttrInfo('http://axschema.org/namePerson/friendly', 1, TRUE, 'nickname');
		$homep_attr = new Auth_OpenID_AX_AttrInfo('http://axschema.org/web/default', 1, TRUE, 'sites');
		$count_attr = new Auth_OpenID_AX_AttrInfo('http://axschema.org/contact/country/home', 1, TRUE, 'country');
		$langu_attr = new Auth_OpenID_AX_AttrInfo('http://axschema.org/pref/language', 1, TRUE, 'language');

		$namef_attr = new Auth_OpenID_AX_AttrInfo('http://axschema.org/namePerson/first', 1, TRUE, 'first');
		$namel_attr = new Auth_OpenID_AX_AttrInfo('http://axschema.org/namePerson/last',  1, TRUE, 'last');

		$ax_request->add($email_attr);
		$ax_request->add($uname_attr);
		$ax_request->add($homep_attr);
		$ax_request->add($namef_attr);
		$ax_request->add($namel_attr);
		$ax_request->add($langu_attr);
		$ax_request->add($count_attr);
		$auth_request->addExtension($ax_request);


		// Redirect the user to the OpenID server for authentication.
		// Store the token for this authentication so we can verify the
		// response.

		// For OpenID 1, send a redirect.  For OpenID 2, use a Javascript
		// form to send a POST request to the server.
		if ($auth_request->shouldSendRedirect()) {
			$redirect_url = $auth_request->redirectURL(Openid_Common_getTrustRoot(),
													   Openid_Common_getReturnTo());

			// If the redirect URL can't be built, display an error
			// message.
			if (Auth_OpenID::isFailure($redirect_url)) {
				displayError("Could not redirect to server: " . $redirect_url->message);
			} else {
				// Send redirect.
				header("Location: ".$redirect_url);
			}
		} else {
			// Generate form markup and render it.
			$form_id = 'openid_message';
			$form = $auth_request->formMarkup(Openid_Common_getTrustRoot(), 
				Openid_Common_getReturnTo(), 
				false,
				array('id' => $form_id));


			// Display an error if the form markup couldn't be generated;
			// otherwise, render the HTML.
			if (Auth_OpenID::isFailure($form)) {
				displayError("Could not redirect to server: " . $form->message);
			} else {
				$this->presenter = 'self';
				$t['form'] = $form;
				$t['js'] = 
				"<script>".
				"var elements = document.getElementById('openid_message').elements;".
				"for (var i = 0; i < elements.length; i++) {".
				"  elements[i].style.display = \"none\";".
				"}".
				" document.getElementById('".$form_id."').submit(); //*/".
				"</script>";
			}
		}
	}


	public function gFinishAuthEvent($req, &$t) {
		if(!Cgn::loadModLibrary('Openid::Openid_Common')) {
			trigger_error("Problem with OpenID engine; expected OpenID URL");
			return FALSE;
		}
		Cgn::loadModLibrary( 'Openid::Openid_OpenID');
		Cgn::loadModLibrary( 'Openid::Openid_Interface');
		Cgn::loadModLibrary( 'Openid::Openid_HMAC');
		Cgn::loadModLibrary( 'Openid::Openid_Nonce');

		Cgn::loadModLibrary( 'Openid::Openid_AX');

		$consumer = Openid_Common_getConsumer();

		// Complete the authentication process using the server's
		// response.
		$returnTo = $this->_getReturnTo('google');
		$response = $consumer->complete($returnTo);

		$msg     = '';
		$success = '';
		// Check the response status.
		if ($response->status == Auth_OpenID_CANCEL) {
			// This means the authentication was cancelled.
			$msg = 'Verification cancelled.';
		} else if ($response->status == Auth_OpenID_FAILURE) {
			// Authentication failed; display the error message.
			$msg = "OpenID authentication failed: " . $response->message;
		} else if ($response->status == Auth_OpenID_SUCCESS) {
			$msg = 'Success!';
			// This means the authentication succeeded; extract the
			// identity URL and Simple Registration data (if it was
			// returned).
			$openid = $response->getDisplayIdentifier();
			$esc_identity = htmlentities($openid);

			$success = sprintf('You have successfully verified ' .
							   '<a href="%s">%s</a> as your identity.',
							   $esc_identity, $esc_identity);

			if ($response->endpoint->canonicalID) {
				$escaped_canonicalID = htmlentities($response->endpoint->canonicalID);
				$success .= '  (XRI CanonicalID: '.$escaped_canonicalID.') ';
			}

			//ax
			$ax_resp = Auth_OpenID_AX_FetchResponse::fromSuccessResponse($response);
			if($ax_resp) {
				var_dump($ax_resp->get('http://axschema.org/namePerson/first'));
				var_dump($ax_resp->get('http://axschema.org/pref/language'));
			}

		} else {
				$success = "<p>No PAPE response was sent by the provider.</p>";
		}
		$t['msg'] = $msg;
	}


	public function finishAuthEvent($req, &$t) {

		if(!Cgn::loadModLibrary('Openid::Openid_Common')) {
			echo "can't open library";
		}
		Cgn::loadModLibrary( 'Openid::Openid_OpenID');
		Cgn::loadModLibrary( 'Openid::Openid_Interface');
		Cgn::loadModLibrary( 'Openid::Openid_HMAC');
		Cgn::loadModLibrary( 'Openid::Openid_Nonce');

		Cgn::loadModLibrary( 'Openid::Openid_AX');

		$consumer = Openid_Common_getConsumer();

		// Complete the authentication process using the server's
		// response.
		$return_to = Openid_Common_getReturnTo();
		$response = $consumer->complete($return_to);

		$msg = '';
		$success = '';
		// Check the response status.
		if ($response->status == Auth_OpenID_CANCEL) {
			// This means the authentication was cancelled.
			$msg = 'Verification cancelled.';
		} else if ($response->status == Auth_OpenID_FAILURE) {
			// Authentication failed; display the error message.
			$msg = "OpenID authentication failed: " . $response->message;
		} else if ($response->status == Auth_OpenID_SUCCESS) {
			// This means the authentication succeeded; extract the
			// identity URL and Simple Registration data (if it was
			// returned).
			$openid = $response->getDisplayIdentifier();
			$esc_identity = htmlentities($openid);

			$success = sprintf('You have successfully verified ' .
							   '<a href="%s">%s</a> as your identity.',
							   $esc_identity, $esc_identity);

			if ($response->endpoint->canonicalID) {
				$escaped_canonicalID = htmlentities($response->endpoint->canonicalID);
				$success .= '  (XRI CanonicalID: '.$escaped_canonicalID.') ';
			}

			$sreg_resp = Auth_OpenID_SRegResponse::fromSuccessResponse($response);

			$sreg = $sreg_resp->contents();

			if (@$sreg['email']) {
				$success .= "  You also returned '".htmlentities($sreg['email']).
					"' as your email.";
			}

			if (@$sreg['nickname']) {
				$success .= "  Your nickname is '".htmlentities($sreg['nickname']).
					"'.";
			}

			if (@$sreg['fullname']) {
				$success .= "  Your fullname is '".htmlentities($sreg['fullname']).
					"'.";
			}

			//ax
			$ax_resp = Auth_OpenID_AX_FetchResponse::fromSuccessResponse($response);
			if($ax_resp) {
				var_dump($ax_resp->get('http://axschema.org/namePerson/first'));
				var_dump($ax_resp->get('http://axschema.org/pref/language'));
			}

		$pape_resp = Auth_OpenID_PAPE_Response::fromSuccessResponse($response);

		if ($pape_resp) {
				if ($pape_resp->auth_policies) {
					$success .= "<p>The following PAPE policies affected the authentication:</p><ul>";

					foreach ($pape_resp->auth_policies as $uri) {
						$escaped_uri = htmlentities($uri);
						$success .= "<li><tt>$escaped_uri</tt></li>";
					}

					$success .= "</ul>";
				} else {
					$success .= "<p>No PAPE policies affected the authentication.</p>";
				}

				if (isset($pape_resp->auth_age)) {
					$age = htmlentities($pape_resp->auth_age);
					$success .= "<p>The authentication age returned by the " .
						"server is: <tt>".$age."</tt></p>";
				}

				if ($pape_resp->nist_auth_level) {
					$auth_level = htmlentities($pape_resp->nist_auth_level);
					$success .= "<p>The NIST auth level returned by the " .
						"server is: <tt>".$auth_level."</tt></p>";
				}

		} else {
				$success .= "<p>No PAPE response was sent by the provider.</p>";
		}
    	}

		$t['success'] = $success;
		$t['msg'] = $msg;
	}

	/**
	 * Try two places to get the openID provider url
	 */
	public function _getOpenIDURL($req) {

		if ($url = $req->cleanString('openid_identifier')) {
			return $url;
		}

		if (empty($_GET['openid_identifier'])) {
			trigger_error("Problem with OpenID engine; expected OpenID URL");
			return FALSE;
		}
	}

	public function _getReturnTo($provider=NULL) {
		if ($provider === 'google') {
	    	return cgn_appurl('openid', 'realm', 'gFinishAuth');
		}
    	return cgn_appurl('openid', 'realm'). '?finishAuth';
	}

	public function _getTrustRoot($provider=NULL) {
		if ($provider === 'google') {
	    	return cgn_appurl('openid', 'realm', 'gFinishAuth');
		}

    	return cgn_appurl('openid', 'realm'). '?finishAuth';
	}

	/**
	 * If this is called, we just want to show the javascript self submitting form in a blank template.
	 */
	public function output($reg, &$t) {
		echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"> <html xmlns="http://www.w3.org/1999/xhtml"> <body> '.$t['form'].$t['js'].'</body> </html>';
	}

}
