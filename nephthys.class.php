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

require_once "nephthys_cfg.php";
require_once "nephthys_db.php";

class NEPHTHYS {

   var $cfg;
   var $db;
   var $cfg_db;
   var $tmpl;
   var $tags;
   var $avail_tags;

   private $runtime_error = false;
   private $dbver;

   /**
    * class constructor
    *
    * this function will be called on class construct
    * and will check requirements, loads configuration,
    * open databases and start the user session
    */
   public function __construct()
   {
      $this->cfg = new NEPHTHYS_CFG;

      /* verify config settings */
      if($this->check_config_options()) {
         exit(1);
      }

      /* set application name and version information */
      $this->cfg->product = "nephthys";
      $this->cfg->version = "1.0";

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
         print $this->cfg->base_path ."/templates_c: directory is not writeable for user ". $this->getuid() ."\n";
         exit(1);
      }

      /* overload Smarty class if our own template handler */
      require_once "nephthys_tmpl.php";
      $this->tmpl = new NEPHTHYS_TMPL($this);

      /* if session is not yet started, do it now */
      if(session_id() == "")
         session_start();

   } // __construct()

   public function __destruct()
   {

   } // __destruct()

   /**
    * show - generate html output
    *
    * this function can be called after the constructor has
    * prepared everyhing. it will load the index.tpl smarty
    * template. if necessary it will registere pre-selects
    * (photo index, photo, tag search, date search) into
    * users session.
    */
   public function show()
   {
      $this->tmpl->assign('searchfor_tag', $_SESSION['searchfor_tag']);
      $this->tmpl->assign('page_title', $this->cfg->page_title);
      $this->tmpl->assign('current_condition', $_SESSION['tag_condition']);
      $this->tmpl->assign('template_path', 'themes/'. $this->cfg->theme_name);

      if(isset($_GET['mode'])) {

         $_SESSION['start_action'] = $_GET['mode'];

         switch($_GET['mode']) {
            case 'showpi':
               if(isset($_GET['tags'])) {
                  $_SESSION['selected_tags'] = $this->extractTags($_GET['tags']);
               }
               if(isset($_GET['from_date']) && $this->isValidDate($_GET['from_date'])) {
                  $_SESSION['from_date'] = strtotime($_GET['from_date'] ." 00:00:00");
               }
               if(isset($_GET['to_date']) && $this->isValidDate($_GET['to_date'])) {
                  $_SESSION['to_date'] = strtotime($_GET['to_date'] ." 23:59:59");
               }
               break;
            case 'showp':
               if(isset($_GET['tags'])) {
                  $_SESSION['selected_tags'] = $this->extractTags($_GET['tags']);
                  $_SESSION['start_action'] = 'showp';
               }
               if(isset($_GET['id']) && is_numeric($_GET['id'])) {
                  $_SESSION['current_photo'] = $_GET['id'];
                  $_SESSION['start_action'] = 'showp';
               }
               if(isset($_GET['from_date']) && $this->isValidDate($_GET['from_date'])) {
                  $_SESSION['from_date'] = strtotime($_GET['from_date'] ." 00:00:00");
               }
               if(isset($_GET['to_date']) && $this->isValidDate($_GET['to_date'])) {
                  $_SESSION['to_date'] = strtotime($_GET['to_date'] ." 23:59:59");
               }
               break;
            case 'export':
               $this->tmpl->show("export.tpl");
               return;
               break;
            case 'slideshow':
               $this->tmpl->show("slideshow.tpl");
               return;
               break;
            case 'rss':
               if(isset($_GET['tags'])) {
                  $_SESSION['selected_tags'] = $this->extractTags($_GET['tags']);
               }
               if(isset($_GET['from_date']) && $this->isValidDate($_GET['from_date'])) {
                  $_SESSION['from_date'] = strtotime($_GET['from_date'] ." 00:00:00");
               }
               if(isset($_GET['to_date']) && $this->isValidDate($_GET['to_date'])) {
                  $_SESSION['to_date'] = strtotime($_GET['to_date'] ." 23:59:59");
               }
               $this->getRSSFeed();
               return;
               break;
         }
      }

      if(isset($_SESSION['from_date']) && isset($_SESSION['to_date']))
         $this->tmpl->assign('date_search_enabled', true);

      $this->tmpl->register_function("sort_select_list", array(&$this, "smarty_sort_select_list"), false);
      $this->tmpl->assign('from_date', $this->get_calendar('from'));
      $this->tmpl->assign('to_date', $this->get_calendar('to'));
      $this->tmpl->assign('content_page', 'welcome.tpl');
      $this->tmpl->show("index.tpl");

   } // show()

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


} // class NEPHTHYS

?>
