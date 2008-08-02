<form action="rpc.php?action=store" id="buckets" onsubmit="return js_create_bucket(this, 'buckets'); return false;" method="post">
<input type="hidden" name="module" value="buckets" />
<input type="hidden" name="mode" value="modify" />
<input type="hidden" name="bucketmode" value="receive" />
<input type="hidden" name="bucket_new" value="1" />

{ page_start header="##SB_HEADER##" subheader="##SB_SUBHEADER##" }

<table>
 <tr>
  <td class="miniheader">##NAME##:</td>
 </tr>
 <tr>
  <td>{ $bucket_name }</td>
 </tr>
 <tr>
  <td>&nbsp;</td>
 </tr>
 { if $bucket_receiver }
 <tr>
  <td class="miniheader">##BUCKET_RECEIVER##:</td>
 </tr>
 <tr>
  <td>
   { $bucket_receiver }
  </td>
 </tr>
 <tr>
  <td>&nbsp;</td>
 </tr>
 { /if }
 <tr>
  <td class="miniheader">##EXPIRES##:</td>
 </tr>
 <tr>
  <td>
  { if $bucket_expire == "never" }
   ##NEVER_EXPIRE##.</td>
  { else }
   { $bucket_expire }
  { /if }
  </td>
 </tr>
 <tr>
  <td>&nbsp;</td>
 </tr>
 <tr>
  <td class="miniheader">##ACCESS_BUCKET##:</td>
 </tr>

 <!-- WebDAV support is enabled -->
 { if $bucket_via_dav }
 <tr>
  <td style="padding-left: 15px;">

  <!-- Browser is Internet Explorer -->
  { if $is_ie }
   <span style="behavior: url(#default#httpFolder); cursor: pointer; font-size: 14px; color: #000000;" onclick="this.navigateFrame('{ $bucket_webdav_path }', '_blank');">
    <a href="{ $bucket_webdav_path }" onclick="return false;" title="##OPEN_VIA_WEBDAV##"><img src="{ $theme_root }/images/webdav.png" />&nbsp;WebDAV</a>
   </span>
  <!-- Every other browser -->
  { else }
    <a href="{ $bucket_webdav_path }" onclick="return false;" title="##OPEN_VIA_WEBDAV##"><img src="{ $theme_root }/images/webdav.png" />&nbsp;WebDAV</a>
  { /if }
  </td>
 </tr>
 { /if }

 <!-- FTP support is enabled -->
 { if $bucket_via_ftp }
 <tr>
  <td style="padding-left: 15px;">
   <a href="{ $bucket_ftp_path }" target="_blank" title="##OPEN_VIA_FTP##"><img src="{ $theme_root }/images/ftp.png" />&nbsp;FTP</a>
  </td>
 </tr>
 { /if }
 <tr>
  <td>&nbsp;</td>
 </tr>
 <tr>
  <td>&nbsp;</td>
 </tr>
 <tr>
  <td>##SB_ABOVE_LINKS##.</td>
 </tr>
 <tr>
  <td>&nbsp;</td>
 </tr>
 <tr>
  <td>&nbsp;</td>
 </tr>
 <tr>
  <td><a href="#" onclick="ajax_show_content('main'); return false;"><img src="{ $theme_root }/images/next.png">&nbsp;##GO_TO_START##</a></td>
 </tr>
</table>

<!-- set focus to the first input field -->
{ page_end focus_to='bucket_name' }
