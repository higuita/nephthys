{include file="header.tpl"}
 <body onload="init_nephthys();">
 
  <form action="rpc.php?action=store" id="slots" onsubmit="saveForm(this, 'slots'); return false;" method="post">
   <input type="hidden" name="module" value="slots" />
  { if ! $slot_idx }
   {start_table icon=$icon_slots alt="slot icon" title="Create a new slot" }
   <input type="hidden" name="slot_new" value="1" />
  { else }
   {start_table icon=$icon_slots alt="protocol icon" title="Modify slot $slot_name" }
   <input type="hidden" name="slot_new" value="0" />
   <input type="hidden" name="namebefore" value="{ $slot_name }" />
   <input type="hidden" name="slot_idx" value="{ $slot_idx }" />
  { /if }

  <table class="withborder">
   <tr>
    <td>Slot:</td>
    <td>
     <input type="text" name="slot_name" size="30" value="{ $slot_name }" />
    </td>
   </tr>
   <tr>
    <td>Sender:</td>
    <td>
     <input type="text" name="slot_sender" size="30" value="{ $slot_sender }" />
    </td>
   </tr>
   <tr>
    <td>Receiver:</td>
    <td>
     <input type="text" name="slot_receiver" size="30" value="{ $slot_receiver }" />
    </td>
   </tr>
   <tr>
    <td>Expire in:</td>
    <td>
     <select name="slot_expire">
      <option value="1">1 Day</option>
      <option value="3">3 Days</option>
      <option value="7">1 Week</option>
      <option value="31">1 Month</option>
      <option value="186">6 Months</option>
      <option value="365">1 Year</option>
      <option value="-1">never</option>
     </select>
    </td>
   </tr>
   <tr>
    <td>Note:</td>
    <td>
     <textarea name="slot_note">{ $slot_note }</textarea>
    </td>
   </tr>
   <tr>
    <td>&nbsp;</td>
    <td><input type="submit" value="Create" /></td>
   </tr>
  </table>
  </form>

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
 </tr>
{ /slot_list }
 </table>

{include file="footer.tpl"}
