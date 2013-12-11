<?php

//2012-02-14

/*

AbstractDatabase, a database abstraction layer for sqlite, mysql & mssql


ChangeLog:

v1.4.0 - 2013-05-03
Made properties protected instead of private

v1.3.0 - 2013-04-15
Added method queryValues()

v1.2.0 R2 - 2012-11-19
Made all variables camelCase

v1.2.0 - 2012-10-23
Added SQL variables to the debug output when using debugSQL()

v1.1.0 - 2012-10-15
Added method debugSQL() that will print out all sql statements before executing them

v1.0.0 - 2012-02-21
Initial release

v1.0.0 RC2 - 2012-02-19

v1.0.0 RC1 - 2012-02-15

v1.0.0 Dev - 2012-02-14


TODO:
Use exceptions instead of error detection to get better error messages

*/

class AbstractDatabase
{
	const SQLITE2 = 1;
	const SQLITE2_MEM = 2;
	const SQLITE3 = 3;
	const SQLITE3_MEM = 4;
	const MYSQL = 5;
	const MSSQL = 6;
	
	protected $type = 0;
	protected $host = '';
	protected $user = '';
	protected $password = '';
	protected $database = '';
	protected $instance = null;
	protected $error = false;
	protected $errorMessage = '';
	protected $errorHandler = null;
	protected $debug = false;
	protected $showErrors = true;
	protected $queries = 0;
	protected $inTransaction = false;
	protected $debugSQL = false;
	protected $driverTypes = array
	(
		self::SQLITE2		=>'sqlite2',
		self::SQLITE2_MEM	=>'sqlite2',
		self::SQLITE3		=>'sqlite',
		self::SQLITE3_MEM	=>'sqlite',
		self::MYSQL			=>'mysql',
		self::MSSQL			=>'mssql'
	);
	
	/*
	for SQLite 2 & 3 the prototype is as follows
	
	GenericDatabase(Type, Database);
	*/
	public function __construct($type, $host, $user=null, $password=null, $database=null)
	{	//2012-02-14
		$this->type = $type;
		
		if($type == self::SQLITE2 || $type == self::SQLITE2_MEM || $type == self::SQLITE3 || $type == self::SQLITE3_MEM)
		{
			$this->database = $host;
		}
		else if($type == self::MYSQL || $type == self::MSSQL)
		{
			$this->host = $host;
			$this->user = $user;
			$this->password = $password;
			$this->database = $database;
		}
		else
		{
			$this->setError('Invalid database type ('.$type.')');
			return false;
		}
		
		$this->instance = $this->connect($this->database);
		
		$this->checkError();
	}
	
