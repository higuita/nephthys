   <div id="tabs" style="{ if ! $login_name } visibility: hidden; { /if } float: left;">
    <ul id="menutabs" class="shadetabs">
     <li><a href="rpc.php?action=get_content&id=main" class="selected" rel="buckets">Buckets</a></li>
     <li><a href="rpc.php?action=get_content&id=profile&mode=edit" rel="users">My Profile</a></li>
     { if $login_priv == "admin" }
     <li><a href="rpc.php?action=get_content&id=users" rel="users">Users</a></li>
     <li><a href="rpc.php?action=get_content&id=config" rel="config">Config</a></li>
     { /if }
     <li><a href="rpc.php?action=get_content&id=credits" rel="credits">Credits</a></li>
     </ul>
   </div>
   <div id="login" style="float: right;">
    { if $login_name }
     Hello { $login_name}, <a href="javascript:js_logout()">click to logout</a>
    { /if }
   </div>
