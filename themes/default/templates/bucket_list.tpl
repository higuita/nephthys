<table class="withborder" style="width: 100%;">
 <tr>
  <td>Bucket</td>
  { if $login_priv == "manager" || $login_priv == "admin" }
  <td>Owner</td>
  { /if }
  <td>Created on</td>
  <td>Expire on</td>
  <td>Options</td>
 </tr>
{ if $user_has_buckets }

{ bucket_list }
 <tr>
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
   <a href="javascript:js_delete_obj('buckets', 'main', '{ $bucket_idx }');"><img src="images/delete.png" /></a>
   <a href="javascript:ajax_notify_bucket({ $bucket_idx });"><img src="images/mail.png" /></a>
   <span style="behavior: url(#default#httpFolder);" onclick="this.navigate('{ $bucket_webdav_path }');">
    <img src="images/webdav.png" />
   </span>
   <a href="{ $bucket_ftp_path }" taget="_blank"><img src="images/ftp.png" /></a>
  </td>
 </tr>
{ /bucket_list }
{ else }
 <tr>
  <td colspan="4">
   <br />
   You have no buckets currently. Create one by choosing one of the above ways you want to share files!
   <br />
  </td>
 </tr>
{ /if }
</table>
