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

class NEPHTHYS_BUCKETS {

   private $db;
   private $parent;
   private $tmpl;
   private $id;
   private $avail_buckets = Array();
   private $buckets = Array(); 

   /**
    * NEPHTHYS_BUCKET constructor
    *
    * Initialize the NEPHTHYS_BUCKET class
    */
   public function __construct()
   {
      global $nephthys;
      $this->parent =& $nephthys;
      $this->db =& $nephthys->db;
      $this->tmpl =& $nephthys->tmpl;

      $this->tmpl->register_block("bucket_list", array(&$this, "smarty_bucket_list"));

      $query_str = "
         SELECT *
         FROM nephthys_buckets
      ";

      if($this->parent->has_user_priv()) {
         $query_str.= "WHERE bucket_owner LIKE '". $_SESSION['user_idx'] ."'";
      }

      $query_str.= "ORDER BY bucket_name ASC";

      $res_buckets = $nephthys->db->db_query($query_str);

      $cnt_buckets = 0;

      while($bucket = $res_buckets->fetchrow()) {
         $this->avail_buckets[$cnt_buckets] = $bucket->bucket_idx;
         $this->buckets[$bucket->bucket_idx] = $bucket;
         $cnt_buckets++;
      }


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
      if(!isset($_GET['idx']) ||
         (isset($_GET['idx']) && !is_numeric($_GET['idx'])))
         $_GET['idx'] = 0;

      switch($_GET['mode']) {
         case 'receive':
            $this->tmpl->assign('bucket_owner', $_SESSION['user_idx']);
            return $this->tmpl->show('receive_form.tpl');
         case 'send':
            $this->tmpl->assign('bucket_owner', $_SESSION['user_idx']);
            return $this->tmpl->show('send_form.tpl');
         case 'edit':
            $this->showEdit($_GET['idx']);
            break;
         case 'notify':
            $this->notify();
            break;
      }

   } // show()

   public function notify()
   {
      if(!($bucket = $this->parent->getbucketDetails($this->id)))
         return;

      $header['From'] = $bucket->bucket_sender;
      $header['To'] = $bucket->bucket_receiver;
      $header['Subject'] = "File sharing information";

      $text = new NEPHTHYS_TMPL($this->parent);
      $text->assign('bucket_sender', $bucket->bucket_sender);
      $text->assign('bucket_receiver', $bucket->bucket_receiver);
      $text->assign('bucket_hash', $bucket->bucket_hash);
      $text->assign('bucket_servername', "www.orf.at");
      $body = $text->fetch('notify.tpl');

      $mailer =& Mail::factory('mail');
      $status = $mailer->send($bucket->bucket_receiver, $header, $body);
      if(PEAR::isError($status)) {
         return $status->getMessage();
      }

      return "ok";

   } // notify()

