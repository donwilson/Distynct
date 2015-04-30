<?php
	namespace Distynct\Abstracts;
	
	abstract class Flash {
		protected static $flash__cache_expiry	= 21600;   // in seconds (default: 6 hours)
		protected static $flash__key_hash		= true;   // should class hash cache key with md5()
		protected static $flash__key_prefix		= "__flash_cache:";
		protected static $flash__key_suffix		= "";
		
		public function __construct($uniq_id) {
			return $this->flash__init($uniq_id);
		}
		
		// flash__process should accept an array of unique ids that need to be pulled
		//    from somewhere and returned with array of 
		abstract protected function flash__process($uniq_ids);
		
		
		// Invalidate Flash-specific cache (designed to be overwritten, don't forget to include flash__invalidate_cache)
		public function invalidateCache() {
			// Call parent invalidator
			$this->flash__invalidate_cache();
		}
	}