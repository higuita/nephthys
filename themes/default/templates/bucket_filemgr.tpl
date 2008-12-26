<div id="floatingwindow" style="padding: 5px;">
{ page_start header="##FILE_MANAGER_TITLE##" subheader=$bucket_name }
<table class="withborder">
 <tr>
  <td width="70%">
   <table width="100%">
    <tr class="subhead">
     <td colspan="5">
      Position: { file_breadcrumb_bar }
     </td>
    </tr>
    <tr>
     <td colspan="2" style="text-align: left;">
      <input type="button" onclick="filemgr_update();" value="Refresh" />
     </td>
     <td colspan="3" style="text-align: right;">
      <input type="text" id="mkdir_name" size=10 />
      <input type="button" onclick="filemgr_mkdir();" value="Mkdir" />
     </td>
    </tr>
    <tr>
     <td colspan="5">&nbsp;</td>
    </tr>
    <tr>
     <td>Name</td>
     <td>Size</td>
     <td>Last modified</td>
     <td>Details</td>
     <td>Action</td>
    </tr>
    { file_list }
    <tr>
     <td>
     { if $item_type == 'dir' }
      <a href="#" onclick="filemgr_chdir('{ $item_name }');">{ $item_name }</a></td>
     { else }
      { $item_name }
     { /if }
     </td>
     <td>{ $item_size }</td>
     <td>{ $item_lastm }</td>
     <td>{ $item_details }</td>
     <td>
      <a href="#" onclick="filemgr_delete('{ $item_name }', '{ $item_path }');">Del</a>
     </td>
    </tr>
    { /file_list}
   </table>
  </td>
  <td width="30%">
   <table width="100%">
    <tr class="subhead">
     <td colspan="5">
      Upload:
     </td>
    </tr>
    <tr>
     <td colspan="5">
      <input type="file" name="bucket_upload" id="bucket_upload" />
      <input type="button" value="Upload" onclick="do_upload();" />
     </td>
    </tr>
   </table>
  </td>
 </tr>
</table>
</div>
