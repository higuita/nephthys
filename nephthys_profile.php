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

class NEPHTHYS_PROFILE {

   private $db;
   private $parent;
   private $tmpl;

   /**
    * NEPHTHYS_PROFILE constructor
    *
    * Initialize the NEPHTHYS_PROFILE class
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
      if(!$this->parent->is_logged_in()) {
         $this->parent->_error($this->parent->_("##MANAGE_USERS##") ." - ". $this->parent->_("##NOT_ALLOWED##"));
         return 0;
      }

      if(!isset($_GET['mode'])) 
         $_GET['mode'] = "show";

      switch($_GET['mode']) {
         default:
         case 'edit':
            return $this->showEdit();
            break;
      }

   } // show()

   /**
    * display interface to edit profile settings
    */
   private function showEdit()
   {
      /* If authentication is enabled, check permissions */
      if(!$this->parent->is_logged_in()) {
         $this->parent->_error($this->parent->_("##MANAGE_USERS##") ." - ". $this->parent->_("##NOT_ALLOWED##"));
         return 0;
      }

      $user = $this->db->db_fetchSingleRow("
         SELECT *
         FROM nephthys_users
         WHERE
            user_idx='". $_SESSION['login_idx'] ."'
      ");

      $this->tmpl->assign('user_idx', $_SESSION['login_idx']);
      $this->tmpl->assign('user_name', $this->parent->unescape($user->user_name));
      $this->tmpl->assign('user_full_name', $this->parent->unescape($user->user_full_name));
      $this->tmpl->assign('user_email', $this->parent->unescape($user->user_email));
      $this->tmpl->assign('user_default_expire', $user->user_default_expire);
      $this->tmpl->assign('user_auto_created', $user->user_auto_created);
      $this->tmpl->assign('user_deny_chpwd', $user->user_deny_chpwd);
      $this->tmpl->assign('user_language', $user->user_language);

      return $this->tmpl->fetch("profile.tpl");

   } // showEdit()
     
   /** 
    * store user values
    */
   public function store()
   {
      if($this->parent->check_privileges('user') && isset($_POST['user_name'])) {
         return $this->parent->_("##FAILURE_CHANGE_LOGIN##");
      }
      if($this->parent->check_privileges('user') &&
         !$this->parent->is_auto_created($_SESSION['login_idx'])
         && isset($_POST['user_email'])) {
         return $this->parent->_("##FAILURE_CHANGE_EMAIL##");
      }

      if(!$this->parent->check_privileges('user') && (!isset($_POST['user_name']) ||
         empty($_POST['user_name']))) {
         return $this->parent->_("##FAILURE_ENTER_USERNAME##");
      }
      if(!$this->parent->is_deny_chpwd($_SESSION['login_idx']) && empty($_POST['user_pass1'])) {
         return $this->parent->_("##FAILURE_EMPTY_PASSWORD##");
      }
      /* it's not a must that the password needs to be available, as
         the user may not have the right to change its Nephthys password.
      */
      if(isset($_POST['user_pass1']) && isset($_POST['user_pass2']) &&
         $_POST['user_pass1'] != $_POST['user_pass2']) {
         return $this->parent->_("##FAILURE_PASSWORD_NOT_MATCH##");
      }

      /* user-privileged are not allowed to change their user-names */
      if(!$this->parent->check_privileges('user')) {

         $sth = $this->db->db_prepare("
            UPDATE nephthys_users
            SET
               user_name=?
            WHERE
               user_idx=?
         ");

         $this->db->db_execute($sth, array(
            $_POST['user_name'],
            $_POST['user_idx'],
         ));
      }

      /* handling email-address update. only privileged- or auto-created users
         are allowed to change their email address. Manually created users are
         not permitted to change their email address.
      */
      if(!$this->parent->check_privileges('user') ||
         $this->parent->is_auto_created($_SESSION['login_idx'])) {

         if(!isset($_POST['user_email']) || empty($_POST['user_email'])) {
            return $this->parent->_("##FAILURE_ENTER_EMAIL##");
         }
         if(!$this->parent->validate_email($_POST['user_email'])) {
            return $this->parent->_("##FAILURE_ENTER_VALID_EMAIL##");
         }

         $sth = $this->db->db_prepare("
            UPDATE nephthys_users
            SET
               user_email=?
            WHERE
               user_idx=?
         ");

         $this->db->db_execute($sth, array(
            $_POST['user_email'],
            $_POST['user_idx'],
         ));
      }

      /* update user's full name, default-expiry and langugage time */
      $sth = $this->db->db_prepare("
         UPDATE nephthys_users
         SET
            user_full_name=?,
            user_default_expire=?,
            user_language=?
         WHERE
            user_idx=?
      ");

      $this->db->db_execute($sth, array(
         $_POST['user_full_name'],
         $_POST['user_default_expire'],
         $_POST['user_language'],
         $_POST['user_idx'],
      ));

      if(isset($_POST['user_pass1'])) {

         /* if a password change was requested, change it here. */
         if($_POST['user_pass1'] != " nochangeMS " &&
            !$this->parent->is_deny_chpwd($_SESSION['login_idx'])) {

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

} // class NEPHTHYS_PROFILE

// vim: set filetype=php expandtab softtabstop=3 tabstop=3 shiftwidth=3 autoindent smartindent:
?>
