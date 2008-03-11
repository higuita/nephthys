{include file="header.tpl"}
 <body onload="init_nephthys();">
  <div id="box">
   <div id="header">
    <a href="javascript:ajax_show_content('main');">{ $product } { $version }</a>
   </div>
 
   <div id="content">
    {include file="main.tpl"}
   </div>
  </div>

{include file="footer.tpl"}
