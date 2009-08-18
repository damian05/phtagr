<?php
/*
 * phtagr.
 * 
 * Multi-user image gallery.
 * 
 * Copyright (C) 2006-2009 Sebastian Felis, sebastian@phtagr.org
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

class ExplorerMenuHelper extends AppHelper
{
  var $helpers = array('html', 'search', 'menu');

  var $_id;

  /** Count the association
    @param association name
    @return Array of accociation name as key and their count as value */
  function _countAssociation($association) {
    $data = array();
    foreach ($this->data as $media) {
      $values = Set::extract("/$association/name", $media);
      foreach ($values as $value) {
        if (!isset($data[$value])) {
          $data[$value] = 1;
        } else {
          $data[$value]++;
        }
      }
    }
    arsort($data);
    return $data;
  }

  function _getAssociationExtra($association, $value, $id) {
    $out = " <div class=\"actionlist\" id=\"$id\">";

    $plural = Inflector::pluralize($association);
    $addLink = $this->search->getUri(false, array($plural => $value), array($plural => '-'.$value));
    $addIcon = $this->html->image('icons/add.png', array('alt' => '+', 'title' => "Include $association $value"));
    $out .= $this->html->link($addIcon, $addLink, false, false, false);

    $delLink = $this->search->getUri(false, array($plural => '-'.$value), array($plural => $value));
    $delIcon = $this->html->image('icons/delete.png', array('alt' => '-', 'title' => "Exclude $association $value"));
    $out .= $this->html->link($delIcon, $delLink, false, false, false);

    if ($this->action == 'user') {
      $worldLink = "/explorer/$association/$value";
      $worldIcon = $this->html->image('icons/world.png', array('alt' => '-', 'title' => "View all media with $association $value"));
      $out .= $this->html->link($worldIcon, $worldLink, false, false, false);
    }

    $out .= "</div>";
    return $out;
  }

  function _getSubMenu($association) {
    $counts = $this->_countAssociation(Inflector::camelize($association));
    $subMenu = array();
    $base = '/explorer';
    if ($this->action == 'user') {
      $base .= '/user/'.$this->params['pass'][0];
    }
    foreach($counts as $name => $count) {
      $id = "item-".$this->_id++;
      $link = $this->html->link($name, "$base/$association/$name");
      $extra = $this->_getAssociationExtra($association, $name, $id);
      $subMenu[] = array(
        'text' => "$link ($count) $extra",
        'type' => 'multi',
        'onmouseover' => "toggleVisibility('$id', 'inline');",
        'onmouseout' => "toggleVisibility('$id', 'inline');"
        );
    }
    return $subMenu;
  }

  function _getQueryOrderMenu() {
    $subMenu = array();
    
    $orders = array(
        'date' => 'Date', 
        'newest' => 'Newest', 
        'changes' => 'Changes', 
        'popularity' => 'Popularity', 
        'random' => 'Random'
      );
    foreach ($orders as $order => $name) {
      $link = $this->search->getUri(false, array('sort' => $order));
      $subMenu[] = array(
          'text' => $this->html->link($name, $link),
          'type' => 'multi'
        );
    }
    return $subMenu;
  }

  function getMainMenu() {
    $data = $this->data;
    $this->search->initialize();
    $items = array();
    $this->_id = 0;

    $search = '/explorer/search';
    /*
    if ($this->query->get('mymedia')) {
      $search .= '/user:'.$this->query->get('user');
    }
    */
    $items[] = array('text' => $this->html->link('Advance Search', $search));
    $items[] = array('text' => $this->html->link('Start Slideshow', 'javascript:startSlideshow();'));

    $subMenu = $this->_getSubMenu('tag');
    if ($subMenu !== false)
      $items[] = array('text' => 'Tags', 'type' => 'text', 'submenu' => array('items' => $subMenu));
    
    $subMenu = $this->_getSubMenu('category');
    if ($subMenu !== false)
      $items[] = array('text' => 'Categories', 'type' => 'text', 'submenu' => array('items' => $subMenu));

    $subMenu = $this->_getSubMenu('location');
    if ($subMenu !== false)
      $items[] = array('text' => 'Locations', 'type' => 'text', 'submenu' => array('items' => $subMenu));

    if ($this->params['action'] != 'search') {
      $items[] = array('text' => 'Order By', 'type' => 'text', 'submenu' => array('items' => $this->_getQueryOrderMenu()));
    }

    $menu = array('items' => $items);
    return $this->menu->getMainMenu($menu);
  }
}
?>
