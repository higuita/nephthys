<table class="withborder" style="width: 100%;">
 <tr>
  <td>Bucket</td>
  { if $login_priv == "manager" || $login_priv == "admin" }
  <td>Owner</td>
  { /if }
  <td>Created on</td>
  <td>Expires on</td>
  <td>Notified</td>
  <td>Open Bucket</td>
  <td>Actions</td>
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
   <span style="behavior: url(#default#httpFolder); cursor: pointer; font-size: 14px; color: #000000;" onclick="this.navigate('{ $bucket_webdav_path }');">
    <a href="{ $bucket_webdav_path }" onclick="return false;" title="Open bucket via WebDAV"><img src="images/webdav.png" />&nbsp;WebDAV</a>
   </span>
   &nbsp;<a href="{ $bucket_ftp_path }" target="_blank" title="Open bucket via FTP"><img src="images/ftp.png" />&nbsp;FTP</a>
  </td>
  <td>
   <a href="javascript:js_delete_obj('buckets', 'main', '{ $bucket_idx }');" title="Delete bucket"><img src="images/delete.png" />&nbsp;Delete</a>
   <a href="javascript:ajax_notify_bucket({ $bucket_idx });" title="Send notification e-mails"><img src="images/mail.png" />&nbsp;Notify</a>
  </td>
 </tr>
{ /bucket_list }
{ else }
 <tr>
  { if $login_priv == "manager" || $login_priv == "admin" }
  <td colspan="7">
  { else }
  <td colspan="6">
  { /if }
   <br />
   You have no buckets currently. Create one by choosing one of the above ways you want to share files!
   <br />
  </td>
 </tr>
{ /if }
</table>
