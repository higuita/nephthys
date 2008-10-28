<div style="float: left;">
{ page_start header="##USER_HEADER##" subheader="##USER_SUBHEADER##" }
</div>
<div style="float: right;" class="new_action">
 <img src="{ $theme_root }/images/user.png" alt="new icon" />
 <a href="javascript:ajax_show_content('users', '&mode=new');">##USER_CREATE_NEW##</a>
</div>
<table class="withborder">
 <tr class="subhead">
  <td>
   <img src="{ $theme_root }/images/user.png" alt="user icon" />&nbsp;<i>##USERNAME##</i>
   { sort_link module='users' column='user_name' order='asc' return='users' }
   { sort_link module='users' column='user_name' order='desc' return='users' }
  </td>
  <td>
   <img src="{ $theme_root }/images/user.png" alt="user icon" />&nbsp;<i>##FULLNAME##</i>
   { sort_link module='users' column='user_full_name' order='asc' return='users' }
   { sort_link module='users' column='user_full_name' order='desc' return='users' }
  </td>
  <td>
   <img src="{ $theme_root }/images/user.png" alt="user icon" />&nbsp;<i>##PRIVILEGES##</i>
   { sort_link module='users' column='user_priv' order='asc' return='users' }
   { sort_link module='users' column='user_priv' order='desc' return='users' }
  </td>
  <td>
   <img src="{ $theme_root }/images/user.png" alt="user icon" />&nbsp;<i>##LASTLOGIN##</i>
   { sort_link module='users' column='user_last_login' order='asc' return='users' }
   { sort_link module='users' column='user_last_login' order='desc' return='users' }
  </td>
  <td>
   <img src="{ $theme_root }/images/action.png" alt="action icon" />&nbsp;<i>##ACTIONS##</i></td>
 </tr>
 { user_list }
 <tr onmouseover="setBackGrdColor(this, 'mouseover');" onmouseout="setBackGrdColor(this, 'mouseout');">
  <td>
   <img src="{ $theme_root }/images/user.png" alt="user icon" />
   <a href="javascript:ajax_show_content('users', '&mode=edit&idx={ $user_idx }');" title="##CLICK_EDIT_USER##">{ $user_name }</a>
  </td>
  <td>
   <img src="{ $theme_root }/images/user.png" alt="user icon" />
   <a href="javascript:ajax_show_content('users', '&mode=edit&idx={ $user_idx }');" title="##CLICK_EDIT_USER##">{ $user_full_name }</a>
  </td>
  <td><img src="{ $theme_root }/images/user.png" />&nbsp;{ $user_priv }</td>
  <td><img src="{ $theme_root }/images/clock.png" />&nbsp;{ $user_last_login }</td>
  <td>
   { if $user_active == 'Y' }
   <a href="javascript:js_toggle_status('users', 'users', '{ $user_idx }', '0');" title="##CLICK_DISABLE_USER##"><img src="{ $theme_root }/images/active.png" alt="active icon" />&nbsp;##ENABLED##</a>
   { else }
   <a href="javascript:js_toggle_status('users', 'users', '{ $user_idx }', '1');" title="##CLICK_ENABLE_USER##"><img src="{ $theme_root }/images/inactive.png" alt="inactive icon" />&nbsp;##DISABLED##</a>
   { /if }
   <a href="javascript: js_delete_obj('users', 'users', '{ $user_idx }');" title="##CLICK_DELETE_USER##"><img src="{ $theme_root }/images/delete.png" alt="delete icon" />&nbsp;##DELETE##</a>
  </td>
 </tr>
 { /user_list }
</table>

{ page_end }
