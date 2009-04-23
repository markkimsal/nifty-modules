<?php

class Cgn_Service_Openid_Main extends Cgn_Service {


	public function mainEvent($req, &$t) {

		if(!Cgn::loadModLibrary('Openid::Openid_Common')) {
			echo "can't open library";
		}
		Cgn::loadModLibrary( 'Openid::Openid_OpenID');
		Cgn::loadModLibrary( 'Openid::Openid_Interface');
		Cgn::loadModLibrary( 'Openid::Openid_HMAC');
		Cgn::loadModLibrary( 'Openid::Openid_Nonce');


	}

	public function tryAuthEvent($req, &$t) {

		if(!Cgn::loadModLibrary('Openid::Openid_Common')) {
			echo "can't open library";
		}
		Cgn::loadModLibrary( 'Openid::Openid_OpenID');
		Cgn::loadModLibrary( 'Openid::Openid_Interface');
		Cgn::loadModLibrary( 'Openid::Openid_HMAC');
		Cgn::loadModLibrary( 'Openid::Openid_Nonce');


		$openid = $this->_getOpenIDURL();
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

		$policy_uris = $_GET['policies'];

		$pape_request = new Auth_OpenID_PAPE_Request($policy_uris);
		if ($pape_request) {
			$auth_request->addExtension($pape_request);
		}

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
//			$form_html = $auth_request->htmlMarkup(Openid_Common_getTrustRoot(), 
//				Openid_Common_getReturnTo(), 
//				false, array('id' => $form_id));

			$form = $auth_request->formMarkup(Openid_Common_getTrustRoot(), 
				Openid_Common_getReturnTo(), 
				false,
				array('id' => $form_id));


			// Display an error if the form markup couldn't be generated;
			// otherwise, render the HTML.
			if (Auth_OpenID::isFailure($form_html)) {
				displayError("Could not redirect to server: " . $form_html->message);
			} else {
$t['form'] = $form;
$t['js'] = 
               "<script>".
               "var elements = document.getElementById('openid_message').elements;".
               "for (var i = 0; i < elements.length; i++) {".
               "  elements[i].style.display = \"none\";".
               "}".
		" document.forms[0].submit(); //*/".
               "</script>";

//echo "form";
	//			print $form_html;
			}
		}
	}

	public function finishAuthEvent($req, &$t) {

		if(!Cgn::loadModLibrary('Openid::Openid_Common')) {
			echo "can't open library";
		}
		Cgn::loadModLibrary( 'Openid::Openid_OpenID');
		Cgn::loadModLibrary( 'Openid::Openid_Interface');
		Cgn::loadModLibrary( 'Openid::Openid_HMAC');
		Cgn::loadModLibrary( 'Openid::Openid_Nonce');


		$consumer = Openid_Common_getConsumer();

		// Complete the authentication process using the server's
		// response.
		$return_to = Openid_Common_getReturnTo();
		$response = $consumer->complete($return_to);

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

				if ($pape_resp->auth_age) {
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

	public function _getOpenIDURL() {
		// Render a default page if we got a submission without an openid
		// value.
		if (empty($_GET['openid_identifier'])) {
			$error = "Expected an OpenID URL.";
			include 'index.php';
			exit(0);
		}

		return $_GET['openid_identifier'];
	}
}
