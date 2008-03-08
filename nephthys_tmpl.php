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

class NEPHTHYS_TMPL extends Smarty {

   var $parent;

   public function __construct($parent)
   {
      if(!file_exists($parent->cfg->base_path .'/themes/'. $parent->cfg->theme_name .'/templates')) {
         print "No templates found in ". $parent->cfg->base_path .'/themes/'. $parent->cfg->theme_name .'/templates';
         exit(1);
      }

      $this->Smarty();
      $this->template_dir = $parent->cfg->base_path .'/themes/'. $parent->cfg->theme_name .'/templates';
      $this->compile_dir  = $parent->cfg->base_path .'/templates_c';
      $this->config_dir   = $parent->cfg->base_path .'/smarty_config';
      $this->cache_dir    = $parent->cfg->base_path .'/smarty_cache';

   } // __construct()

   public function show($template)
   {
      $this->display($template);

   } // show()

}

?>
