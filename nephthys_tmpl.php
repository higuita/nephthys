<?php

/***************************************************************************
 *
 * Copyright (c) by Andreas Unterkircher, unki@netshadow.at
 * All rights reserved
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 675 Mass Ave, Cambridge, MA 02139, USA.
 *
 ***************************************************************************/

class NEPHTHYS_TMPL extends Smarty {

   private $parent;

   public function __construct()
   {
      global $nephthys;

      $this->parent =& $nephthys;

      if(!file_exists($nephthys->cfg->base_path .'/themes/'. $nephthys->cfg->theme_name .'/templates')) {
         print "No templates found in ". $nephthys->cfg->base_path .'/themes/'. $nephthys->cfg->theme_name .'/templates';
         exit(1);
      }

      $this->Smarty();
      $this->template_dir = $nephthys->cfg->base_path .'/themes/'. $nephthys->cfg->theme_name .'/templates';
      $this->compile_dir  = $nephthys->cfg->base_path .'/templates_c';
      $this->config_dir   = $nephthys->cfg->base_path .'/smarty_config';
      $this->cache_dir    = $nephthys->cfg->base_path .'/smarty_cache';

      $this->assign('user_name', $nephthys->get_user_name($_SESSION['user_idx']));
      $this->assign('user_priv', $nephthys->get_user_priv($_SESSION['user_idx']));
      $this->assign('user_idx', $_SESSION['user_idx']);
      $this->assign('bucket_sender', $nephthys->get_users_email());
      $this->assign('page_title', $nephthys->cfg->page_title);
      $this->assign('product', $nephthys->cfg->product);
      $this->assign('version', $nephthys->cfg->version);
      $this->assign('template_path', 'themes/'. $nephthys->cfg->theme_name);
      $this->register_function("start_table", array(&$this, "smarty_startTable"), false);
      $this->register_function("page_end", array(&$this, "smarty_page_end"), false);
      $this->register_function("import_bucket_list", array(&$this, "smarty_import_bucket_list"), false);
      $this->register_function("expiration_list", array(&$this, "smarty_expiration_list"), false);

   } // __construct()

   public function show($template)
   {
      $this->display($template);

   } // show()

   public function smarty_startTable($params, &$smarty)
   {
      $this->assign('title', $params['title']);
      $this->assign('icon', $params['icon']);
      $this->assign('alt', $params['alt']);
      $this->show('start_table.tpl');

   } // smarty_function_startTable()

   public function smarty_page_end($params, &$smarty)
   {
      if(isset($params['focus_to'])) {
         $this->assign('focus_to', $params['focus_to']);
      }

      $this->show('page_end.tpl');

   } // smarty_function_startTable()

   public function smarty_import_bucket_list()
   {
      $bucket = new NEPHTHYS_BUCKETS();
      $bucket->showList();

   } // smarty_import_bucket_list()

   public function smarty_expiration_list($params, &$smarty)
   {
      $select = "<select name=\"". $params['name'] ."\">\n";

      foreach($this->parent->cfg->expirations as $expire) {

         list($days, $name, $require_priv) = split(";", $expire);

         if($require_priv == "user" ||
            ($require_priv != "user" && !$this->parent->has_user_priv())) {

            $select.= "<option value=\"". $days ."\"";
            if(isset($params['current']) && $params['current'] == $days)
               $select.= " selected=\"selected\"";
            $select.= ">". $name."</option>\n";
         }
      }

      $select.= "</select>\n";
      print $select;

   } //smarty_expiration_list()

}

?>
