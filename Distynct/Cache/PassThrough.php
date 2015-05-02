<?php
	namespace Distynct\Cache;
	
	class PassThrough extends \Distynct\Abstracts\Cache {
		public function __construct() {
			
		}
		
		public function get($key, $callback=null, $expire=0) {
			if(is_array($key)) {
				if(is_callable($callback)) {
					return call_user_func_array($callback, array( $key ));
				}
				
				return array_fill_keys(array_keys($key), $callback);
			}
			
			if(is_callable($callback)) {
				return call_user_func($callback);
			}
			
			return $callback;
		}
		
		public function set($key, $value=null, $expire=0) {
			return true;
		}
		
		public function delete($key) {
			return true;
		}
	}