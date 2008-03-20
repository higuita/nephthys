<pre id="target"></pre>
<form action="rpc.php?action=store" id="users" onsubmit="js_submit_form(this, 'users'); return false;" method="post">
<input type="hidden" name="module" value="users" />
<input type="hidden" name="mode" value="modify" />
{ if ! $user_idx }
 {start_table icon=$icon_users alt="user icon" title="Create a new User" }
 <input type="hidden" name="user_new" value="1" />
{ else }
 {start_table icon=$icon_users alt="user icon" title="Modify User $user_name" }
 <input type="hidden" name="user_new" value="0" />
 <input type="hidden" name="namebefore" value="{ $user_name }" />
 <input type="hidden" name="user_idx" value="{ $user_idx }" />
{ /if }
<table style="width: 100%;" class="withborder">
 <tr>
  <td colspan="3">
   <img src="{ $icon_users }" alt="user icon" />
   General
  </td>
 </tr>
 <tr>
  <td>
   Name:
  </td>
  <td>
   <input type="text" name="user_name" size="30" value="{ $user_name }" />
  </td>
  <td>
   Enter the user/login name.
  </td>
 </tr>
 <tr>
  <td>
   Password:
  </td>
  <td>
   <input type="password" name="user_pass1" size="30" value="{ if $user_idx } nochangeMS { /if }" />
  </td>
  <td>
   Enter password of the user.
  </td>
 </tr>
 <tr>
  <td>
   again
  </td>
  <td>
   <input type="password" name="user_pass2" size="30" value="{ if $user_idx } nochangeMS { /if }" />
  </td>
  <td>
   &nbsp;
  </td>
 </tr>
 <tr>
  <td>
   E-mail
  </td>
  <td>
   <input type="text" name="user_email" size="30" value="{ $user_email }" />
  </td>
  <td>
   &nbsp;
  </td>
 </tr>

 <tr>
  <td>
   Status:
  </td>
  <td>
   <input type="radio" name="user_active" value="Y" { if $user_active == "Y" } checked="checked" { /if } />Enabled
   <input type="radio" name="user_active" value="N" { if $user_active != "Y" } checked="checked" { /if } />Disabled
  </td>
  <td>
   Enable or disable user account.
  </td>
 </tr>
 <tr>
  <td style="text-align: center;"><a href="javascript:ajax_show_content('users');" title="Back"><img src="{ $icon_arrow_left }" alt="arrow left icon" /></a></td>
  <td><input type="submit" value="Save" /></td>
  <td>Save your settings.</td>
 </tr>
</table>
</form>
{ page_end focus_to='user_name' }
