<?php
	namespace Distynct;
	
	/**
	 * Key-defined event processing
	 *
	 * @author Don Wilson
	 * @package Distynct
	 * @licence Proprietary (closed-source)
	 */
	
	class Hook {
		private $hooks = array();
		
		 /**
		 * Assign value(s) to key(s), empty previous value(s) assigned to key(s) if overwrite is true
		 * 
	     * @param string|mixed $key
	     * @param mixed $value
	     * @param boolean $overwrite
	     * @return self
	     */
		public function add($key, $value=false, $overwrite=false) {
			if($overwrite) {
				unset($this->hooks[ $key ]);
			}
			
			if(is_array($key)) {
				foreach($key as $_key => $_value) {
					if(!isset($this->hooks[ $_key ])) {
						$this->hooks[ $_key ] = array($_value);
					} else {
						$this->hooks[ $_key ][] = $_value;
					}
				}
			} elseif(is_callable($value)) {
				if(!isset($this->hooks[ $key ])) {
					$this->hooks[ $key ] = array($value);
				} else {
					$this->hooks[ $key ][] = $value;
				}
			}
			
			return $this;
		}
		
		/**
		 * Process any hook(s) given key, empty if dump is true
		 * 
		 * @param string $key
		 * @param boolean $dump
		 * @return self
		 */
	    
		public function run($key, $dump=false) {
			if(isset($this->hooks[ $key ])) {
				foreach($this->hooks[ $key ] as $hook) {
					call_user_func($hook);
				}
				
				if($dump) {
					unset($this->hooks[ $key ]);
				}
			}
			
			return $this;
		}
	}