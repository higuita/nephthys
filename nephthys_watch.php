#!/usr/bin/php
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

if(!isset($_SERVER['argv'])) {
   die("This script should only be called via command line.");
}

require_once "nephthys.class.php";

$GLOBALS['nephthys'] = new NEPHTHYS;
$db = $nephthys->db;

$nephthys_buckets = new NEPHTHYS_BUCKETS;

/* get all buckets */
$buckets = $db->db_query("
   SELECT *
   FROM nephthys_buckets b
   INNER JOIN nephthys_users u
      ON
         b.bucket_owner=u.user_idx
");


while($bucket = $buckets->fetchRow()) {

   /* check if the bucket can expire. -1 means it never expires */
   if($bucket->bucket_expire != -1) {

      $found_error = false;

      if(($bucket->bucket_create + ($bucket->bucket_expire * 86400)) <= mktime()) {

         print "Owner: ". $bucket->user_name .", Bucket: ". $bucket->bucket_name ." has expired: ";

         if(file_exists($nephthys->cfg->data_path ."/". $bucket->bucket_hash)) {

            if(!$nephthys_buckets->del_data_directory($bucket->bucket_hash)) {
               print "Can't delete bucket directory ". $this->cfg->data_path ."/". $bucket->bucket_hash .".";
               $found_error = true;
            }

            $db->db_query("
               DELETE FROM nephthys_buckets
               WHERE bucket_idx LIKE '". $bucket->bucket_idx ."'
            ");

            if(!$found_error)
               print "deleted\n";
            else
               print "\n";

            break;
         }
      }
   }
}

?>
