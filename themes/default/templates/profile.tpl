<form action="rpc.php?action=store" id="profile" onsubmit="return js_submit_form(this, 'main'); return false;" method="post">
<input type="hidden" name="module" value="profile" />
<input type="hidden" name="mode" value="modify" />
<input type="hidden" name="user_idx" value="{ $user_idx }" />

{ page_start header="My Profile" subheader="Update your profile details:" }

<table>
 <tr>
  <td colspan="2">Username:</td>
 </tr>
 <tr>
  <td>
   { if $login_priv == "manager" || $login_priv == "admin" }
    <input type="text" name="user_name" class="inputedit" maxlength="250" value="{ $user_name }" />
   { else }
    { $user_name }
   { /if }
  </td>
 </tr>
 <tr>
  <td colspan="2">&nbsp;</td>
 </tr>
 <tr>
  <td colspan="2">Fullname:</td>
 </tr>
 <tr>
  <td>
   <input type="text" name="user_full_name" class="inputedit" maxlength="250" value="{ $user_full_name }" />
  </td>
 </tr>
 <tr>
  <td colspan="2">&nbsp;</td>
 </tr>
 <tr>
  <td colspan="2">Email:</td>
 </tr>
 <tr>
  <td>
   { if $login_priv == "manager" || $login_priv == "admin" || $user_auto_created == 'Y' }
    <input type="text" name="user_email" class="inputedit" maxlength="250" value="{ $user_email }" onchange="js_validate_email(this, 'user_email_error');" />
   { else }
    { $user_email }
   { /if }
  </td>
  <td>
   <div id="user_email_error" style="visibility: hidden; padding-left: 10px; color: #aa0000;"></div>
  </td>
 </tr>
 <tr>
  <td colspan="2">&nbsp;</td>
 </tr>
 <tr>
  <td>Password:</td>
 </tr>
 <tr>
 <td>
  <input type="password" name="user_pass1" class="inputedit" value="{ if $user_idx } nochangeMS { /if }" />
 </td>
 </tr>
 <tr>
  <td>again</td>
 </tr>
 <tr>
  <td>
   <input type="password" name="user_pass2" class="inputedit" value="{ if $user_idx } nochangeMS { /if }" />
  </td>
 </tr>
 <tr>
  <td colspan="2">&nbsp;</td>
 </tr>
 <tr>
  <td colspan="2">Default expiration time used for buckets:</td>
 </tr>
 <tr>
  <td>
   { expiration_list name="user_default_expire" current=$user_default_expire }
  </td>
 </tr>
 <tr>
  <td colspan="2">&nbsp;</td>
 </tr>
 <tr>
  <td><input type="submit" value="Save and go back to start-page" /></td>
 </tr>
 <tr>
  <td><div id="generalerror" style="visibility: hidden;"></div></td>
 </tr>
</table>
</form>

{ page_end }
