{include file="header.tpl"}
 <body onload="init_nephthys();">

  <!-- surrounding box -->
  <div id="box">

   <div id="header">
    <a href="#" onclick="menutabs.expandit('main');"><img src="images/nephthys.png" />&nbsp;{ $product } { $version }</a><br>
   </div>

   <div id="menu">
    { include file="menu.tpl" }
   </div>

   <div id="content" style="clear: both;"></div>

  </div>
  <!-- /surrounding box -->

{include file="footer.tpl"}
