<?php
	namespace Distynct;
	
	use \api as api;
	
	class Ajax {
		const STATUS_SUCCESS	= "success";
		const STATUS_ERROR		= "error";
		const STATUS_UNKNOWN	= "unknown";
		
		private $status;
		private $message;
		private $cargo		= array();
		
		private $callback	= null;
		
		
		public function __construct() {
			$this->status	= self::STATUS_SUCCESS;
			$this->message	= "";
		}
		
		// Internal methods
		public function kill_page() {
			api::request()->setContentType("application/json");
			
			die( ((null !== $this->callback)?"/**/". $this->callback ."(":"") . json_encode(array(
				'status'	=> $this->status,
				'message'	=> $this->message,
				'cargo'		=> $this->cargo,
			), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE) . ((null !== $this->callback)?");":"") );
		}
		
		
		// Public methods
		
		public function set_callback($callback_fn="") {
			if(("" !== $callback_fn) && preg_match("#^[A-Za-z\$_][A-Za-z0-9\_\.\$\[\]]*$#si", $callback_fn) && !preg_match("#([\.]+|[\[\]])$#si", $callback_fn)) {
				$this->callback = $callback_fn;
			}
		}
		
		public function load_cargo($cargo) {
			$this->cargo = array_merge($this->cargo, $cargo);
			
			return $this;
		}
		
		public function die_success($message=null) {
			$this->status	= self::STATUS_SUCCESS;
			$this->message	= $message;
			
			return $this->kill_page();
		}
		
		public function die_error($message=null) {
			$this->status	= self::STATUS_ERROR;
			$this->message	= $message;
			
			return $this->kill_page();
		}
		
		public function die_unknown($message=null) {
			$this->status	= self::STATUS_UNKNOWN;
			$this->message	= $message;
			
			return $this->kill_page();
		}
		
		
		// Magic methods
		
		public function &__get($key) {
			if(isset($this->cargo[ $key ])) {
				return $this->cargo[ $key ];
			}
			
			$this->cargo[ $key ] = null;
			
			return $this->cargo[ $key ];
		}
		
		public function __set($key, $value) {
			$this->cargo[ $key ] = $value;
		}
		
		public function __isset($key) {
			return isset($this->cargo[ $key ]);
		}
		
		public function __unset($key) {
			unset($this->cargo[ $key ]);
		}
	}