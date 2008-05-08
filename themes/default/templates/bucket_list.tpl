<table class="withborder" style="width: 100%;">
 <tr>
  <td>Bucket</td>
  { if $login_priv == "manager" || $login_priv == "admin" }
  <td>Owner</td>
  { /if }
  <td>Created on</td>
  <td>Expires on</td>
  <td>Notified</td>
  <td>Options</td>
 </tr>
{ if $user_has_buckets }

{ bucket_list }
 <tr onmouseover="setBackGrdColor(this, 'mouseover');" onmouseout="setBackGrdColor(this, 'mouseout');">
  <td>
   <a href="javascript:ajax_show_content('buckets', '&mode=edit&idx={ $bucket_idx }');">{ $bucket_name }</a>
  </td>
  { if $login_priv == "manager" || $login_priv == "admin" }
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
   { $bucket_notified }
  </td>
  <td style="vertical-align: middle;">
   <a href="javascript:js_delete_obj('buckets', 'main', '{ $bucket_idx }');"><img src="images/delete.png" />&nbsp;Delete</a>
   <a href="javascript:ajax_notify_bucket({ $bucket_idx });"><img src="images/mail.png" />&nbsp;Notify</a>
   <span style="behavior: url(#default#httpFolder); cursor: pointer; font-size: 14px; color: #000000;" onclick="this.navigate('{ $bucket_webdav_path }');">
    <img src="images/webdav.png" />&nbsp;WebDAV
   </span>
   <a href="{ $bucket_ftp_path }" taget="_blank"><img src="images/ftp.png" />&nbsp;FTP</a>
  </td>
 </tr>
{ /bucket_list }
{ else }
 <tr>
  <td colspan="5">
   <br />
   You have no buckets currently. Create one by choosing one of the above ways you want to share files!
   <br />
  </td>
 </tr>
{ /if }
</table>
