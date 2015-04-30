<?php
	namespace Distynct;
	
	use \api as api;
	use \escape as escape;
	
	class Template {
		const ESCAPE_NONE	= 0;
		const ESCAPE_ATTR	= 1;
		const ESCAPE_HTML	= 2;
		
		const TEMPLATE_SUFFIX	= ".php";
		
		private $config = array();
		private $debug;
		private $path;
		private $template_files = array();
		private $vars = array();
		
		private $escape_level = self::ESCAPE_NONE;
		
		public function __construct() {
			$this->config = Config::get('Template');
			
			if(!empty($this->config['path'])) {
				$this->path = rtrim($this->config['path'], "/\\") . DIRECTORY_SEPARATOR;
			} else {
				throw new Exception("Template.path configuration key not set.");
			}
			
			$this->debug = (!empty($this->config['path']));
			
			if(!empty($this->config['hooks'])) {
				api::hook()->add( $this->config['hooks'] );
			}
			
			api::hook()->run('Template.init');
		}
		
		private function clean_tpl_name($tpl) {
			$tpl = str_replace("\\", "/", $tpl);
			$tpl = str_replace("../", "", $tpl);
			$tpl = ltrim($tpl, " /");
			
			if(false !== stripos($tpl, ".php")) {
				$tpl = preg_replace("#\.php$#si", "", $tpl);
			}
			
			return $tpl;
		}
		
		public function exists($tpl) {
			if(!isset($this->template_files[ $tpl ])) {
				$this->template_files[ $tpl ] = file_exists($this->path . $tpl . self::TEMPLATE_SUFFIX);
			}
			
			return $this->template_files[ $tpl ];
		}
		
		public function view($tpl) {
			if("" !== ($tpl = $this->clean_tpl_name($tpl))) {
				if($this->exists($tpl)) {
					if(false === @include($this->path . $tpl . self::TEMPLATE_SUFFIX)) {
						if($this->debug) {
							print "Template '". $tpl ."' not found or ended unexpectedly.";
						}
					}
				}
			}
			
			return $this;
		}
		
		private function escape($string) {
			if(is_string($string)) {
				switch($this->escape_level) {
					case self::ESCAPE_ATTR:
						$string = \escape::attribute($string);
					break;
					
					case self::ESCAPE_HTML:
						$string = \escape::html($string);
					break;
				}
			}
			
			return $string;
		}
		
		private function reset_escape() {
			$this->escape_level = self::ESCAPE_NONE;
			
			return $this;
		}
		
		public function set($name, $value=null) {
			if(is_array($name)) {
				foreach($name as $_name => $_value) {
					$this->vars[ $_name ] = $_value;
				}
			} else {
				$this->vars[ $name ] = $value;
			}
			
			return $this;
		}
		
		public function get($name) {
			$return = null;
			
			if(isset($this->vars[ $name ])) {
				$return = $this->escape( $this->vars[ $name ] );
			}
			
			$this->reset_escape();
			
			return $return;
		}
		
		public function __set($name, $value) {
			$this->vars[ $name ] = $value;
			
			$this->reset_escape();
		}
		
		public function __get($name) {
			$return = null;
			
			if(isset($this->vars[ $name ])) {
				$return = $this->escape($this->vars[ $name ]);
			}
			
			$this->reset_escape();
			
			return $return;
		}
		
		public function __isset($name) {
			$this->reset_escape();
			
			return isset($this->vars[ $name ]);
		}
		
		public function __unset($name) {
			$this->reset_escape();
			
			unset($name);
			
			return $this;
		}
		
		
		// Escape helpers
		public function esc_attr() {
			$this->escape_level = self::ESCAPE_ATTR;
			
			return $this;
		}
		
		public function esc_html() {
			$this->escape_level = self::ESCAPE_HTML;
			
			return $this;
		}
	}