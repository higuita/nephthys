<form action="rpc.php?action=store" id="buckets" onsubmit="return js_create_bucket(this, 'buckets'); return false;" method="post">
<input type="hidden" name="module" value="buckets" />
<input type="hidden" name="mode" value="modify" />
<input type="hidden" name="bucketmode" value="receive" />
<input type="hidden" name="bucket_new" value="1" />

{ page_start header="Bucket successfully created" subheader="The bucket has been created for you." }

<table>
 <tr>
  <td class="miniheader">Name:</td>
 </tr>
 <tr>
  <td>{ $bucket_name }</td>
 </tr>
 <tr>
  <td>&nbsp;</td>
 </tr>
 { if $bucket_receiver }
 <tr>
  <td class="miniheader">Bucket receiver:</td>
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
  <td class="miniheader">Expires:</td>
 </tr>
 <tr>
  <td>
  { if $bucket_expire == "never" }
   This bucket will never expire.</td>
  { else }
   { $bucket_expire }
  { /if }
  </td>
 </tr>
 <tr>
  <td>&nbsp;</td>
 </tr>
 <tr>
  <td class="miniheader">You can access the bucket by the following ways to add files:</td>
 </tr>

 <!-- WebDAV support is enabled -->
 { if $bucket_via_dav }
 <tr>
  <td style="padding-left: 15px;">

  <!-- Browser is Internet Explorer -->
  { if $is_ie }
   <span style="behavior: url(#default#httpFolder); cursor: pointer; font-size: 14px; color: #000000;" onclick="this.navigateFrame('{ $bucket_webdav_path }', '_blank');">
    <a href="{ $bucket_webdav_path }" onclick="return false;" title="Open bucket via WebDAV"><img src="images/webdav.png" />&nbsp;WebDAV</a>
   </span>
  <!-- Every other browser -->
  { else }
    <a href="{ $bucket_webdav_path }" onclick="return false;" title="Open bucket via WebDAV"><img src="images/webdav.png" />&nbsp;WebDAV</a>
  { /if }
  </td>
 </tr>
 { /if }

 <!-- FTP support is enabled -->
 { if $bucket_via_ftp }
 <tr>
  <td style="padding-left: 15px;">
   <a href="{ $bucket_ftp_path }" target="_blank" title="Open bucket via FTP"><img src="images/ftp.png" />&nbsp;FTP</a>
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
  <td>The above links you can also find on the "Start Page" for later usuage.</td>
 </tr>
 <tr>
  <td>&nbsp;</td>
 </tr>
 <tr>
  <td>&nbsp;</td>
 </tr>
 <tr>
  <td><a href="#" onclick="ajax_show_content('main'); return false;"><img src="images/next.png">&nbsp;Go to Start Page</a></td>
 </tr>
</table>

<!-- set focus to the first input field -->
{ page_end focus_to='bucket_name' }
