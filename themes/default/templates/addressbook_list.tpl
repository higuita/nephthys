{ page_start header="##ADDRESS_BOOK##" subheader="##ABLIST_SUBHEADER##:" }

<table class="withborder">
 <tr class="subhead">
  <td>
   <img src="{ $theme_root }/images/contact.png" />&nbsp;##NAME##
   { sort_link module='addressbook' column='contact_name' order='asc' return='addressbook' }
   { sort_link module='addressbook' column='contact_name' order='desc' return='addressbook' }
  </td>
  { if $login_priv == "manager" || $login_priv == "admin" }
  <td>
   <img src="{ $theme_root }/images/user.png" />&nbsp;##OWNER##
   { sort_link module='addressbook' column='contact_owner' order='asc' return='addressbook' }
   { sort_link module='addressbook' column='contact_owner' order='desc' return='addressbook' }
  </td>
  { /if }
  <td><img src="{ $theme_root }/images/action.png" />&nbsp;##ACTIONS##</td>
 </tr>

{ if $user_has_contacts }

{ contact_list }
 <tr onmouseover="setBackGrdColor(this, 'mouseover');" onmouseout="setBackGrdColor(this, 'mouseout');">
  <td>
   <a href="javascript:ajax_show_content('addressbook', '&mode=edit&idx={ $contact_idx }');" title="##CLICK_EDIT_CONTACT##"><img src="{ $theme_root }/images/contact.png" />&nbsp;{ $contact_name }</a>
  </td>
  { if $login_priv == "manager" || $login_priv == "admin" }
  <td>
   <a href="javascript:ajax_show_content('users', '&mode=edit&idx={ $contact_owner_idx }');" title="##CLICK_EDIT_USER##"><img src="{ $theme_root }/images/user.png" />&nbsp;{ $contact_owner }</a>
  </td>
  { /if }
  <td>
   <a href="javascript:js_delete_obj('addressbook', 'addressbook', '{ $contact_idx }');" title="##CLICK_DELETE_CONTACT##"><img src="{ $theme_root }/images/delete.png" />&nbsp;##DELETE##</a>
  </td>
 </tr>
{ /contact_list }

{ else }

 <tr>
  { if $login_priv == "manager" || $login_priv == "admin" }
  <td colspan="3">
  { else }
  <td colspan="2">
  { /if }
   <br />
    ##ABLIST_EMPTY_AB##
   <br />
  </td>
 </tr>

{ /if }

</table>
