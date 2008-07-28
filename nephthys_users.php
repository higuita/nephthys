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

class NEPHTHYS_USERS {

   private $db;
   private $parent;
   private $tmpl;

   /**
    * NEPHTHYS_USERS constructor
    *
    * Initialize the NEPHTHYS_USERS class
    */
   public function __construct()
   {
      global $nephthys;
      $this->parent =& $nephthys;
      $this->db =& $nephthys->db;
      $this->tmpl =& $nephthys->tmpl;

   } // __construct()
  
   /* interface output */
   public function show()
   {
      if(!$this->parent->is_logged_in() || $this->parent->check_privileges('user')) {
         print $this->_("##NOT_ALLOWED##");
         return 0;
      }

      if(!isset($_GET['mode'])) 
         $_GET['mode'] = "show";
      if(!isset($_GET['idx']) ||
         (isset($_GET['idx']) && !is_numeric($_GET['idx'])))
         $_GET['idx'] = 0;

      switch($_GET['mode']) {
         default:
         case 'show':
            $this->showList();
            break;
         case 'new':
         case 'edit':
            $this->showEdit($_GET['idx']);
            break;
      }

   } // show()

   private function showList()
   {
      $this->avail_users = Array();
      $this->users = Array();

      $cnt_users = 0;

      $res_users = $this->db->db_query("
         SELECT *
         FROM nephthys_users
         ORDER BY user_name ASC
      ");
	
      while($user = $res_users->fetchrow()) {
         $this->avail_users[$cnt_users] = $user->user_idx;
         $this->users[$user->user_idx] = $user;
         $cnt_users++;
      }

      $this->tmpl->register_block("user_list", array(&$this, "smarty_user_list"));
      $this->tmpl->show("users_list.tpl"); 

   } // showList()

   /**
    * display interface to create or edit users
    */
   private function showEdit($idx)
   {
      /* If authentication is enabled, check permissions */
      if(!$this->parent->is_logged_in() || $this->parent->check_privileges('user')) {
         print $this->_("##NOT_ALLOWED##");
         return 0;
      }

      if($idx != 0) {
         $user = $this->db->db_fetchSingleRow("
            SELECT *
            FROM nephthys_users
            WHERE
               user_idx='". $idx ."'
         ");

         $this->tmpl->assign('user_idx', $idx);
         $this->tmpl->assign('user_name', $user->user_name);
         $this->tmpl->assign('user_full_name', $user->user_full_name);
         $this->tmpl->assign('user_email', $user->user_email);
         $this->tmpl->assign('user_priv', $user->user_priv);
         $this->tmpl->assign('user_priv_expire', $user->user_priv_expire);
         $this->tmpl->assign('user_active', $user->user_active);

      }
      else {
         $this->tmpl->assign('user_active', 'Y');
      }
   
      $this->tmpl->show("users_edit.tpl");

   } // showEdit()
     
   /** 
    * store user values
    */
   public function store()
   {
      if(!$this->parent->is_logged_in() || $this->parent->check_privileges('user')) {
         return $this->_("##NOT_ALLOWED##");
      }

      isset($_POST['user_new']) && $_POST['user_new'] == 1 ? $new = 1 : $new = NULL;

      if(!isset($_POST['user_name']) || $_POST['user_name'] == "") {
         return $this->_("##FAILURE_ENTER_USERNAME##");
      }
      if(isset($new) && $this->parent->check_user_exists($_POST['user_name'])) {
         return $this->_("##FAILURE_USER_NOT_EXISTS##");
      }
      if($_POST['user_pass1'] == "") {
         return $this->_("##FAILURE_EMPTY_PASSWORD##");
      }
      if($_POST['user_pass1'] != $_POST['user_pass2']) {
         return $this->_("##FAILURE_PASSWORD_NOT_MATCH##");
      }	       
      if(!isset($_POST['user_email']) || $_POST['user_email'] == "") {
         return $this->_("##FAILURE_ENTER_EMAIL##");
      }
      if(!$this->parent->validate_email($_POST['user_email'])) {
         return $this->_("##FAILURE_ENTER_VALID_EMAIL##");
      }
 
      if(isset($new)) {

         $sth = $this->db->db_prepare("
            INSERT INTO nephthys_users (
               user_idx, user_name, user_full_name, user_pass,
               user_email, user_priv, user_priv_expire, user_active
            ) VALUES (
               NULL, ?, ?, ?,
               ?, ?, ?, ?
            )
         ");

         $this->db->db_execute($sth, array(
            $_POST['user_name'],
            $_POST['user_full_name'],
            sha1($_POST['user_pass1']),
            $_POST['user_email'],
            $_POST['user_priv'],
            $_POST['user_priv_expire'],
            $_POST['user_active'],
         ));

      }
      else {

         $sth = $this->db->db_prepare("
            UPDATE nephthys_users
            SET
               user_name=?,
               user_full_name=?,
               user_email=?,
               user_priv=?,
               user_priv_expire=?,
               user_active=?
            WHERE
               user_idx=?
         ");

         $this->db->db_execute($sth, array(
            $_POST['user_name'],
            $_POST['user_full_name'],
            $_POST['user_email'],
            $_POST['user_priv'],
            $_POST['user_priv_expire'],
            $_POST['user_active'],
            $_POST['user_idx'],
         ));

         if($_POST['user_pass1'] != " nochangeMS ") {

            $sth = $this->db->db_prepare("
               UPDATE nephthys_users
               SET
                  user_pass=?
               WHERE
                  user_idx=?
            ");

            $this->db->db_execute($sth, array(
               sha1($_POST['user_pass1']),
               $_POST['user_idx'],
            ));

         }
      }
		  
      return "ok";

   } // store()

   /**
    * delete user
    */
   public function delete()
   {
      if(!$this->parent->is_logged_in() || $this->parent->check_privileges('user')) {
         return $this->_("##NOT_ALLOWED##");
      }

      if(isset($_POST['idx']) && is_numeric($_POST['idx'])) {
         $idx = $_POST['idx'];

         $this->db->db_query("
            DELETE FROM nephthys_users
            WHERE
               user_idx='". $idx ."'
         ");

         return "ok";
	    }

      return "unkown error";
   
   } // delete()

   /**
    * toggle user active/inactive
    */
   public function toggleStatus()
   {
      if(!$this->parent->is_logged_in() || $this->parent->check_privileges('user')) {
         return $this->_("##NOT_ALLOWED##");
      }

      if(isset($_POST['idx']) && is_numeric($_POST['idx'])) {
         if($_POST['to'] == 1)
            $new_status='Y';
         else
            $new_status='N';

         $this->db->db_query("
            UPDATE nephthys_users
            SET
               user_active='". $new_status ."'
            WHERE
               user_idx='". $_POST['idx'] ."'");

         return "ok";
      }
   
      return "unkown error";

   } // toggleStatus()

   /**
    * template function which will be called from the user listing template
    */
   public function smarty_user_list($params, $content, &$smarty, &$repeat)
   {
      $index = $this->tmpl->get_template_vars('smarty.IB.user_list.index');
      if(!$index) {
         $index = 0;
      }

      if($index < count($this->avail_users)) {

         $user_idx = $this->avail_users[$index];
         $user =  $this->users[$user_idx];

         $this->tmpl->assign('user_idx', $user_idx);
         $this->tmpl->assign('user_name', $user->user_name);
         $this->tmpl->assign('user_active', $user->user_active);

         $index++;
         $this->tmpl->assign('smarty.IB.user_list.index', $index);
         $repeat = true;
      }
      else {
         $repeat =  false;
      }

      return $content;

   } // smarty_user_list()

} // class NEPHTHYS_USERS

?>
