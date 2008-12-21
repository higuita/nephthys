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

require_once "nephthys.class.php";

class NEPHTHYS_RPC {

   public function __construct()
   {
      session_start();

   } // __construct()

   public function process_ajax_request()
   {
      require_once 'HTML/AJAX/Server.php';

      $server = new HTML_AJAX_Server();
      $server->handleRequest();

      $nephthys = new NEPHTHYS();

      /* if no action is specified, no need to further process this
       * function here.
       */
      if(!isset($_GET['action']) && !isset($_POST['action']))
         return;

      if(isset($_GET['action']))
         $action = $_GET['action'];
      if(isset($_POST['action']))
         $action = $_POST['action'];

      switch($action) {
         case 'get_content':
            $nephthys->get_content();
            break;
         case 'get_menu':
            $nephthys->get_menu();
            break;
         case 'store':
            print $nephthys->store();
            break;
         case 'login':
            print $nephthys->login();
            break;
         case 'logout':
            print $nephthys->logout();
            break;
         case 'notifybucket':
            print $nephthys->notifybucket();
            break;
         case 'deletebucket':
            print $nephthys->delete_bucket();
            break;
         case 'validateemail':
            if(isset($_POST['address']) && !empty($_POST['address']) && $nephthys->is_valid_email($_POST['address']))
               print "ok";
            else
               print "failed";
            break;
         case 'sortorder':
            print $nephthys->update_sort_order();
            break;
         case 'getxmllist':
            print $nephthys->get_xml_list();
            break;
         case 'get_bucket_info':
            print $nephthys->get_bucket_info();
            break;
         default:
            print "unkown action ". $action;
            break;
      }

   } // process_ajax_request();

} // class NEPHTHYS_RPC

$rpc = new NEPHTHYS_RPC();
$rpc->process_ajax_request();

// vim: set filetype=php expandtab softtabstop=3 tabstop=3 shiftwidth=3 autoindent smartindent:
?>
