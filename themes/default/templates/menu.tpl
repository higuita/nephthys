   <div id="tabs" style="float: left;">
    <ul id="menutabs" class="shadetabs">
     <li><a href="rpc.php?action=get_content&id=main" class="selected" rel="buckets" id="main" title="Bucket listing">Start Page</a></li>
     <li><a href="rpc.php?action=get_content&id=profile&mode=edit" rel="profile" id="profile" title="Manage your profile">My Profile</a></li>
     <li><a href="rpc.php?action=get_content&id=addressbook" rel="addressbook" id="addressbook" title="Manage your address-book">Address-Book</a></li>
     { if $login_priv == "admin" }
     <li><a href="rpc.php?action=get_content&id=users" rel="users" id="users" title="Manage Nephthys users">Users</a></li>
     { /if }
     <li><a href="rpc.php?action=get_content&id=help" rel="help" id="help" title="Nephthys Help">Help</a></li>
     <li><a href="rpc.php?action=get_content&id=about" rel="about" id="about" title="Nephthys info">About</a></li>
     </ul>
   </div>
   <div id="login" style="float: right;">
    { if $login_name }
     Hello { $login_name}{if !$hide_logout }, <a href="javascript:js_logout()" title="Logout Nephthys and reset session">click to logout{ /if }</a>
    { /if }
   </div>
