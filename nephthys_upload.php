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

class NEPHTHYS_UPLOAD {

   private $db;
   private $parent;
   private $tmpl;
   private $bucket_hash;
   private $bucket_items;
   private $avail_items;

   /**
    * NEPHTHYS_UPLOAD constructor
    *
    * Initialize the NEPHTHYS_UPLOAD class
    */
   public function __construct()
   {
      require_once "nephthys.class.php";

      $nephthys = new NEPHTHYS;
      $buckets  = new NEPHTHYS_BUCKETS;

      /* uploadifys flash object has no access to the browsers cookie.
         so it will not send session informations (PHPSEЅSID in cookie)
         with HTTP headers.
         so we check here if the session has been started already,
         otherwise we are going to look for a provided sessionid
         in $_POST.
      */
      if(session_id() == "" || (session_id() != "" && !isset($_SESSION['login_idx']))) {

         // destroy current session.
         if(ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
               $params['path'], $params['domain'],
               $params['secure'], $params['httponly']
            );
         }
         session_destroy();

         // look for provided session id
         if(!isset($_POST['sessionid']) || empty($_POST['sessionid'])) {
            error_log("session-id not set!");
            return false;
         }
         session_id($_POST['sessionid']);
         session_start();
      }

      /* user logged in? */
      if(!$nephthys->is_logged_in()) {
         print "not enough permissions!";
         return false;
      }

      $this->parent =& $nephthys;
      $this->db     =& $nephthys->db;
      $this->tmpl   =& $nephthys->tmpl;

      $GLOBALS['nephthys'] = $nephthys;
      $GLOBALS['buckets']  = $buckets;

