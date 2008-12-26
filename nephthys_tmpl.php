<?php

/***************************************************************************
 *
 * Nephthys - file sharing management
 * Copyright (c) by Andreas Unterkircher, unki@netshadow.at
 *
 *  This file is part of Nephthys.
 *
 *  Nephthys is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  Nephthys is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with Nephthys. If not, see <http://www.gnu.org/licenses/>.
 *
 ***************************************************************************/

class NEPHTHYS_TMPL extends Smarty {

   private $parent;

   public function __construct()
   {
      global $nephthys;

      $this->_translationTable        = Array();
      $this->_loadedTranslationTables = Array();

      $this->parent =& $nephthys;

      if(!file_exists($nephthys->cfg->base_path .'/themes/'. $nephthys->cfg->theme_name .'/templates')) {
         $this->parent->_error("No templates found in ". $nephthys->cfg->base_path .'/themes/'. $nephthys->cfg->theme_name .'/templates');
         exit(1);
      }

      // for debugging - disable Smarty caching
      //$this->caching = 0;
      //$this->force_compile = true;

      $this->Smarty();

      $this->template_dir = $nephthys->cfg->base_path .'/themes/'. $nephthys->cfg->theme_name .'/templates';
      $this->compile_dir  = $nephthys->cfg->tmpl_path .'/templates_c';
      $this->config_dir   = $nephthys->cfg->tmpl_path .'/smarty_config';
      $this->cache_dir    = $nephthys->cfg->tmpl_path .'/smarty_cache';
      $this->theme_root   = $nephthys->cfg->web_path .'themes/'. $nephthys->cfg->theme_name;

      if(isset($_SESSION['login_idx']) && is_numeric($_SESSION['login_idx'])) {
         $this->assign('login_name', $nephthys->get_user_name($_SESSION['login_idx']));
         $this->assign('login_priv', $nephthys->get_user_priv($_SESSION['login_idx']));
         $this->assign('login_idx', $_SESSION['login_idx']);
      }

      $this->assign('theme_root', $this->theme_root);
      $this->assign('bucket_sender', $nephthys->get_my_email());
      $this->assign('page_title', $nephthys->cfg->page_title);
      $this->assign('product', $nephthys->cfg->product);
      $this->assign('version', $nephthys->cfg->version);
      $this->assign('db_version', $nephthys->cfg->db_version);
      $this->assign('bucket_via_dav', $nephthys->cfg->bucket_via_dav);
      $this->assign('bucket_via_ftp', $nephthys->cfg->bucket_via_ftp);
      $this->assign('bucket_via_http_upload', $nephthys->cfg->bucket_via_http_upload);
      $this->assign('template_path', 'themes/'. $nephthys->cfg->theme_name);
      $this->register_function("page_start", array(&$this, "smarty_page_start"), false);
      $this->register_function("page_end", array(&$this, "smarty_page_end"), false);
      $this->register_function("save_button", array(&$this, "smarty_save_button"), false);
      $this->register_function("import_bucket_list", array(&$this, "smarty_import_bucket_list"), false);
      $this->register_function("expiration_list", array(&$this, "smarty_expiration_list"), false);
      $this->register_function("language_list", array(&$this, "smarty_language_list"), false);
      $this->register_function("owner_list", array(&$this, "smarty_owner_list"), false);
      $this->register_function("sort_link", array(&$this, "smarty_sort_link"), false);

      $this->register_postfilter(array(&$this, "smarty_prefilter_i18n"));

   } // __construct()

   public function smarty_page_start($params, &$smarty)
   {
      if(isset($params['header']))
         $this->assign('header', $params['header']);
      if(isset($params['subheader']))
         $this->assign('subheader', $params['subheader']);

      return $this->fetch('page_start.tpl');

   } // smarty_function_page_start()

   public function smarty_page_end($params, &$smarty)
   {
      if(isset($params['focus_to'])) {
         $this->assign('focus_to', $params['focus_to']);
      }

      return $this->fetch('page_end.tpl');

   } // smarty_function_startTable()

   public function smarty_save_button($params, &$smarty)
   {
      if(isset($params['text'])) {
         $this->assign('text', $params['text']);
      }

      return $this->fetch('save_button.tpl');

   } // smarty_function_startTable()

