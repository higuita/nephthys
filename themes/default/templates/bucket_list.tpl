<table class="withborder">
 <tr>
  <td>Bucket</td>
  { if $user_priv == "manager" || $user_priv == "admin" }
  <td>Owner</td>
  { /if }
  <td>Created on</td>
  <td>Expire on</td>
  <td>Options</td>
 </tr>
{ bucket_list }
 <tr>
  <td>
   { $bucket_name }
  </td>
  { if $user_priv == "manager" || $user_priv == "admin" }
  <td>
   <a href="javascript:ajax_show_content('users', '&mode=edit&idx={ $bucket_owner_idx }');">{ $bucket_owner }</a>
  </td>
  { /if }
  <td>
   { $bucket_created }
  </td>
  <td>
   { $bucket_expire }
  </td>
  <td>
   <a href="javascript:js_delete_obj('buckets', 'main', '{ $bucket_idx }');"><img src="icons/delete.png" /></a>
   <a href="javascript:ajax_notify_bucket({ $bucket_idx });"><img src="icons/mail.png" /></a>
   <span style="behavior: url(#default#httpFolder);" onclick="this.navigate('{ $bucket_webdav_path }');">
    <img src="icons/webdav.png" />
   </span>
   <a href="{ $bucket_ftp_path }"><img src="icons/ftp.png" /></a>
  </td>
 </tr>
{ /bucket_list }
</table>
