   <div id="tabs" style="float: left;">
    <ul id="menutabs" class="shadetabs">
     <li><a href="rpc.php?action=get_content&id=main" class="selected" rel="buckets" id="main" title="Bucket listing">##START_PAGE##</a></li>
     <li><a href="rpc.php?action=get_content&id=profile&mode=edit" rel="profile" id="profile" title="Manage your profile">##MY_PROFILE##</a></li>
     <li><a href="rpc.php?action=get_content&id=addressbook" rel="addressbook" id="addressbook" title="Manage your address-book">##ADDRESS_BOOK##</a></li>
     { if $login_priv == "admin" }
     <li><a href="rpc.php?action=get_content&id=users" rel="users" id="users" title="Manage Nephthys users">##USERS##</a></li>
     { /if }
     <li><a href="rpc.php?action=get_content&id=help" rel="help" id="help" title="Nephthys Help">##HELP##</a></li>
     <li><a href="rpc.php?action=get_content&id=about" rel="about" id="about" title="Nephthys info">##ABOUT##</a></li>
     </ul>
   </div>
   <div id="login" style="float: right;">
    { if $login_name }
     ##HELLO## { $login_name}{if !$hide_logout }, <a href="javascript:js_logout()" title="Logout Nephthys and reset session">##CLICK_LOGOUT##{ /if }</a>
    { /if }
   </div>
