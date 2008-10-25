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
   private $_translationTable;        // currently loaded translation table
   private $_loadedTranslationTables; // array of all loaded translation tables

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

      /* check if the bucket root directory ($data_path) exists */
      if(!file_exists($this->cfg->data_path)) {
         print "[". $this->cfg->data_path ."] directory does not exist\n";
         exit(1);
      }
      /* check if the webservers user is allowed to modify the bucket
         root directory ($data_path). This is necessary to create &
         delete bucket directories.
      */
      if(!is_writeable($this->cfg->data_path)) {
         print "[". $this->cfg->data_path ."] directory is not writeable for user ". $this->getuid() ."\n";
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
               /* update the last login time of this user */
               $this->update_last_login($user->user_idx);
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
                     /* update the last login time of this user */
                     $this->update_last_login($user->user_idx);
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

      if(isset($user) &&
         isset($user->user_language) &&
         !empty($user->user_language) &&
         in_array($user->user_language, array_keys($this->cfg->avail_langs))) {

         $this->cfg->language = $user->user_language;

      }

      /* load translation table for the current language */
      $this->load_translation_table();

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
      $this->tmpl->assign('disk_used', $this->get_unit($this->get_used_diskspace()));
      $this->tmpl->assign('disk_free', $this->get_unit($this->get_free_diskspace()));

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
      @include_once 'Console/Getopt.php';
      if(isset($php_errormsg) && preg_match('/Failed opening.*for inclusion/i', $php_errormsg)) {
         print "PEAR Console_Getopt package is missing<br />\n";
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

         $email_addr = trim($email_addr);
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
      /* if email has been entered in the format
            fullname <email-address>
         then we need to extract the address first
      */
      if(preg_match('/^(.+)\s\<(.+)\>/', $email, $matches)) {
         $email = $matches[2];
      }

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
      $this->cfg->version = "1.4";
      $this->cfg->db_version = 6;

      return true;

   } // load_config()

   /**
    * check login
    *
    * this function gets called via RPC to verify users entered
    * credential informations and permit or deny finally login.
    * @return string
    */
   public function login()
   {
      if(isset($_POST['login_name']) && !empty($_POST['login_name']) &&
         isset($_POST['login_pass']) && !empty($_POST['login_pass'])) {

         /* get user details */
         if($user = $this->get_user_details_by_name($_POST['login_name'])) {

            /* reject inactive users */
            if($user->user_active != 'Y')
               return $this->_("##FAILURE_USER_LOGON##");

            /* do not allow auto-created users to login (they have no password set...) */
            if($user->user_auto_created != 'Y' &&
               $user->user_pass == sha1($_POST['login_pass'])) {

               $_SESSION['login_name'] = $_POST['login_name'];
               $_SESSION['login_idx'] = $user->user_idx;

               /* update the last login time of this user */
               $this->update_last_login($user->user_idx);

               return "ok";
            }
            else {
               return $this->_("##FAILURE_PASSWORD##");
            }
         }
         else {
            return $this->_("##FAILURE_USER_LOGON##");
         }
      }
      else {
         return $this->_("##FAILURE_USER_PASS##");
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
    * user has permission for long-time buckets
    *
    * this function returns true, if the user is allowed to
    * create long-time buckets while he has only "user" privileges.
    */
   public function has_bucket_privileges()
   {
      if($user = $this->get_user_details_by_idx($_SESSION['login_idx'])) {
         if($user->user_priv_expire == 'Y')
            return true;
      }

      return false;

   } // has_bucket_privileges()

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
    * check if current user is owner of contact
    *
    * this function returns true, if the current user is owner
    * of the supplied address-book contact. Otherwise it will
    * return false
    *
    * @param int $bucket_idx
    * @return bool
    */
   public function is_contact_owner($contact_idx)
   {
      if($contact = $this->db->db_fetchSingleRow("
            SELECT *
            FROM nephthys_addressbook
            WHERE contact_idx LIKE '". $contact_idx ."'
         ")) {

         if($contact->contact_owner == $_SESSION['login_idx'])
            return true;
      }

      return false;

   } // is_contact_owner()

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
            user_active, user_auto_created,
            user_deny_chpwd
         ) VALUES (
            NULL, ?, 'user', 'Y', 'Y', 'Y'
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
    * return true if user is _not_ allowed to change its password
    * @param integer $idx
    * @return boolean
    */
   public function is_deny_chpwd($user_idx)
   {
      if($user = $this->db->db_fetchSingleRow("
         SELECT user_deny_chpwd
         FROM nephthys_users
         WHERE
            user_idx LIKE '". $user_idx ."'
         ")) {

         if(isset($user->user_deny_chpwd) && $user->user_deny_chpwd == 'Y')
            return true;

      }

      return false;

   } // is_deny_chpwd()

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

   /**
    * check Nephthys database
    *
    * this function checks the Nephthys database, if all
    * tables are in place or if an database upgrade has
    * to be done.
    */
   private function check_db_tables()
   {
      /* The following section checks if the necessary tables exist
         in the database. If not (usually on the first Nephthys run),
         they will be created and filled automatically.
      */

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
                  `user_priv_expire` varchar(1) default NULL,
                  `user_auto_created` varchar(1) default NULL,
                  `user_deny_chpwd` varchar(1) default NULL,
                  `user_language` varchar(6) default NULL,
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
                  user_priv_expire varchar(1),
                  user_auto_created varchar(1),
                  user_deny_chpwd varchar(1),
                  user_language varchar(6)
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
               'N',
               'N',
               'N',
               'en')
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
                  `contact_name` varchar(255) default NULL,
                  `contact_email` varchar(255) default NULL,
                  `contact_owner` int(11) default NULL,
                  PRIMARY KEY  (`contact_idx`)
                  ) ENGINE=MyISAM AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;
               ";
               break;
            case 'sqlite':
               $db_create = "CREATE TABLE nephthys_addressbook (
                  contact_idx INTEGER PRIMARY KEY,
                  contact_name varchar(255),
                  contact_email varchar(255),
                  contact_owner INTEGER
               )";
               break;
         }

         if(!$this->db->db_exec($db_create)) {
            die("Can't create table nephthys_meta");
         }
      }


      /* The following section keeps track of database upgrades. Nephthys
         notes the database revision in a own table called nephthys_meta.
      */

      /* db version 3 */
      if($this->get_db_version() < 3) {

         /* add bucket-never-expire column to nephthys_users */
         switch($this->cfg->db_type) {
            default:
            case 'mysql':
               $this->db->db_alter_table(
                  "nephthys_users",
                  "add",
                  "user_priv_expire",
                  "varchar(1)
                   AFTER
                   user_default_expire"
               );
               break;
            case 'sqlite':

               /* SQlite v2 does not support ALTER TABLE, so we need
                  to take the help of a temporary table.
               */
               if(!$this->db->db_start_transaction())
                  die("Can not start database transaction");

               $result = $this->db->db_exec("
                  CREATE TEMPORARY TABLE nephthys_users_tmp (
                     user_idx INTEGER PRIMARY KEY,
                     user_name varchar(255),
                     user_full_name varchar(255),
                     user_pass varchar(255),
                     user_email varchar(255),
                     user_priv varchar(16),
                     user_active varchar(1),
                     user_last_login int,
                     user_default_expire int,
                     user_priv_expire varchar(1),
                     user_auto_created varchar(1)
                  );
               ");

               if(!$result) {
                  $this->db->db_rollback_transaction();
                  die("Upgrade failover - tranaction rollback");
               }

               $result = $this->db->db_exec("
                  INSERT INTO nephthys_users_tmp
                     SELECT
                        user_idx,
                        user_name,
                        user_full_name,
                        user_pass,
                        user_email,
                        user_priv,
                        user_active,
                        user_last_login,
                        user_default_expire,
                        NULL,
                        user_auto_created
                     FROM nephthys_users;
               ");

               if(!$result) {
                  $this->db->db_rollback_transaction();
                  die("Upgrade failover - tranaction rollback");
               }

               $result = $this->db->db_exec("
                  DROP TABLE nephthys_users;
               ");

               if(!$result) {
                  $this->db->db_rollback_transaction();
                  die("Upgrade failover - tranaction rollback");
               }

               $result = $this->db->db_exec("
                  CREATE TABLE nephthys_users (
                     user_idx INTEGER PRIMARY KEY,
                     user_name varchar(255),
                     user_full_name varchar(255),
                     user_pass varchar(255),
                     user_email varchar(255),
                     user_priv varchar(16),
                     user_active varchar(1),
                     user_last_login int,
                     user_default_expire int,
                     user_priv_expire varchar(1),
                     user_auto_created varchar(1)
                  );
               ");

               if(!$result) {
                  $this->db->db_rollback_transaction();
                  die("Upgrade failover - tranaction rollback");
               }

               $result = $this->db->db_exec("
                  INSERT INTO nephthys_users
                     SELECT *
                     FROM nephthys_users_tmp;
               ");

               if(!$result) {
                  $this->db->db_rollback_transaction();
                  die("Upgrade failover - tranaction rollback");
               }

               $result = $this->db->db_exec("
                  DROP TABLE nephthys_users_tmp;
               ");

               if(!$result) {
                  $this->db->db_rollback_transaction();
                  die("Upgrade failover - tranaction rollback");
               }

               if(!$this->db->db_commit_transaction())
                  die("Can not commit database transaction");

               break;
         }

         $this->set_db_version(3);

      } /* // db version 3 */

      /* db version 4 */
      if($this->get_db_version() < 4) {

         /* add column user_language to nephthys_users */

         switch($this->cfg->db_type) {
            default:
            case 'mysql':
               $this->db->db_alter_table(
                  "nephthys_users",
                  "add",
                  "user_language",
                  "varchar(6)"
               );
               break;

            case 'sqlite':

               /* SQlite v2 does not support ALTER TABLE, so we need
                  to take the help of a temporary table.
               */
               if(!$this->db->db_start_transaction())
                  die("Can not start database transaction");

               $result = $this->db->db_exec("
                  CREATE TEMPORARY TABLE nephthys_users_tmp (
                     user_idx INTEGER PRIMARY KEY,
                     user_name varchar(255),
                     user_full_name varchar(255),
                     user_pass varchar(255),
                     user_email varchar(255),
                     user_priv varchar(16),
                     user_active varchar(1),
                     user_last_login int,
                     user_default_expire int,
                     user_priv_expire varchar(1),
                     user_auto_created varchar(1),
                     user_language varchar(6)
                  );
               ");

               if(!$result) {
                  $this->db->db_rollback_transaction();
                  die("Upgrade failover - tranaction rollback");
               }

               $result = $this->db->db_exec("
                  INSERT INTO nephthys_users_tmp
                     SELECT
                        user_idx,
                        user_name,
                        user_full_name,
                        user_pass,
                        user_email,
                        user_priv,
                        user_active,
                        user_last_login,
                        user_default_expire,
                        user_priv_expire,
                        user_auto_created,
                        NULL
                     FROM nephthys_users;
               ");

               if(!$result) {
                  $this->db->db_rollback_transaction();
                  die("Upgrade failover - tranaction rollback");
               }

               $result = $this->db->db_exec("
                  DROP TABLE nephthys_users;
               ");

               if(!$result) {
                  $this->db->db_rollback_transaction();
                  die("Upgrade failover - tranaction rollback");
               }

               $result = $this->db->db_exec("
                  CREATE TABLE nephthys_users (
                     user_idx INTEGER PRIMARY KEY,
                     user_name varchar(255),
                     user_full_name varchar(255),
                     user_pass varchar(255),
                     user_email varchar(255),
                     user_priv varchar(16),
                     user_active varchar(1),
                     user_last_login int,
                     user_default_expire int,
                     user_priv_expire varchar(1),
                     user_auto_created varchar(1),
                     user_language varchar(6)
                  );
               ");

               if(!$result) {
                  $this->db->db_rollback_transaction();
                  die("Upgrade failover - tranaction rollback");
               }

               $result = $this->db->db_exec("
                  INSERT INTO nephthys_users
                     SELECT *
                     FROM nephthys_users_tmp;
               ");

               if(!$result) {
                  $this->db->db_rollback_transaction();
                  die("Upgrade failover - tranaction rollback");
               }

               $result = $this->db->db_exec("
                  DROP TABLE nephthys_users_tmp;
               ");

               if(!$result) {
                  $this->db->db_rollback_transaction();
                  die("Upgrade failover - tranaction rollback");
               }

               if(!$this->db->db_commit_transaction())
                  die("Can not commit database transaction");

               break;
         }

         $this->set_db_version(4);

      } /* // db version 4 */

      /* db version 5 */
      if($this->get_db_version() < 5) {

         /* add column contact_name to nephthys_addressbook */

         switch($this->cfg->db_type) {
            default:
            case 'mysql':
               $this->db->db_alter_table(
                  "nephthys_addressbook",
                  "add",
                  "contact_name",
                  "varchar(255) default NULL"
               );
               break;

            case 'sqlite':

               /* SQlite v2 does not support ALTER TABLE, so we need
                  to take the help of a temporary table.
               */
               if(!$this->db->db_start_transaction())
                  die("Can not start database transaction");

               $result = $this->db->db_exec("
                  CREATE TEMPORARY TABLE nephthys_addressbook_tmp (
                     contact_idx INTEGER PRIMARY KEY,
                     contact_name varchar(255),
                     contact_email varchar(255),
                     contact_owner int
                  );
               ");

               if(!$result) {
                  $this->db->db_rollback_transaction();
                  die("Upgrade failover - tranaction rollback");
               }

               $result = $this->db->db_exec("
                  INSERT INTO nephthys_addressbook_tmp
                     SELECT
                        contact_idx,
                        NULL,
                        contact_email,
                        contact_owner
                     FROM nephthys_addressbook;
               ");

               if(!$result) {
                  $this->db->db_rollback_transaction();
                  die("Upgrade failover - tranaction rollback");
               }

               $result = $this->db->db_exec("
                  DROP TABLE nephthys_addressbook;
               ");

               if(!$result) {
                  $this->db->db_rollback_transaction();
                  die("Upgrade failover - tranaction rollback");
               }

               $result = $this->db->db_exec("
                  CREATE TABLE nephthys_addressbook (
                     contact_idx INTEGER PRIMARY KEY,
                     contact_name varchar(255),
                     contact_email varchar(255),
                     contact_owner int
                  );
               ");

               if(!$result) {
                  $this->db->db_rollback_transaction();
                  die("Upgrade failover - tranaction rollback");
               }

               $result = $this->db->db_exec("
                  INSERT INTO nephthys_addressbook
                     SELECT *
                     FROM nephthys_addressbook_tmp;
               ");

               if(!$result) {
                  $this->db->db_rollback_transaction();
                  die("Upgrade failover - tranaction rollback");
               }

               $result = $this->db->db_exec("
                  DROP TABLE nephthys_addressbook_tmp;
               ");

               if(!$result) {
                  $this->db->db_rollback_transaction();
                  die("Upgrade failover - tranaction rollback");
               }

               if(!$this->db->db_commit_transaction())
                  die("Can not commit database transaction");

               break;
         }

         $this->set_db_version(5);

      } /* // db version 5 */

      /* db version 6 */
      if($this->get_db_version() < 6) {

         /* add column user_deny_chpwd to nephthys_users */

         switch($this->cfg->db_type) {
            default:
            case 'mysql':
               $this->db->db_alter_table(
                  "nephthys_users",
                  "add",
                  "user_deny_chpwd",
                  "varchar(1) default NULL"
               );
               break;

            case 'sqlite':

               /* SQlite v2 does not support ALTER TABLE, so we need
                  to take the help of a temporary table.
               */
               if(!$this->db->db_start_transaction())
                  die("Can not start database transaction");

               $result = $this->db->db_exec("
                  CREATE TEMPORARY TABLE nephthys_users_tmp (
                     user_idx INTEGER PRIMARY KEY,
                     user_name varchar(255),
                     user_full_name varchar(255),
                     user_pass varchar(255),
                     user_email varchar(255),
                     user_priv varchar(16),
                     user_active varchar(1),
                     user_last_login int,
                     user_default_expire int,
                     user_priv_expire varchar(1),
                     user_auto_created varchar(1),
                     user_deny_chpwd varchar(1),
                     user_language varchar(6)
                  )
               ");

               if(!$result) {
                  $this->db->db_rollback_transaction();
                  die("Upgrade failover - tranaction rollback");
               }

               $result = $this->db->db_exec("
                  INSERT INTO nephthys_users_tmp
                     SELECT
                        user_idx,
                        user_name,
                        user_full_name,
                        user_pass,
                        user_email,
                        user_priv,
                        user_active,
                        user_last_login,
                        user_default_expire,
                        user_priv_expire,
                        user_auto_created,
                        NULL,
                        user_language
                     FROM nephthys_users;
               ");

               if(!$result) {
                  $this->db->db_rollback_transaction();
                  die("Upgrade failover - tranaction rollback");
               }

               $result = $this->db->db_exec("
                  DROP TABLE nephthys_users;
               ");

               if(!$result) {
                  $this->db->db_rollback_transaction();
                  die("Upgrade failover - tranaction rollback");
               }

               $result = $this->db->db_exec("
                  CREATE TABLE nephthys_users (
                     user_idx INTEGER PRIMARY KEY,
                     user_name varchar(255),
                     user_full_name varchar(255),
                     user_pass varchar(255),
                     user_email varchar(255),
                     user_priv varchar(16),
                     user_active varchar(1),
                     user_last_login int,
                     user_default_expire int,
                     user_priv_expire varchar(1),
                     user_auto_created varchar(1),
                     user_deny_chpwd varchar(1),
                     user_language varchar(6)
                  )
               ");

               if(!$result) {
                  $this->db->db_rollback_transaction();
                  die("Upgrade failover - tranaction rollback");
               }

               $result = $this->db->db_exec("
                  INSERT INTO nephthys_users
                     SELECT *
                     FROM nephthys_users_tmp;
               ");

               if(!$result) {
                  $this->db->db_rollback_transaction();
                  die("Upgrade failover - tranaction rollback");
               }

               $result = $this->db->db_exec("
                  DROP TABLE nephthys_users_tmp;
               ");

               if(!$result) {
                  $this->db->db_rollback_transaction();
                  die("Upgrade failover - tranaction rollback");
               }

               if(!$this->db->db_commit_transaction())
                  die("Can not commit database transaction");

               break;
         }

         /* per default we deny every auto-created user
            to change his password.
         */
         $this->db->db_query("
            UPDATE
               nephthys_users
            SET
               user_deny_chpwd='Y'
            WHERE
               user_auto_created LIKE 'Y'
         ");
         /* per default we allowe every non auto-created user
            to change his password.
         */
          $this->db->db_query("
            UPDATE
               nephthys_users
            SET
               user_deny_chpwd='N'
            WHERE
               user_auto_created NOT LIKE 'Y'
         ");

         $this->set_db_version(6);

      } /* // db version 6 */

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
         $email_addr = trim($email_addr);
         array_push($to_ab, $email_addr);
      }

      /* loop over all contacts */
      foreach($to_ab as $address) {

         $fullname = '';
         $address = $this->escape($address);

         /* when entered in the format
               fullname <email-address>
            we need to extract the parts of that string first
         */
         if(preg_match('/^(.+)\s\<(.+)\>/', $address, $matches)) {
            $fullname = $matches[1];
            $address = $matches[2];
         }

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
               contact_idx,
               contact_email,
               contact_owner,
               contact_name
            ) VALUES (
               NULL,
               ?,
               ?,
               ?
            )
         ");

         $this->db->db_execute($sth, array(
            $address,
            $_SESSION['login_idx'],
            $fullname,
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

      /* strip leading or pending whitespaces */
      $_GET['search'] = trim($_GET['search']);

      /* if string contains multiple receivers separated by
         a comma character, just handle the last one entered.
      */
      if($matches = explode(',', $_GET['search'])) {
         $_GET['search'] = trim($matches[count($matches)-1]);
      }

      $length = 15;
      $i = 1;

      $matched_contacts = Array();

      header("Content-Type: text/xml");

      $string = "<?xml version=\"1.0\" encoding=\"utf-8\" ?>\n";
      $string.= "<results>\n";

      $contacts = $this->db->db_query("
         SELECT
            contact_idx,
            contact_name,
            contact_email
         FROM
            nephthys_addressbook
         WHERE
            contact_owner LIKE '". $_SESSION['login_idx'] ."'
      ");

      while($contact = $contacts->fetchRow()) {

         /* ignore empty searches */
         if(empty($_GET['search']))
            break;

         if((
               preg_match("/". $_GET['search'] ."/i", $contact->contact_email) ||
               preg_match("/". $_GET['search'] ."/i", $contact->contact_name)
            )&&
            count($matched_contacts) < $length) {

            $string.= " <rs id=\"". $i ."\" ";

            /* if a contact-name is available, add it as info for autosuggest */
            if(isset($contact->contact_name) && !empty($contact->contact_name))
               $string.= " info=\"". $this->unescape($contact->contact_name, false) ."\">";
            else
               $string.= " info=\"\">";

            $string.= $this->unescape($contact->contact_email, false);
            $string.= "</rs>\n";

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
      return $bytes;

   } // get_free_diskspace()


   /**
    * return used disk space
    *
    * this functions returns the used disk space of that
    * disk where $data_path resists.
    *
    * @param string $path
    * @return string
    */
   private function get_used_diskspace($path = NULL)
   {
      /* this function will be recursive. if no path is provided
         as parameter, use the $data_path to start from.
      */
      if(!isset($path))
         $path = $this->cfg->data_path;

      $bytes = 0;
      $dirhandle = opendir($path);

      while($file = readdir($dirhandle)) {
         if($file!="." && $file!="..") {
            if(is_dir($path."/".$file)) {
               $bytes = $bytes + $this->get_used_diskspace($path."/".$file);
            }
            else {
               $bytes = $bytes + filesize($path."/".$file);
            }
         }
      }

      closedir($dirhandle);

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
      $symbols = array('b', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');

      /* if $bytes = 0, return 0b */
      if(empty($bytes))
         return '0'. $symbols[0];

      /* if something went wrong and no value was supplied, return */
      if(!is_numeric($bytes))
         return "n/a";

      $exp = floor(log($bytes)/log(1024));

      /* if $bytes was to small, return 0b */
      if($exp == -INF)
         return '0'. $symbols[0];

      return sprintf('%.2f '.$symbols[$exp], ($bytes/pow(1024, floor($exp))));

   } // get_unit()

   /**
    * get database version
    *
    * this function queries the nephthys_meta table
    * and returns the current database version.
    *
    * @return integer
    */
   public function get_db_version()
   {
      if($row = $this->db->db_fetchSingleRow("
         SELECT meta_value
         FROM
            nephthys_meta
         WHERE
            meta_key LIKE 'Nephthys Database Version'
         ")) {

         return $row->meta_value;

      }

      return 0;

   } // get_db_version()

   /**
    * set database version
    *
    * this function updates the nephthys_meta table
    * with the version number provided as the first
    * parameter.
    *
    * @param int $version
    */
   public function set_db_version($version)
   {
      if(isset($version) && $version > 0) {

         $this->db->db_exec("
            UPDATE
               nephthys_meta
            SET
               meta_value='". $version ."'
            WHERE
               meta_key LIKE 'Nephthys Database Version'
         ");

      }

   } // set_db_version()

   public function _($text)
   {
      return $this->get_translation($text);
   }

   function get_language()
   {
      return $this->cfg->language;

   } // get_language()

   function load_translation_table()
   {
      $locale = $this->get_language();

      $path = $this->cfg->base_path
         . '/themes/'
         . $this->cfg->theme_name
         . '/lang/'
         . $locale
         . '.lang';

      if (isset($this->_loadedTranslationTables[$locale])) {
         if (in_array($path, $this->_loadedTranslationTables[$locale])) {
            // Translation table was already loaded
            return true;
         }
      }

      /* if the language file is not available, stop execution. */
      if(!file_exists($path) || !is_readable($path)) {
         die("Can not open language file $path");
      }

      $entries = file($path);
      $this->_translationTable[$locale][$path] = Array();
      $this->_loadedTranslationTables[$locale][] = $path;

      foreach ($entries as $row) {

         $row = trim($row);

         // ignore empty lines
         if(empty($row))
            continue;

         // ignore lines with comments
         if (substr(ltrim($row),0,2) == '//') // ignore comments
            continue;

         $keyValuePair = explode('=',$row);

         // multiline values: the first line with an equal sign '=' will start a new key=value pair
         if(sizeof($keyValuePair) == 1) {
            $this->_translationTable[$locale][$key] .= ' ' . chop($keyValuePair[0]);
            continue;
         }

         $key = trim($keyValuePair[0]);
         $value = $keyValuePair[1];
         if (!empty($key)) {
            $this->_translationTable[$locale][$key] = chop($value);
         }
      }

      return true;
   }

   function get_translation($key)
   {
      $locale = $this->get_language();

      // if get_tranlation() get called via RPC (indirect), the translation
      // table may not be loaded yet.
      if (!isset($this->_loadedTranslationTables[$locale]))
         $this->load_translation_table($locale);

      $trans = $this->_translationTable[$locale];

      /* get the real translation key */
      $key = preg_replace('/##(.+?)##/', '${1}', $key);

      if (is_array($trans)) {
         if (isset($trans[$key])) {
            return $trans[$key];
         }
      }

      return "Can not find translation for key $key";
   }

   /**
    * escape string to avoid SQL injection
    *
    * this function will let call a MySQL own function
    * to escape all dangerous stuff within the provided
    * text. If the MySQL function is not available, it
    * just returns the provided string simply escaped
    * with addslashes()
    *
    * @param string $text
    * @return string
    */
   public function escape($text)
   {
      /* if text has already been escaped, we need to strip
         slashes before
      */

      if($this->cfg->db_type == 'mysql' &&
         function_exists('mysql_real_escape_string')) {

         if(get_magic_quotes_gpc() == 1)
            $text = stripslashes($text);

         return mysql_real_escape_string($text);
      }

      if($this->cfg->db_type == 'sqlite' &&
         function_exists('sqlite_escape_string')) {

         if(get_magic_quotes_gpc() == 1)
            $text = stripslashes($text);

         return sqlite_escape_string($text);
      }

      return addslashes($text);

   } // escape()

   /**
    * unescape string and translate some characters to HTML
    *
    * this function gets used on strings previously modified
    * by escape(). It will strip of slashes and translate
    * some special characters (quotes for example) to HTML
    * entities (if $encode_html == true).
    *
    * @param string $text
    * @param boolean $encode_html
    * @return string
    */
   public function unescape($text, $encode_html = true)
   {
      /* if text has already been escaped, we need to strip
         slashes before
      */

      //$text = stripslashes($text);

      if($encode_html)
         return htmlspecialchars($text);

      return $text;

   } // unescape()

   /**
    * update users last login time
    *
    * this function updates the users last login time
    * in the database table nephthys_users.
    * @param int $user_idx
    */
   private function update_last_login($user_idx)
   {
      $this->db->db_query("
         UPDATE
            nephthys_users
         SET
            user_last_login='". mktime() ."'
         WHERE
            user_idx LIKE '". $user_idx ."'
      ");

   } // update_last_login()

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
   var $sqlite_path = "/srv/www/nephthys_db/nephthys.db";
   var $smarty_path = "/usr/share/php/smarty";
   var $logging     = "display";
   var $log_file    = "nephthys_err.log";
   var $ignore_js   = false;
   var $hide_logout = false;
   var $use_https   = false;

   var $bucket_via_dav = true;
   var $bucket_via_ftp = true;

   var $allow_server_auth = false;
   var $user_auto_create  = false;
   var $expirations       = Array(
      "1;1 ##DAY##;user",
      "3;3 ##DAYS##;user",
      "7;1 ##WEEK##;user",
      "30;1 ##MONTH##;user",
      "186;6 ##MONTHS##;manager",
      "365;1 ##YEAR##; manager",
      "-1;##NEVER##; manager",
   );

   var $language    = "en";
   var $avail_langs = Array(
      "en" => "English",
      "de" => "German",
      "ru" => "Russian",
      "it" => "Italian",
      "es" => "Spanish",
      "nl" => "Dutch",
   );

} // class NEPHTHYS_DEFAULT_CFG

// vim: set filetype=php expandtab softtabstop=3 tabstop=3 shiftwidth=3 autoindent smartindent:
?>
