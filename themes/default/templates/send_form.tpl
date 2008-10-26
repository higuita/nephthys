<form action="rpc.php?action=store" id="buckets" onsubmit="return js_create_bucket(this, 'buckets'); return false;" method="post">
<input type="hidden" name="module" value="buckets" />
<input type="hidden" name="mode" value="modify" />
<input type="hidden" name="bucketmode" value="send" />
<input type="hidden" name="bucket_new" value="1" />

{ page_start header="##SHARE_I_WANT##" }

<table>
 <tr>
  <td colspan="2">##FORM_BUCKET_NAME##:</td>
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
  <td colspan="2">##FORM_SENDER_EMAIL##:
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
 { /if }
 <tr>
  <td colspan="2">##FORM_RECEIVER_EMAIL## (##FORM_RECEIVER_OPT##):
  <div class="forminfo">##FORM_RECEIVER_SEPARATE##.</div>
 </tr>
 <tr>
  <td>
   <input type="text" name="bucket_receiver" id="bucket_receiver" class="inputedit" maxlength="250" value="{ $bucket_receiver }" onfocus="load_autosuggest('bucket_receiver');" onchange="js_validate_email(this, 'receiveremail');" onblur="js_validate_email(this, 'receiveremail');" acdropdown="true" autocomplete_list="url:rpc.php?action=getxmllist&search=[S]&length=10" />
  </td>
  <td>
   <div id="receiveremail" class="warning"></div>
  </td>
 </tr>
 <tr>
  <td colspan="2">
   <input type="checkbox" class="checkbox" name="bucket_receiver_to_ab" value="Y" checked="checked">&nbsp;##FORM_ADD_AB##
  </td>
 </tr>
 <tr>
  <td colspan="2">&nbsp;</td>
 </tr>
 <tr>
  <td colspan="2">##FORM_BUCKET_EXPIRE##?</td>
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
  <td colspan="2"><input type="checkbox" name="bucket_notify_on_expire" value="Y" { if $bucket_notify_on_expire == "Y" } checked="checked" { /if } />&nbsp;##NOTIFY_ON_EXPIRE##</td>
 </tr>
 <tr>
  <td colspan="2">&nbsp;</td>
 </tr>
 <tr>
  <td>{ save_button text="##FORM_CREATE_BUCKET##" }</td>
 </tr>
 <tr>
  <td><div id="generalerror" class="warning"></div></td>
 </tr>
</table>
</form>

<!-- set focus to the first input field -->
{ page_end focus_to='bucket_name' }
