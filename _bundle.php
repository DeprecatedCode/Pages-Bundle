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

class PageMatchException extends \Exception { }
class PageTemplateException extends \Exception { }

class Bundle extends \Evolution\SQL\SQLBundle {
	
	public $portal;

	public function __construct($dir) {
		Service::bind(array($this, "route"), 'portal:route:pages');
		
		/**
		 * Add to manager
		 */
		Configure::add('manage.bundle', __NAMESPACE__, 'pages');
		parent::__construct($dir);
	}
	
	public function template($theme, $template) {

		if(empty($theme))
			throw new PageTemplateException("You must specify a theme for \"A\" page.");
		
		if(!is_dir("$this->portal/themes/$theme"))
			throw new PageTemplateException("The theme directory `$theme` does not exist.");
		
		$file = "$this->portal/themes/$theme/template/$template.tpl";
		
		if(!file_exists($file))
			throw new PageTemplateException("The template `$file` could not be found.");
		
		return file_get_contents($file);
	}
	
	public function route($path, $dirs) {
		//echo "<div style='white-space:pre;font-family:andale mono; padding:20px;font-size:12px;'>";
		$dir = $dirs[0];
		$this->portal = $dir;
		if(isset($path[0]) && $path[0] == 'static') {
			Trace::$allow = false;
			array_shift($path);
			$file = array_pop($path);
			$ext = substr($file,strrpos($file,'.')+1);
			switch($ext) {
				case 'js':
					Header("content-type: application/x-javascript");
				break;
				case 'css':
					Header("content-type: text/css");
				break;
				case 'jpg':
					Header("content-type: image/jpeg");
				break;
				case 'img':
				break;
			}
			readfile($dir."/themes/".implode('/', $path).'/'.$ext.'/'.$file);
			
			throw new Completion;
		}
		
		if(!isset($path[0])) throw new PageMatchException("No segment was provided please provide a URL segment.");
		
		$url = implode('/',$path);
		
		// load all the pages to scan for
		$pages = $this->getPages()->condition('segment', array_shift($path));
		
		$slug = array_shift($path);
		
		$matches = array();
		foreach($pages as $page) {
			
			/**
			 * If no page matcher throw an exception
			 */
			if(!$page->matcher)
				throw new PageMatchException("No matcher was specified when calling `/$url`. you must provide `:id`, `:slug`, or `<i>string</i>`.");
			
			if(!is_null($slug)) {
				if($page->matcher == $slug) {
					$matches[] = array('matched' => 'exact', 'key' => $slug, 'page' => $page);
				}
				elseif($page->matcher == ':slug' && preg_match("/^[A-Za-z-]+$/", $slug)) {
					$matches[] = array('matched' => ':slug', 'key' => $slug, 'page' => $page);
				}
				elseif($page->matcher == ':id' && preg_match("/^[0-9]+$/", $slug)) {
					$matches[] = array('matched' => ':id', 'key' => $slug, 'page' => $page);
				}
			}
			elseif($page->matcher == '') {
				$matches[] = array('matched' => false, 'page' => $page);
			}
		}
		
		if(count($matches) > 1) throw new Exception("Multiple Matches for `/$url`");
		else if(count($matches) == 0) return;
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