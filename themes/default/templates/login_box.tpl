<form id="login" onsubmit="js_login(); return false;">
 <table>
  <tr>
   <td class="tablehead">
    Login to Nephthys
   </td>
  </tr>
  <tr><td>&nbsp;</td></tr>
  <tr>
   <td>
    <table class="withborder2" style="margin-left:auto; margin-right:auto; text-align: right;">
     <tr>
      <td>
       Username:&nbsp;
      </td>
      <td>
       <input type="text" name="login_name" size="15" />
      </td>
     </tr>
     <tr>
      <td>
       Password:&nbsp;
      </td>
      <td>
       <input type="password" name="login_pass" size="15" />
      </td>
     </tr>
     <tr><td colspan="2">&nbsp;</td>
     <tr>
      <td>
       &nbsp;
      </td>
      <td style="text-align: left;">
       <input type="submit" value="Login" />
      </td>
     </tr>
    </table>
   </td>
  </tr>
 </table>
</form>
{ page_end focus_to='login_name' }
