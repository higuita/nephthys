  <form action="rpc.php?action=store" id="profile" onsubmit="return js_submit_form(this, 'profile'); return false;" method="post">
  <input type="hidden" name="module" value="profile" />
  <input type="hidden" name="mode" value="modify" />
  <input type="hidden" name="user_idx" value="{ $user_idx }" />
  <table>
   <tr>
    <td colspan="2">&nbsp;</td>
   </tr>
   <tr>
    <td colspan="2">Username:
   </tr>
   <tr>
    <td>
     { if $login_priv == "manager" || $login_priv == "admin" }
     <input type="text" name="user_name" style="width: 400px" maxlength="250" value="{ $user_name }" />
     { else }
     { $user_name }
     { /if }
    </td>
   </tr>
   <tr>
    <td colspan="2">&nbsp;</td>
   </tr>
   <tr>
    <td colspan="2">Fullname:
   </tr>
   <tr>
    <td>
     <input type="text" name="user_full_name" style="width: 400px" maxlength="250" value="{ $user_full_name }" />
    </td>
   </tr>
   <tr>
    <td colspan="2">&nbsp;</td>
   </tr>
   <tr>
    <td colspan="2">Email:
   </tr>
   <tr>
    <td>
     { if $login_priv == "manager" || $login_priv == "admin" }
     <input type="text" name="user_email" style="width: 400px" maxlength="250" value="{ $user_email }" onchange="js_validate_email(this, 'user_email');" />
     { else }
     { $user_email }
     { /if }
    </td>
    <td>
     <div id="user_email" style="visibility: hidden;"></div>
    </td>
   </tr>
   <tr>
    <td colspan="2">&nbsp;</td>
   </tr>
   <tr>
    <td>
     Password:
    </td>
   </tr>
   <tr>
    <td>
     <input type="password" name="user_pass1" style="width: 400px" value="{ if $user_idx } nochangeMS { /if }" />
    </td>
   </tr>
   <tr>
    <td>
     again
    </td>
   </tr>
   <tr>
    <td>
     <input type="password" name="user_pass2" style="width: 400px" value="{ if $user_idx } nochangeMS { /if }" />
    </td>
   </tr>
   <tr>
    <td colspan="2">&nbsp;</td>
   </tr>
   <tr>
    <td><input type="submit" value="Save" /></td>
   </tr>
   <tr>
    <td><div id="generalerror" style="visibility: hidden;"></div></td>
   </tr>
  </table>
  </form>
