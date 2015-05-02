<?php
	namespace Distynct\Abstracts;
	
	use \api as api;
	
	abstract class Controller {
		public function __construct() {
			api::register('controller', $this);
		}
		
		abstract protected function get();
	}