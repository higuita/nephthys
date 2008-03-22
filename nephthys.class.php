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

require_once "nephthys_db.php";
require_once "nephthys_buckets.php";
require_once "nephthys_users.php";

class NEPHTHYS {

   public $cfg;
   public $db;
   public $tmpl;
   public $current_user;

   private $runtime_error = false;

   /**
    * class constructor
    *
    * this function will be called on class construct
    * and will check requirements, loads configuration,
    * open databases and start the user session
    */
   public function __construct()
   {
      $GLOBALS['nephthys'] =& $this;

      /* load config, exit if it fails */
      if(!$this->load_config()) {
         exit(1);
      }

      if(!isset($this->cfg->servername)) {
         $this->cfg->servername = $_SERVER['SERVER_NAME'];
      }

      $this->sort_orders= array(
         'date_asc' => 'Date &uarr;',
         'date_desc' => 'Date &darr;',
         'name_asc' => 'Name &uarr;',
         'name_desc' => 'Name &darr;',
         'tags_asc' => 'Tags &uarr;',
         'tags_desc' => 'Tags &darr;',
      );

      /* Check necessary requirements */
      if(!$this->checkRequirements()) {
         exit(1);
      }

      $this->db  = new NEPHTHYS_DB($this);

      if(!is_writeable($this->cfg->base_path ."/templates_c")) {
         print "[". $this->cfg->base_path ."/templates_c] directory is not writeable for user ". $this->getuid() ."\n";
         exit(1);
      }

      /* if session is not yet started, do it now */
      if(session_id() == "")
         session_start();

      /*if(!isset($_SERVER['REMOTE_USER']) || empty($_SERVER['REMOTE_USER'])) {
         print "It seems you are not authenticated through the server";
         exit(1);
      }
      */

      if(isset($this->cfg->allow_server_auth) && $this->cfg->allow_server_auth == true) {
         $_SESSION['user_name'] = $_SERVER['REMOTE_USER'];
      }

      /* overload Smarty class if our own template handler */
      require_once "nephthys_tmpl.php";
      $this->tmpl = new NEPHTHYS_TMPL();

   } // __construct()

   public function __destruct()
   {

   } // __destruct()

   /**
    * init - generate html output
    *
    * this function can be called after the constructor has
    * prepared everyhing. it will load the index.tpl smarty
    * template. if necessary it will registere pre-selects
    * (photo index, photo, tag search, date search) into
    * users session.
    */
   public function init()
   {
      $this->tmpl->show("index.tpl");

   } // init()

   /**
    * outputs the main content template
    */
   public function show()
   {
      $this->tmpl->show("main.tpl");

   } // show()

   /**
    * outputs the menu template()
    */
   public function get_menu()
   {
      $this->tmpl->show("menu.tpl");

   } // get_menu()

   /**
    * return main content
    */
   public function get_content()
   {
      if(!$this->is_logged_in()) {
         $this->tmpl->show("login_box.tpl");
         return;
      }

      if(isset($_GET['id']) && is_string($_GET['id']))
         $request = $_GET['id'];
      if(isset($_POST['id']) && is_string($_POST['id']))
         $request = $_POST['id'];

      switch($request) {
         case 'main':
            $obj = $this;
            break;
         case 'users':
            $obj = new NEPHTHYS_USERS($this);
            break;
         case 'groups':
            $obj = new NEPHTHYS_GROUPS($this);
            break;
         case 'buckets':
            $obj = new NEPHTHYS_BUCKETS($this);
            break;
         case 'credits':
            return $this->tmpl->show("credits.tpl");
            break;
      }

      if(isset($obj))
         return $obj->show();

   } // get_content()

   public function store()
   {
      if(!$this->is_logged_in()) {
         return "login first";
      }

      if(isset($_POST['module'])) {
         switch($_POST['module']) {
            case 'users':
               $obj = new NEPHTHYS_USERS($this);
               break;
            case 'buckets':
               $obj = new NEPHTHYS_BUCKETS;
               break;
         }

         if(isset($obj)) {
            switch($_POST['mode']) {
               case 'modify': return $obj->store(); break;
               case 'delete': return $obj->delete(); break;
               case 'toggle': return $obj->toggleStatus(); break;
            }
         }
      }

   } // store()

