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

use PHPUnit\Framework\TestCase;

class DatabseTest extends TestCase
{

    public function testConnection()
    {
        $db = Database::connectSQLite(":memory:");
        
        $this->assertTrue(!!$db);
    }

    public function testConnectionFailure()
    {
        $this->expectException(\PDOException::class);
        
        Database::connectMySQL("nouser", "nopass");
    }

    public function testSilentTransactions()
    {
        $db = Database::connectSQLite(":memory:");
        
        $commitResult = $db->commit();
        $rollBackResult = $db->rollBack();
        $beginResult1 = $db->beginTransaction();
        $beginResult2 = $db->beginTransaction();
        
        $this->assertFalse($commitResult);
        $this->assertFalse($rollBackResult);
        $this->assertTrue($beginResult1);
        $this->assertFalse($beginResult2);
    }

    public function testSilentTransactionsDisabled()
    {
        $db = new Database("sqlite::memory:");
        
        $this->expectException(\PDOException::class);
        
        $db->commit();
    }

    public function testRunQuery()
    {
        $db = Database::connectSQLite(":memory:");
        
        $this->assertEquals([["col" => "1"]], $db->query("SELECT 1 AS col")->fetchAll());
    }

    public function testSelectNow()
    {
        $db = Database::connectSQLite(":memory:");
        
        $this->assertEquals([["col" => date("Y-m-d")]], 
            $db->query("SELECT date('now') AS col")->fetchAll());
    }
}