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

      $res_buckets = $nephthys->db->db_query("
         SELECT *
         FROM nephthys_buckets
         ORDER BY bucket_name ASC
      ");

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
            return $this->tmpl->show('receive_form.tpl');
         case 'send':
            return $this->tmpl->show('send_form.tpl');
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
         !$this->validate_email($_POST['bucket_receiver'])) {
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
               bucket_expire, bucket_note, bucket_hash, bucket_active
            ) VALUES (
               '". $_POST['bucket_name'] ."',
               '". $_POST['bucket_sender'] ."',
               '". $_POST['bucket_receiver'] ."',
               '". mktime() ."',
               '". $_POST['bucket_expire'] ."',
               '". $_POST['bucket_note'] ."',
               '". $hash ."',
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
                  bucket_active='Y'
               WHERE
                  bucket_idx='". $_POST['bucket_idx'] ."'
            ");
      }

      return "ok";

   } // bucketModify()

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

         $this->tmpl->assign('bucket_idx', $bucket_idx);
         $this->tmpl->assign('bucket_name', $bucket->bucket_name);
         $this->tmpl->assign('bucket_sender', $bucket->bucket_sender);
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


} // class NEPHTHYS_BUCKETS

?>
