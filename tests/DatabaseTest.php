<?php

use PHPUnit\Framework\TestCase;

class DatabaseTest extends TestCase
{
    private NewDatabase $db;

    protected function setUp(): void
    {
        define('IN_WEB', true);
        require_once 'config/config.defaults.php';
        var_dump($cfg_db);
        if (empty($cfg_db['dbtype'])) {
            throw new \RuntimeException("Error: 'dbtype' no está definida en \$cfg_db, verifica config.defaults.php.");
        }
        $this->db = new NewDatabase($cfg_db);
    }

    public function testConnection()
    {
        $this->db->connect();
        $this->assertNotNull($this->db->getConnection(), "Connection should not be null");
    }

    public function testQueryExecution()
    {
        $this->db->connect();
        $result = $this->db->query("CREATE TABLE IF NOT EXISTS test_table (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(255) NOT NULL)");
        $this->assertTrue($result, "Query should execute successfully");
    }

    public function testInsertAndFetch()
    {
        $this->db->connect();
        $this->db->query("INSERT INTO test_table (name) VALUES (:name)", [':name' => 'John Doe']);

        $user = $this->db->fetchOne("SELECT * FROM test_table WHERE name = :name", [':name' => 'John Doe']);
        $this->assertNotNull($user, "User should be found");
        $this->assertEquals('John Doe', $user['name'], "User name should match");
    }

    protected function tearDown(): void
    {
        // Limpiamos la base de datos después de las pruebas
        $this->db->query("DROP TABLE IF EXISTS test_table");
        $this->db->disconnect();
    }
}
