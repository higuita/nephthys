  <form action="rpc.php?action=store" id="buckets" onsubmit="return js_create_bucket(this, 'buckets'); return false;" method="post">
  <input type="hidden" name="module" value="buckets" />
  <input type="hidden" name="mode" value="modify" />
  <input type="hidden" name="bucket_owner" value="{ $login_idx }" />
  <input type="hidden" name="bucket_idx" value="{ $bucket_idx }" />
  <input type="hidden" name="bucket_new" value="0" />
  <table>
   <tr>
    <td colspan="2">&nbsp;</td>
   </tr>
   <tr>
    <td colspan="2">Bucket name:</td>
   </tr>
   <tr>
    <td>
     <input type="text" name="bucket_name" style="width: 400px" maxlength="250" value="{ $bucket_name }" />
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
     <input type="text" name="bucket_sender" style="width: 400px" maxlength="250" value="{ $bucket_sender }" onchange="js_validate_email(this, 'senderemail');" />
    </td>
    <td>
     <div id="senderemail" style="visibility: hidden;"></div>
    </td>
   </tr>
   <tr>
    <td colspan="2">&nbsp;</td>
   </tr>
   { else }
    { $bucket_sender }
   { /if }
   <tr>
    <td colspan="2">Receivers email:
   </tr>
   <tr>
    <td>
     <input type="text" name="bucket_receiver" style="width: 400px" maxlength="250" value="{ $bucket_receiver }" onchange="js_validate_email(this, 'receiveremail');" />
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
    <td colspan="2">Note which will be added to notification email (optional):</td>
   </tr>
   <tr>
    <td>
     <textarea name="bucket_note" style="width: 400px; height: 50px;">{ $bucket_note }</textarea>
    </td>
   </tr>
   <tr>
    <td colspan="2">&nbsp;</td>
   </tr>
   <tr>
    <td><input type="submit" value="Save and return to front-page" /></td>
   </tr>
   <tr>
    <td><div id="generalerror" style="visibility: hidden;"></div></td>
   </tr>
  </table>
  </form>
