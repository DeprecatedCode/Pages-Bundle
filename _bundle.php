<?php
namespace Evolution\Pages;
use e;
use Evolution\Kernel\Service;
use Evolution\Text\JSON;
use Evolution\Kernel\Load;
use Evolution\Kernel\Completion;
use Evolution\Kernel\IncompleteException;
use Evolution\Kernel\Configure;
use Evolution\Kernel\Trace;
use Evolution\Kernel\View;
use Exception;


class Bundle extends \Evolution\SQL\SQLBundle {

	public function __construct($dir) {
		Service::bind(array($this, "route"), 'portal:route:pages');
		
		/**
		 * Add to manager
		 */
		Configure::add('manage.bundle', __NAMESPACE__, 'pages');
		parent::__construct($dir);
	}
	
	public function route($path, $dirs) {
		//echo "<div style='white-space:pre;font-family:andale mono; padding:20px;font-size:12px;'>";
		$dir = $dirs[0];
		if(isset($path[0]) && $path[0] == 'static') {
			Trace::$allow = false;
			array_shift($path);
			$file = array_pop($path);
			$ext = substr($file,strrpos($file,'.'));
			switch($ext) {
				case '.js':
					Header("content-type: application/x-javascript");
				case '.css':
					Header("content-type: text/css");
				case '.img':
				break;
			}
			echo file_get_contents($dir."/themes/".implode('/', $path).'/'.$file);
			
			throw new Completion;
		}
		// load all the pages to scan for
		$pages = $this->getPages()->condition('segment', $path[0]);
		$matches = array();
		foreach($pages as $page) {
			if(isset($path[1])) {
				if($page->matcher == $path[1]) {
					$matches[] = array('matched' => 'exact', 'key' => $path[1], 'page' => $page);
				}
				elseif($page->matcher == ':slug' && preg_match("/^[A-Za-z-]+$/", $path[1])) {
					$matches[] = array('matched' => ':slug', 'key' => $path[1], 'page' => $page);
				}
				elseif($page->matcher == ':id' && preg_match("/^[0-9]+$/", $path[1])) {
					$matches[] = array('matched' => ':id', 'key' => $path[1], 'page' => $page);
				}
			}
			elseif($page->matcher == '') {
				$matches[] = array('matched' => false, 'page' => $page);
			}
		}
		
		if(count($matches) > 1) throw new Exception("Multiple Matches for ".implode('/',$path));
		else $matches[0]['page']->render($matches[0]);
		throw new Completion;
		// get the first match
		// get the complexity of the match

		// check to see if there are multiple matches and return the most complex match, or return the highest priority
	}
	
	public function save() {
		
		$fields = explode(' ', 'name segment scope matcher template theme map status parent_id');
		$reqfields = explode(' ', 'name segment template theme status');
		
		$ok = true;
		foreach($reqfields as $field) {
			if(empty($_POST[$field])) {
				$ok = false;
				break;
			}
		}
		
		if($ok) die('OK');
		
		new View(array(
			'title' => 'Error Saving Page',
			'body' => '<div class="section"><h1>Invalid '.ucfirst($field).'</h1><div class="error">Please go back and enter a new value for <code>'.ucfirst($field).'</code></div>
					<div style="padding-top: 30px"><input type="submit" value="Go Back" onclick="javascript:history.go(-1);" /></div>
				</div>'
		));
	}
}