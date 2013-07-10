
<div class="section-title">
  <h1>ALive Registrierung</h1>
</div>

<div>
{form_open('register', 'class="page_form"')}

	<table style="width:80%">
		<tr>
			<td><label for="register_username">{lang("username", "register")}</label></td>
			<td>
				<input type="text" name="register_username" id="register_username" value="{set_value('register_username')}" onChange="Validate.checkUsername()"/>
				<span id="username_error">{$username_error}</span>
			</td>
		</tr>
		<tr>
			<td><label for="register_email">{lang("email", "register")}</label></td>
			<td>
				<input type="email" name="register_email" id="register_email" value="{set_value('register_email')}" onChange="Validate.checkEmail()"/>
				<span id="email_error">{$email_error}</span>
			</td>
		</tr>
		<tr>
			<td><label for="register_password">{lang("password", "register")}</label></td>
			<td>
				<input type="password" name="register_password" id="register_password" value="{set_value('register_password')}" onChange="Validate.checkPassword()"/>
				<span id="password_error">{$password_error}</span>
			</td>
		</tr>
		<tr>
			<td><label for="register_password_confirm">{lang("confirm", "register")}</label></td>
			<td>
				<input type="password" name="register_password_confirm" id="register_password_confirm" value="{set_value('register_password_confirm')}" onChange="Validate.checkPasswordConfirm()"/>
				<span id="password_confirm_error">{$password_confirm_error}</span>
			</td>
		</tr>
		<tr>
			<td><label for="register_expansion">{lang("expansion", "register")}</label></td>
			<td>
				<select id="register_expansion" name="register_expansion">
					{foreach from=$expansions key=id item=expansion}
						<option value="{$id}">{$expansion}</option>
					{/foreach}
				</select>
			</td>
		</tr>

		{if $use_captcha}
			<tr>
				<td><label for="captcha"><img src="{$url}application/modules/register/controllers/getCaptcha.php?{uniqid()}" /></label></td>
				<td>
					<input type="text" name="register_captcha" id="register_captcha"/>
					
					<span id="captcha_error">{$captcha_error}</span>
				</td>
			</tr>
		{/if}

    <tr>
      <td>&nbsp;</td>
      <td>
        <button class="ui-button button1" name="login_submit" type="submit">
          <span class="button-left"><span class="button-right">{lang("submit", "register")}</span></span>
        </button>
      </td>
    </tr>
	</table>

{form_close()}
</div>