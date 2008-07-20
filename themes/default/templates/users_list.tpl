{ page_start header="User - Management" }

<table class="withborder">
 <tr>
  <td style="text-align: center;" colspan="2">
   <img src="{ $theme_root }/images/user.png" alt="new icon" />
   <a href="javascript:ajax_show_content('users', '&mode=new');">Create a new User</a>
  </td>
 </tr>
 <tr>
  <td colspan="2">&nbsp;</td>
 </tr>
 <tr>
  <td><img src="{ $theme_root }/images/user.png" alt="user icon" />&nbsp;<i>Name</i></td>
  <td style="text-align: center;"><i>Options</i></td>
 </tr>
 { user_list }
 <tr onmouseover="setBackGrdColor(this, 'mouseover');" onmouseout="setBackGrdColor(this, 'mouseout');">
  <td>
   <img src="{ $theme_root }/images/user.png" alt="user icon" />
   <a href="javascript:ajax_show_content('users', '&mode=edit&idx={ $user_idx }');">{ $user_name }</a>
  </td>
  <td style="text-align: center;">
   <a href="javascript: js_delete_obj('users', 'users', '{ $user_idx }');" title="Delete"><img src="{ $theme_root }/images/delete.png" alt="delete icon" /></a>
   { if $user_active == 'Y' }
   <a href="javascript:js_toggle_status('users', 'users', '{ $user_idx }', '0');"><img src="{ $theme_root }/images/active.png" alt="active icon" /></a>
   { else }
   <a href="javascript:js_toggle_status('users', 'users', '{ $user_idx }', '1');"><img src="{ $theme_root }/images/inactive.png" alt="inactive icon" /></a>
   { /if }
  </td>
 </tr>
 { /user_list }
</table>

{ page_end }
