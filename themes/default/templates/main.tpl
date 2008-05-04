{ if !$user_email }
 <br />
 <font style='color: #ff0000;'>Your E-Mail address is not set. You can do this in the "My Profile" tab.</font>
 <br />
{ /if }
 <table>
  <tr>
   <td style="width: 50%; text-align: center;">
    <a href="javascript:ajax_show_content('buckets', '&mode=send');"><img src="images/files_from_user.png" /><br />I want to share some files.</a>
   </td>
   <td style="width: 50%; text-align: center;">
    <a href="javascript:ajax_show_content('buckets', '&mode=receive');"><img src="images/files_to_user.png" /><br />Someone wants to send me files.</a></td>
   </td>
  </tr>
 </table>
 <hr />
 { import_bucket_list }
