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
use Exception;


class Bundle extends \Evolution\SQL\SQLBundle {

	public function __construct($dir) {
		Service::bind(array($this, "route"), 'portal:route:pages');
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

}