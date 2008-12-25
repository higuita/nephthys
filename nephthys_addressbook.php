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

class NEPHTHYS_ADDRESSBOOK {

   private $db;
   private $parent;
   private $tmpl;
   private $id;

   /**
    * NEPHTHS_ADDRESSBOOK constructor
    *
    * Initialize the NEPHTHS_ADDRESSBOOK class
    */
   public function __construct($id = NULL)
   {
      global $nephthys;
      $this->parent =& $nephthys;
      $this->db =& $nephthys->db;
      $this->tmpl =& $nephthys->tmpl;

      if(!empty($id))
         $this->id = $id;

      $this->tmpl->register_block("contact_list", array(&$this, "smarty_contact_list"));

      $query_str = "
         SELECT
            *
         FROM
            nephthys_addressbook ab
      ";

      if(!$this->parent->check_privileges('admin') &&
         !$this->parent->check_privileges('manager')) {
         $query_str.= "WHERE contact_owner LIKE '". $_SESSION['login_idx'] ."'";
      }

      /* get the current sort-order */
      $column = $this->parent->get_sort_column('addressbook');
      $order  = $this->parent->get_sort_order('addressbook');

      // if sort should happen on bucket-owners, sort by the real
      // user_name instead of the user_idx (which is stored in
      // bucket_owner).
      if($column == 'contact_owner') {
         $query_str.= "
            INNER JOIN
               nephthys_users u
            ON
               ab.contact_owner=u.user_idx
            ORDER BY
               u.user_name ". $order;
      }
      else {
         $query_str.= "
            ORDER BY
               ". $column ." ". $order;
      }

      $res_contacts = $nephthys->db->db_query($query_str);

      $cnt_contacts = 0;

      while($contact = $res_contacts->fetchrow()) {
         $this->avail_contacts[$cnt_contacts] = $contact->contact_idx;
         $this->contacts[$contact->contact_idx] = $contact;
         $cnt_contacts++;
      }

      $this->tmpl->assign('user_has_contacts', $cnt_contacts);

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
      if(!isset($_GET['idx']) ||
         (isset($_GET['idx']) && !is_numeric($_GET['idx'])))
         $_GET['idx'] = 0;

      switch($_GET['mode']) {
         default:
         case 'show':
            return $this->showList();
            break;
         case 'edit':
            return $this->showEdit($_GET['idx']);
            break;
      }

   } // show()

   public function store()
   {
      /* if not a privilged user, then set the owner to his id */
      if($this->parent->check_privileges('user')) {
         $_POST['contact_owner'] = $_SESSION['login_idx'];
      }

      isset($_POST['contact_new']) && $_POST['contact_new'] == 1 ? $new = 1 : $new = NULL;

      if(!isset($_POST['contact_email']) || empty($_POST['contact_email'])) {
         return $this->parent->_("##FAILURE_ENTER_EMAIL##");
      }
      if(!$this->parent->is_valid_email($_POST['contact_email'])) {
         return $this->parent->_("##FAILURE_ENTER_SENDER##");
      }

      if(isset($new)) {

         $sth = $this->db->db_prepare("
            INSERT INTO nephthys_addressbook (
               contact_idx, contact_name,
               contact_email, contact_owner
            ) VALUES (
               NULL, ?, ?, ?
            )
         ");

         $this->db->db_execute($sth, array(
            $_POST['contact_name'],
            $_POST['contact_email'],
            $_POST['contact_owner'],
         ));

         $this->id = $this->db->db_getid();

      }
      else {

            $sth = $this->db->db_prepare("
               UPDATE nephthys_addressbook
               SET
                  contact_name=?,
                  contact_email=?,
                  contact_owner=?
               WHERE
                  contact_idx=?
            ");

            $this->db->db_execute($sth, array(
               $_POST['contact_name'],
               $_POST['contact_email'],
               $_POST['contact_owner'],
               $_POST['contact_idx'],
            ));
      }

      return "ok";

   } // store()

   public function showList()
   {
      return $this->tmpl->fetch("addressbook_list.tpl");

   } // showList()

   /**
    * template function which will be called from the addressbook listing template
    */
   public function smarty_contact_list($params, $content, &$smarty, &$repeat)
   {
      $index = $this->tmpl->get_template_vars('smarty.IB.contact_list.index');
      if(!$index) {
         $index = 0;
      }

      if($index < count($this->avail_contacts)) {

         $contact_idx = $this->avail_contacts[$index];
         $contact =  $this->contacts[$contact_idx];

         $user_priv = $this->parent->get_user_priv($_SESSION['login_idx']);
         $contact_owner = $this->parent->get_user_name($contact->contact_owner);

         $this->tmpl->assign('contact_idx', $contact_idx);

         if(isset($contact->contact_name) && !empty($contact->contact_name)) {
            $this->tmpl->assign(
               'contact_name',
               $contact->contact_name ."&nbsp;&lt;". $contact->contact_email ."&gt;"
            );
         }
         else {
            $this->tmpl->assign('contact_name', $contact->contact_email);
         }
         $this->tmpl->assign('contact_owner', $contact_owner);
         $this->tmpl->assign('contact_owner_idx', $contact->contact_owner);

         $index++;
         $this->tmpl->assign('smarty.IB.contact_list.index', $index);
         $repeat = true;
      }
      else {
         $repeat =  false;
      }

      return $content;

   } // smarty_contact_list()

   public function delete()
   {
      if(isset($_POST['idx']) && is_numeric($_POST['idx'])) {

         /* ensure unprivileged users can only delete their own contacts */
         if($this->parent->check_privileges('user') && !$this->parent->is_contact_owner($_POST['idx'])) {
            return "You are only allowed to delete contacts you own!";
         }

         $this->db->db_query("
            DELETE FROM nephthys_addressbook
            WHERE contact_idx LIKE '". $_POST['idx'] ."'
         ");
      }

      print "ok";

   } // delete()

   /**
    * display interface to create or edit addressbook entires
    *
    * @param int $idx
    */
   private function showEdit($idx)
   {
      /* If authentication is enabled, check permissions */
      if(!$this->parent->is_logged_in()) {
         $this->parent->_error($this->parent->_("MANAGE_AB") ." - ". $this->parent->_("NOT_ALLOWED"));
         return 0;
      }

      if($idx != 0) {
         $contact = $this->db->db_fetchSingleRow("
            SELECT *
            FROM nephthys_addressbook
            WHERE
               contact_idx LIKE '". $idx ."'
         ");

         $this->tmpl->assign('contact_idx', $idx);
         $this->tmpl->assign('contact_name', $this->parent->unescape($contact->contact_name));
         $this->tmpl->assign('contact_email', $this->parent->unescape($contact->contact_email));
         $this->tmpl->assign('contact_owner', $this->parent->unescape($contact->contact_owner));

      }

      return $this->tmpl->fetch("addressbook_edit.tpl");

   } // showEdit()

} // class NEPHTHYS_ADDRESSBOOK

// vim: set filetype=php expandtab softtabstop=3 tabstop=3 shiftwidth=3 autoindent smartindent:
?>