	private function connect($database)
	{	//2012-02-14
		$instance = null;
		
		if(!in_array($this->driverTypes[$this->type], PDO::getAvailableDrivers()))
		{
			$this->setError('PDO Driver not found ('.$this->driverTypes[$this->type].')');
			return;
		}
		
		try
		{
			if($this->type == self::SQLITE2)
			{
				$instance = new PDO('sqlite2:'.$database);
			}
			else if($this->type == self::SQLITE2_MEM)
			{
				$instance = new PDO('sqlite2::memory:');
			}
			else if($this->type == self::SQLITE3)
			{
				$instance = new PDO('sqlite:'.$database);
			}
			else if($this->type == self::SQLITE3_MEM)
			{
				$instance = new PDO('sqlite::memory:');
				
				//$instance = new PDO('sqlite::memory:', null, null, array(PDO::ATTR_PERSISTENT=>true));
			}
			else if($this->type == self::MYSQL)
			{
				if(strpos($this->host, ':') !== false)
				{
					$hostPort = explode(':', $this->host);
					$varDSN = 'host='.$hostPort[0].';port='.$hostPort[1].';';
				}
				else
				{
					$varDSN = 'host='.$this->host.';';
				}
				
				$instance = new PDO('mysql:'.$varDSN.'dbname='.$database, $this->user, $this->password);
				
				$instance->exec('SET NAMES utf8;');
			}
			else if($this->type == self::MSSQL)
			{
				if(strpos($this->host, ':') !== false)
				{
					$hostPort = explode(':', $this->host);
					$varDSN = 'Server='.$hostPort[0].','.$hostPort[1].';';
				}
				else
				{
					$varDSN = 'Server='.$this->host.';';
				}
				
				$instance = new PDO('sqlsrv:'.$varDSN.'Database='.$database, $this->user, $this->password);
			}
		}
		catch(PDOException $ex)
		{
			$this->setError($ex->getMessage());
		}
		
		$instance->setAttribute(PDO::ATTR_CASE, PDO::CASE_NATURAL);
		$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);
		$instance->setAttribute(PDO::ATTR_ORACLE_NULLS, PDO::NULL_NATURAL);
		$instance->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, false);
		$instance->setAttribute(PDO::ATTR_TIMEOUT, 10);
		$instance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
		
		return $instance;
	}
	
	public function debugSQL($show=true)
	{	//2012-10-15
		$this->debugSQL = $show;
	}
	
	/*
	check if an error has occurred, and reference the error message
	*/
	public function error(&$err=null)
	{	//2012-02-14
		$err = $this->errorMessage;
		return $this->error;
	}
	
	private function errorSource($trace)
	{	//2012-02-21
		$len = count($trace);
		for($i=($len-1);$i>=0;$i--)	//find the last entry where 'class' is set to this class or the child class, this should be the first call made to the database thus the start of the error
		{
			if(isset($trace[$i]['class']) && ($trace[$i]['class'] == __CLASS__ || $trace[$i]['class'] == get_class($this)))
			{
				return $trace[$i];
			}
		}
		
		if($this->debug)
		{
			print __CLASS__.': No valid trace found!';
			exit;
		}
		else
		{
			return false;
		}
	}
	
	/*
	Sets an error message, and calls the error display mechanism
	*/
	protected function setError($message, $prefix=null)
	{	//2012-02-14
		$this->errorMessage = $message;
		$this->error = true;
		
		if($prefix === null)
		{
			$prefix = get_class($this).': ';
		}
		
		if($this->errorHandler !== null)
		{
			$callback = $this->errorHandler;
			$traceOptions = ((defined('DEBUG_BACKTRACE_IGNORE_ARGS'))?DEBUG_BACKTRACE_IGNORE_ARGS:0);
			
			$fullTrace = debug_backtrace($traceOptions);
			$errorSource = $this->errorSource($fullTrace);
			
			$callback($this->errorMessage, $errorSource, $this->showErrors, $fullTrace);
		}
		else if($this->showErrors)
		{
			print $prefix.$this->errorMessage;
			exit;
		}
	}
	
	private function checkError($obj=null)
	{	//2012-02-14
		if($obj === null)
		{
			$errInfo = $this->instance->errorInfo();
		}
		else
		{
			$errInfo = $obj->errorInfo();
		}
		
		if($errInfo[0] !== '00000' && $errInfo[0] !== '')
		{
			$this->setError('Error: '.$errInfo[2].' ('.$errInfo[0].')');
			return true;
		}
		else
		{
			return false;
		}
	}
	
	/*
	Sets a custom error handler (overrides the showErrors() setting)
	*/
	public function setErrorHandler($callback)
	{	//2012-02-14
		if(is_callable($callback))
		{
			$this->errorHandler = $callback;
			return true;
		}
		else
		{
			return false;
		}
	}
	
	/*
	Only use for debugging, not development
	*/
	public function debug($enable=null)
	{	//2012-02-14
		if($enable === null)
		{
			return $this->debug;
		}
		else
		{
			$this->debug = (bool)$enable;
			
			if($this->debug)
			{
				$this->instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
			}
			else
			{
				$this->instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT);	//TODO: use exceptions instead (PDO::ERRMODE_EXCEPTION)
			}
		}
	}
	
	/*
	NOTE: this setting has no direct effect when a custom error handler is used
	however, the value is passed to the custom error handler so it might act based on this value
	*/
	public function showErrors($show=null)
	{	//2012-02-15
		if($show === null)
		{
			return $this->showErrors;
		}
		else
		{
			$this->showErrors = (bool)$show;
		}
	}
	
	/*
	Returns the number of currently completed queries to the database
	*/
	public function getQueries()
	{	//2012-02-15
		return $this->queries;
	}
	
	private function createStatement($sql, $vars)
	{	//2012-02-15
		if($this->debugSQL)
		{
			print 'SQL ('.($this->queries+1).'):'.CRLF.$sql.CRLF;
			print 'Vars:'.CRLF;
			print_r($vars);
		}
		
		foreach($vars as $name => $value)
		{
			if(strpos($sql, ':'.$name) === false)	//if the variable isn't used; remove it from the list
			{
				unset($vars[$name]);
			}
			else	//to enable variable names with invalid characters
			{
				if(preg_match('/^[a-z0-9_]+$/i', $name) != 1)	//if variable has invalid characters
				{
					preg_match_all('/[a-z0-9_]+/i', $name, $matches);
					
					$newName = implode('_', $matches[0]);
					$sql = str_replace(':'.$name, ':'.$newName, $sql);
					unset($vars[$name]);
					$vars[$newName] = $value;
				}
			}
		}
		
		$statement = $this->instance->prepare($sql);
		
		$this->checkError();
		
		foreach($vars as $var => $value)
		{
			@$statement->bindValue(':'.$var, $value);
		}
		
		$this->checkError($statement);
		
		@$statement->execute();
		
		$this->checkError($statement);
		
		$this->queries++;
		
		return $statement;
	}
	
	public function beginTransaction()
	{	//2012-02-14
		return ($this->inTransaction = $this->instance->beginTransaction());
	}
	
	public function commit()
	{	//2012-02-14
		if($this->inTransaction)
		{
			return !($this->inTransaction = !$this->instance->commit());
		}
		else
		{
			return false;
		}
	}
	
	public function rollBack()
	{	//2012-02-14
		if($this->inTransaction)
		{
			return !($this->inTransaction = !$this->instance->rollBack());
		}
		else
		{
			return false;
		}
	}
	
	/*
	Executes the given SQL, and returns the number of affected rows
	*/
	public function exec($sql, $vars=array())
	{	//2012-02-15
		$stmt = $this->createStatement($sql, $vars);
		
		return $stmt->rowCount();
	}
	
	/*
	Queries the database with the given sql and returns a dataset with results
	*/
	public function query($sql, $vars=array())
	{	//2012-02-14
		$stmt = $this->createStatement($sql, $vars);
		
		return $stmt->fetchAll();
	}
	
	/*
	Queries the database with the given sql and returns a single result row
	*/
	public function queryRow($sql, $vars=array())
	{	//2012-02-15
		$stmt = $this->createStatement($sql, $vars);
		
		$row = $stmt->fetch();
		
		return (($row === false)?null:$row);
	}
	
	/*
	Queries the database with the given sql and returns the value of the first column in the result
	*/
	public function queryValue($sql, $vars=array())
	{	//2012-02-15
		$stmt = $this->createStatement($sql, $vars);
		
		$value = $stmt->fetchColumn();
		
		return (($value === false)?null:$value);
	}
	
	public function queryValues($sql, $vars=array())
	{	//2013-04-15
		$values = array();
		foreach($this->query($sql, $vars) as $row) $values[] = current($row);
		
		return $values;
	}
	
	public function lastInsertID()
	{	//2012-02-15
		return $this->instance->lastInsertId();
	}
	
	/*
	Returns an array with the names of all tables in the database
	*/
	public function getTables($database=null)
	{	//2012-02-14
		$database = (($database === null)?$this->database:$database);
		
		$tables = array();
		
		if($this->type == self::SQLITE2 || $this->type == self::SQLITE2_MEM || $this->type == self::SQLITE3 || $this->type == self::SQLITE3_MEM)
		{
			$rows = $this->query('SELECT * FROM sqlite_master');
			
			foreach($rows as $table)
			{
				$tables[] = $table['name'];
			}
		}
		else if($this->type == self::MYSQL)
		{
			$db = new self($this->type, $this->host, $this->user, $this->password, 'information_schema');
			$rows = $db->query('SELECT * FROM TABLES WHERE TABLE_SCHEMA = :database', array('database'=>$database));
			
			foreach($rows as $table)
			{
				$tables[] = $table['TABLE_NAME'];
			}
		}
		else if($this->type == self::MSSQL)
		{
			$this->setError('getTables(): Not implemented for this database type ('.$this->driverTypes[$this->type].')');
		}
		
		return $tables;
	}
}

?>