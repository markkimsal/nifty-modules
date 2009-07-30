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
        background: #FFF;
        margin-top: 1em;
        padding: 1em;
      }

      .verify-form img {
		border:none;
		margin-right:1em;
		}
  </style>

    <h1>Login with OpenID&reg;</h1>
    <div class="verify-form" id="verify-form-google">
			<a href="<?=cgn_appurl('openid','main','gAuth', array( 'openid_identifier'=>urlencode('https://www.google.com/accounts/o8/id')));?>" border="0"><img border="0" src="<?=cgn_url().'media/icons/default/login_btn_google.png';?>" title="Login with Google" alt="Login with Google"/></a>
			<a href="<?=cgn_appurl('openid','main','yAuth');?>" border="0"><img border="0" src="<?=cgn_url().'media/icons/default/login_btn_yahoo.png';?>" title="Login with Yahoo" alt="Login with Yahoo"/></a>

		<form method="get" action="<?=cgn_appurl('openid','main','tryAuth');?>">
		Any OpenID&reg;&nbsp;Provider:
		<br/>
			<input type="hidden" name="action" value="verify" />
			<input type="text" name="openid_identifier" value="http://" size="55" />
			<input type="submit" value="Verify" />
		</form>
	</div>


