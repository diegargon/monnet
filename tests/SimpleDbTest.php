<?php
define('IN_WEB', true);

// Incluye solo una vez
require_once 'class/Database.php';

use PHPUnit\Framework\TestCase;

class SimpleDatabaseTest extends TestCase
{
    public function testDatabaseInstance()
    {
        $db = new Database();
        $this->assertInstanceOf(Database::class, $db);
    }
}
