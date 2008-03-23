<?php

/***************************************************************************
 *
 * Copyright (c) by Andreas Unterkircher, unki@netshadow.at
 * All rights reserved
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
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
         $this->parent->printError("<img src=\"". ICON_USERS ."\" alt=\"user icon\" />&nbsp;". _("Manage Users"), _("You do not have enough permissions to access this module!"));
         return 0;
      }

      if(!isset($_GET['mode'])) 
         $_GET['mode'] = "show";

      switch($_GET['mode']) {
         case 'edit':
            $this->showEdit($_GET['idx']);
            break;
      }

   } // show()

   /**
    * display interface to edit profile settings
    */
   private function showEdit($idx)
   {
      /* If authentication is enabled, check permissions */
      if(!$this->parent->is_logged_in()) {
         $this->parent->printError("<img src=\"". ICON_USERS ."\" alt=\"user icon\" />&nbsp;". _("Manage Users"), _("You do not have enough permissions to access this module!"));
         return 0;
      }

      $user = $this->db->db_fetchSingleRow("
         SELECT *
         FROM nephthys_users
         WHERE
            user_idx='". $_SESSION['login_idx'] ."'
      ");

      $this->tmpl->assign('user_idx', $_SESSION['login_idx']);
      $this->tmpl->assign('user_name', $user->user_name);
      $this->tmpl->assign('user_full_name', $user->user_full_name);
      $this->tmpl->assign('user_email', $user->user_email);

      $this->tmpl->show("profile.tpl");

   } // showEdit()
     
   /** 
    * store user values
    */
   public function store()
   {
      if($this->parent->check_privileges('user') && isset($_POST['user_name'])) {
         return _("You are not allowed to change your login name!");
      }
      if($this->parent->check_privileges('user') && isset($_POST['user_email'])) {
         return _("You are not allowed to change your email address!");
      }

      if(!$this->parent->check_privileges('user') && (!isset($_POST['user_name']) ||
         empty($_POST['user_name']))) {
         return _("Please enter a user name!");
      }
      if(empty($_POST['user_pass1'])) {
         return _("Empty passwords are not allowed!");
      }
      if($_POST['user_pass1'] != $_POST['user_pass2']) {
         return _("The two entered passwords do not match!");
      }	       
      if(!$this->parent->check_privileges('user') && (!isset($_POST['user_email']) ||
         empty($_POST['user_email']))) {
         return _("Please enter a email address!");
      }
      if(!$this->parent->check_privileges('user') &&
         !$this->parent->validate_email($_POST['user_email'])) {
         return _("Please enter a valid email address!");
      }

      if(!$this->parent->check_privileges('user')) {
         $this->db->db_query("
            UPDATE nephthys_users
            SET
               user_name='". $_POST['user_name'] ."',
               user_email='". $_POST['user_email'] ."'
            WHERE
               user_idx='". $_POST['user_idx'] ."'
         ");
      }

      $this->db->db_query("
         UPDATE nephthys_users
         SET
            user_full_name='". $_POST['user_full_name'] ."'
         WHERE
            user_idx='". $_POST['user_idx'] ."'
      ");

      if($_POST['user_pass1'] != " nochangeMS ") {
         $this->db->db_query("
            UPDATE nephthys_users
            SET
               user_pass='". sha1($_POST['user_pass1']) ."' 
            WHERE
               user_idx='". $_POST['user_idx'] ."'
         ");
      }
		  
      return "ok";

   } // store()

} // class NEPHTHYS_PROFILE

?>
