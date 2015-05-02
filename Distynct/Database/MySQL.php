<?php
	namespace Distynct\Database;
	
	use \Distynct\Config;
	
	/**
	 * @todo:	new_link option to ::connect()
	 */
	
	class MySQL extends \Distynct\Abstracts\Database {
		private $link = null;
		
		public function connect() {
			if(null === $this->link) {
				if((false === $this->host) && isset(self::$config['host'])) {
					$this->host = self::$config['host'];
				}
				
				if((false === $this->username) && isset(self::$config['user'])) {
					$this->username = self::$config['user'];
				}
				
				if((false === $this->password) && isset(self::$config['pass'])) {
					$this->password = self::$config['pass'];
				}
				
				if(false === ($this->link = @mysql_connect($this->host, $this->username, $this->password))) {
					return false;
				}
				
				if((false === $this->database) && isset(self::$config['name'])) {
					$this->database = self::$config['name'];
				}
				
				if(false === @mysql_select_db($this->database, $this->link)) {
					return false;
				}
			}
			
			return (false !== $this->link);
		}
		
		public function set_database($database) {
			if(!$this->connect()) {
				return false;
			}
			
			if(($this->database !== $database) && (false !== @mysql_select_db($database, $this->link))) {
				$this->database = $database;
			}
			
			/*
			if($this->database !== $database) {
				throw new Exception("Unable to select database '". $database ."'.");
			}
			*/
			
			return $this;
		}
		
		public function set_charset($charset) {
			if(!$this->connect()) {
				return false;
			}
			
			if(($this->charset !== $charset) && (false !== @mysql_set_charset($charset, $this->link))) {
				$this->charset = $charset;
			}
			
			/*
			if($this->charset !== $charset) {
				throw new Exception("Unable to set charset '". $charset ."'.");
			}
			*/
			
			return $this;
		}
		
		private function run_query($query) {
			if(!$this->connect()) {
				return false;
			}
			
			/*
			$result = @mysql_query($query, $this->link);
			
			if(0 !== @mysql_errno($this->link)) {
				throw new Exception("Unable to process query (#". mysql_errno($this->link) .": ". mysql_error($this->link) .")");
			}
			
			return $result;
			*/
			
			return @mysql_query($query, $this->link);
		}
		
		public function query($query) {
			if(!$this->connect()) {
				return false;
			}
			
			return (false !== $this->run_query($query));
		}
		
		public function prepare($query, $args) {
			$args = func_get_args();
			
			array_shift($args);
			
			if(isset($args[0]) && is_array($args[0])) {
				$args = $args[0];
			}
			
			$query = str_replace(array("'%s'", "\"%s\""), "%s", $query);
			$query = preg_replace("#(?<!%)%f#", "%F", $query);
			$query = preg_replace("#(?<!%)%s#", "'%s'", $query);
			
			foreach($args as $key => $value) {
				$args[ $key ] = $this->escape($value);
			}
			
			return @vsprintf($query, $args);
		}
		
		
		
		public function get_results($sql, $column_to_key=false) {
			// param checking
			if(!$this->connect()) {
				return false;
			}
			
			if(false === ($result = $this->run_query($sql))) {
				return false;
			}
			
			//if(@mysql_num_rows($result) <= 0) {
			//	return null;
			//}
			
			$results = array();
			
			while($row = @mysql_fetch_assoc($result)) {
				if((false !== $column_to_key) && isset($row[ $column_to_key ])) {
					$results[ $row[ $column_to_key ] ] = $row;
				} else {
					$results[] = $row;
				}
			}
			
			return $results;
		}
		
		public function get_row($sql, $row_offset=0) {
			// param checking
			if(!is_numeric($row_offset) || ($row_offset < 0)) {
				return null;
			}
			
			if(!$this->connect()) {
				return false;
			}
			
			if(false === ($result = $this->run_query($sql))) {
				return false;
			}
			
			if(@mysql_num_rows($result) <= 0) {
				return null;
			}
			
			if($row_offset > 0) {
				if(false === @mysql_data_seek($result, $row_offset)) {
					return null;
				}
			}
			
			return @mysql_fetch_assoc($result);
		}
		
		public function get_var($sql, $col_offset=0, $row_offset=0) {
			// param checking
			if(!is_numeric($col_offset) || ($col_offset < 0) || !is_numeric($row_offset) || ($row_offset < 0)) {
				return null;
			}
			
			if(false === ($result = $this->run_query($sql))) {
				return false;
			}
			
			if($row_offset > 0) {
				if(false === @mysql_data_seek($result, $row_offset)) {
					return null;
				}
			}
			
			if(false === ($row = @mysql_fetch_array($result))) {
				return null;
			}
			
			if(!isset($row[ $col_offset ])) {
				return null;
			}
			
			return $row[ $col_offset ];
		}
		
		public function get_col($sql, $col_offset=0) {
			if(!is_numeric($col_offset) || ($col_offset < 0)) {
				return null;
			}
			
			if(false === ($result = $this->run_query($sql))) {
				return false;
			}
			
			if(@mysql_num_rows($result) <= 0) {
				return null;
			}
			
			$rows = array();
			$checked = false;
			
			while($row = @mysql_fetch_array($result)) {
				if(false === $checked) {
					if(!isset($row[ $col_offset ])) {
						return null;
					}
					
					$checked = true;
				}
				
				$rows[] = $row[ $col_offset ];
			}
			
			return $rows;
		}
		
		
		// Mysql specific?
		public function insert_id() {
			if(!$this->connect()) {
				return false;
			}
			
			return @mysql_insert_id($this->link);
		}
		
		
		// Escape strings
		private function real_escape($data) {
			if((null !== $this->link) && $this->connect()) {
				return @mysql_real_escape_string($data, $this->link);
			}
			
			return addslashes($data);
		}
		
		public function escape($data) {
			if(is_array($data)) {
				foreach($data as $k => $v) {
					if(is_array($v)) {
						$data[ $k ] = $this->escape($v);
					} else {
						$data[ $k ] = $this->real_escape($v);
					}
				}
			} else {
				$data = $this->real_escape($data);
			}
			
			return $data;
		}
	}