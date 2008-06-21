{include file="header.tpl"}
 <body onload="init_nephthys();">

  <!-- overall box -->
  <div id="box">

   <div id="header">
    <a href="javascript:ajax_show_content('main');">{ $product } { $version }</a><br>
   </div>

   <div id="menu">
    { include file="menu.tpl" }
   </div>

   <div id="content" style="clear: both;"></div>

  </div>
  <!-- /overall box -->

{include file="footer.tpl"}
