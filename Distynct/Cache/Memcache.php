<?php
	namespace Distynct\Cache;
	
	use \Distynct\Config;
	
	/*
		Config Settings:
			Cache.Memcache.servers = [
				{'host': "127.0.0.1"[, 'port': 11211]}
			]
		
	*/
	
	
	class Memcache extends \Distynct\Abstracts\Cache {
		private $handler;
		private $hash_key = false;
		private $key_prefix = false;
		
		const SET_FLAG = MEMCACHE_COMPRESSED;
		
		public function __construct() {
			if(!class_exists('Memcache')) {
				throw new \Exception("Required class 'Memcache' not found.");
			}
			
			$this->handler = new \Memcache();
			
			$config = Config::get('Cache.Memcache');
			
			if(empty($config['servers'])) {
				throw new \Exception("Config 'Cache.Memcache.servers' is required yet empty.");
			}
			
			foreach($config['servers'] as $server) {
				$this->handler->addServer($server['host'], (!empty($server['port'])?$server['port']:11211));
			}
			
			if(!empty($config['hash_key'])) {
				$this->hash_key = !empty($config['hash_key']);
			}
			
			if(isset($config['key_prefix']) && ("" !== $config['key_prefix']) && (false !== $config['key_prefix']) && (null !== $config['key_prefix'])) {
				$this->key_prefix = !empty($config['key_prefix']);
			}
			
			return $this;
		}
		
		public function get($key, $callback=null, $expire=0) {
			if(is_array($key)) {
				// handle array
				
				// if cache is missing for some/all keys, callable callback is sent
				//    a single parameter: a simple array of missing cache keys and EXPECTS
				//    back an array with missing cache keys => value for each missing cache key
				
				$values = $this->handler->get($key);
				
				if((null !== $callback) && (count($values) !== count($key))) {
					$missing_keys = array_diff($key, array_keys($values));
					
					if(count($missing_keys)) {
						if(is_callable($callback)) {
							$added_values = call_user_func_array($callback, array($missing_keys));
						} else {
							$added_values = array_fill_keys($missing_keys, $callback);
						}
						
						foreach($added_values as $_key => $_value) {
							$this->set($_key, $_value, $expire);
							
							$values[ $_key ] = $_value;
						}
					}
				}
				
				return $values;
			}
			
			if((false === ($value = $this->handler->get($key))) && (null !== $callback)) {
				if(is_callable($callback)) {
					$value = call_user_func($callback);
				} else {
					$value = $callback;
				}
				
				$this->set($key, $value, $expire);
			}
			
			return $value;
		}
		
		public function set($key, $value, $expire=0) {
			return $this->handler->set($key, $value, self::SET_FLAG, $expire);
		}
		
		public function delete($key) {
			$this->handler->delete($key);
		}
		
		// talk directly to the handler
		public function __call($name, $arguments) {
			if(method_exists($this->handler, $name)) {
				call_user_func_array(array($this->handler, $name), $arguments);
			} else {
				throw new \Exception("Cache method '". $name ."' doesn't exist.");
			}
		}
		
		// keyify
		private function keyify($key) {
			if(false !== $this->key_prefix) {
				$key = $this->key_prefix . $key;
			}
			
			if(false !== $this->hash_key) {
				$key = md5($this->hash_key);
			}
			
			return $key;
		}
	}