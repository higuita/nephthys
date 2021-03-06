<form action="rpc.php?action=store" id="users" onsubmit="js_submit_form(this, 'users'); return false;" method="post">
<input type="hidden" name="module" value="users" />
<input type="hidden" name="mode" value="modify" />
{ if ! $user_idx }
 { page_start header="##USER_CREATE_NEW##" }
 <input type="hidden" name="user_new" value="1" />
{ else }
 { page_start header="##USER_MODIFY## $user_name" }
 <input type="hidden" name="user_new" value="0" />
 <input type="hidden" name="namebefore" value="{ $user_name }" />
 <input type="hidden" name="user_idx" value="{ $user_idx }" />
{ /if }

<table>
 <tr>
  <td>##USERNAME##:</td>
 </tr>
 <tr>
  <td>
   <input type="text" name="user_name" class="inputedit" value="{ $user_name }" />
  </td>
 </tr>
 <tr>
  <td>&nbsp;</td>
 </tr>
 <tr>
  <td>##FULLNAME##:</td>
 </tr>
 <tr>
  <td>
   <input type="text" name="user_full_name" class="inputedit" value="{ $user_full_name }" />
  </td>
 </tr>
 <tr>
  <td>&nbsp;</td>
 </tr>
 <tr>
  <td>##PASSWORD##:</td>
 </tr>
 <tr>
  <td>
   <input type="password" name="user_pass1" class="inputedit" value="{ if $user_idx } nochangeMS { /if }" />
  </td>
 </tr>
 <tr>
  <td>&nbsp;</td>
 </tr>
 <tr>
  <td>
   ##PASSWORD## (##AGAIN##):
  </td>
 </tr>
 <tr>
  <td>
   <input type="password" name="user_pass2" class="inputedit" value="{ if $user_idx } nochangeMS { /if }" />
  </td>
 </tr>
 <tr>
  <td>&nbsp;</td>
 </tr>
 <tr>
  <td>##EMAIL##:</td>
 </tr>
 <tr>
  <td>
   <input type="text" name="user_email" class="inputedit" value="{ $user_email }" />
  </td>
 </tr>
 <tr>
  <td>&nbsp;</td>
 </tr>
 <tr>
  <td colspan="2">##DEFAULT_BUCKET_EXPIRE##:</td>
 </tr>
 <tr>
  <td>
   { expiration_list name="user_default_expire" current=$user_default_expire }
  </td>
 </tr>
 <tr>
  <td>&nbsp;</td>
 </tr>
 <tr>
  <td>##LANGUAGE##:</td>
 </tr>
 <tr>
  <td>{ language_list name="user_language" current=$user_language }</td>
 </tr>
 <tr>
  <td>&nbsp;</td>
 </tr>
 <tr>
  <td>##PRIVILEGES##:</td>
 </tr>
 <tr>
  <td>
   <select name="user_priv">
    <option value="user" { if $user_priv == "user" } selected="selected" { /if }>##USER##</option>
    <option value="manager" { if $user_priv == "manager" } selected="selected" { /if }>##MANAGER##</option>
    <option value="admin" { if $user_priv == "admin" } selected="selected" { /if }>##ADMIN##</option>
   </select>
  </td>
 </tr>
 <tr>
  <td>&nbsp;</td>
 </tr>
 <tr>
  <td>##USER_LONG_TIME_BUCKET##:</td>
 </tr>
 <tr>
  <td>
   <select name="user_priv_expire">
    <option value="N" { if $user_priv_expire != 'Y' } selected="selected" { /if }>##NO##</option>
    <option value="Y" { if $user_priv_expire == 'Y' } selected="selected" { /if }>##YES##</option>
   </select>
  </td>
 </tr>
 <tr>
  <td>&nbsp;</td>
 </tr>
 <tr>
  <td>
   <table>
    <tr>
     <td>##USER_DENY_CHPWD##:</td>
     <td><input type="radio" name="user_deny_chpwd" value="N" { if $user_deny_chpwd != "Y" } checked="checked" { /if } />##ALLOW##</td>
     <td><input type="radio" name="user_deny_chpwd" value="Y" { if $user_deny_chpwd == "Y" } checked="checked" { /if } />##DENY##</td>
    </tr>
    <tr>
     <td colspan="3">&nbsp;</td>
    </tr>
    <tr>
     <td>##STATUS##:</td>
     <td><input type="radio" name="user_active" value="Y" { if $user_active == "Y" } checked="checked" { /if } />##ENABLED##</td>
     <td><input type="radio" name="user_active" value="N" { if $user_active != "Y" } checked="checked" { /if } />##DISABLED##</td>
    </tr>
   </table>
  </td>
 </tr>
 <tr>
  <td>&nbsp;</td>
 </tr>
 <tr>
  <td>{ save_button text="##USER_SAVE##" }</td>
 </tr>
</table>
</form>

{ page_end focus_to='user_name' }