   /**
    * output sort link
    *
    * this function outputs the sort-links used to trigger
    * the javascript function update_sort_order() to alternate
    * the column-sort-ordering.
    */
   public function smarty_sort_link($params, &$smarty)
   {
      if(!isset($params['module']) ||
         !isset($params['column']) ||
         !isset($params['order'])) {

         $smarty->trigger_error('assign: module, column and order are required parameters');
         
      }

      $module = $params['module'];
      $column = $params['column'];
      $order  = $params['order'];

      $string = "<a href=\"#\" onclick=\"update_sort_order("
         ."'". $module ."', "
         ."'". $column ."', "
         ."'". $order ."'";

      if(isset($params['return'])) {
         $string.= ", "
            ."'". $params['return'] ."'";
      }

      $string.= ");\" title=\"";

      if($order == 'asc') {
         $string.= $this->parent->_("##ASCENDING##");
      }
      elseif($order == 'desc') {
         $string.= $this->parent->_("##DESCENDING##");
      }

      $string.= "\"><img src=\"";
      
      if($order == 'asc') {
         if(isset($_SESSION['sort_order'][$module]) &&
            $_SESSION['sort_order'][$module]['column'] == $column &&
            $_SESSION['sort_order'][$module]['order'] == "asc") {

            $string.= $this->theme_root ."/images/sort_asc_sel.png";
         }
         else {

            $string.= $this->theme_root ."/images/sort_asc.png";
         }
      }
      elseif($order == 'desc') {
         if(isset($_SESSION['sort_order'][$module]) &&
            $_SESSION['sort_order'][$module]['column'] == $column &&
            $_SESSION['sort_order'][$module]['order'] == "desc") {

            $string.= $this->theme_root ."/images/sort_desc_sel.png";
         }
         else {

            $string.= $this->theme_root ."/images/sort_desc.png";
         }
      }

      $string.= "\" /></a>";

      return $string;

   } // smarty_sort_link()

   public function smarty_import_bucket_list()
   {
      $bucket = new NEPHTHYS_BUCKETS();
      return $bucket->showList();

   } // smarty_import_bucket_list()

   public function smarty_expiration_list($params, &$smarty)
   {
      $select = "<select name=\"". $params['name'] ."\">\n";

      foreach($this->parent->cfg->expirations as $expire) {

         list($days, $name, $require_priv) = preg_split("/;/", $expire);

         /* "user" privileged users are not allowed to create long-time
            existing buckets. Only if the got the right assigned by an
            admin.

            if the expiry-entry requires higher privileges...
             ... and the current user has no higher privileges
             ... and his is not equipped with the long-time bucket priv
            then go to the next entry...
         */
         if($require_priv != "user" && (
               $this->parent->check_privileges('user') &&
               !$this->parent->has_bucket_privileges()
            )) {

            continue;
         }

         /* if translation is requested, try to get it */
         if(preg_match('/(##.+?##)/', $name, $period)) {

            $period = $this->parent->_($period[0]);
            $name = preg_replace('/##.+?##/', $period, $name);
         }

         $select.= "<option value=\"". $days ."\"";
         if(isset($params['current']) && $params['current'] == $days)
            $select.= " selected=\"selected\"";
         $select.= ">". $name."</option>\n";

      }

      $select.= "</select>\n";
      return $select;

   } //smarty_expiration_list()

   public function smarty_owner_list($params, &$smarty)
   {
      $users = $this->parent->db->db_query("
         SELECT *
         FROM nephthys_users
         WHERE user_active='Y'
         ORDER BY user_name ASC
      ");

      $select = "<select name=\"". $params['name'] ."\">\n";

      while($user = $users->fetchRow()) {

         $select.= "<option value=\"". $user->user_idx ."\"";
         if(isset($params['current']) && $params['current'] == $user->user_idx)
            $select.= " selected=\"selected\"";
         $select.= ">". $user->user_name."</option>\n";
      }

      $select.= "</select>\n";
      return $select;

   } //smarty_owner_list()

   public function smarty_language_list($params, &$smarty)
   {
      $select = "<select name=\"". $params['name'] ."\">\n";

      foreach($this->parent->cfg->avail_langs as $locale => $lang) {

         $select.= "<option value=\"". $locale ."\"";
         if(isset($params['current']) && $params['current'] == $locale)
            $select.= " selected=\"selected\"";
         $select.= ">". $lang."</option>\n";

      }

      $select.= "</select>\n";
      return $select;

   } //smarty_language_list()

   public function fetch($_smarty_tpl_file, $_smarty_cache_id = null, $_smarty_compile_id = null, $_smarty_display = false)
   {
      // We need to set the cache id and the compile id so a new script will be
      // compiled for each language. This makes things really fast ;-)
      $_smarty_compile_id = $this->parent->get_language().'-'.$_smarty_compile_id;
      $_smarty_cache_id = $_smarty_compile_id;

      // Now call parent method
      return parent::fetch( $_smarty_tpl_file, $_smarty_cache_id, $_smarty_compile_id, $_smarty_display );

   } // fetch()

   /**
    * smarty_prefilter_i18n()
    * This function takes the language file, and rips it into the template
    *
    * @param $tpl_source
    * @return
    **/
   public function smarty_prefilter_i18n($tpl_source, &$smarty)
   {
      // Now replace the matched language strings with the entry in the file
      return preg_replace_callback('/##(.+?)##/', array(&$this, '_compile_lang'), $tpl_source);

   } // smarty_prefilter_i18n()

   /**
    * _compile_lang
    * Called by smarty_prefilter_i18n function it processes every language
    * identifier, and inserts the language string in its place.
    *
    */
   public function _compile_lang($key)
   {
      return $this->parent->get_translation($key[1]);

   } // _compile_lang()

} // class SmartyML

// vim: set filetype=php expandtab softtabstop=3 tabstop=3 shiftwidth=3 autoindent smartindent:
?>