      $this->tmpl->register_block("file_list", array(&$this, "smarty_file_list"));
      $this->tmpl->register_function("file_breadcrumb_bar", array(&$this, "smarty_file_breadcrumb_bar"), false);

   } // __construct()

   public function filemgr()
   {
      global $nephthys;
      global $buckets;

      /* stop if we miss both of them... */
      if(!isset($_POST['id']) && !isset($_SESSION['current_bucket']))
         return false;

      /* if we are going to handle a new bucket, reset all session details */
      if(isset($_POST['id']) && $_POST['id'] != $_SESSION['current_bucket']) {
         unset($_SESSION['current_path']);
         unset($_SESSION['current_bucket']);
      }

      /* verify bucket id */
      if(isset($_POST['id']) && !is_numeric($_POST['id']))
         return false;

      if(isset($_POST['id']))
         $_SESSION['current_bucket'] = $_POST['id'];

      /* locate bucket details, fail if impossible */
      if(!($bucket = $buckets->get_bucket_details($_SESSION['current_bucket'])))
         return false;

      /* re-check ownership */
      if(!$nephthys->is_bucket_owner($_SESSION['current_bucket']))
         return false;

      $this->bucket_hash = $bucket->bucket_hash;

      /* no current path in session information */
      if(!isset($_SESSION['current_path']) || empty($_SESSION['current_path']))
         $this->set_current_path();

      /* invalid path in session information */
      if(!$this->is_valid_current_path())
         $this->set_current_path();

      $nephthys->tmpl->assign('bucket_name', "Bucket: ". $bucket->bucket_name);
 
      if(!isset($_POST['command']))
         $_POST['command'] = 'show';

      switch($_POST['command']) {
         case 'upload':
            $retval = $this->handle_upload();
            break;
         case 'delete':
            $retval = $this->delete_item();
            break;
         case 'mkdir':
            $retval = $this->mkdir();
            break;
         case 'chdir':
            $retval = $this->chdir();
            break;
         default:
         case 'show':
            $retval = $this->show();
            break;
      }

      return $retval;

   } // filemgr()
  
   /* interface output */
   private function show()
   {
      if(!$bucket_path = $this->get_current_path())
         return false;

      $this->bucket_items = Array();
      $this->avail_items  = Array();

      $this->bucket_items = $this->get_dir_items($bucket_path);

      if(count($this->bucket_items) > 0) {
         /* Sort items - directories first, then files. From A-Z */
         foreach ($this->bucket_items as $key => $row) {
            $types[$key] = $row['type'];
            $names[$key] = $row['name'];
         }
         // Sort the data with volume descending, edition ascending
         // Add $data as the last parameter, to sort by the common key
         array_multisort($types, SORT_ASC, $names, SORT_ASC, $this->bucket_items);

         foreach($this->bucket_items as $item) {
            array_push($this->avail_items, $item['name']);
         }
      }

      return $this->tmpl->fetch("bucket_filemgr.tpl");

   } // show()

   /**
    * handle HTTP POST uploads
    *
    * this function actually handles the uploaded files. it validates
    * the file was really stored in the local filesystem, the requested
    * path to put the file is valid ­ then it moves the file there.
    *
    * @return boolean
    */
   private function handle_upload()
   {
      // really some file got uploaded?
      if(!isset($_FILES) || empty($_FILES))
         return false;
      if(!isset($_FILES['filemgr_upload']))
         return false;

      /* verify uploaded file is really there... */
      if(!file_exists($_FILES['filemgr_upload']['tmp_name'])) {
         print "Uploaded file "
            . basename($_FILES['filemgr_upload']['name'])
            . " not found in server's filesystem ("
            . $_FILES['filemgr_upload']['tmp_name']
            .")";
      }

      $src = $_FILES['filemgr_upload']['tmp_name'];

      /* locate the path to store the uploaded file into... */
      $bucket_path = $this->set_current_path($_POST['upload_path']);

      /* verify path really exists in local filesytsem... */
      if(!file_exists($bucket_path)) {
         return "Directory "
            . $bucket_path
            . " to upload "
            . $_FILES['filemgr_upload']['name']
            . " into does not exist!";
      }

      $dst = $bucket_path ."/". $_FILES['filemgr_upload']['name'];
   
      /* is there already a same-named file... */
      if(file_exists($dst)) {
         unlink($dst);
      }

      if(move_uploaded_file($src, $dst) === false) {
         return "An error occured when trying to move "
            . basename($_FILES['filemgr_upload']['name'])
            . " from "
            . $_FILES['filemgr_upload']['tmp_name']
            . " to "
            . $bucket_path
            . "/"
            . $_FILES['filemgr_upload']['name'];
      }

      return "success";

   } // handle_upload()

   public function smarty_file_list($params, $content, &$smarty, &$repeat)
   {
      if(count($this->bucket_items) <= 0) {
         $repeat = false;
         return "Directory is empty";
      }

      $index = $this->tmpl->get_template_vars('smarty.IB.item_list.index');
      if(!$index) {
         $index = 0;
      }

      if($index > count($this->avail_items)) {
         $repeat = false;
         return;
      }

      $item =  $this->bucket_items[$index];

      $this->tmpl->assign('item_type', $item['type']);
      $this->tmpl->assign('item_name', $item['name']);
      $this->tmpl->assign('item_size', $item['size']);
      $this->tmpl->assign('item_lastm', strftime("%Y-%m-%d %H:%M:%S", $item['last']));
      
      if($item['type'] == 'dir') {
         $this->tmpl->assign('item_details', $item['count_dirs'] ." subdirs, ". $item['count_files'] ." files");
      }
      else {
         $this->tmpl->assign('item_details', '');
      }

      $index++;
      $this->tmpl->assign('smarty.IB.item_list.index', $index);
      $repeat = true;

      return $content;

   } //smarty_file_list()

   public function smarty_file_breadcrumb_bar($params, &$smarty)
   {
      $string = "<a href=\"#\" onclick=\"filemgr_chdir('/');\">/</a>";

      if(!isset($_GET['path']))
         return $string;

      $bucket_path.= "/". $_GET['path'];
      $path_parts = split("/", $bucket_path);

      foreach($path_parts as $part) {

         if($part == "")
            continue;

         $string.= "&nbsp;»&nbsp;";
         $string.= "<a href=\"#\" onclick=\"filemgr_chdir('". $part ."');\">". $part ."</a>";
         
      }

      return $string;

   } // smarty_file_breadcrumb_bar()

   private function get_current_path()
   {
      if(isset($_SESSION['current_path']))
         return $_SESSION['current_path'];

      return false;

   } // get_current_path()

   private function set_current_path($path = null)
   {
      /* does the session know about, in which path we are currently in? */
      if(isset($_SESSION['current_path']) && !empty($_SESSION['current_path']))
         $bucket_base = $_SESSION['current_path'];

      /* if the current path is invalid, reset it */
      if(!$this->is_valid_current_path())
         unset($_SESSION['current_path']);

      /* if we still not know, where we are, move us to the buckets main dir */
      if(!isset($_SESSION['current_path']) || empty($_SESSION['current_path']))
         $bucket_base = $this->get_bucket_path();

      /* is the path really existing? */
      if(!file_exists($bucket_base)) {
         /* ok, we really do not know, where we are */
         unset($_SESSION['current_path']);
         return false;
      }

      $bucket_path = $bucket_base;

      /* shall we change the path now? */
      if(isset($path) && is_string($path))
         $bucket_path.= "/". $path;

      /* is someone tryining to mess up our path? */
      if(realpath($bucket_path) === false)
         $bucket_path = $bucket_base;

      /* if someone tried to do something bad with the provided path, and
         tries to go below bucket-base, set it back to buckets home dir.
       */
      $quoted_config_path = preg_quote($bucket_base, '/');
      $quoted_real_path   = preg_quote(realpath($bucket_path), '/');

      if(
         $pos = strpos($quoted_real_path, $quoted_config_path) === false
            ||
         $pos != 0
      ) {
         $bucket_path = $bucket_base;
      }

      $_SESSION['current_path'] = $bucket_path;

      return realpath($bucket_path);

   } // set_current_path()

   /**
    * validates the current path
    *
    * @return bool
    */
   private function is_valid_current_path()
   {
      /* check if current path is set */
      if(!isset($_SESSION['current_path']) || empty($_SESSION['current_path']))
         return false;

      /* is current path a valid path? */
      if(!file_exists($_SESSION['current_path']))
         return false;

      /* is buckets path known? */
      if(!$bucket_path = $this->get_bucket_path())
         return false;

      $bucket_path = str_replace("/", "\\/", $bucket_path);

      /* is the bucket_path still in the current_path? */
      if(!preg_match("/^". $bucket_path ."/", realpath($_SESSION['current_path'])))
         return false;

      return true;

   } // is_valid_current_path()

   /**
    * extracts information from a directory.
    *
    * list up all files (size, modification time, ...) and
    * directories (dir stats, modification time, ...).
    *
    * @param string $path
    * @return mixed
    */
   public function get_dir_items($path)
   {
      $items = Array();

      if(!file_exists($path)) {
         print "Directory ". $path ." does not exist!";
         return false;
      }

      $dir = opendir($path);

      while($item = readdir($dir)) {

         if($item == "." || $item == "..")
            continue;

         $fullpath = $path ."/". $item;

         $object = Array();

         if(is_file($fullpath)) {
            $object['type'] = 'file';
            $object['name'] = $item;
            $object['size'] = $this->parent->get_unit(filesize($fullpath));
            $object['last'] = filemtime($fullpath);
         }

         if(is_dir($fullpath)) {
            $sub_info = $this->parent->get_dir_info($fullpath);
            $object['type']  = 'dir';
            $object['name']  = $item;
            $object['size']  = $this->parent->get_unit($this->parent->get_used_diskspace($fullpath));
            $object['last']  = $sub_info['last_mod'];
            $object['count_dirs']  = $sub_info['dirs'];
            $object['count_files'] = $sub_info['files'];
         }
         array_push($items, $object);
      }

      return $items;

   } // get_dir_items()

   /**
    * delete item from bucket directory
    *
    * this function deletes files or directories from bucket
    * directories.
    *
    * @return boolean
    */
   private function delete_item()
   {
      if(!isset($_POST['item']) || !is_string($_POST['item']))
         return false;

      if(!isset($_POST['path']) || !is_string($_POST['path']))
         return false;

      /* locate the path to store the uploaded file into... */
      $_POST['path'] = $this->get_current_path($_POST['path']);

      $full_path = realpath($_POST['path'] ."/". $_POST['item']);

      if(!file_exists($full_path))
         return false;

      if(is_file($full_path))
         unlink($full_path);
      elseif(is_dir($full_path))
         $this->parent->deltree($full_path);

      return "ok";

   } // delete_item()

   /**
    * create new directory in bucket directory
    *
    * @return boolean
    */
   private function mkdir()
   {
      if(!isset($_POST['path']) || !is_string($_POST['path']))
         return false;

      $local_path = $this->get_current_path();
      $local_path.= "/". $_POST['path'];

      if(file_exists($local_path)) {
         return "Directory ". $_POST['path'] ." already exists!";
      }

      if(!mkdir($local_path))
         return false;

      return "ok";

   } // mkdir()

   /**
    * change directory
    *
    */
   private function chdir()
   {
      if(!$bucket_path = $this->set_current_path($_POST['path']))
         return false;

      $bucket_path = realpath($bucket_path);

      if(!file_exists($bucket_path))
         return "not existing directory";

      $_SESSION['current_path'] = $_POST['path'];

      return "ok";

   } // chdir()

   /**
    * returns buckets filesystem path
    *
    * @return string
    */
   private function get_bucket_path()
   {
      if(!isset($this->bucket_hash))
         return false;

      return $this->parent->cfg->data_path ."/". $this->bucket_hash;

   } // get_bucket_path()

} // class NEPHTHYS_UPLOAD

// vim: set filetype=php expandtab softtabstop=3 tabstop=3 shiftwidth=3 autoindent smartindent:
?>
