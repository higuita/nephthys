<?php

/***************************************************************************
 *
 * Copyright (c) by Andreas Unterkircher, unki@netshadow.at
 * All rights reserved
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
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

class NEPHTHYS_BUCKET {

   private $db;
   private $parent;
   private $tmpl;
   private $id;

   /**
    * NEPHTHYS_BUCKET constructor
    *
    * Initialize the NEPHTHYS_BUCKET class
    */
   public function __construct($parent, $id)
   {
      $this->parent = &$parent;
      $this->db = &$parent->db;
      $this->tmpl = &$parent->tmpl;
      $this->id = $id;

   } // __construct()

   public function notify()
   {
      if(!($bucket = $this->parent->getbucketDetails($this->id)))
         return;

      $header['From'] = $bucket->bucket_sender;
      $header['To'] = $bucket->bucket_receiver;
      $header['Subject'] = "File sharing information";

      $text = new NEPHTHYS_TMPL($this->parent);
      $text->assign('bucket_sender', $bucket->bucket_sender);
      $text->assign('bucket_receiver', $bucket->bucket_receiver);
      $text->assign('bucket_hash', $bucket->bucket_hash);
      $text->assign('bucket_servername', "www.orf.at");
      $body = $text->fetch('notify.tpl');

      $mailer =& Mail::factory('mail');
      $status = $mailer->send($bucket->bucket_receiver, $header, $body);
      if(PEAR::isError($status)) {
         return $status->getMessage();
      }

      return "ok";

   } // notify()

} // class NEPHTHYS_BUCKET

?>
