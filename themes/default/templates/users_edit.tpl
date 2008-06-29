<form action="rpc.php?action=store" id="users" onsubmit="js_submit_form(this, 'users'); return false;" method="post">
<input type="hidden" name="module" value="users" />
<input type="hidden" name="mode" value="modify" />
{ if ! $user_idx }
 { page_start header="Create a new User" }
 <input type="hidden" name="user_new" value="1" />
{ else }
 { page_start header="Modify User $user_name" }
 <input type="hidden" name="user_new" value="0" />
 <input type="hidden" name="namebefore" value="{ $user_name }" />
 <input type="hidden" name="user_idx" value="{ $user_idx }" />
{ /if }

<table>
 <tr>
  <td>Username:</td>
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
  <td>Password:</td>
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
   Password (again):
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
  <td>E-mail:</td>
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
  <td>Privileges:</td>
 </tr>
 <tr>
  <td>
   <select name="user_priv">
    <option value="user" { if $user_priv == "user" } selected="selected" { /if }>User</option>
    <option value="manager" { if $user_priv == "manager" } selected="selected" { /if }>Manager</option>
    <option value="admin" { if $user_priv == "admin" } selected="selected" { /if }>Administrator</option>
   </select>
  </td>
 </tr>
 <tr>
  <td>&nbsp;</td>
 </tr>
 <tr>
  <td>Status:
   <input type="radio" name="user_active" value="Y" { if $user_active == "Y" } checked="checked" { /if } />Enabled
   <input type="radio" name="user_active" value="N" { if $user_active != "Y" } checked="checked" { /if } />Disabled
  </td>
 </tr>
 <tr>
  <td>&nbsp;</td>
 </tr>
 <tr>
  <td>{ save_button text="Save user and return to user-list" }</td>
 </tr>
</table>
</form>

{ page_end focus_to='user_name' }
