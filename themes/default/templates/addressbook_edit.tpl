<form action="rpc.php?action=store" id="addressbook" onsubmit="return js_submit_form(this, 'addressbook'); return false;" method="post">
<input type="hidden" name="module" value="addressbook" />
<input type="hidden" name="mode" value="modify" />
<input type="hidden" name="contact_owner" value="{ $login_idx }" />
<input type="hidden" name="contact_idx" value="{ $contact_idx }" />
<input type="hidden" name="contact_new" value="0" />

{ page_start header="##ABEDIT_TITLE##" }

<table>
 <tr>
  <td colspan="2">##NAME##:</td>
 </tr>
 <tr>
  <td>
   <input type="text" name="contact_name" class="inputedit" maxlength="250" value="{ $contact_name }" />
  </td>
 </tr>
 <tr>
  <td colspan="2">&nbsp;</td>
 </tr>
 <tr>
  <td colspan="2">##EMAIL##:</td>
 </tr>
 <tr>
  <td>
   <input type="text" name="contact_email" class="inputedit" maxlength="250" value="{ $contact_email }" />
  </td>
 </tr>
 <tr>
  <td colspan="2">&nbsp;</td>
 </tr>
 { if $login_priv == "manager" || $login_priv == "admin" }
 <tr>
  <td>##OWNER##:</td>
 </tr>
 <tr>
  <td>
   { owner_list name="contact_owner" current=$contact_owner }
  </td>
 </tr>
 <tr>
  <td colspan="2">&nbsp;</td>
 </tr>
 { /if }
 <tr>
  <td>{ save_button text="##ABEDIT_SAVE##" }</td>
 </tr>
 <tr>
  <td><div id="generalerror" style="visibility: hidden;"></div></td>
 </tr>
</table>
</form>

{ page_end focus_to='contact_name' }
