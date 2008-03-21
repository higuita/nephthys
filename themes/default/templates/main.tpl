 <table>
  <tr>
   <td style="width: 50%; text-align: center;">
    <a href="javascript:ajax_show_content('buckets', '&mode=send');"><img src="icons/files_from_user.png" /><br />I want to share some files.</a>
   </td>
   <td style="width: 50%; text-align: center;">
    <a href="javascript:ajax_show_content('buckets', '&mode=receive');"><img src="icons/files_to_user.png" /><br />Someone wants to send me files.</a></td>
   </td>
  </tr>
 </table>
 <br /><br /><br />
 <table class="withborder">
 <tr>
  <td>Bucket</td>
  <td>Created on</td>
  <td>&nbsp;</td>
 </tr>
 { bucket_list }
 <tr>
  <td>
   { $bucket_name }
  </td>
  <td>
   { $bucket_creation_time }
  </td>
  <td>
   <a href="javascript:ajax_delete_bucket({ $bucket_idx });"><img src="icons/delete.png" /></a>
   <a href="javascript:ajax_notify_bucket({ $bucket_idx });"><img src="icons/mail.png" /></a>
   <span style="behavior: url(#default#httpFolder);" onclick="this.navigate('{ $bucket_webdav_path }');">
    <img src="icons/webdav.png" />
   </span>
   <a href="{ $bucket_ftp_path }"><img src="icons/ftp.png" /></a>
  </td>
 </tr>
{ /bucket_list }
 </table>
