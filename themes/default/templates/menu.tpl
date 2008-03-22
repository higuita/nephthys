   <div id="tabs" style="{ if ! $user_name } visibility: hidden; { /if } float: left;">
    <ul id="menutabs" class="shadetabs">
     <li><a href="rpc.php?action=get_content&id=main" class="selected" rel="buckets">Buckets</a></li>
     { if $user_priv == "admin" }
     <li><a href="rpc.php?action=get_content&id=users" rel="users">Users</a></li>
     <li><a href="rpc.php?action=get_content&id=groups" rel="groups">Groups</a></li>
     { /if }
     <li><a href="rpc.php?action=get_content&id=credits" rel="credits">Credits</a></li>
     </ul>
   </div>
   <div id="login" style="float: right;">
    { if $user_name }
     Hello { $user_name}, <a href="javascript:js_logout()">click to logout</a>
    { /if }
   </div>
