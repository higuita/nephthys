<form action="rpc.php?action=store" id="buckets" onsubmit="return js_create_bucket(this, 'buckets'); return false;" method="post">
<input type="hidden" name="module" value="buckets" />
<input type="hidden" name="mode" value="modify" />
<input type="hidden" name="bucket_owner" value="{ $login_idx }" />
<input type="hidden" name="bucket_idx" value="{ $bucket_idx }" />
<input type="hidden" name="bucket_new" value="0" />

{ page_start header="##BEDIT_HEADER## $bucket_name" }

<table>
 <tr>
  <td colspan="2">##BEDIT_F1_NAME##:</td>
 </tr>
 <tr>
  <td>
   <input type="text" name="bucket_name" class="inputedit" maxlength="250" value="{ $bucket_name }" />
  </td>
 </tr>
 <tr>
  <td colspan="2">&nbsp;</td>
 </tr>
 { if $login_priv == "manager" || $login_priv == "admin" }
 <tr>
  <td colspan="2">##BEDIT_F2_NAME##:
 </tr>
 <tr>
  <td>
   <input type="text" name="bucket_sender" class="inputedit" maxlength="250" value="{ $bucket_sender }" onchange="js_validate_email(this, 'senderemail');" />
  </td>
  <td>
   <div id="senderemail" class="warning"></div>
  </td>
 </tr>
 <tr>
  <td colspan="2">&nbsp;</td>
 </tr>
 { else }
 <tr>
  <td colspan="2">##BEDIT_F3_NAME##:
 </tr>
 <tr>
  <td colspan="2">
  { $bucket_sender }
  </td>
 </tr>
 <tr>
  <td colspan="2">&nbsp;</td>
 </tr>
 { /if }
 <tr>
  <td colspan="2">##FORM_RECEIVER_EMAIL##:
  <div class="forminfo">##FORM_RECEIVER_SEPARATE##.</div>
 </tr>
 <tr>
  <td>
   <input type="text" name="bucket_receiver" id="bucket_receiver" class="inputedit" maxlength="250" value="{ $bucket_receiver }" onfocus="load_autosuggest('bucket_receiver');" onchange="js_validate_email(this, 'receiveremail');" onblur="js_validate_email(this, 'receiveremail');"/>
  </td>
  <td>
   <div id="receiveremail" class="warning"></div>
  </td>
 </tr>
 <tr>
  <td colspan="2">&nbsp;</td>
 </tr>
 <tr>
  <td colspan="2">##EXPIRES##</td>
 </tr>
 <tr>
  <td>
   { expiration_list name="bucket_expire" current=$bucket_expire }
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
   { owner_list name="bucket_owner" current=$bucket_owner }
  </td>
 </tr>
 <tr>
  <td colspan="2">&nbsp;</td>
 </tr>
 { /if }
 <tr>
  <td colspan="2">##FORM_ADDITIONAL_TEXT##:</td>
 </tr>
 <tr>
  <td>
   <textarea name="bucket_note" class="notearea">{ $bucket_note }</textarea>
  </td>
 </tr>
 <tr>
  <td colspan="2">&nbsp;</td>
 </tr>
 <tr>
  <td>{ save_button text="##SAVE_GO_TO_START##" }</td>
 </tr>
 <tr>
  <td><div id="generalerror" class="warning"></div></td>
 </tr>
</table>
</form>

{ page_end }
