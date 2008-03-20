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
   public function __construct()
   {
      global $nephthys;
      $this->parent =& $nephthys;
      $this->db =& $nephthys->db;
      $this->tmpl =& $nephthys->tmpl;

   } // __construct()

   /* interface output */
   public function show()
   {
      if(!$this->parent->is_logged_in()) {
         $this->parent->printError("<img src=\"". ICON_USERS ."\" alt=\"user icon\" />&nbsp;". _("Manage Users"), _("You do not have enough permissions to access this module!"));
         return 0;
      }
       if(!isset($_GET['mode']))
         $_GET['mode'] = "show";
      if(!isset($_GET['idx']) ||
         (isset($_GET['idx']) && !is_numeric($_GET['idx'])))
         $_GET['idx'] = 0;

      switch($_GET['mode']) {
         case 'receive':
            return $this->tmpl->show('receive_form.tpl');
         case 'send':
            return $this->tmpl->show('send_form.tpl');
         case 'notify':
            $this->notify();
            break;
      }

   } // show()

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
