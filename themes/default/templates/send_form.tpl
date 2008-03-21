  <form action="rpc.php?action=store" id="buckets" onsubmit="js_create_bucket(this, 'buckets'); return false;" method="post">
  <input type="hidden" name="module" value="buckets" />
  <input type="hidden" name="bucketmode" value="send" />
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
     <input type="text" name="bucket_name" style="width: 400px;" maxlength="250" value="{ $bucket_name }" />
    </td>
   </tr>
   <tr>
    <td colspan="2">&nbsp;</td>
   </tr>
   { if $user_priv == "manager" || $user_priv == "admin" }
   <tr>
    <td colspan="2">Enter the senders email address:
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
    <td colspan="2">Should this bucket expire after it has been created?</td>
   </tr>
   <tr>
    <td>
     <select name="bucket_expire">
      <option value="1">1 Day</option>
      <option value="3">3 Days</option>
      <option value="7">1 Week</option>
      <option value="31">1 Month</option>
      <option value="186">6 Months</option>
      <option value="365">1 Year</option>
      <option value="-1">never</option>
     </select>
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
  <!-- set focus to the first input field -->
  <img src="icons/1x1.png" onload="document.forms[0].bucket_name.focus();" />
