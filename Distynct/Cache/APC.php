<?php
	namespace Distynct\Cache;
	
	class APC {
		private $apc_is_installed = false;
		
		public function __construct() {
			$this->apc_is_installed = function_exists("apc_add");
		}
		
		public function get($key, $callback=null, $expire=0) {
			if(!$this->isActive()) {
				return false;
			}
			
			if(is_array($key)) {
				// handle array
				
				$values = apc_fetch($key);
				
				if((null !== $callback) && (count($values) !== count($key))) {
					$missing_keys = array_diff($key, array_keys($values));
					
					if(count($missing_keys)) {
						if(is_callable($callback)) {
							$added_values = call_user_func_array($callback, array($missing_keys));
						} else {
							$added_values = array_fill_keys($missing_keys, $callback);
						}
						
						foreach($added_values as $_key => $_value) {
							apc_store($_key, $_value, $expire);
							
							$values[ $_key ] = $_value;
						}
					}
				}
				
				return $values;
			}
			
			if((false === ($value = apc_fetch($key))) && (null !== $callback)) {
				if(is_callable($callback)) {
					$value = call_user_func($callback);
				} else {
					$value = $callback;
				}
				
				apc_store($key, $value, $expire);
			}
			
			return $value;
		}
		
		public function set($key, $value=null, $expire=0) {
			if(!$this->isActive()) {
				return false;
			}
			
			return apc_store($key, $value, $expire);
		}
		
		public function delete($key) {
			if(!$this->isActive()) {
				return false;
			}
			
			return apc_delete($key);
		}
		
		
		// Is APC Installed?
		private function isActive() {
			return $this->apc_is_installed;
		}
	}