   /**
    * check if all requirements are met
    */
   private function checkRequirements()
   {
      /* Check for HTML_AJAX PEAR package, lent from Horde project */
      ini_set('track_errors', 1);
      @include_once 'HTML/AJAX/Server.php';
      if(isset($php_errormsg) && preg_match('/Failed opening.*for inclusion/i', $php_errormsg)) {
         print "PEAR HTML_AJAX package is missing<br />\n";
         $missing = true;
      }
      @include_once 'MDB2.php';
      if(isset($php_errormsg) && preg_match('/Failed opening.*for inclusion/i', $php_errormsg)) {
         print "PEAR MDB2 package is missing<br />\n";
         $missing = true;
      }
      @include_once 'MDB2/Driver/mysql.php';
      if(isset($php_errormsg) && preg_match('/Failed opening.*for inclusion/i', $php_errormsg)) {
         print "PEAR MDB2-mysql package is missing<br />\n";
         $missing = true;
      }
      @include_once 'Mail.php';
      if(isset($php_errormsg) && preg_match('/Failed opening.*for inclusion/i', $php_errormsg)) {
         print "PEAR Mail package is missing<br />\n";
         $missing = true;
      }
      @include_once $this->cfg->smarty_path .'/libs/Smarty.class.php';
      if(isset($php_errormsg) && preg_match('/Failed opening.*for inclusion/i', $php_errormsg)) {
         print "Smarty is missing<br />\n";
         $missing = true;
      }
      ini_restore('track_errors');

      if(isset($missing))
         return false;

      return true;

   } // checkRequirements()

   private function _debug($text)
   {
      if($this->fromcmd) {
         print $text;
      }

   } // _debug()

   /**
    * returns type of webprotocol which is
    * currently used
    */
   private function get_web_protocol()
   {
      if(!isset($_SERVER['HTTPS']))
         return "http";
      else
         return "https";
   } // get_web_protocol()

   /**
    * return url to this installation
    */
   private function get_nephthys_url()
   {
      return $this->get_web_protocol() ."://". $this->get_server_name() . $this->cfg->web_path;
   } // get_nephthys_url()
   
   /**
    * check file exists and is readable
    *
    * returns true, if everything is ok, otherwise false
    * if $silent is not set, this function will output and
    * error message
    */
   private function check_readable($file, $silent = null)
   {
      if(!file_exists($file)) {
         if(!isset($silent))
            print "File \"". $file ."\" does not exist.\n";
         return false;
      }

      if(!is_readable($file)) {
         if(!isset($silent))
            print "File \"". $file ."\" is not reachable for user ". $this->getuid() ."\n";
         return false;
      }

      return true;

   } // check_readable()

   /**
    * parse the provided URI and will returned the
    * requested chunk
    */
   public function parse_uri($uri, $mode)
   {
      if(($components = parse_url($uri)) !== false) {

         switch($mode) {
            case 'filename':
               return basename($components['path']);
               break;
            case 'dirname':
               return dirname($components['path']);
               break;
            case 'fullpath':
               return $components['path'];
               break;
         }
      }

      return $uri;

   } // parse_uri()

   /**
    * validate config options
    *
    * this function checks if all necessary configuration options are
    * specified and set.
    */
   private function check_config_options()
   {
      if(!isset($this->cfg->page_title) || $this->cfg->page_title == "")
         $this->_error("Please set \$page_title in nephthys_cfg");

      if(!isset($this->cfg->base_path) || $this->cfg->base_path == "")
         $this->_error("Please set \$base_path in nephthys_cfg");

      if(!isset($this->cfg->web_path) || $this->cfg->web_path == "")
         $this->_error("Please set \$web_path in nephthys_cfg");

      if(!isset($this->cfg->smarty_path) || $this->cfg->smarty_path == "")
         $this->_error("Please set \$smarty_path in nephthys_cfg");

      if(!isset($this->cfg->theme_name))
         $this->_error("Please set \$theme_name in nephthys_cfg");

      if(!isset($this->cfg->mysql_host))
         $this->_error("Please set \$mysql_host in nephthys_cfg");

      if(!isset($this->cfg->mysql_db))
         $this->_error("Please set \$mysql_db in nephthys_cfg");

      if(!isset($this->cfg->mysql_user))
         $this->_error("Please set \$mysql_user in nephthys_cfg");

      if(!isset($this->cfg->mysql_pass))
         $this->_error("Please set \$mysql_pas in nephthys_cfg");

      if(!isset($this->cfg->logging))
         $this->_error("Please set \$logging in nephthys_cfg");

      if(isset($this->cfg->logging) && $this->cfg->logging == 'logfile') {

         if(!isset($this->cfg->log_file))
            $this->_error("Please set \$log_file because you set logging = log_file in nephthys_cfg");

         if(!is_writeable($this->cfg->log_file))
            $this->_error("The specified \$log_file ". $log_file ." is not writeable!");

      }

      /* check for pending slash on web_path */
      if(!preg_match("/\/$/", $this->cfg->web_path))
         $this->cfg->web_path.= "/";

      return $this->runtime_error;

   } // check_config_options()