   public function store()
   {
      /* if not a privileged user, then set the email address from his profile */
      if($this->parent->has_user_priv()) {
         $_POST['bucket_sender'] = $this->parent->get_users_email();
      }
      /* if not a privilged user, then set the owner to his id */
      if($this->parent->has_user_priv()) {
         $_POST['bucket_owner'] = $_SESSION['user_idx'];
      }

      isset($_POST['bucket_new']) && $_POST['bucket_new'] == 1 ? $new = 1 : $new = NULL;

      if(!isset($_POST['bucket_name']) || empty($_POST['bucket_name'])) {
         return _("Please enter a name for this bucket!");
      }
      if(!isset($_POST['bucket_sender']) || empty($_POST['bucket_name'])) {
         return _("Please enter a sender for this bucket!");
      }
      if(!$this->parent->validate_email($_POST['bucket_sender'])) {
         return _("Please enter a valid sender email address! test" . $_POST['bucket_sender']);
      }
      if(isset($_POST['bucketmode']) && $_POST['bucketmode'] == "receive" &&
         !isset($_POST['bucket_receiver']) || empty($_POST['bucket_name'])) {
         return _("Please enter a receiver for this bucket!");
      }
      if(isset($_POST['bucketmode']) && $_POST['bucketmode'] == "receive" &&
         !$this->parent->validate_email($_POST['bucket_receiver'])) {
         return _("Please enter a valid receiver email address!");
      }

      if(isset($new)) {

         if(isset($_POST['bucket_receiver']))
            $hash = $this->parent->get_sha_hash($_POST['bucket_sender'], $_POST['bucket_receiver']);
         else {
            $_POST['bucket_receiver'] = "";
            $hash = $this->parent->get_sha_hash($_POST['bucket_sender']);
         }

         $this->db->db_query("
            INSERT INTO nephthys_buckets (
               bucket_name, bucket_sender, bucket_receiver, bucket_created,
               bucket_expire, bucket_note, bucket_hash, bucket_owner,
               bucket_active
            ) VALUES (
               '". $_POST['bucket_name'] ."',
               '". $_POST['bucket_sender'] ."',
               '". $_POST['bucket_receiver'] ."',
               '". mktime() ."',
               '". $_POST['bucket_expire'] ."',
               '". $_POST['bucket_note'] ."',
               '". $hash ."',
               '". $_POST['bucket_owner'] ."',
               'Y')
         ");

         if(!mkdir($this->parent->cfg->data_path ."/". $hash)) {
            return "There was a error creating the bucket directory. Contact your administrator!";
         }

      }
      else {
           $this->db->db_query("
               UPDATE nephthys_buckets
               SET
                  bucket_name='". $_POST['bucket_name'] ."',
                  bucket_sender='". $_POST['bucket_sender'] ."',
                  bucket_receiver='". $_POST['bucket_receiver'] ."',
                  bucket_expire='". $_POST['bucket_expire'] ."',
                  bucket_note='". $_POST['bucket_note'] ."',
                  bucket_owner='". $_POST['bucket_owner'] ."',
                  bucket_active='Y'
               WHERE
                  bucket_idx='". $_POST['bucket_idx'] ."'
            ");
      }

      return "ok";

   } // store()

   public function showList()
   {
      $this->tmpl->show("bucket_list.tpl");

   } // showList()

   /**
    * template function which will be called from the buckets listing template
    */
   public function smarty_bucket_list($params, $content, &$smarty, &$repeat)
   {
      $index = $this->tmpl->get_template_vars('smarty.IB.bucket_list.index');
      if(!$index) {
         $index = 0;
      }

      if($index < count($this->avail_buckets)) {

         $bucket_idx = $this->avail_buckets[$index];
         $bucket =  $this->buckets[$bucket_idx];

         $user_priv = $this->parent->get_user_priv($_SESSION['user_idx']);

         $bucket_expire = $bucket->bucket_created + ($bucket->bucket_expire*86400);
         $bucket_owner = $this->parent->get_user_name($bucket->bucket_owner);

         $this->tmpl->assign('bucket_idx', $bucket_idx);
         $this->tmpl->assign('bucket_name', $bucket->bucket_name);
         $this->tmpl->assign('bucket_created', strftime("%Y-%m-%d", $bucket->bucket_created));
         $this->tmpl->assign('bucket_expire', strftime("%Y-%m-%d", $bucket_expire));
         $this->tmpl->assign('bucket_owner', $bucket_owner);
         $this->tmpl->assign('bucket_owner_idx', $bucket->bucket_owner);
         $this->tmpl->assign('bucket_receiver', $bucket->bucket_receiver);

         $index++;
         $this->tmpl->assign('smarty.IB.bucket_list.index', $index);
         $repeat = true;
      }
      else {
         $repeat =  false;
      }

      return $content;

   } // smarty_bucket_list()

   public function delete()
   {
      if(isset($_POST['idx']) && is_numeric($_POST['idx'])) {

         /* ensure unprivileged users can only delete their own buckets */
         if($this->parent->has_user_priv() && !$this->parent->is_bucket_owner($_SESSION['user_idx'])) {
            return "You are only allowed to delete buckets you own!";
         }

         $hash = $this->get_bucket_hash($_POST['idx']);

         if(!$hash) {
            return "Can't locate hash value of the bucket that has to be deleted.";
         }

         if(!$this->del_data_directory($hash)) {
            return "Removing bucket directory ". $this->parent->cfg->data_path ."/". $hash ." not possible";
         }

         $this->db->db_query("
            DELETE FROM nephthys_buckets
            WHERE bucket_idx LIKE '". $_POST['idx'] ."'
         ");
      }

      print "ok";

   } // delete()

   /**
    * return bucket's SHA1 hash
    *
    * this function will return the SHA1 hash of the
    * requested bucket (by database primary key)
    */
   private function get_bucket_hash($idx)
   {
      if($row = $this->db->db_fetchSingleRow("
            SELECT bucket_hash
            FROM nephthys_buckets
            WHERE bucket_idx LIKE '". $idx ."'
         ")) {

         if(isset($row->bucket_hash))
            return $row->bucket_hash;

      }

      return 0;

   } // get_bucket_hash();

   private function del_data_directory($hash)
   {
      $invalid_path = Array("/", "/usr", "/var", "/home", "/boot");
      /*
       * ensure that this function can not malfunction
       */
      if(in_array($this->parent->cfg->data_path, $invalid_path))
         die;

      if($this->data_directory_exists($hash))
         return $this->deltree($this->parent->cfg->data_path ."/". $hash);

      return false;

   } // del_data_directory()

   private function deltree($f)
   {
      if (is_dir($f)) {
         foreach(glob($f.'/*') as $sf) {
            print $sf;
            if (is_dir($sf) && !is_link($sf)) {
               $this->deltree($sf);
               // rmdir($sf); <== old place with arg "$sf"
            } else {
               unlink($sf);
            }
         }
         rmdir($f); // <== new place with new arg "$f"
         return true;
      }

      return false;

   } // deltree()

   private function scan_full_dir($rootDir, $allowext, $allData=array()) {
      $dirContent = scandir($rootDir);
      foreach($dirContent as $key => $content) {
         $path = $rootDir.'/'.$content;
         $ext = substr($content, strrpos($content, '.') + 1);

         if(in_array($ext, $allowext)) {
            if(is_file($path) && is_readable($path)) {
               $allData[] = $path;
            }elseif(is_dir($path) && is_readable($path)) {
               // recursive callback to open new directory
               $allData = $this->scan_full_dir($path, $allData);
            }
         }
      }
      return $allData;
   } // scan_full_dir()

   /**
    * check if data directory exists
    *
    * returns true, if the specified data-directory + hash-named
    * directory really exists.
    */
   private function data_directory_exists($hash)
   {
      if(file_exists($this->parent->cfg->data_path ."/". $hash))
         return true;

      return false;

   } // data_directory_exists()

   /**
    * display interface to create or edit users
    */
   private function showEdit($idx)
   {
      /* If authentication is enabled, check permissions */
      if(!$this->parent->is_logged_in()) {
         $this->parent->printError("<img src=\"". ICON_USERS ."\" alt=\"user icon\" />&nbsp;". _("Manage Users"), _("You do not have enough permissions to access this module!"));
         return 0;
      }

      if($idx != 0) {
         $bucket = $this->db->db_fetchSingleRow("
            SELECT *
            FROM nephthys_buckets
            WHERE
               bucket_idx LIKE '". $idx ."'
         ");

         $this->tmpl->assign('bucket_idx', $idx);
         $this->tmpl->assign('bucket_name', $bucket->bucket_name);
         $this->tmpl->assign('bucket_sender', $bucket->bucket_sender);
         $this->tmpl->assign('bucket_receiver', $bucket->bucket_receiver);
         $this->tmpl->assign('bucket_expire', $bucket->bucket_expire);
         $this->tmpl->assign('bucket_note', $bucket->bucket_note);
         $this->tmpl->assign('bucket_owner', $bucket->bucket_owner);
         $this->tmpl->assign('bucket_active', $bucket->bucket_active);

      }

      $this->tmpl->show("bucket_edit.tpl");

   } // showEdit()

} // class NEPHTHYS_BUCKETS

?>
