  <form action="rpc.php?action=store" id="buckets" onsubmit="return js_create_bucket(this, 'buckets'); return false;" method="post">
  <input type="hidden" name="module" value="buckets" />
  <input type="hidden" name="mode" value="modify" />
  <input type="hidden" name="bucket_owner" value="{ $user_idx }" />
  <input type="hidden" name="bucketmode" value="receive" />
  <input type="hidden" name="bucket_new" value="1" />
  <table>
   <tr>
    <td colspan="2">&nbsp;</td>
   </tr>
   <tr>
    <td colspan="2">Give the bucket a name:</td>
   </tr>
   <tr>
    <td>
     <input type="text" name="bucket_name" style="width: 400px" maxlength="250" value="{ $bucket_name }" />
    </td>
   </tr>
   <tr>
    <td colspan="2">&nbsp;</td>
   </tr>
   { if $user_priv == "manager" || $user_priv == "admin" }
   <tr>
    <td colspan="2">Enter your or the senders email address:
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
   { /if }
   <tr>
    <td colspan="2">Enter the receiver email address:
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
    <td colspan="2">Should this bucket expire after it has been created?</td>
   </tr>
   <tr>
    <td>
     { expiration_list name="bucket_expire" }
    </td>
   </tr>
   <tr>
    <td colspan="2">&nbsp;</td>
   </tr>
   <tr>
    <td colspan="2">Notes which should be sent with instructions:</td>
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
    <td><input type="submit" value="Create" /></td>
   </tr>
   <tr>
    <td><div id="generalerror" style="visibility: hidden;"></div></td>
   </tr>
  </table>
  </form>
