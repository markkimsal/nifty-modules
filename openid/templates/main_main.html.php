  <style type="text/css">
      .alert {
        border: 1px solid #e7dc2b;
        background: #fff888;
      }
      .success {
        border: 1px solid #669966;
        background: #88ff88;
      }
      .error {
        border: 1px solid #ff0000;
        background: #ffaaaa;
      }
      .verify-form {
        border: 1px solid #777777;
        background: #dddddd;
        margin-top: 1em;
        padding-bottom: 0em;
      }
  </style>

    <div class="verify-form" id="verify-form-google">
	<form method="get" action="<?=cgn_appurl('openid','main','gAuth');?>">
        <input type="hidden" name="openid_identifier" value="https://www.google.com/accounts/o8/id" size="55" />
		<input type="image" src="<?=cgn_url().'media/icons/default/login_btn_google.png';?>" value="Login with Google" />
      </form>
    </div>



    <h1>PHP OpenID Authentication Example</h1>
    <p>
      This example consumer uses the <a
      href="http://www.openidenabled.com/openid/libraries/php/">PHP
      OpenID</a> library. It just verifies that the URL that you enter
      is your identity URL.
    </p>

    <?php if (isset($msg)) { print "<div class=\"alert\">$msg</div>"; } ?>
    <?php if (isset($error)) { print "<div class=\"error\">$error</div>"; } ?>
    <?php if (isset($success)) { print "<div class=\"success\">$success</div>"; } ?>

    <div class="verify-form" id="verify-form">
	<form method="get" action="<?=cgn_appurl('openid','main','tryAuth');?>">
        Identity&nbsp;URL:
        <input type="hidden" name="action" value="verify" />
        <input type="text" name="openid_identifier" value="" size="55" />

        <p>Optionally, request these PAPE policies:</p>
        <p>
        <?php global $pape_policy_uris; foreach ($pape_policy_uris as $i => $uri) {
          print "<input type=\"checkbox\" name=\"policies[]\" value=\"$uri\" />";
          print "$uri<br/>";
        } ?>
        </p>

        <input type="submit" value="Verify" />
      </form>
    </div>
