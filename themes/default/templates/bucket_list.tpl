<table class="withborder">
 <tr class="subhead">
  <td><img src="{ $theme_root }/images/bucket_small.png" />&nbsp;##NAME##</td>
  <td><img src="{ $theme_root }/images/open.png" />&nbsp;##BLIST_COL_2_NAME##</td>
  { if $login_priv == "manager" || $login_priv == "admin" }
  <td><img src="{ $theme_root }/images/user.png" />&nbsp;##OWNER##</td>
  { /if }
  <td><img src="{ $theme_root }/images/clock.png" />&nbsp;##CREATED##</td>
  <td><img src="{ $theme_root }/images/clock_red.png" />&nbsp;##EXPIRES##</td>
  <td><img src="{ $theme_root }/images/email.png" />&nbsp;##NOTIFIED##</td>
  <td><img src="{ $theme_root }/images/action.png" />&nbsp;##ACTIONS##</td>
 </tr>

{ if $user_has_buckets }

{ bucket_list }
 <tr onmouseover="setBackGrdColor(this, 'mouseover');" onmouseout="setBackGrdColor(this, 'mouseout');">
  <td>
   <a href="javascript:ajax_show_content('buckets', '&mode=edit&idx={ $bucket_idx }');" title="##CLICK_EDIT_BUCKET##"><img src="{ $theme_root }/images/bucket_small.png" />&nbsp;{ $bucket_name }</a>
  </td>
  <td style="vertical-align: middle;">

   <!-- WebDAV support is enabled -->
   { if $bucket_via_dav }

    <!-- Browser is Internet Explorer -->
    { if $is_ie }
     <span class="ie_davlink" style="behavior: url(#default#httpFolder);" onclick="this.navigateFrame('{ $bucket_webdav_path }', '_blank');">
      <a href="{ $bucket_webdav_path }" onclick="return false;" title="##OPEN_VIA_WEBDAV##"><img src="{ $theme_root }/images/webdav.png" />&nbsp;WebDAV</a>
     </span>
    <!-- Every other browser -->
    { else }
      <a href="{ $bucket_webdav_path }" onclick="return false;" title="##OPEN_VIA_WEBDAV##"><img src="{ $theme_root }/images/webdav.png" />&nbsp;WebDAV</a>
    { /if }

    &nbsp;

   { /if }

   <!-- FTP support is enabled -->
   { if $bucket_via_ftp }
   <a href="{ $bucket_ftp_path }" target="_blank" title="##OPEN_VIA_FTP##"><img src="{ $theme_root }/images/ftp.png" />&nbsp;FTP</a>
   { /if }
  </td>

  { if $login_priv == "manager" || $login_priv == "admin" }
  <td>
   <a href="javascript:ajax_show_content('users', '&mode=edit&idx={ $bucket_owner_idx }');" title="##CLICK_EDIT_USER##"><img src="{ $theme_root }/images/user.png" />&nbsp;{ $bucket_owner }</a>
  </td>
  { /if }
  <td><img src="{ $theme_root }/images/clock.png" />&nbsp;{ $bucket_created }</td>
  <td><img src="{ $theme_root }/images/clock_red.png" />&nbsp;{ $bucket_expire }</td>
  <td><img src="{ $theme_root }/images/email.png" />&nbsp;{ if $bucket_notified == 'Y' } ##YES## { else } ##NO## { /if}</td>
  <td>
   <a href="javascript:js_delete_obj('buckets', 'main', '{ $bucket_idx }');" title="##CLICK_DELETE_BUCKET##"><img src="{ $theme_root }/images/delete.png" />&nbsp;##DELETE##</a>
   <a href="javascript:ajax_notify_bucket({ $bucket_idx });" title="##CLICK_SEND_EMAIL##"><img src="{ $theme_root }/images/mail.png" />&nbsp;##NOTIFY##</a>
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
    ##BLIST_EMPTY_BL##
   <br />
  </td>
 </tr>
{ /if }
</table>
