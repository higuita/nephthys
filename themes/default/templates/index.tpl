{include file="header.tpl"}
 <body onload="init_netphyths();">
 
  <form action="rpc.php?action=store" id="slots" onsubmit="saveForm(this, 'slots'); return false;" method="post">
  <input type="hidden" name="action" value="modify" />
  { if ! $slot_idx }
   {start_table icon=$icon_slots alt="slot icon" title="Create a new slot" }
   <input type="hidden" name="slot_new" value="1" />
  { else }
   {start_table icon=$icon_slots alt="protocol icon" title="Modify slot $slot_name" }
   <input type="hidden" name="slot_new" value="0" />
   <input type="hidden" name="namebefore" value="{ $slot_name }" />
   <input type="hidden" name="slot_idx" value="{ $slot_idx }" />
  { /if }
  <table style="width: 100%" class="withborder">

   Slot: <input type="text" name="slot_name" size="30" />
   <br />
   Sender: <input type="text" name="slot_sender" size="30" />
   <br />
   Receiver: <input type="text" name="slot_receiver" size="30" />
   <br />
   Vadlid for:
   <select name="slot_valid_till">

   </select>
   
   <br />
   Note: <textarea name="slot_note"></textarea>
   <br />

  </table>

{include file="footer.tpl"}
