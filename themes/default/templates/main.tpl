{ if !$login_email }
 <br />
 <font style='color: #ff0000;'>Your E-Mail address is not set. You can do this in the "My Profile" tab.</font>
 <br />
{ /if }
 <table style="margin-left: auto; margin-right: auto;">
  <tr>
   <td style="width: 50%; text-align: center;">
    <a href="javascript:ajax_show_content('buckets', '&mode=send');" title="Create a bucket to share some files."><img src="images/files_from_user.png" /><br />I want to share some files.</a>
   </td>
   <td style="width: 50%; text-align: center;">
    <a href="javascript:ajax_show_content('buckets', '&mode=receive');" title="Create a bucket to share some files."><img src="images/files_to_user.png" /><br />Someone wants to send me files.</a>
   </td>
  </tr>
 </table>
 <hr />
 { import_bucket_list }
