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
   die("This script should only be called via command line.");
}

require_once "nephthys.class.php";

$GLOBALS['nephthys'] = new NEPHTHYS;
$db = $nephthys->db;

$nephthys_buckets = new NEPHTHYS_BUCKETS;

/* get all buckets */
$buckets = $db->db_query("
   SELECT
      b.bucket_idx as bucket_idx,
      b.bucket_name as bucket_name,
      b.bucket_expire as bucket_expire,
      b.bucket_created as bucket_created,
      b.bucket_hash as bucket_hash,
      u.user_name as user_name
   FROM nephthys_buckets b
   INNER JOIN nephthys_users u
      ON
         b.bucket_owner=u.user_idx
");

while($bucket = $buckets->fetchRow()) {

   /* check if the bucket can expire. -1 means it never expires */
   if($bucket->bucket_expire != -1) {

      $found_error = false;

      if(($bucket->bucket_created + ($bucket->bucket_expire * 86400)) <= mktime()) {

         print "Owner: ". $bucket->user_name .", Bucket: ". $bucket->bucket_name ." has expired: ";

         if(file_exists($nephthys->cfg->data_path ."/". $bucket->bucket_hash)) {

            if(!$nephthys_buckets->del_data_directory($bucket->bucket_hash)) {
               print "Can't delete bucket directory ". $this->cfg->data_path ."/". $bucket->bucket_hash .".";
               $found_error = true;
            }
         }

         if(!$found_error) {
            $db->db_query("
               DELETE FROM nephthys_buckets
               WHERE bucket_idx LIKE '". $bucket->bucket_idx ."'
            ");

            print "deleted\n";
         }
         else {
            print "\n";
         }
      }
   }
}

?>
