  <form action="rpc.php?action=store" id="buckets" onsubmit="js_create_bucket(this, 'buckets'); return false;" method="post">
  <input type="hidden" name="module" value="buckets" />
  <input type="hidden" name="bucketmode" value="receive" />
  <input type="hidden" name="bucket_new" value="1" />
  <table class="withborder">
   <tr>
    <td colspan="2">Enter a short description, so the receiver knows what he receives:</td>
   </tr>
   <tr>
    <td>
     <input type="text" name="bucket_name" size="30" value="{ $bucket_name }" />
    </td>
   </tr>
   { if $user_priv == "manager" || $user_priv == "admin" }
   <tr>
    <td colspan="2">Enter your or the senders email address:
   </tr>
   <tr>
    <td>
     <input type="text" name="bucket_sender" size="30" value="{ $bucket_sender }" onchange="js_validate_email(this, 'senderemail');" />
    </td>
    <td>
     <div id="senderemail" style="visibility: hidden;"></div>
    </td>
   </tr>
   { /if }
   <tr>
    <td colspan="2">Enter the receiver email address:
   </tr>
   <tr>
    <td>
     <input type="text" name="bucket_receiver" size="30" value="{ $bucket_receiver }" onchange="js_validate_email(this, 'receiveremail');" />
    </td>
    <td>
     <div id="receiveremail" style="visibility: hidden;"></div>
    </td>
   </tr>
   <tr>
    <td colspan="2">When should this bucket expire?</td>
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
     </select> from now
    </td>
   </tr>
   <tr>
    <td colspan="2">Enter a short note which will be sent with the instructions:</td>
   </tr>
   <tr>
    <td>
     <textarea name="bucket_note">{ $bucket_note }</textarea>
    </td>
   </tr>
   <tr>
    <td><input type="submit" value="Create" /></td>
   </tr>
   <tr>
    <td><div id="generalerror" style="visibility: hidden;"></div></td>
   </tr>
  </table>
  </form>
