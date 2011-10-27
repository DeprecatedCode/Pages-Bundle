<?php

namespace Evolution\Pages\Models;
use e;
use Exception;

class Page extends \Evolution\SQL\Model {
	
	
	public function render($data) {
		\Evolution\Kernel\Trace::$allow = false;
		
		// check to see if this has a scope set and try loading the scope of this page.
		if($this->scope) {
			try {
				$model = e::map($this->scope.':'.$data['key']);
			}
			catch(Exception $e) {
				throw new Exception('Could not find the item you are looking for.');
			}
		}
		
		// ---------------- REMOVE FROM DEMO----------------------------------------
		// THIS IS JUST AN ARRAY TO SERIALIZE INTO THE MAP FOR DEMO PURPOSE ONLY
		// !!!!! REMOVE THIS ASAP
		$map = array(
			"title" => "owner.title",
			"header" => "owner.title",
			"slug" => "owner.slug",
			"description" => "owner.description",
			"content:header-media" => "<img src='awesomephoto' width='{width}' height='height' />"
		);
		$this->map = serialize($map);
		// ----------- END REMOVE FROM DEMO-------------------------------------------
		
		$html = e::pages()->template($this->theme, $this->template);


		$node = \Evolution\LHTML\Parser::parseString($html);
		$scope = $node->_data();
		$scope->owner = $model;
		foreach($map as $key => $item) {
			$scope->$key = $scope->$item;
		}


		// render this page
		echo($node->build());
	}
	
	
}