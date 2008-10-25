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
                  print "invalid option";
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

      /* get all buckets */
      $buckets = $this->db->db_query("
         SELECT
            b.bucket_idx as bucket_idx,
            b.bucket_name as bucket_name,
            b.bucket_expire as bucket_expire,
            b.bucket_created as bucket_created,
            b.bucket_hash as bucket_hash,
            u.user_name as user_name
         FROM
            nephthys_buckets b
         INNER JOIN nephthys_users u
            ON
               b.bucket_owner=u.user_idx
      ");

      while($bucket = $buckets->fetchRow()) {

         /* check if the bucket can expire. -1 means it never expires */
         if($bucket->bucket_expire != -1) {

            $found_error = false;

            $log_msg =
               "Bucket: ". $bucket->bucket_name .", ".
               "Owner: ". $bucket->user_name .", ".
               "Expired on: ". date("%c", $bucket->bucket_expire) .", ";

            /* has the bucket expired? */
            if(($bucket->bucket_created + ($bucket->bucket_expire * 86400)) <= mktime()) {

               /* does the bucket-directory still exist? */
               if(file_exists($this->parent->cfg->data_path ."/". $bucket->bucket_hash)) {
                  /* lets delete the bucket-directory recursivly */
                  if(!$nephthys_buckets->del_data_directory($bucket->bucket_hash)) {
                     $this->parent->_error("ERROR: Can't delete bucket directory ". $this->parent->cfg->data_path ."/". $bucket->bucket_hash .".");
                     $found_error = true;
                  }
               }
               else {
                  $this->parent->_error("WARNING: Bucket directory ". $this->parent->cfg->data_path ."/". $bucket->bucket_hash ." no longer exists.");
               }

               /* if directory-deletion was successful, delete bucket from database */
               if(empty($found_error)) {
                  $this->db->db_query("
                     DELETE FROM nephthys_buckets
                     WHERE bucket_idx LIKE '". $bucket->bucket_idx ."'
                  ");

                  if(!empty($this->verbose))
                     $log_msg.= "deleted";
               }
               else {
               }

               if(empty($found_error) && !empty($this->verbose))
                  $this->parent->_error($log_msg);
            }
         }
      }

   } // watch()

} // NEPHTHYS_WATCH

$class = new NEPHTHYS_WATCH;

// vim: set filetype=php expandtab softtabstop=3 tabstop=3 shiftwidth=3 autoindent smartindent:
?>
