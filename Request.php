<?php
	namespace Distynct;
	
	class Request {
		private static $config		= null;
		private static $vars		= null;
		private static $url			= null;
		private static $base_url;
		private static $method;
		private static $is_ajax		= null;
		private static $is_mobile	= null;
		private static $is_tablet	= null;
		private static $is_robot	= null;   // googlebot, crawlers, etc
		
		public function __construct() {
			if(null === self::$config) {
				self::$config	= Config::get('site', array());
				
				if(!isset(self::$config['schema'])) {
					self::$config['schema'] = "http";
				}
				
				if(!isset(self::$config['domain'])) {
					self::$config['domain'] = getenv("HTTP_HOST");
				}
				
				self::$base_url	= self::$config['schema'] ."://". self::$config['domain'];
				self::$method	= getenv("REQUEST_METHOD");
			}
			
			return $this;
		}
		
		public function setUrl($url, $redirect_on_diff=true) {
			if(!preg_match("#^https?\://#si", $url)) {
				$url = self::$base_url ."/". ltrim($url, "/");
			}
			
			self::$url = $url;
			
			if($redirect_on_diff && (self::$url !== self::$base_url . getenv("REQUEST_URI"))) {
				$this->redirect(self::$url);
			}
			
			return $this;
		}
		
		public function getUrl() {
			if(null === self::$url) {
				self::$url = self::$base_url . getenv("REQUEST_URI");
			}
			
			return self::$url;
		}
		
		public function redirect($url, $status=302) {
			if(headers_sent()) {
				print "<meta http-equiv=\"refresh\" content=\"0; url=". $url ."\">\n";
			} else {
				header("Location: ". $url, true, $status);
			}
			
			die;
		}
		
		public function get($name, $else_return=null) {
			if(null === self::$vars) {
				self::$vars = array();
				
				$request_uri = getenv("REQUEST_URI");
				
				if(false !== ($qm_pos = strpos($request_uri, "?"))) {
					parse_str(substr($request_uri, ($qm_pos + 1)), self::$vars);
				}
			}
			
			if(isset(self::$vars[ $name ])) {
				return self::$vars[ $name ];
			}
			
			return $else_return;
		}
		
		
		// Static methods
		
		public static function isAjaxRequest() {
			if(null === self::$is_ajax) {
				self::$is_ajax = ("xmlhttprequest" === strtolower(getenv("HTTP_X_REQUESTED_WITH")));
			}
			
			return self::$is_ajax;
		}
		
		public static function isMobile() {
			if(null === self::$is_mobile) {
				self::$is_mobile = false;
			}
			
			return self::$is_mobile;
		}
		
		public static function isTablet() {
			if(null === self::$is_tablet) {
				self::$is_tablet = false;
			}
			
			return self::$is_tablet;
		}
		
		public static function isRobot() {
			if(null === self::$is_robot) {
				self::$is_robot = false;
			}
			
			return self::$is_robot;
		}
		
		public static function setContentType($content_type="text/html") {
			header("Content-Type: ". $content_type, true);
		}
		
		public static function setNoCache() {
			header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
			header("Last-Modified: ". gmdate("D, d M Y H:i:s") ." GMT");
			header("Cache-Control: no-store, no-cache, must-revalidate");
			header("Cache-Control: post-check=0, pre-check=0", false);
			header("Pragma: no-cache");
		}
		
		
		// Magic methods
		
		public function __get($name) {
			return $this->get($name);
		}
		
		public function __isset($name) {
			return (null !== $this->get($name));
		}
	}