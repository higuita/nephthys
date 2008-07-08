<table class="withborder">
 <tr class="subhead">
  <td><img src="images/bucket_small.png" />&nbsp;Name</td>
  <td><img src="images/open.png" />&nbsp;Open Bucket to add Files</td>
  { if $login_priv == "manager" || $login_priv == "admin" }
  <td><img src="images/user.png" />&nbsp;Owner</td>
  { /if }
  <td><img src="images/clock.png" />&nbsp;Created</td>
  <td><img src="images/clock_red.png" />&nbsp;Expires</td>
  <td><img src="images/email.png" />&nbsp;Notified</td>
  <td>Actions</td>
 </tr>

{ if $user_has_buckets }

{ bucket_list }
 <tr onmouseover="setBackGrdColor(this, 'mouseover');" onmouseout="setBackGrdColor(this, 'mouseout');">
  <td>
   <a href="javascript:ajax_show_content('buckets', '&mode=edit&idx={ $bucket_idx }');" title="Click to edit this bucket"><img src="images/bucket_small.png" />&nbsp;{ $bucket_name }</a>
  </td>
  <td style="vertical-align: middle;">

   <!-- WebDAV support is enabled -->
   { if $bucket_via_dav }

    <!-- Browser is Internet Explorer -->
    { if $is_ie }
     <span style="behavior: url(#default#httpFolder); cursor: pointer; font-size: 14px; color: #000000;" onclick="this.navigateFrame('{ $bucket_webdav_path }', '_blank');">
      <a href="{ $bucket_webdav_path }" onclick="return false;" title="Open bucket via WebDAV"><img src="images/webdav.png" />&nbsp;WebDAV</a>
     </span>
    <!-- Every other browser -->
    { else }
      <a href="{ $bucket_webdav_path }" onclick="return false;" title="Open bucket via WebDAV"><img src="images/webdav.png" />&nbsp;WebDAV</a>
    { /if }

    &nbsp;

   { /if }

   <!-- FTP support is enabled -->
   { if $bucket_via_ftp }
   <a href="{ $bucket_ftp_path }" target="_blank" title="Open bucket via FTP"><img src="images/ftp.png" />&nbsp;FTP</a>
   { /if }
  </td>

  { if $login_priv == "manager" || $login_priv == "admin" }
  <td>
   <a href="javascript:ajax_show_content('users', '&mode=edit&idx={ $bucket_owner_idx }');"><img src="images/user.png" />&nbsp;{ $bucket_owner }</a>
  </td>
  { /if }
  <td><img src="images/clock.png" />&nbsp;{ $bucket_created }</td>
  <td><img src="images/clock_red.png" />&nbsp;{ $bucket_expire }</td>
  <td><img src="images/email.png" />&nbsp;{ $bucket_notified }</td>
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
