<?php
	namespace Distynct\Abstracts;
	
	abstract class Database {
		protected static $config = null;
		
		protected $host;
		protected $username;
		protected $password;
		protected $database;
		protected $charset;
		
		public function __construct($host=false, $username=false, $password=false, $database=false) {
			if(null === self::$config) {
				// Load static configuration on first instance of class
				self::$config = \Distynct\Config::get('Database');
			}
			
			$this->host = $host;
			$this->username = $username;
			$this->password = $password;
			$this->database = $database;
		}
		
		abstract public function connect();
		
		abstract public function set_database($database);
		abstract public function set_charset($charset);
		
		abstract public function escape($data);
		
		abstract public function query($query);
		abstract public function prepare($query, $args);
		
		abstract public function get_results($sql, $column_to_key);
		abstract public function get_row($sql, $row_offset);
		abstract public function get_var($sql, $col_offset, $row_offset);
		abstract public function get_col($sql, $col_offset);
		
		abstract public function insert_id();
	}