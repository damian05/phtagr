<?php
/*
 * phtagr.
 * 
 * Multi-user image gallery.
 * 
 * Copyright (C) 2006-2008 Sebastian Felis, sebastian@phtagr.org
 * 
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; version 2 of the 
 * License.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */
class HomeController extends AppController
{
	// Important to set the davroot in the Webdav Server
	var $name = 'home';

  var $components = array('Search');
  var $helpers = array('html');
  var $uses = array('Image', 'Tag', 'Category');

  function index() {
    $cloud = $this->Search->getCloud(50);
    $this->set('cloudTags', $cloud);

    $cloud = $this->Search->getCloud(50, 'Category');
    $this->set('cloudCategories', $cloud);
  }
}
?>