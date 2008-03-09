 <table>
  <tr>
   <td><a href="javascript:create_slot('send');">I want to share files with someone.</a></td>
   <td><a href="javascript:create_slot('receive');">Someone wants to share some files with me.</a></td>
  </tr>
 </table>
 <br /><br /><br />
 <table>
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
   <a href="javascript:notifySlot({ $slot_idx });">Notify</a>
  </td>
 </tr>
{ /slot_list }
 </table>
