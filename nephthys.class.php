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
require_once "nephthys_addressbook.php";
require_once "nephthys_users.php";
require_once "nephthys_profile.php";

class NEPHTHYS {

   public $cfg;
   public $db;
   public $tmpl;
   public $current_user;
   public $browser_info;

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
         print "Error during load_config()<br />\n";
         exit(1);
      }

      // if servername has not been set in the configuration
      // get it from the webserver. Only necessary if not
      // called from command line.
      if(!isset($this->cfg->servername) && !$this->is_cmdline()) {
         if(!isset($_SERVER['SERVER_NAME']))
            die("Can't get server name out of \$_SERVER['SERVER_NAME']");
         $this->cfg->servername = $_SERVER['SERVER_NAME'];
      }

      /* Check necessary requirements */
      if(!$this->checkRequirements()) {
         exit(1);
      }

      $this->browser_info = new Net_UserAgent_Detect();

      if(!$this->is_cmdline() && (!isset($this->cfg->ignore_js) || empty($this->cfg->ignore_js))) {

         if(!$this->browser_info->hasFeature('javascript')) {
            print "It seems your browser is not capable of supporting JavaScript or it has been disabled.<br />\n";
            print "Nephthys will not correctly work without JavaScript!<br />\n";
            exit;
         }

     }

      /* if database type is set to sqlite, database exists
         but is not readable ...
      */
      if($this->cfg->db_type == "sqlite" &&
         file_exists($this->cfg->sqlite_path) &&
         !is_readable($this->cfg->sqlite_path)) {
         print "[". $this->cfg->sqlite_path ."] SQLite database is not readable for user ". $this->getuid() ."\n";
         exit(1);
      }

      /* if database type is set to sqlite, database exists
         but is not writeable ...
      */
      if($this->cfg->db_type == "sqlite" &&
         file_exists($this->cfg->sqlite_path) &&
         !is_writable($this->cfg->sqlite_path)) {
         print "[". $this->cfg->sqlite_path ."] SQLite database is not writeable for user ". $this->getuid() ."\n";
         exit(1);
      }

      /* if database type is set to sqlite, database does not exist
         yet and directory to store database is not writeable...
      */
      if($this->cfg->db_type == "sqlite" &&
         !file_exists($this->cfg->sqlite_path) &&
         !is_writable(dirname($this->cfg->sqlite_path))) {
         print "[". $this->cfg->sqlite_path ."] SQLite database can not be created in directory by user ". $this->getuid() ."\n";
         exit(1);
      }

      $this->db  = new NEPHTHYS_DB($this);

      $this->check_db_tables();

      if(!is_writable($this->cfg->base_path ."/templates_c")) {
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

      if(!$this->is_cmdline() &&
         isset($this->cfg->allow_server_auth) && $this->cfg->allow_server_auth == true
         && (!isset($_SERVER['REMOTE_USER']) || empty($_SERVER['REMOTE_USER']))) {
         print "Server authentication is enabled in Nephthys config but server does not "
            ."provide details in REMOTE_USER variable.\n";

         exit(1);
      }

      /* if server-authentication is allowed... */
      if(isset($this->cfg->allow_server_auth) &&
         $this->cfg->allow_server_auth == true) {

         /* if the user exists in Nephthys user table ... */
         if($user = $this->get_user_details_by_name($_SERVER['REMOTE_USER'])) {
            /* if user is active, register informations to session */
            if($user->user_active == 'Y') {
               $_SESSION['login_name'] = $user->user_name;
               $_SESSION['login_idx'] = $user->user_idx;
            }
         }
         /* otherwise, if auto-creation is enabled, create it... */
         else {

            /* is user-auto-creation enabled? */
            if(isset($this->cfg->user_auto_create) &&
               $this->cfg->user_auto_create == true) {

               if(isset($_SERVER['REMOTE_USER']) &&
                  $idx = $this->create_user($_SERVER['REMOTE_USER'])) {
                  if($user = $this->get_user_details_by_idx($idx)) {
                     $_SESSION['login_name'] = $user->user_name;
                     $_SESSION['login_idx'] = $user->user_idx;
                  }
               }
            }
         }
      }
      else {
         /* local authentication, if login data is already available */
         if(isset($_SESSION['login_idx']) && is_numeric($_SESSION['login_idx']))
            $user = $this->get_user_details_by_idx($_SESSION['login_idx']);
      }

      /* overload Smarty class if our own template handler */
      require_once "nephthys_tmpl.php";
      $this->tmpl = new NEPHTHYS_TMPL();

      if(isset($user->user_email) && !empty($user->user_email))
         $this->tmpl->assign('login_email', $user->user_email);

      /* if browser is type Internet Explorer set a template variable to
         inidicate to templates that browser is IE.
      */
      if(isset($this->browser_info) && $this->browser_info->isIE())
         $this->tmpl->assign('is_ie', true);

      $this->tmpl->assign('hide_logout', $this->cfg->hide_logout);
      $this->tmpl->assign('disk_used', $this->get_used_diskspace());
      $this->tmpl->assign('disk_free', $this->get_free_diskspace());

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
            $obj = new NEPHTHYS_USERS();
            break;
         case 'buckets':
            $obj = new NEPHTHYS_BUCKETS();
            break;
         case 'profile':
            $obj = new NEPHTHYS_PROFILE();
            break;
         case 'addressbook':
            $obj = new NEPHTHYS_ADDRESSBOOK();
            break;
         case 'about':
            return $this->tmpl->show("about.tpl");
            break;
         case 'help':
            return $this->tmpl->show("help.tpl");
            break;
         case 'savedbucket':
            $obj = new NEPHTHYS_BUCKETS();
            return $obj->showBucket();
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
               $obj = new NEPHTHYS_USERS;
               break;
            case 'buckets':
               $obj = new NEPHTHYS_BUCKETS;
               break;
            case 'profile':
               $obj = new NEPHTHYS_PROFILE;
               break;
            case 'addressbook':
               $obj = new NEPHTHYS_ADDRESSBOOK;
               break;
            default:
               return "unkown module";
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
         unset($php_errormsg);
      }
      // If database type is set to MySQL
      if($this->cfg->db_type == "mysql") {
         @include_once 'MDB2/Driver/mysql.php';
         if(isset($php_errormsg) && preg_match('/Failed opening.*for inclusion/i', $php_errormsg)) {
            print "PEAR MDB2-mysql package is missing<br />\n";
            $missing = true;
            unset($php_errormsg);
         }
      }
      // If database type is set to SQLite
      if($this->cfg->db_type == "sqlite") {
          @include_once 'MDB2/Driver/sqlite.php';
         if(isset($php_errormsg) && preg_match('/Failed opening.*for inclusion/i', $php_errormsg)) {
            print "PEAR MDB2-sqlite package is missing<br />\n";
            $missing = true;
            unset($php_errormsg);
         }
      }
      @include_once 'Mail.php';
      if(isset($php_errormsg) && preg_match('/Failed opening.*for inclusion/i', $php_errormsg)) {
         print "PEAR Mail package is missing<br />\n";
         $missing = true;
         unset($php_errormsg);
      }
      @include_once 'Net/UserAgent/Detect.php';
      if(isset($php_errormsg) && preg_match('/Failed opening.*for inclusion/i', $php_errormsg)) {
         print "PEAR Net_UserAgent_Detect package is missing<br />\n";
         $missing = true;
         unset($php_errormsg);
      }
      @include_once $this->cfg->smarty_path .'/libs/Smarty.class.php';
      if(isset($php_errormsg) && preg_match('/Failed opening.*for inclusion/i', $php_errormsg)) {
         print "Smarty template engine can not be found in ". $this->cfg->smarty_path ."/libs/Smarty.class.php<br />\n";
         $missing = true;
         unset($php_errormsg);
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
    * return the type of protocol used
    *
    * this function returns wether HTTP or HTTPS
    * is used for the client connection.
    *
    * @return string
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
    *
    * @return string
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

         if(!is_writable($this->cfg->log_file))
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
      /* if no user is logged in yet, return */
      if(!isset($_SESSION['login_name']))
         return NULL;

      $row = $this->db->db_fetchSingleRow("
         SELECT user_email
         FROM nephthys_users
         WHERE user_name LIKE '". $_SESSION['login_name'] ."'
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
            user_name LIKE '". $user_name ."'")) {

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
            user_idx LIKE '". $user_idx ."'")) {

         return $user;
      }

      return NULL;

   } // get_user_details()

   /**
    * returns user name
    *
    * @return string
    */
   public function get_user_name($user_idx)
   {
      if($user = $this->get_user_details_by_idx($user_idx)) {

         return $user->user_name;

      }

      return NULL;

   } // get_user_name()

   /**
    * return the specified users full name
    *
    * @return string
    */
   public function get_user_fullname($user_idx)
   {
      if($user = $this->get_user_details_by_idx($user_idx)) {

         return $user->user_full_name;

      }

      return NULL;

   } // get_user_fullname()

   /**
    * returns user privilege
    */
   public function get_user_priv($user_idx)
   {
      if($user = $this->get_user_details_by_idx($user_idx)) {

         return $user->user_priv;

      }

      return NULL;

   } // get_user_priv()

   /**
    * returns users default expiration time
    */
   public function get_user_expire($user_idx)
   {
      if($user = $this->get_user_details_by_idx($user_idx)) {
         return $user->user_default_expire;
      }
      return NULL;

   } // get_user_expire()

   /**
    * returns true if a user is logged in, otherwise false
    */
   public function is_logged_in()
   {
      if(isset($_SESSION['login_name']) && !empty($_SESSION['login_name']) &&
         $this->is_valid_user($_SESSION['login_name'])) {

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
    * validates all provided email addresses.
    * multiple email addresses are seperated by comma
    *
    * @param string $email
    * @return boolean
    */
   public function is_valid_email($email)
   {
      /* only one email address? */
      if(strstr($email, ',') === false)
         return $this->validate_email($email);

      /* multiple email addresses */
      $emails = split(",", $email);
      foreach($emails as $email_addr) {
         /* return as soon as an invalid address has been found */
         if(!$this->validate_email($email_addr))
            return false;
      }
      return true;

   } // is_valid_email()

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
         return $bucket->notify();
      }

      return "unkown bucket";

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
      $this->cfg->version = "1.1";
      $this->cfg->db_version = 2;

      return true;

   } // load_config()

   /**
    * check login
    */
   public function login()
   {
      if(isset($_POST['login_name']) && !empty($_POST['login_name']) &&
         isset($_POST['login_pass']) && !empty($_POST['login_pass'])) {

         /* get user details */
         if($user = $this->get_user_details_by_name($_POST['login_name'])) {

            /* reject inactive users */
            if($user->user_active != 'Y')
               return _("Invalid or inactive User.");

            /* do not allow auto-created users to login (they have no password set...) */
            if($user->user_auto_created != 'Y' &&
               $user->user_pass == sha1($_POST['login_pass'])) {

               $_SESSION['login_name'] = $_POST['login_name'];
               $_SESSION['login_idx'] = $user->user_idx;

               return _("ok");
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
    * returns true if the requests user privilege is matching
    * with the actually user privileges
    */
   public function check_privileges($priv)
   {
      if($user = $this->get_user_details_by_idx($_SESSION['login_idx'])) {
         if($user->user_priv == $priv)
            return true;
      }
      return false;

   } // check_privileges()

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

         if($bucket->bucket_owner == $_SESSION['login_idx'])
            return true;
      }

      return false;

   } // is_bucket_owner()

   /**
    * returns true if the requested user exists
    */
   public function check_user_exists($user_name)
   {
      switch($this->cfg->db_type) {
         default:
         case 'mysql':
            /* MySQL does case-censetive search by adding BINARY... */
            if($this->db->db_fetchSingleRow("
               SELECT user_idx
               FROM nephthys_users
               WHERE
                  user_name LIKE BINARY '". $user_name ."'
               ")) {
               return true;
            }
            break;
         case 'sqlite':
            if($this->db->db_fetchSingleRow("
               SELECT user_idx
               FROM nephthys_users
               WHERE
                  user_name LIKE '". $user_name ."'
               ")) {
               return true;
            }
            break;
      }

      return false;

   } // check_user_exists()

   public function _error($text)
   {
      switch($this->cfg->logging) {
         default:
         case 'display':
            print $text ."<br />\n";
            break;
         case 'errorlog':
            error_log($text);
            break;
         case 'logfile':
            error_log($text, 3, $his->cfg->log_file);
            break;
      }

      $this->runtime_error = true;

   } // _error()

   /**
    * generate complete bucket URL
    *
    * This function generates a complete URL to a specified
    * bucket provided via its hash value. It will either
    * return a WebDAV or FTP URL (specified by type).
    *
    * @param string $type
    * @param string $hash
    * @return string
    */
   public function get_url($type, $hash)
   {
      switch($type) {
         case 'ftp':
            $url = "ftp://";
            break;
         case 'dav':
            /* should a HTTPS URL be generated? */
            if(isset($this->cfg->use_https) && !empty($this->cfg->use_https))
               $url = "https://";
            else
               $url = "http://";
            break;
      }

      $url.= $this->cfg->servername;

      switch($type) {
         case 'ftp':
            $url.= $this->cfg->ftp_path;
            break;
         case 'dav':
            $url.= $this->cfg->dav_path;
            break;
      }

      $url.= "/". $hash ."/";

      return $url;

   } // get_url()

   /**
    * create user
    * @param string $username
    * @return object
    */
   private function create_user($username)
   {
      $sth = $this->db->db_prepare("
         INSERT INTO nephthys_users (
            user_idx, user_name, user_priv,
            user_active, user_auto_created
         ) VALUES (
            NULL, ?, 'user', 'Y', 'Y'
         )
      ");

      $this->db->db_execute($sth, array($username));

      return $this->db->db_getid();

   } // create_user()

   /**
    * return true if user is auto-created
    * @param integer $idx
    * @return boolean
    */
   public function is_auto_created($user_idx)
   {
      if($user = $this->db->db_fetchSingleRow("
         SELECT user_auto_created
         FROM nephthys_users
         WHERE
            user_idx LIKE '". $user_idx ."'
         ")) {

         if(isset($user->user_auto_created) && $user->user_auto_created == 'Y')
            return true;

      }

      return false;

   } // is_auto_created()

   /**
    * check if called from command line
    *
    * this function will return true, if called from command line
    * otherwise false.
    * @return boolean
    */
   private function is_cmdline()
   {
      if(isset($_ENV['SHELL']) && !empty($_ENV['SHELL']))
         return true;

      return false;

   } // is_cmdline()

   private function check_db_tables()
   {
      if(!$this->db->db_check_table_exists("nephthys_buckets")) {
         switch($this->cfg->db_type) {
            default:
            case 'mysql':
               $db_create = "CREATE TABLE `nephthys_buckets` (
                  `bucket_idx` int(11) NOT NULL auto_increment,
                  `bucket_name` varchar(255) default NULL,
                  `bucket_sender` varchar(255) default NULL,
                  `bucket_receiver` varchar(255) default NULL,
                  `bucket_hash` varchar(64) default NULL,
                  `bucket_created` int(11) default NULL,
                  `bucket_expire` int(11) default NULL,
                  `bucket_note` text,
                  `bucket_owner` int(11) default NULL,
                  `bucket_active` varchar(1) default NULL,
                  `bucket_notified` varchar(1) default NULL,
                  PRIMARY KEY  (`bucket_idx`)
                  ) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;
               ";
               break;
            case 'sqlite':
               $db_create = "CREATE TABLE nephthys_buckets (
                  bucket_idx INTEGER PRIMARY KEY,
                  bucket_name varchar(255),
                  bucket_sender varchar(255),
                  bucket_receiver varchar(255),
                  bucket_hash varchar(64),
                  bucket_created int,
                  bucket_expire int,
                  bucket_note text,
                  bucket_owner int,
                  bucket_active varchar(1),
                  bucket_notified varchar(1)
               )";
               break;
         }

         if(!$this->db->db_exec($db_create)) {
            die("Can't create table nephthys_buckets");
         }
      }

      if(!$this->db->db_check_table_exists("nephthys_users")) {
         switch($this->cfg->db_type) {
            default:
            case 'mysql':
               $db_create = "CREATE TABLE `nephthys_users` (
                  `user_idx` int(11) NOT NULL auto_increment,
                  `user_name` varchar(255) default NULL,
                  `user_full_name` varchar(255) default NULL,
                  `user_pass` varchar(255) default NULL,
                  `user_email` varchar(255) default NULL,
                  `user_priv` varchar(16) default NULL,
                  `user_active` varchar(1) default NULL,
                  `user_last_login` int(11) default NULL,
                  `user_default_expire` int(11) default NULL,
                  `user_auto_created` varchar(1) default NULL,
                  PRIMARY KEY  (`user_idx`)
                  ) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;
               ";
               break;
            case 'sqlite':
               $db_create = "CREATE TABLE nephthys_users (
                  user_idx INTEGER PRIMARY KEY,
                  user_name varchar(255),
                  user_full_name varchar(255),
                  user_pass varchar(255),
                  user_email varchar(255),
                  user_priv varchar(16),
                  user_active varchar(1),
                  user_last_login int,
                  user_default_expire int,
                  user_auto_created varchar(1)
                  )
               ";
               break;
         }

         if(!$this->db->db_exec($db_create)) {
            die("Can't create table nephthys_users");
         }

         $this->db->db_exec("
            INSERT INTO nephthys_users
            VALUES (
               NULL,
               'admin',
               '',
               'd033e22ae348aeb5660fc2140aec35850c4da997',
               '',
               'admin',
               'Y',
               NULL,
               7,
               NULL)
         ");

      }

      if(!$this->db->db_check_table_exists("nephthys_meta")) {
         switch($this->cfg->db_type) {
            default:
            case 'mysql':
               $db_create = "CREATE TABLE `nephthys_meta` (
                  `meta_idx` int(11) NOT NULL auto_increment,
                  `meta_key` varchar(255) default NULL,
                  `meta_value` varchar(255) default NULL,
                  PRIMARY KEY  (`meta_idx`)
                  ) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;
               ";
               break;
            case 'sqlite':
               $db_create = "CREATE TABLE nephthys_meta (
                  meta_idx INTEGER PRIMARY KEY,
                  meta_key varchar(255),
                  meta_value varchar(255)
               )";
               break;
         }

         if(!$this->db->db_exec($db_create)) {
            die("Can't create table nephthys_meta");
         }

         $this->db->db_exec("
            INSERT INTO nephthys_meta
            VALUES (
               NULL,
               'Nephthys Database Version',
               '". $this->cfg->db_version ."'
            )
         ");
      }

      if(!$this->db->db_check_table_exists("nephthys_addressbook")) {
         switch($this->cfg->db_type) {
            default:
            case 'mysql':
               $db_create = "CREATE TABLE `nephthys_addressbook` (
                  `contact_idx` int(11) NOT NULL auto_increment,
                  `contact_email` varchar(255) default NULL,
                  `contact_owner` int(11) default NULL,
                  PRIMARY KEY  (`contact_idx`)
                  ) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;
               ";
               break;
            case 'sqlite':
               $db_create = "CREATE TABLE nephthys_addressbook (
                  contact_idx INTEGER PRIMARY KEY,
                  contact_email varchar(255),
                  contact_owner INTEGER
               )";
               break;
         }

         if(!$this->db->db_exec($db_create)) {
            die("Can't create table nephthys_meta");
         }
      }

   } // check_db_tables()

   /**
    * add a email address to user's address book
    *
    * @param string $email
    */
   public function add_to_addressbook($email)
   {
      $to_ab = Array();

      /* only one email address? */
      if(strstr($email, ',') === false)
         array_push($to_ab, $email);

      /* multiple email addresses */
      $emails = split(",", $email);
      foreach($emails as $email_addr) {
         /* return as soon as an invalid address has been found */
         array_push($to_ab, $email_addr);
      }

      /* loop over all contacts */
      foreach($to_ab as $address) {

         /* do nothing if such a contact already exists */
         if($this->db->db_fetchSingleRow("
            SELECT *
            FROM nephthys_addressbook
            WHERE
               contact_email LIKE '". $address ."'
            ")) {
            continue;
         }

         $sth = $this->db->db_prepare("
            INSERT INTO nephthys_addressbook (
               contact_idx, contact_email, contact_owner
            ) VALUES (
               NULL, ?, ?
            )
         ");

         $this->db->db_execute($sth, array(
            $address,
            $_SESSION['login_idx'],
         ));

      }

   } // add_to_addressbook()

   /**
    * returns the value for the autocomplete tag-search
    * @return string
    */
   public function get_xml_list()
   {
      if(!isset($_GET['search']) || !is_string($_GET['search']))
         $_GET['search'] = '';

      $length = 15;
      $i = 1;

      $matched_contacts = Array();

      header("Content-Type: text/xml");

      $string = "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\n";
      $string.= "<results>\n";

      $contacts = $this->db->db_query("
         SELECT
            contact_idx, contact_email
         FROM
            nephthys_addressbook
         WHERE
            contact_owner LIKE '". $_SESSION['login_idx'] ."'
      ");

      while($contact = $contacts->fetchRow()) {

         if(!empty($_GET['search']) &&
            preg_match("/". $_GET['search'] ."/i", $contact->contact_email) &&
            count($matched_contacts) < $length) {

            $string.= " <rs id=\"". $i ."\" info=\"\">". htmlentities($contact->contact_email) ."</rs>\n";
            $i++;
         }

         /* if we have collected enough items, break out */
         if(count($matched_contacts) >= $length)
            break;
      }

      $string.= "</results>\n";

      return $string;

   } // get_xml_list()

   /**
    * return available disk space
    *
    * this function returns the available disk space of that
    * disk where $data_path resists.
    *
    * @return string
    */
   private function get_free_diskspace()
   {
      $bytes = disk_free_space($this->cfg->data_path);
      $bytes = $this->get_unit($bytes);
      return $bytes;

   } // get_free_diskspace()


   /**
    * return used disk space
    *
    * this functions returns the used disk space of that
    * disk where $data_path resists.
    *
    * @return string
    */
   private function get_used_diskspace()
   {
      $bytes = disk_total_space($this->cfg->data_path);
      $bytes = $this->get_unit($bytes);
      return $bytes;

   } // get_used_diskspace()

   /**
    * return size of unit
    *
    * this function returns the suitable unit for the
    * provided amount of bytes.
    *
    * @param int $bytes
    * @return string
    */
   private function get_unit($bytes)
   {
      /* if something went wrong and no value was supplied, return */
      if(!is_numeric($bytes))
         return "n/a";

      $symbols = array('b', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
      $exp = floor(log($bytes)/log(1024));
      return sprintf('%.2f '.$symbols[$exp], ($bytes/pow(1024, floor($exp))));

   } // get_unit()

} // class NEPHTHYS

/***************************************************************************
 *
 * NEPHTHYS_DEFAULTS class, inerhites by nephthys_cfg.php
 *
 ***************************************************************************/

class NEPHTHYS_DEFAULT_CFG {

   var $page_title  = "Nephthys - file sharing";
   var $base_path   = "/srv/www/htdocs/nephthys";
   var $data_path   = "/srv/www/nephthys_data";
   var $web_path    = "/nephthys";
   var $ftp_path    = "";
   var $dav_path    = "/transfer";

   var $theme_name  = "default";
   var $db_type     = "mysql";
   var $mysql_host  = "localhost";
   var $mysql_db    = "nephthys";
   var $mysql_user  = "user";
   var $mysql_pass  = "password";
   var $sqlite_path = "/srv/www/nephthys/nephthys.db";
   var $smarty_path = "/usr/share/php/smarty";
   var $logging     = "display";
   var $log_file    = "nephthys_err.log";
   var $ignore_js   = false;
   var $hide_logout = false;
   var $use_https   = false;
   var $bucket_via_dav = true;
   var $bucket_via_ftp = true;

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
