<?php
	namespace Distynct;
	
	class Cookie {
		private static $config = null;
		
		public function __construct() {
			if(null === self::$config) {
				self::$config = Config::get('Cookie', array());
				
				if(!isset(self::$config['prefix'])) {
					self::$config['prefix'] = "";
				}
				
				if(!isset(self::$config['path'])) {
					self::$config['path'] = "/";
				}
				
				if(!isset(self::$config['domain'])) {
					self::$config['domain'] = ".". trim(getenv("HTTP_HOST"), ".");
				}
				
				if(!isset(self::$config['secure'])) {
					self::$config['secure'] = false;
				}
				
				if(!isset(self::$config['httponly'])) {
					self::$config['httponly'] = false;
				}
			}
		}
		
		private function set_cookie($name, $value=null, $expire=0) {
			if(false === setcookie(self::$config['prefix'] . $name, $value, $expire, self::$config['path'], self::$config['domain'], self::$config['secure'], self::$config['httponly'])) {
				return false;
			}
			
			if((null === $value) || ($expire < time())) {
				$_COOKIE[ self::$config['prefix'] . $name ] = null;
				unset($_COOKIE[ self::$config['prefix'] . $name ]);
			} else {
				$_COOKIE[ self::$config['prefix'] . $name ] = $value;
			}
			
			return true;
		}
		
		public function get($name, $else_return=null) {
			if(isset($_COOKIE[ self::$config['prefix'] . $name ])) {
				return $_COOKIE[ self::$config['prefix'] . $name ];
			}
			
			return $else_return;
		}
		
		public function set($name, $value=null, $expire=0) {
			return self::set_cookie($name, $value, $expire);
		}
		
		public function delete($name) {
			return self::set_cookie($name, null, time() - 3600);
		}
		
		
		// Magic methods
		
		public function __get($name) {
			return $this->get($name, null);
		}
		
		public function __set($name, $value) {
			return $this->set($name, $value);
		}
		
		public function __isset($name) {
			return (null !== $this->get($name));
		}
		
		public function __unset($name) {
			$this->delete($name);
		}
	}