<form id="login" onsubmit="js_login(); return false;">

{ page_start header="Login to Nephthys" }

<table>
 <tr>
  <td style="text-align: right;">Username:&nbsp;</td>
  <td><input type="text" name="login_name" class="inputlogin" /></td>
 </tr>
 <tr>
  <td style="text-align: right;">Password:&nbsp;</td>
  <td><input type="password" name="login_pass" class="inputlogin" /></td>
 </tr>
 <tr><td colspan="2">&nbsp;</td>
 <tr>
  <td>&nbsp;</td>
  <td style="text-align: left;"><input type="submit" value="Login" />
  </td>
 </tr>
</table>
</form>

{ page_end focus_to='login_name' }
