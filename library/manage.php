<?php

namespace Evolution\Pages;
use Evolution\Router;
use Evolution\Manage\Tile;
use e;

/**
 * Evolution Environment Manage
 */
class Manage {
	
	public $title = 'Pages';
	private static $cols = 'name segment scope matcher template theme map status parent_id';
	
	public function page($path) {
		$feature = array_shift($path);
		if($feature == 'edit')
			return $this->editPage(array_shift($path));
		
		$out = '';
		$out .= $this->section('active', 'Active Pages &bull; <a href="'.Router\BundleURL('manage').'/pages/edit/new">Add New Page</a>');
		return $out;
	}
	
	public function tile() {
	    $tile = new Tile('pages');
    	$tile->body .= '<h2>Manage your '.plural(e::pages()->getPages()->count(), 'installed page/s').'</h2>';
    	return $tile;
    }

	public function section($which, $title = 'Section', $var = null) {
		$which = 'section'.ucfirst($which);
		$out = '<div class="section"><h1>' . $title . '</h1>';
		$out .= $this->$which($var);
		$out .= '</div>';
		return $out;
	}
	
	public function sectionEmpty() {
		return '';
	}
	
	public function sectionActive() {
		$out = '';
		$list = e::pages()->getPages()->condition('status', 'active');
		$out .= $this->pagesTable($list);
		return $out;
	}
	
	public function pagesTable(&$list) {
		
		// Use for sorting tables? :: acemnorsuvwxz
		
		$out = '<table class="list"><tr>';
		$cols = explode(' ', self::$cols);
		foreach($cols as $name) {
			$ex = explode('_', $name);
			$name = ucfirst(array_shift($ex));
			$out .= "<th>$name</th>";
		}
		$out .= '</tr>';
		
		foreach($list as $item) {
			$out .= '<tr>';
			
			foreach($cols as $prop) {
				$out .= "<td>".htmlspecialchars($item->$prop)."</td>";
			}
			
			$out .= '</tr>';
		}
		
		return $out;
	}
	
	public function editPage($slug) {
		if("x".(0+1*$slug) === "x$slug") {
			$page = e::pages()->getPage($slug);
			$do = 'Editing Page #' . $page->id;
		} else if($slug === 'new') {
			$page = null;
			$do = 'Add New Page';
		} else
			return $this->section('empty', '<a href="'.Router\BundleURL('manage').'/pages">Back</a> &bull; Page Not Found');
		
		return $this->section('form', '<a href="'.Router\BundleURL('manage').'/pages">Cancel</a> &bull; ' . $do, $page);
	}
	
	public function sectionForm($page) {
		$out = '<form action="'.Router\BundleURL('pages').'.save" method="post">';
		
		$cols = explode(' ', self::$cols);
		foreach($cols as $oname) {
			$ex = explode('_', $oname);
			$name = ucfirst(array_shift($ex));
			$select = null;
			$val = is_object($page) ? $page->$oname : '';
			switch($oname) {
				case 'theme':
					$select = array('basename' => glob(\Evolution\Site\Root . '/portals/m/themes/*'));
					break;
				case 'status':
					$select = array('array' => array('active', 'inactive', 'review', 'testing'));
					break;
				default:
					$val = htmlspecialchars($val);
					$input = "<input name='$oname' value='$val' />";
			}
			if(!is_null($select)) {
				$input = "<select name='$oname'>";
				foreach($select as $type => $arr) {
					foreach($arr as $item) {
						switch($type) {
							case 'basename':
								$item = basename($item);
								break;
						}
						$selected = $val == $item ? ' selected="selected"' : '';
						$item  = htmlspecialchars($item);
						$input .= '<option value="'.$item.'"'.$selected.'>'.$item.'</option>';
					}
				}
				$input .= '</select>';
			}
			$out .= "<div class='var'><label>$name</label>$input<div class='clear'></div></div>";
		}
		
		$out .= '<div style="clear:both; padding: 30px 0;"><input type="submit" value="Save Page" /></div>';
		$out .= '</form>';
		return $out;
	}
}