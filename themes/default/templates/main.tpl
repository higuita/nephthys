{ if !$login_email }
 <br />
 <font style='color: #ff0000;'>##MAIN_EMAIL_NOT_SET##</font>
 <br />
{ /if }

{ page_start header="##START_PAGE##" subheader="##MAIN_SUBHEADER##:" }

<div id="chooser" style="float: left;">
 <a href="javascript:ajax_show_content('buckets', '&mode=send');" title="##SHARE_SOME_FILES##."><img src="{ $theme_root }/images/files_from_user.png" class="imgborder" /><br />##SHARE_I_WANT##.</a>
</div>
<div id="chooser" style="float: right;">
 <a href="javascript:ajax_show_content('buckets', '&mode=receive');" title="##SHARE_SOME_FILES##."><img src="{ $theme_root }/images/files_to_user.png" class="imgborder" /><br />##SHARE_SOMEONE_WANT##.</a>
</div>

<br class="cb" />
<br class="cb" />
<hr />
<br class="cb" />

{ page_start header="##MAIN_BL_HEADER##" subheader="##MAIN_BL_SUBHEADER##:" }

{ import_bucket_list }

{ page_end }

   { if $login_priv == "manager" || $login_priv == "admin" }
   <div id="diskuseage" class="diskusage">##DISK_USED##: { $disk_used } / ##DISK_FREE##: { $disk_free }</div>
   { /if }
