{ page_start header="Addressbook" subheader="Manage your addressbook contacts:" }

<table class="withborder">
 <tr class="subhead">
  <td><img src="images/contact_small.png" />&nbsp;Contact</td>
  { if $login_priv == "manager" || $login_priv == "admin" }
  <td><img src="images/user.png" />&nbsp;Owner</td>
  { /if }
  <td>Actions</td>
 </tr>

{ contact_list }
 <tr onmouseover="setBackGrdColor(this, 'mouseover');" onmouseout="setBackGrdColor(this, 'mouseout');">
  <td>
   <a href="javascript:ajax_show_content('addressbook', '&mode=edit&idx={ $contact_idx }');" title="Click to edit this contact"><img src="images/contact_small.png" />&nbsp;{ $contact_email }</a>
  </td>
  { if $login_priv == "manager" || $login_priv == "admin" }
  <td>
   <a href="javascript:ajax_show_content('users', '&mode=edit&idx={ $contact_owner_idx }');"><img src="images/user.png" />&nbsp;{ $contact_owner }</a>
  </td>
  { /if }
  <td>
   <a href="javascript:js_delete_obj('addressbook', 'addressbook', '{ $contact_idx }');" title="Delete contact"><img src="images/delete.png" />&nbsp;Delete</a>
  </td>
 </tr>
{ /contact_list }

</table>