   /**
    * return the current process-user
    */
   private function getuid()
   {
      if($uid = posix_getuid()) {
         if($user = posix_getpwuid($uid)) {
            return $user['name'];
         }
      }

      return 'n/a';

   } // getuid()

   /**
    * returns the current logged-on user's email address
    */
   public function get_users_email()
   {
      $row = $this->db->db_fetchSingleRow("
         SELECT user_email
         FROM nephthys_users
         WHERE user_name LIKE '". $_SESSION['user_name'] ."'
      ");

      if(isset($row->user_email)) {
         return $row->user_email;
      }

      return NULL;

   } // get_users_email()

   /**
    * return all user details for the provided user_name
    */
   private function get_user_details_by_name($user_name)
   {
      if($user = $this->db->db_fetchSingleRow("
         SELECT *
         FROM nephthys_users
         WHERE
            user_name LIKE '". $user_name ."'
         AND
            user_active='Y'")) {

         return $user;
      }

      return NULL;

   } // get_user_detail_by_name()

   /**
    * return all user details for the provided user_idx
    */
   private function get_user_details_by_idx($user_idx)
   {
      if($user = $this->db->db_fetchSingleRow("
         SELECT *
         FROM nephthys_users
         WHERE
            user_idx LIKE '". $user_idx ."'
         AND
            user_active='Y'")) {

         return $user;
      }

      return NULL;

   } // get_user_details()

   /**
    * returns user name
    */
   public function get_user_name($user_idx)
   {
      if($user = $this->get_user_details_by_idx($user_idx)) {

         return $user->user_name;

      }

      return NULL;
   }

   /**
    * returns user privilege
    */
   public function get_user_priv($user_idx)
   {
      if($user = $this->get_user_details_by_idx($user_idx)) {

         return $user->user_priv;

      }

      return NULL;
   }

   /**
    * returns true if a user is logged in, otherwise false
    */
   public function is_logged_in()
   {
      if(isset($_SESSION['user_name']) && !empty($_SESSION['user_name']) &&
         $this->is_valid_user($_SESSION['user_name'])) {

         return true;

      }

      return false;

   } // is_logged_in()

   /**
    * return true if the user exists
    */
   private function is_valid_user($user_name)
   {
      if($this->db->db_fetchSingleRow("
            SELECT user_idx
            FROM nephthys_users
            WHERE user_name LIKE '". $user_name ."'
         ")) {

         return true;

      }

      return false;

   } // is_valid_user()

   /**
    * return bucket details
    */
   public function getbucketDetails($idx)
   {
      if($row = $this->db->db_fetchSingleRow("
            SELECT *
            FROM nephthys_buckets
            WHERE bucket_idx LIKE '". $idx ."'
         ")) {

         return $row;

      }
   } // getbucketDetails()

   /***
    * verify email address
    *
    * found on: http://www.ilovejackdaniels.com/php/email-address-validation/
   */
   public function validate_email($email)
   {
      //if php version < 5.2
      if ( version_compare( phpversion(), "5.2","<" ) ) {
         // First, we check that there's one @ symbol, and that the lengths are right
         if (!ereg("^[^@]{1,64}@[^@]{1,255}$", $email)) {
            // Email invalid because wrong number of characters in one section, or wrong number of @ symbols.
            return false;
         }
         // Split it into sections to make life easier
         $email_array = explode("@", $email);
         $local_array = explode(".", $email_array[0]);
         for ($i = 0; $i < sizeof($local_array); $i++) {
            if (!ereg("^(([A-Za-z0-9!#$%&'*+/=?^_`{|}~-][A-Za-z0-9!#$%&'*+/=?^_`{|}~\.-]{0,63})|(\"[^(\\|\")]{0,62}\"))$", $local_array[$i])) {
               return false;
            }
         }
         if (!ereg("^\[?[0-9\.]+\]?$", $email_array[1])) { // Check if domain is IP. If not, it should be valid domain name
            $domain_array = explode(".", trim($email_array[1]));
            if (sizeof($domain_array) < 2) {
               return false; // Not enough parts to domain
            }
            for ($i = 0; $i < sizeof($domain_array); $i++) {
               if (!ereg("^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|([A-Za-z0-9]+))$", $domain_array[$i])) {
                  return false;
               }
            }
         } else {
            //regular expression verifies that each component is a number from 1 to 3 characters in length
            if (!ereg("^([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})\.([0-9]{1,3})$", $email_array[1])){
               return false;
            }
         }
      } else if ( filter_var( $email, FILTER_VALIDATE_EMAIL ) === false ){
         return false;
      }
      return true;

   } // validate_email() 

   /**
    * generates a SHA-1 hash from the provided parameters
    * and some random stuff
    */
   public function get_sha_hash($sender, $receiver = false)
   {
      if(!$receiver)
         $receiver = mktime();

      return sha1($sender . $receiver . rand(0, 32768));

   } // get_sha_hash()

   public function notifybucket()
   {
      if(isset($_POST['id']) && is_numeric($_POST['id'])) {
         $bucket = new NEPHTHYS_BUCKETS($_POST['id']);
         $bucket->notify();
      }

   } // notifybucket()

   /**
    * load Nephthys configuration file
    */
   private function load_config()
   {
      ini_set('track_errors', 1);
      @include_once "nephthys_cfg.php";
      if(isset($php_errormsg) && preg_match('/Failed opening.*for inclusion/i', $php_errormsg)) {
         print "Can't read nephthys_cfg.php or have no permission to do it. Follow the documentation\n";
         print "create nephthys_cfg.php from nephthys_cfg.php.dist<br />\n";
         return false;
      }
      ini_restore('track_errors');

      $this->cfg = new NEPHTHYS_CFG;

      /* verify config settings */
      if($this->check_config_options()) {
         return false;
      }

      /* set application name and version information */
      $this->cfg->product = "Nephthys";
      $this->cfg->version = "1.0";

      return true;

   } // load_config()

   /**
    * check login
    */
   public function login()
   {
      if(isset($_POST['user_name']) && $_POST['user_name'] != "" &&
         isset($_POST['user_pass']) && $_POST['user_pass'] != "") {

         if($user = $this->get_user_details_by_name($_POST['user_name'])) {
            if($user->user_pass == sha1($_POST['user_pass'])) {
               $_SESSION['user_name'] = $_POST['user_name'];
               $_SESSION['user_idx'] = $user->user_idx;

               return "ok";
            }
            else {
               return _("Invalid Password.");
            }
         }
         else {
            return _("Invalid or inactive User.");
         }
      }
      else {
         return _("Please enter Username and Password.");
      }

   } // check_login()

   /**
    * destroy the current user session to force logout
    */
   public function logout()
   {
      foreach($_SESSION as $k => $v) {
         unset($_SESSION[$k]);
      }

      session_destroy();

      return "ok";

   } // destroySession()

   /**
    * returns true if user has only "user" privileges
    */
   public function has_user_priv()
   {
      if($user = $this->get_user_details_by_idx($_SESSION['user_idx'])) {
         if($user->user_priv == "user")
            return true;
      }

      return false;

   } // has_user_priv()

   /**
    * returns true, if user is owner of the supplied bucket
    */
   public function is_bucket_owner($bucket_idx)
   {
      if($bucket = $this->db->db_fetchSingleRow("
            SELECT *
            FROM nephthys_buckets
            WHERE bucket_idx LIKE '". $bucket_idx ."'
         ")) {

         if($bucket->bucket_owner == $_SESSION['user_idx'])
            return true;
      }

      return false;

   } // is_bucket_owner()

} // class NEPHTHYS

/***************************************************************************
 *
 * NEPHTHYS_DEFAULTS class, inerhites by nephthys_cfg.php
 *
 ***************************************************************************/

class NEPHTHYS_DEFAULT_CFG {

   var $page_title  = "Nephthys - file sharing";
   var $base_path   = "/var/www/htdocs/nephthys";
   var $data_path   = "/srv/www/nephthys_data";
   var $web_path    = "/nephthys";
   var $theme_name  = "default";
   var $mysql_host  = "localhost";
   var $mysql_db    = "nephthys";
   var $mysql_user  = "user";
   var $mysql_pass  = "password";
   var $smarty_path = "/usr/share/php/smarty";
   var $logging     = "display";
   var $log_file    = "nephthys_err.log";

   var $allow_server_auth = false;
   var $user_auto_create = false;
   var $expirations  = Array(
      "1;1 Day;user",
      "3;3 Days;user",
      "7;1 Week;user",
      "30;1 Month;user",
      "186;6 Months;manager",
      "365;1 Year; manager",
      "-1;never; manager",
   );

} // class NEPHTHYS_DEFAULT_CFG

?>
