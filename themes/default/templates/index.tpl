{include file="header.tpl"}
 <body onload="init_nephthys();">
  <div id="box">
   <div id="header">
    <a href="javascript:ajax_show_content('main');">{ $product } { $version }</a><br>
    { if $user_name }
     Hello { $user_name}, <a href="javascript:js_logout()">click to logout</a>
    { /if }
   </div>
 
   <ul id="menutabs" class="shadetabs">
   <li><a href="rpc.php?action=get_content&id=main" class="selected" rel="buckets">Buckets</a></li>
   <li><a href="rpc.php?action=get_content&id=users" rel="users">Users</a></li>
   <li><a href="rpc.php?action=get_content&id=groups" rel="groups">Groups</a></li>
   </ul>
   <div id="content">
    hier ist der content
    {* include file="main.tpl" *}
   </div>
  </div>

{include file="footer.tpl"}
