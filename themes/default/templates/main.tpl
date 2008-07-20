{ if !$login_email }
 <br />
 <font style='color: #ff0000;'>Your E-Mail address is not set. You can do this in the "My Profile" tab.</font>
 <br />
{ /if }

{ page_start header="Start Page" subheader="Choose from the following options:" }

<div id="chooser" style="float: left;">
 <a href="javascript:ajax_show_content('buckets', '&mode=send');" title="Create a bucket to share some files."><img src="{ $theme_root }/images/files_from_user.png" class="imgborder" /><br />I want to share some files.</a>
</div>
<div id="chooser" style="float: right;">
 <a href="javascript:ajax_show_content('buckets', '&mode=receive');" title="Create a bucket to share some files."><img src="{ $theme_root }/images/files_to_user.png" class="imgborder" /><br />Someone wants to send me files.</a>
</div>

<br class="cb" />
<br class="cb" />
<hr />
<br class="cb" />

{ page_start header="Bucket List" subheader="The following buckets are currently available:" }

{ import_bucket_list }

{ page_end }

   { if $login_priv == "manager" || $login_priv == "admin" }
   <div id="diskuseage" class="diskusage">Used: { $disk_used } / Free: { $disk_free }</div>
   { /if }
