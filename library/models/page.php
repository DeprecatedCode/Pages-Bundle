<?php

namespace Evolution\Pages\Models;
use e;
use Exception;

class Page extends \Evolution\SQL\Model {
	
	
	public function render($data) {
		// check to see if this has a scope set and try loading the scope of this page.
		if($this->scope) {
			try {
				$model = e::map($this->scope.':'.$data['key']);
				dump($model);
			}
			catch(Exdception $e) {
				throw new Exception('Could not find the item you are looking for.');
			}
		}
		// render this page
		dump($project);
	}
	
	
}