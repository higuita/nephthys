   <div id="tabs" style="{ if ! $login_name } visibility: hidden; { /if } float: left;">
    <ul id="menutabs" class="shadetabs">
     <li><a href="rpc.php?action=get_content&id=main" class="selected" rel="buckets" title="Bucket listing">Buckets</a></li>
     <li><a href="rpc.php?action=get_content&id=profile&mode=edit" rel="users" title="Manage your profile">My Profile</a></li>
     { if $login_priv == "admin" }
     <li><a href="rpc.php?action=get_content&id=users" rel="users" title="Manage Nephthys users">Users</a></li>
     { /if }
     <li><a href="rpc.php?action=get_content&id=credits" rel="credits" title="Show credit page">Credits</a></li>
     </ul>
   </div>
   <div id="login" style="float: right;">
    { if $login_name }
     Hello { $login_name}, <a href="javascript:js_logout()" title="Logout Nephthys and reset session">click to logout</a>
    { /if }
   </div>
