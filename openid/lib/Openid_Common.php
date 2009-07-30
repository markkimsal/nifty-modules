<?

/**
 * This file was modified to work with the Cognifty Framework
 */


/**
 * Require the OpenID consumer code.
 */
Cgn::loadModLibrary("Openid::Openid_Consumer");

/**
 * Require the "file store" module, which we'll need to store
 * OpenID information.
 */
if(!Cgn::loadModLibrary("Openid::Openid_FileStore")) {
	echo "no file store";
}

/**
 * Require the Simple Registration extension API.
 */
Cgn::loadModLibrary("Openid::Openid_SReg");

/**
 * Require the PAPE extension module.
 */
Cgn::loadModLibrary("Openid::Openid_PAPE");


global $pape_policy_uris;
$pape_policy_uris = array(
			  PAPE_AUTH_MULTI_FACTOR_PHYSICAL,
			  PAPE_AUTH_MULTI_FACTOR,
			  PAPE_AUTH_PHISHING_RESISTANT
			  );

function getStore() {
    /**
     * This is where the example will store its OpenID information.
     * You should change this path if you want the example store to be
     * created elsewhere.  After you're done playing with the example
     * script, you'll have to remove this directory manually.
     */
    $store_path = BASE_DIR."/var/_php_consumer_test";

    if (!file_exists($store_path) &&
        !mkdir($store_path)) {
        print "Could not create the FileStore directory '$store_path'. ".
            " Please check the effective permissions.";
        exit(0);
    }

    return new Auth_OpenID_FileStore($store_path);
}

function Openid_Common_getConsumer() {
    /**
     * Create a consumer object using the store object created
     * earlier.
     */
    $store = getStore();
    $consumer =& new Auth_OpenID_Consumer($store);
    return $consumer;
}

function getScheme() {
    $scheme = 'http';
    if (isset($_SERVER['HTTPS']) and $_SERVER['HTTPS'] == 'on') {
        $scheme .= 's';
    }
    return $scheme;
}

function Openid_Common_getReturnTo() {
    return cgn_appurl('openid', 'realm') .'?finishAuth';
}

function Openid_Common_getTrustRoot() {
    return cgn_appurl('openid', 'realm');
/*    return sprintf("%s://%s:%s%s/",
                   getScheme(), $_SERVER['SERVER_NAME'],
                   $_SERVER['SERVER_PORT'],
                   dirname($_SERVER['PHP_SELF']));
 */
}

