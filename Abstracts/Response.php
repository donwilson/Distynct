<?php
	namespace Distynct\Abstracts;
	
	class Response extends \Exception {
		public function __toString() {
			return __CLASS__ . ": [". $this->code ."]: ". $this->message ."\n";
		}
	}