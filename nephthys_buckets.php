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
   public function __construct($id = NULL)
   {
      global $nephthys;
      $this->parent =& $nephthys;
      $this->db =& $nephthys->db;
      $this->tmpl =& $nephthys->tmpl;

      if(!empty($id))
         $this->id = $id;

      $this->tmpl->register_block("bucket_list", array(&$this, "smarty_bucket_list"));

      $query_str = "
         SELECT
            b.bucket_idx as bucket_idx,
            b.bucket_name as bucket_name,
            b.bucket_sender as bucket_sender,
            b.bucket_receiver as bucket_receiver,
            b.bucket_hash as bucket_hash,
            b.bucket_created as bucket_created,
            b.bucket_expire as bucket_expire,
            b.bucket_note as bucket_note,
            b.bucket_owner as bucket_owner,
            b.bucket_active as bucket_active,
            b.bucket_notified as bucket_notified,
            b.bucket_notify_on_expire as bucket_notify_on_expire
         FROM
            nephthys_buckets b
      ";

      /* get the current sort-order */
      $column = $this->parent->get_sort_column('buckets');
      $order  = $this->parent->get_sort_order('buckets');

      // if sort should happen on bucket-owners, sort by the real
      // user_name instead of the user_idx (which is stored in
      // bucket_owner).
      if($column == 'bucket_owner') {

         $query_str.= "
            LEFT OUTER JOIN
               nephthys_users u
            ON
               b.bucket_owner=u.user_idx
         ";

         /* equipped with just user privileges, show only personal buckets */
         if(!$this->parent->check_privileges('admin') &&
            !$this->parent->check_privileges('manager')) {
            $query_str.= "WHERE b.bucket_owner LIKE '". $_SESSION['login_idx'] ."'";
         }

         $query_str.= "
            ORDER BY
               u.user_name ". $order;
      }
      else {

         /* equipped with just user privileges, show only personal buckets */
         if(!$this->parent->check_privileges('admin') &&
            !$this->parent->check_privileges('manager')) {
            $query_str.= "WHERE b.bucket_owner LIKE '". $_SESSION['login_idx'] ."'";
         }

         $query_str.= "
            ORDER BY
               ". $column ." ". $order;
      }

      $res_buckets = $nephthys->db->db_query($query_str);

      $cnt_buckets = 0;

      while($bucket = $res_buckets->fetchrow()) {
         $this->avail_buckets[$cnt_buckets] = $bucket->bucket_idx;
         $this->buckets[$bucket->bucket_idx] = $bucket;
         $cnt_buckets++;
      }

      $this->tmpl->assign('user_has_buckets', $cnt_buckets);

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
         case 'receive':
            $this->tmpl->assign('bucket_owner', $_SESSION['login_idx']);
            $this->tmpl->assign('bucket_expire', $this->parent->get_user_expire($_SESSION['login_idx']));
            return $this->tmpl->fetch('receive_form.tpl');
         case 'send':
            $this->tmpl->assign('bucket_owner', $_SESSION['login_idx']);
            $this->tmpl->assign('bucket_expire', $this->parent->get_user_expire($_SESSION['login_idx']));
            return $this->tmpl->fetch('send_form.tpl');
         case 'edit':
            return $this->showEdit($_GET['idx']);
            break;
         case 'notify':
            return $this->notify();
            break;
      }

   } // show()

   /**
    * display a page containing bucket info
    *
    * this function returns a page containing information
    * about the requested (or previously created) bucket.
    *
    * @return string
    */
   public function showBucket()
   {
      if(!$this->parent->is_logged_in()) {
         $this->parent->_error($this->parent->_("##MANAGE_USERS##") ." - ". $this->parent->_("##NOT_ALLOWED##"));
         return 0;
      }

      if(!isset($_GET['idx']) || empty($_GET['idx']) ||
         !is_numeric($_GET['idx']))
         return;

      if($bucket = $this->db->db_fetchSingleRow("
         SELECT *
         FROM
            nephthys_buckets
         WHERE
            bucket_idx LIKE '". $_GET['idx'] ."'")) {

         $this->tmpl->assign('bucket_idx', $bucket->bucket_idx);
         $this->tmpl->assign('bucket_name', $this->parent->unescape($bucket->bucket_name));
         $this->tmpl->assign('bucket_expire', $this->parent->get_user_expire($_SESSION['login_idx']));

         if($bucket->bucket_expire != "-1")
            $bucket_expire = $bucket->bucket_created + ($bucket->bucket_expire*86400);

         $bucket_ftp = $this->parent->get_url('ftp', $bucket->bucket_hash);
         $bucket_webdav = $this->parent->get_url('dav', $bucket->bucket_hash);
         $bucket_webdav_vista = $this->parent->get_url('dav_vista', $bucket->bucket_hash);

         if($bucket->bucket_expire != "-1")
            $this->tmpl->assign('bucket_expire', strftime("%Y-%m-%d", $bucket_expire));
         else
            $this->tmpl->assign('bucket_expire', $this->parent->_('##NEVER##'));

         $this->tmpl->assign('bucket_receiver', $this->parent->unescape($bucket->bucket_receiver));
         $this->tmpl->assign('bucket_webdav_path', $bucket_webdav);
         $this->tmpl->assign('bucket_webdav_path_vista', $bucket_webdav_vista);
         $this->tmpl->assign('bucket_ftp_path', $bucket_ftp);

         return $this->tmpl->fetch('saved_bucket.tpl');

      }

      return;

   } // showBucket()

   /**
    * get bucket information & details
    *
    * this function returns informations about the requested
    * bucket. how much diskspace it uses, ...
    *
    * @return string
    */
   public function get_bucket_info()
   {
      if(!($bucket = $this->get_bucket_details($this->id)))
         return "unkown bucket";

      $bucket_path = $this->parent->cfg->data_path
         ."/"
         . $bucket->bucket_hash;

      if(($used_diskspace = $this->parent->get_used_diskspace($bucket_path)) === false) {
         return "Can not locate bucket in filesystem to get used diskspace";
      }

      $bucket_size = $this->parent->get_unit($used_diskspace);
      $bucket_details = $this->parent->get_dir_info($bucket_path);

      $this->tmpl->assign('count_files', $bucket_details['files']);
      $this->tmpl->assign('count_dirs', $bucket_details['dirs']);
      $this->tmpl->assign('bucket_size', $bucket_size);
      if($bucket_details['last_mod'] > 0) {
         $this->tmpl->assign('bucket_last_mod', strftime("%c", $bucket_details['last_mod']));
      }

      $body = $this->tmpl->fetch('bucket_info.tpl');

      return $body;

   } // get_bucket_info()

   public function notify()
   {
      if(!($bucket = $this->get_bucket_details($this->id)))
         return;

      $bucket->bucket_sender = $this->parent->unescape($bucket->bucket_sender, false);
      $bucket->bucket_receiver = $this->parent->unescape($bucket->bucket_receiver, false);

      /* the bucket sender */
      $sender = $bucket->bucket_sender;
      $sender_text = $bucket->bucket_sender;

      /* if a bucket receiver has been specified, send mail to the receiver
         and in CC also to the sender
      */
      if(isset($bucket->bucket_receiver) && !empty($bucket->bucket_receiver)) {
         $receiver = Array($bucket->bucket_receiver, $bucket->bucket_sender);
         $receiver_text = $bucket->bucket_receiver;
      }
      else {
         $receiver = Array($bucket->bucket_sender);
         $receiver_text = $bucket->bucket_sender;
      }

      $ftp_url = $this->parent->get_url('ftp', $bucket->bucket_hash);
      $http_url = $this->parent->get_url('dav', $bucket->bucket_hash);

      if($bucket->bucket_expire != -1) {
         $bucket_expire = $bucket->bucket_created + ($bucket->bucket_expire*86400);
         $bucket_expire = strftime("%d. %b. %Y", $bucket_expire);
      }
      else {
         $bucket_expire = "never";
      }

      /* prepare the mail headers */
      $header['From'] = $sender_text;
      $header['To'] = $receiver_text;
      $header['Subject'] = "File sharing information";
      $header['Content-Type'] = "text/plain; charset=UTF-8";
      /* if a bucket receiver has been specified, send mail to the receiver
         and in CC also to the sender
      */
      if(isset($bucket->bucket_receiver) && !empty($bucket->bucket_receiver))
         $header['CC'] = $bucket->bucket_sender;


      /* prepare the notification text out of the smarty template */
      $text = new NEPHTHYS_TMPL($this->parent);
      $text->assign('bucket_sender', $sender_text);
      $text->assign('bucket_receiver', $receiver_text);

      /* if the user has updated his profile with the full name, use it, otherwise
         take the login name instead.
      */
      if($this->parent->get_user_fullname($bucket->bucket_owner))
         $text->assign('bucket_sender_name', $this->parent->get_user_fullname($bucket->bucket_owner));
      else
         $text->assign('bucket_sender_name', $this->parent->get_user_name($bucket->bucket_owner));

      $text->assign('bucket_ftp_url', $ftp_url);
      $text->assign('bucket_http_url', $http_url);
      $text->assign('bucket_servername', $this->parent->cfg->servername);
      $text->assign('bucket_expire', $bucket_expire);

      /* if a bucket description has been specified, assign it to the template */
      if(isset($bucket->bucket_note) && !empty($bucket->bucket_note)) {
         $bucket->bucket_note = $this->parent->unescape($bucket->bucket_note, false);
         $text->assign('bucket_note', $bucket->bucket_note);
      }

      /* now translate the template and return the result as a string */
      $body = $text->fetch('notify.tpl');

      // if you want to use php's own mail() function, remove the
      // comment from the next two lines and wipe out the sendmail
      // lines below.
      // $mailer =& Mail::factory('mail');
      // $status = $mailer->send($receiver, $header, $body);

      // usually this do not need to be set.
      // $params['sendmail_path'] = '/usr/bin/sendmail';
      $params['sendmail_arg'] = '-f'. $sender;

      $mailer =& Mail::factory('sendmail', $params);
      $status = $mailer->send($receiver, $header, $body);

      if(PEAR::isError($status)) {
         return $status->getMessage();
      }

      /* set a flag in the database, that the bucket has been notified */
      $this->db->db_query("
         UPDATE nephthys_buckets
         SET
            bucket_notified='Y'
         WHERE
            bucket_idx LIKE '". $this->id ."'
      ");

      return "ok;". $this->parent->_("##NOTIFY_SUCCESS##");

   } // notify()

   /**
    * notify expired bucket
    *
    * this bucket notifies the bucket-owner about expiring buckets
    * @param int $bucket_idx
    */
   public function notify_expired_bucket($bucket_idx)
   {
      $bucket = $this->get_bucket_details($bucket_idx);

      $owner_email = $this->parent->get_user_email($bucket->bucket_owner);
      $owner_email = $this->parent->unescape($owner_email, false);

      /* the bucket sender */
      if(isset($this->parent->cfg->system_mail)) {
         $sender = $this->parent->cfg->system_mail;
         $sender_text = $this->parent->cfg->system_mail;
      }
      else {
         $sender = $owner_email;
         $sender_text = $owner_email;
      }

      /* the bucket receiver */
      $receiver = Array($owner_email);
      $receiver_text = $owner_email;

      /* prepare the mail headers */
      $header['From'] = $sender_text;
      $header['To'] = $receiver_text;
      $header['Subject'] = "Your Nephthys bucket has expired";
      $header['Content-Type'] = "text/plain; charset=UTF-8";

      /* prepare the notification text out of the Smarty template */
      $text = new NEPHTHYS_TMPL($this->parent);
      $text->assign('bucket_name', $bucket->bucket_name);

      /* now translate the template and return the result as a string */
      $body = $text->fetch('notify_expired.tpl');

      // if you want to use php's own mail() function, remove the
      // comment from the next two lines and wipe out the sendmail
      // lines below.
      // $mailer =& Mail::factory('mail');
      // $status = $mailer->send($receiver, $header, $body);

      // usually this do not need to be set.
      // $params['sendmail_path'] = '/usr/bin/sendmail';
      $params['sendmail_arg'] = '-f'. $sender;

      $mailer =& Mail::factory('sendmail', $params);
      $status = $mailer->send($receiver, $header, $body);

      if(PEAR::isError($status)) {
         die($status->getMessage());
      }

      return true;

   } // notify_expired_buckets()

   public function store()
   {
      /* if not a privileged user, then set the email address from his profile */
      if($this->parent->check_privileges('user')) {
         $_POST['bucket_sender'] = $this->parent->get_my_email();
      }
      /* if not a privilged user, then set the owner to his id */
      if($this->parent->check_privileges('user')) {
         $_POST['bucket_owner'] = $_SESSION['login_idx'];
      }

      isset($_POST['bucket_new']) && $_POST['bucket_new'] == 1 ? $new = 1 : $new = NULL;

      if(!isset($_POST['bucket_name']) || empty($_POST['bucket_name'])) {
         return $this->parent->_("##FAILURE_ENTER_BUCKET_NAME##");
      }
      if(!isset($_POST['bucket_sender']) || empty($_POST['bucket_name'])) {
         return $this->parent->_("##FAILURE_ENTER_BUCKET_SENDER##");
      }
      if(!$this->parent->is_valid_email($_POST['bucket_sender'])) {
         return $this->parent->_("##FAILURE_ENTER_VALID_SENDER##");
      }
      if(isset($_POST['bucketmode']) && $_POST['bucketmode'] == "receive" &&
         !isset($_POST['bucket_receiver']) || empty($_POST['bucket_name'])) {
         return $this->parent->_("##FAILURE_ENTER_BUCKET_RECEIVER##");
      }
      if(isset($_POST['bucketmode']) && $_POST['bucketmode'] == "receive" &&
         !$this->parent->is_valid_email($_POST['bucket_receiver'])) {
         return $this->parent->_("##FAILURE_ENTER_VALID_RECEIVER##");
      }
      /* for "send" it's not a must to specify a receiver, anyway, if one is there
         validate it...
      */
      if(isset($_POST['bucketmode']) && $_POST['bucketmode'] == "send" &&
         isset($_POST['bucket_receiver']) && !empty($_POST['bucket_receiver']) &&
         !$this->parent->is_valid_email($_POST['bucket_receiver'])) {
         return $this->parent->_("##FAILURE_ENTER_VALID_RECEIVER##");
      }

      /* first of all we add the email address to the addressbook if requested.
         If after something goes wrong, the address is already in the database
         and user saves some keystrokes...

         but only if the "add email to address-book" is checked and a receiver
         address has been specified.
      */
      if(isset($_POST['bucket_receiver_to_ab']) &&
         $_POST['bucket_receiver_to_ab'] == 'Y' &&
         isset($_POST['bucket_receiver']) &&
         !empty($_POST['bucket_receiver'])) {
         $this->parent->add_to_addressbook($_POST['bucket_receiver']);
      }

      if(!isset($_POST['bucket_notify_on_expire']))
         $_POST['bucket_notify_on_expire'] = 'N';

      if(isset($new)) {

         if(isset($_POST['bucket_receiver']))
            $hash = $this->parent->get_sha_hash($_POST['bucket_sender'], $_POST['bucket_receiver']);
         else {
            $_POST['bucket_receiver'] = "";
            $hash = $this->parent->get_sha_hash($_POST['bucket_sender']);
         }

         $sth = $this->db->db_prepare("
            INSERT INTO nephthys_buckets (
               bucket_idx,
               bucket_name, bucket_sender, bucket_receiver, bucket_created,
               bucket_expire, bucket_note, bucket_hash, bucket_owner,
               bucket_active, bucket_notify_on_expire
            ) VALUES (
               NULL,
               ?, ?, ?, '". mktime() ."',
               ?, ?, '". $hash ."', ?,
               'Y', ?
            )
         ");

         $this->db->db_execute($sth, array(
            $_POST['bucket_name'],
            $_POST['bucket_sender'],
            $_POST['bucket_receiver'],
            $_POST['bucket_expire'],
            $_POST['bucket_note'],
            $_POST['bucket_owner'],
            $_POST['bucket_notify_on_expire'],
         ));

         $this->id = $this->db->db_getid();
         $last_id = $this->id;

         if(!mkdir($this->parent->cfg->data_path ."/". $hash)) {
            return "There was a error creating the bucket directory. Contact your administrator!";
         }

         if(isset($_POST['bucketmode']) && $_POST['bucketmode'] == "receive" &&
            isset($_POST['notifybucket']) && $_POST['notifybucket'] == "true") {

            $this->notify();

         }

         // Create IE WebDAV-open-HTML file
         $bucket_webdav = $this->parent->get_url('dav', $hash);
         $this->tmpl->assign('bucket_webdav_path', $bucket_webdav);
         $html_file = $this->tmpl->fetch("ie_webdav.tpl");

         if($fileh = fopen($this->parent->cfg->data_path ."/". $hash ."/webdav.html", 'w')) {
            fwrite($fileh, $html_file);
            fclose($fileh);
         }

      }
      else {

        $sth = $this->db->db_prepare("
            UPDATE nephthys_buckets
            SET
               bucket_name=?,
               bucket_sender=?,
               bucket_receiver=?,
               bucket_expire=?,
               bucket_note=?,
               bucket_owner=?,
               bucket_active='Y',
               bucket_notify_on_expire=?
            WHERE
               bucket_idx=?
         ");

         $this->db->db_execute($sth, array(
            $_POST['bucket_name'],
            $_POST['bucket_sender'],
            $_POST['bucket_receiver'],
            $_POST['bucket_expire'],
            $_POST['bucket_note'],
            $_POST['bucket_owner'],
            $_POST['bucket_notify_on_expire'],
            $_POST['bucket_idx'],
         ));

      }

      if(!isset($last_id))
         return "ok";

      return "ok;". $last_id;

   } // store()

   public function showList()
   {
      return $this->tmpl->fetch("bucket_list.tpl");

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

         $user_priv = $this->parent->get_user_priv($_SESSION['login_idx']);

         if($bucket->bucket_expire != "-1")
            $bucket_expire = $bucket->bucket_created + ($bucket->bucket_expire*86400);
         $bucket_owner = $this->parent->get_user_name($bucket->bucket_owner);

         $bucket_ftp = $this->parent->get_url('ftp', $bucket->bucket_hash);
         $bucket_webdav = $this->parent->get_url('dav', $bucket->bucket_hash);
         $bucket_webdav_vista = $this->parent->get_url('dav_vista', $bucket->bucket_hash);

         $this->tmpl->assign('bucket_idx', $bucket_idx);
         $this->tmpl->assign('bucket_name', $this->parent->unescape($bucket->bucket_name));
         $this->tmpl->assign('bucket_created', strftime("%Y-%m-%d", $bucket->bucket_created));
         if($bucket->bucket_expire != "-1")
            $this->tmpl->assign('bucket_expire', strftime("%Y-%m-%d", $bucket_expire));
         else
            $this->tmpl->assign('bucket_expire', $this->parent->_('##NEVER##'));
         $this->tmpl->assign('bucket_owner', $this->parent->unescape($bucket_owner));
         $this->tmpl->assign('bucket_owner_idx', $bucket->bucket_owner);
         $this->tmpl->assign('bucket_receiver', $this->parent->unescape($bucket->bucket_receiver));
         $this->tmpl->assign('bucket_webdav_path', $bucket_webdav);
         $this->tmpl->assign('bucket_webdav_path_vista', $bucket_webdav_vista);
         $this->tmpl->assign('bucket_ftp_path', $bucket_ftp);
         $this->tmpl->assign('bucket_notified', $bucket->bucket_notified);
         $this->tmpl->assign('bucket_hash', $bucket->bucket_hash);

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
         if($this->parent->check_privileges('user') && !$this->parent->is_bucket_owner($_POST['idx'])) {
            return "You are only allowed to delete buckets you own!";
         }

         $hash = $this->get_bucket_hash($_POST['idx']);

         if(!$hash) {
            return "Can't locate hash value of the bucket that was requested to be deleted.";
         }

         if(!$this->del_data_directory($hash)) {
            $this->parent->_error("Removing bucket directory ". $this->parent->cfg->data_path ."/". $hash ." not possible");
         }

         $this->delete_bucket($_POST['idx']);
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

   public function del_data_directory($hash)
   {
      /* if something went wrong before, do not delete anything */
      if(!is_string($hash) || empty($hash))
         return false;

      $invalid_path = Array(
         "/",
         "/usr",
         "/var",
         "/home",
         "/boot",
         $this->parent->cfg->base_path);

      /*
       * ensure that this function can not malfunction
       */
      if(in_array($this->parent->cfg->data_path, $invalid_path))
         return false;

      if($this->data_directory_exists($hash))
         return $this->parent->deltree($this->parent->cfg->data_path ."/". $hash);

      return false;

   } // del_data_directory()

   /**
    * check if data directory exists
    *
    * returns true, if the specified data-directory + hash-named
    * directory really exists.
    * @param string $hash
    * @return bool
    */
   private function data_directory_exists($hash)
   {
      if(file_exists($this->parent->cfg->data_path ."/". $hash))
         return true;

      return false;

   } // data_directory_exists()

   /**
    * display interface to create or edit users
    * @param int $idx
    */
   private function showEdit($idx)
   {
      /* If authentication is enabled, check permissions */
      if(!$this->parent->is_logged_in()) {
         $this->parent->_error($this->parent->_("##MANAGE_USERS##") ." - ". $this->parent->_("##NOT_ALLOWED##"));
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
         $this->tmpl->assign('bucket_name', $this->parent->unescape($bucket->bucket_name));
         $this->tmpl->assign('bucket_sender', $this->parent->unescape($bucket->bucket_sender));
         $this->tmpl->assign('bucket_receiver', $this->parent->unescape($bucket->bucket_receiver));
         $this->tmpl->assign('bucket_expire', $bucket->bucket_expire);
         $this->tmpl->assign('bucket_note', $this->parent->unescape($bucket->bucket_note));
         $this->tmpl->assign('bucket_owner', $this->parent->unescape($bucket->bucket_owner));
         $this->tmpl->assign('bucket_active', $bucket->bucket_active);
         $this->tmpl->assign('bucket_notify_on_expire', $bucket->bucket_notify_on_expire);

      }

      return $this->tmpl->fetch("bucket_edit.tpl");

   } // showEdit()

   /**
    * get bucket details
    *
    * this function returns a object containing all
    * informations about a bucket-object in database.
    * @param int $idx
    * @return object
    */
   public function get_bucket_details($idx)
   {
      if($bucket = $this->db->db_fetchSingleRow("
         SELECT *
         FROM
            nephthys_buckets
         WHERE
            bucket_idx='". $idx ."'")) {

         return $bucket;

      }

      return NULL;

   } // get_bucket_details()

   /**
    * get expired buckets
    *
    * this function will return an array consiting the row id's of all
    * expired buckets.
    * @return array
    */
   public function get_expired_buckets()
   {

      $expired_buckets = Array();

      /* get all buckets */
      $buckets = $this->db->db_query("
         SELECT
            b.bucket_idx as bucket_idx,
            b.bucket_expire as bucket_expire,
            b.bucket_created as bucket_created
         FROM
            nephthys_buckets b
         INNER JOIN
            nephthys_users u
         ON
            b.bucket_owner=u.user_idx
      ");

      while($bucket = $buckets->fetchRow()) {

         /* don't care about never-expiring buckets */
         if($bucket->bucket_expire == -1)
            continue;

         /* check if the bucket has expired */
         if(($bucket->bucket_created + ($bucket->bucket_expire * 86400)) <= mktime()) {
            array_push($expired_buckets, $bucket->bucket_idx);
         }
      }

      return $expired_buckets;

   } // get_expired_buckets()

   /**
    * delete bucket
    *
    * this function deletes a bucket ONLY from the database identified
    * by its row id.
    * @param int $idx
    * @return bool
    */
   public function delete_bucket($idx)
   {
      if($this->db->db_query("
         DELETE FROM
            nephthys_buckets
         WHERE
            bucket_idx LIKE '". $idx ."'")) {

         return true;

      }

      return false;

   } // delete_bucket()

} // class NEPHTHYS_BUCKETS

// vim: set filetype=php expandtab softtabstop=3 tabstop=3 shiftwidth=3 autoindent smartindent:
?>
