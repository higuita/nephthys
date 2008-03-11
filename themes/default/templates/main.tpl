 <table>
  <tr>
   <td style="width: 50%; text-align: center;">
    <a href="javascript:ajax_show_content('send');">I want to share some files.</a>
   </td>
   <td style="width: 50%; text-align: center;">
    <a href="javascript:ajax_show_content('receive');">Someone wants to send me files.</a></td>
   </td>
  </tr>
 </table>
 <br /><br /><br />
 <table class="withborder">
 <tr>
  <td>Name:</td>
  <td>Sender:</td>
  <td>Receiver:</td>
 </tr>
 { slot_list }
 <tr>
  <td>
   { $slot_name }
  </td>
  <td>
   { $slot_sender }
  </td>
  <td>
   { $slot_receiver }
  </td>
  <td>
   <a href="javascript:ajax_delete_slot({ $slot_idx });">Delete</a>
   <a href="javascript:ajax_notify_slot({ $slot_idx });">Notify</a>
  </td>
 </tr>
{ /slot_list }
 </table>
