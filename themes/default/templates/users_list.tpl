{ page_start header="##USER_HEADER##" }

<table class="withborder">
 <tr>
  <td style="text-align: center;" colspan="2">
   <img src="{ $theme_root }/images/user.png" alt="new icon" />
   <a href="javascript:ajax_show_content('users', '&mode=new');">##USER_CREATE_NEW##</a>
  </td>
 </tr>
 <tr>
  <td colspan="2">&nbsp;</td>
 </tr>
 <tr>
  <td><img src="{ $theme_root }/images/user.png" alt="user icon" />&nbsp;<i>##NAME##</i></td>
  <td style="text-align: center;"><i>##OPTIONS##</i></td>
 </tr>
 { user_list }
 <tr onmouseover="setBackGrdColor(this, 'mouseover');" onmouseout="setBackGrdColor(this, 'mouseout');">
  <td>
   <img src="{ $theme_root }/images/user.png" alt="user icon" />
   <a href="javascript:ajax_show_content('users', '&mode=edit&idx={ $user_idx }');" title="##CLICK_EDIT_USER##">{ $user_name }</a>
  </td>
  <td style="text-align: center;">
   <a href="javascript: js_delete_obj('users', 'users', '{ $user_idx }');" title="##CLICK_DELETE_USER##"><img src="{ $theme_root }/images/delete.png" alt="delete icon" /></a>
   { if $user_active == 'Y' }
   <a href="javascript:js_toggle_status('users', 'users', '{ $user_idx }', '0');" title="##CLICK_DISABLE_USER##"><img src="{ $theme_root }/images/active.png" alt="active icon" /></a>
   { else }
   <a href="javascript:js_toggle_status('users', 'users', '{ $user_idx }', '1');" title="##CLICK_ENABLE_USER##"><img src="{ $theme_root }/images/inactive.png" alt="inactive icon" /></a>
   { /if }
  </td>
 </tr>
 { /user_list }
</table>

{ page_end }
