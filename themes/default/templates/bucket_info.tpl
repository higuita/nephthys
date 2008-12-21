<br />
This bucket contains:<br />
<br />
<img src='{ $theme_root }/images/items.png' />&nbsp;{ $count_files} files<br />
<img src='{ $theme_root }/images/directories.png' />&nbsp;{ $count_dirs} directories<br />
<br />
with a total size of { $bucket_size }.
{ if $bucket_last_mod }
It was modified last on { $bucket_last_mod }.<br />
{ else }
It has not been modified yet.<br />
{ /if }
