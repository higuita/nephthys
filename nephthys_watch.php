#!/usr/bin/php
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

if(!isset($_SERVER['argv'])) {
   die("This script needs to be called from command line.");
}

class NEPHTHYS_WATCH {

   private $db;
   private $parent;
   private $verbose;

   /**
    * NEPHTHYS_WATCH constructor
    *
    * Initialize the NEPHTHYS_WATCH class
    */
   public function __construct()
   {
      require_once "nephthys.class.php";

      $nephthys = new NEPHTHYS;
      $this->parent =& $nephthys;
      $this->db =& $nephthys->db;

      $this->verbose = false;

      $short_options = "";
      $short_options.= "h"; /* help */
      $short_options.= "v"; /* overwrite */

      $long_options = array(
         "help",
         "verbose",
      );

      /* command line option specified? */
      if(isset($_SERVER['argc']) && $_SERVER['argc'] > 1) {
         /* validate */
         $con = new Console_Getopt;
         $args = $con->readPHPArgv();
         $options = $con->getopt($args, $short_options, $long_options);

         if(PEAR::isError($options)) {
            die ("Error in command line: " . $options->getMessage() . "\n");
         }

         foreach($options[0] as $opt) {
            switch($opt[0]) {
               case 'h':
               case '--help':
                  print "nephthys_watch.php - cleanup expired buckets\n"
                     ."http://oss.netshadow.at\n"
                     ."\n"
                     ."   ./nephthys_watch.php <options>\n"
                     ."\n"
                     ."   -h ... this help text\n"
                     ."   -v ... be verbose\n"
                     ."\n";
                  exit(0);
                  break;
               case 'v':
               case '--verbose':
                  $this->verbose = true;
                  break;
               default:
                  $this->parent->_error("invalid option(s) provided. use --help to see possibile options.");
                  exit(1);
                  break;
            }
         }
      }

      $this->watch();

   } // __construct()

   public function watch()
   {
      $nephthys_buckets = new NEPHTHYS_BUCKETS;
      $expired_buckets = $nephthys_buckets->get_expired_buckets();

      foreach($expired_buckets as $bucket) {

         $found_error = false;

         $bucket_details = $nephthys_buckets->get_bucket_details($bucket);

         $log_msg =
            "Bucket: ". $bucket_details->bucket_name .", ".
            "Owner: ". $this->parent->get_user_name($bucket_details->bucket_owner) .", ".
            "Expired on: ". date("%c", $bucket_details->bucket_expire) .", ";

         /* does the bucket-directory still exist? */
         if(file_exists($this->parent->cfg->data_path ."/". $bucket_details->bucket_hash)) {
            /* lets delete the bucket-directory recursivly */
            if(!$nephthys_buckets->del_data_directory($bucket_details->bucket_hash)) {
               $this->parent->_error("ERROR: Can't delete bucket directory ". $this->parent->cfg->data_path ."/". $bucket_details->bucket_hash .".");
               $found_error = true;
            }
         }
         else {
            $this->parent->_error("WARNING: Bucket directory ". $this->parent->cfg->data_path ."/". $bucket_details->bucket_hash ." no longer exists.");
         }

         /* if directory-deletion was successful, delete bucket from database */
         if(empty($found_error)) {

            /* check if deletion on this bucket needs to be notified */
            if($bucket_details->bucket_notify_on_expire == 'Y') {
               $nephthys_buckets->notify_expired_bucket($bucket);
            }
            /* delete bucket from database */
            $nephthys_buckets->delete_bucket($bucket);

            if(!empty($this->verbose)) {
               $log_msg.= "deleted";
            }
         }

         if(empty($found_error) && !empty($this->verbose)) {
            $this->parent->_error($log_msg);
         }
      }

   } // watch()

} // NEPHTHYS_WATCH

$class = new NEPHTHYS_WATCH;

// vim: set filetype=php expandtab softtabstop=3 tabstop=3 shiftwidth=3 autoindent smartindent:
?>
