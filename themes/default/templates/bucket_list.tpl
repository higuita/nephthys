<table class="withborder">
 <tr class="subhead">
  <td>
   <img src="{ $theme_root }/images/bucket_small.png" />&nbsp;##NAME##
   { sort_link module='buckets' column='bucket_name' order='asc' }
   { sort_link module='buckets' column='bucket_name' order='desc' }
  </td>
  <td><img src="{ $theme_root }/images/open.png" />&nbsp;##BLIST_COL_2_NAME##</td>
  { if $login_priv == "manager" || $login_priv == "admin" }
  <td>
   <img src="{ $theme_root }/images/user.png" />&nbsp;##OWNER##
   { sort_link module='buckets' column='bucket_owner' order='asc' }
   { sort_link module='buckets' column='bucket_owner' order='desc' }
  </td>
  { /if }
  <td>
   <img src="{ $theme_root }/images/clock.png" />&nbsp;##CREATED##
   { sort_link module='buckets' column='bucket_created' order='asc' }
   { sort_link module='buckets' column='bucket_created' order='desc' }
  </td>
  <td>
   <img src="{ $theme_root }/images/clock_red.png" />&nbsp;##EXPIRES##
   { sort_link module='buckets' column='bucket_expire' order='asc' }
   { sort_link module='buckets' column='bucket_expire' order='desc' }
  </td>
  <td>
   <img src="{ $theme_root }/images/email.png" />&nbsp;##NOTIFIED##
   { sort_link module='buckets' column='bucket_notified' order='asc' }
   { sort_link module='buckets' column='bucket_notified' order='desc' }
  </td>
  <td><img src="{ $theme_root }/images/action.png" />&nbsp;##ACTIONS##</td>
 </tr>

{ if $user_has_buckets }

{ bucket_list }
 <tr onmouseover="setBackGrdColor(this, 'mouseover');" onmouseout="setBackGrdColor(this, 'mouseout');">
  <td>
   <a href="#" onclick="ajax_show_content('buckets', '&mode=edit&idx={ $bucket_idx }');" title="##CLICK_EDIT_BUCKET##"><img src="{ $theme_root }/images/bucket_small.png" />&nbsp;{ $bucket_name }</a>
   <a id='bucketinfo{ $bucket_idx }' onclick="ajax_get_bucket_info({ $bucket_idx });" title="Click to show details about this bucket"><img src="{ $theme_root }/images/info.png" /></a>
  </td>
  <td style="vertical-align: middle;">

   <!-- WebDAV support is enabled -->
   { if $bucket_via_dav }

    <!-- Browser is Internet Explorer, but not on Vista or newer -->
    { if $is_ie and !$is_vista }
     <span class="ie_davlink" style="behavior: url(#default#httpFolder);" onclick="this.navigateFrame('{ $bucket_webdav_path }', '_blank');">
      <a href="{ $bucket_webdav_path }" onclick="return false;" title="##OPEN_VIA_WEBDAV##"><img src="{ $theme_root }/images/webdav.png" />&nbsp;WebDAV</a>
     </span>
    <!-- Browser is Internet Explorer, on Vista or newer -->
    { elseif $is_ie and $is_vista }
      <a href="{ $bucket_webdav_path_vista }" onclick="return false;" title="##OPEN_VIA_WEBDAV##"><img src="{ $theme_root }/images/webdav.png" />&nbsp;WebDAV</a>
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
   <a href="#" onclick="ajax_show_content('users', '&mode=edit&idx={ $bucket_owner_idx }');" title="##CLICK_EDIT_USER##"><img src="{ $theme_root }/images/user.png" />&nbsp;{ $bucket_owner }</a>
  </td>
  { /if }
  <td><img src="{ $theme_root }/images/clock.png" />&nbsp;{ $bucket_created }</td>
  <td><img src="{ $theme_root }/images/clock_red.png" />&nbsp;{ $bucket_expire }</td>
  <td><img src="{ $theme_root }/images/email.png" />&nbsp;{ if $bucket_notified == 'Y' } ##YES## { else } ##NO## { /if}</td>
  <td>
   <a href="#" onclick="js_delete_obj('buckets', 'main', '{ $bucket_idx }');" title="##CLICK_DELETE_BUCKET##"><img src="{ $theme_root }/images/delete.png" />&nbsp;##DELETE##</a>
   <a href="#" onclick="ajax_notify_bucket({ $bucket_idx });" title="##CLICK_SEND_EMAIL##"><img src="{ $theme_root }/images/mail.png" />&nbsp;##NOTIFY##</a>
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
