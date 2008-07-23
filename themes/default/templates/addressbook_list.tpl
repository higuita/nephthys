{ page_start header="Address-Book" subheader="Manage your address-book contacts:" }

<table class="withborder">
 <tr class="subhead">
  <td><img src="{ $theme_root }/images/contact.png" />&nbsp;Email</td>
  { if $login_priv == "manager" || $login_priv == "admin" }
  <td><img src="{ $theme_root }/images/user.png" />&nbsp;Owner</td>
  { /if }
  <td><img src="{ $theme_root }/images/action.png" />&nbsp;Actions</td>
 </tr>

{ if $user_has_contacts }

{ contact_list }
 <tr onmouseover="setBackGrdColor(this, 'mouseover');" onmouseout="setBackGrdColor(this, 'mouseout');">
  <td>
   <a href="javascript:ajax_show_content('addressbook', '&mode=edit&idx={ $contact_idx }');" title="Click to edit this contact"><img src="{ $theme_root }/images/contact.png" />&nbsp;{ $contact_email }</a>
  </td>
  { if $login_priv == "manager" || $login_priv == "admin" }
  <td>
   <a href="javascript:ajax_show_content('users', '&mode=edit&idx={ $contact_owner_idx }');"><img src="{ $theme_root }/images/user.png" />&nbsp;{ $contact_owner }</a>
  </td>
  { /if }
  <td>
   <a href="javascript:js_delete_obj('addressbook', 'addressbook', '{ $contact_idx }');" title="Delete contact"><img src="{ $theme_root }/images/delete.png" />&nbsp;Delete</a>
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
   Currently your address-book does not contain any contacts. They will be added when you create buckets and have entered a receiver email address. Then this email address will be automatically added to your address-book.
   <br />
  </td>
 </tr>

{ /if }

</table>