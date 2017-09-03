<?php
/**
 * This file is part of the MIDL framework
 *
 * @link https://github.com/midl-framework/
 * @author Abdulhalim Kara <ahalimkara@gmail.com>
 * @license MIT License
 * @copyright (c) 2017 Abdulhalim Kara
 */
namespace midl\database;

class Database extends \PDO
{

    /**
     * Default options
     *
     * @var array
     */
    protected static $options = [\PDO::ATTR_EMULATE_PREPARES => false, 
        \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC, \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION];

    /**
     *
     * @var int
     */
    protected $transactionCounter = 0;

    /**
     * If this is true beginTransaction, commit and rollBack functions
     * will not throw exceptions when you try to commit or rollback before calling
     * beginTransaction or calling beginTransaction while there is an active transaction.
     *
     * @var bool
     */
    protected $silentTransactions = false;

    /**
     *
     * @param string $dsn
     * @param string $username [optional]
     * @param string $password [optional]
     * @param array $options [optional]
     * @param bool $silentTransactions [optional]
     * @throws \PDOException PDOException will be thrown on failure
     */
    public function __construct($dsn, $username = null, $password = null, $options = null, $silentTransactions = false)
    {
        $this->silentTransactions = $silentTransactions;
        
        parent::__construct($dsn, $username, $password, $options);
    }

    /**
     * A utility function to connect MySQL.
     * By default it sets PDO error mode to throw exception and
     * sets silentTransactions to true, if this is not the desired functionality
     * then new Database() with desired options should be used.
     *
     * @param string $username [optional]
     * @param string $password [optional]
     * @param string $dbname [optional]
     * @param string $hostname [optional]
     * @return Database Database instance will be returned on success connection
     * @throws \PDOException
     */
    public static function connectMySQL($username = null, $password = null, $dbname = null, $hostname = "localhost")
    {
        $dbname = $dbname ? ";dbname=$dbname" : "";
        
        $dsn = "mysql:host={$hostname}$dbname;charset=utf8";
        
        $db = new static($dsn, $username, $password, static::$options, true);
        $db->query("SET time_zone='" . date("P") . "'");
        
        return $db;
    }

    /**
     * A utility function to connect PostgreSQL.
     * By default it sets PDO error mode to throw exception and
     * sets silentTransactions to true, if this is not the desired functionality
     * then new Database() with desired options should be used.
     *
     * @param string $username [optional]
     * @param string $password [optional]
     * @param string $dbname [optional]
     * @param string $hostname [optional]
     * @return Database Database instance will be returned on success connection
     * @throws \PDOException
     */
    public static function connectPostgreSQL($username = null, $password = null, $dbname = null, $hostname = "localhost")
    {
        $dbname = $dbname ? ";dbname=$dbname" : "";
        
        $dsn = "pgsql:host={$hostname};port=5432$dbname";
        
        return new static($dsn, $username, $password, static::$options, true);
    }

    /**
     * A utility function to connect SQLite.
     * By default it sets PDO error mode to throw exception and
     * sets silentTransactions to true, if this is not the desired functionality
     * then new Database() with desired options should be used.
     *
     * @param string $filename
     * @param string $version [optional] For SQLite3 leave this blank, for SQLite2 send "2"
     * @return Database Database instance will be returned on success connection
     * @throws \PDOException
     */
    public static function connectSQLite($filename, $version = "")
    {
        $dsn = "sqlite{$version}:$filename";
        
        return new static($dsn, null, null, static::$options, true);
    }

    public function select($statement, array $params = null)
    {
        $stmnt = $this->prepare($statement);
        $stmnt->execute($params);
        return $stmnt->fetchAll();
    }

    public function selectRow($statement, array $params = null, $index = 0)
    {
        $stmnt = $this->prepare($statement);
        $stmnt->execute($params);
        return $stmnt->fetchAll(\PDO::FETCH_COLUMN, $index);
    }
    
    public function beginTransaction()
    {
        if (!$this->silentTransactions || !$this->inTransaction())
            return parent::beginTransaction();
        
        return false;
    }

    public function commit()
    {
        if (!$this->silentTransactions || $this->inTransaction())
            return parent::commit();
        
        return false;
    }

    public function rollBack()
    {
        if (!$this->silentTransactions || $this->inTransaction())
            return parent::rollBack();
        
        return false;
    }
}
