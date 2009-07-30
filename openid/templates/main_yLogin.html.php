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

    <h1>Login with Yahoo!&reg;</h1>
    <div class="verify-form" id="verify-form-google">
		<img border="0" src="<?=cgn_url().'media/icons/default/login_btn_yahoo.png';?>" title="Login with Yahoo" alt="Login with Yahoo"/>

		<form method="get" action="<?=cgn_appurl('openid','main','yAuth');?>">
		Enter your Yahoo! username
		<br/>
			https://me.yahoo.com/<input type="text" name="username" value="username" size="35" />
			<input type="submit" value="Verify" />
		</form>
	</div>
