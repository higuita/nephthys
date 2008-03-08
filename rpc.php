<?php

/* *************************************************************************
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
 * *************************************************************************/

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

      }

   } // process_ajax_request();

} // class NEPHTHYS_RPC

$rpc = new NEPHTHYS_RPC();
$rpc->process_ajax_request();

?>