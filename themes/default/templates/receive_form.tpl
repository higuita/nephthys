<form action="rpc.php?action=store" id="buckets" onsubmit="return js_create_bucket(this, 'buckets'); return false;" method="post">
<input type="hidden" name="module" value="buckets" />
<input type="hidden" name="mode" value="modify" />
<input type="hidden" name="bucketmode" value="receive" />
<input type="hidden" name="bucket_new" value="1" />

{ page_start header="Someone wants to send me files" }

<table>
 <tr>
  <td colspan="2">Give the bucket a name:</td>
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
  <td colspan="2">Yours or senders email:
 </tr>
 <tr>
  <td>
   <input type="text" name="bucket_sender" class="inputedit" maxlength="250" value="{ $bucket_sender }" onchange="js_validate_email(this, 'senderemail');" />
  </td>
  <td>
   <div id="senderemail" style="visibility: hidden;"></div>
  </td>
 </tr>
 <tr>
  <td colspan="2">&nbsp;</td>
 </tr>
 { /if }
 <tr>
  <td colspan="2">Receivers email:
 </tr>
 <tr>
  <td>
   <input type="text" name="bucket_receiver" class="inputedit" maxlength="250" value="{ $bucket_receiver }" onchange="js_validate_email(this, 'receiveremail');" />
  </td>
  <td>
   <div id="receiveremail" style="visibility: hidden;"></div>
  </td>
 </tr>
 <tr>
  <td colspan="2">&nbsp;</td>
 </tr>
 <tr>
  <td colspan="2">When will this bucket expire?</td>
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
  <td>Owner:</td>
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
  <td colspan="2">Text to be added to notification email (optional):</td>
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
  <td colspan="2">
   <input type="checkbox" name="notifybucket" value="true" checked=\"checked\">&nbsp;
   Send email notification to receiver immediately after creating bucket.
  </td>
 </tr>
 <tr>
  <td colspan="2">&nbsp;</td>
 </tr>
 <tr>
  <td><input type="submit" value="Save bucket and return to front-page" /></td>
 </tr>
 <tr>
  <td><div id="generalerror" style="visibility: hidden;"></div></td>
 </tr>
</table>
</form>

<!-- set focus to the first input field -->
{ page_end focus_to='bucket_name' }
