<?php

/**
 *
 * @author diego/@/envigo.net
 * @package
 * @subpackage
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */

class DBManager
{
    private AppContext $ctx;
    private ?\PDO $connection = null;
    private string $dsn;
    private string $username;
    private string $password;

    /**
     *
     * @param AppContext $ctx
     * @throws \InvalidArgumentException
     */
    public function __construct(AppContext $ctx)
    {
        $this->ctx = $ctx;
        $config = $ctx->get('cfg');

        $dbType = $config['dbtype'];
        $host = $config['dbhost'];
        $port = $config['dbport'] ?? null; // Puerto opcional
        $dbName = $config['dbname'];
        $username = $config['dbuser'];
        $password = $config['dbpassword'];

        // Construir DSN dependiendo del tipo de base de datos
        switch ($dbType) {
            case 'mysql':
            case 'mysqli':
                $this->dsn = "mysql:host=$host;dbname=$dbName;charset=utf8";
                break;
            case 'pgsql':
                $this->dsn = "pgsql:host=$host;port=$port;dbname=$dbName";
                break;
            case 'sqlite':
                $this->dsn = "sqlite:$dbName"; // Para SQLite, dbName debe ser una ruta al archivo
                break;
            case 'sqlsrv':
                $this->dsn = "sqlsrv:server=$host;Database=$dbName";
                break;
            default:
                throw new \InvalidArgumentException("Unsupported DB type: $dbType");
        }

        $this->username = $username;
        $this->password = $password;
        $this->connect();
    }

    /**
     * Conectar a la base de datos
     *
     * @throws RuntimeException Si la conexión falla
     */
    public function connect(): void
    {
        try {
            $this->connection = new \PDO($this->dsn, $this->username, $this->password);
            $this->connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        } catch (\PDOException $e) {
            throw new \RuntimeException("Connection failed: " . $e->getMessage());
        }
    }

    /**
     * Desconectar de la base de datos
     */
    public function disconnect(): void
    {
        $this->connection = null;
    }

    /**
     * Ejecutar una consulta SQL
     *
     * @param string $sql Consulta SQL a ejecutar
     * @param array<string, mixed> $params Parámetros de consulta
     *
     * @return array<string, mixed>|null
     */
    public function query(string $sql, array $params = []): bool
    {
        $stmt = $this->connection->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Retrieve a single result row
     *
     * @param string $sql Consulta SQL para obtener datos
     * @param array<string, mixed> $params Parámetros de consulta
     *
     * @return array<int, array<string, mixed>>
     */
    public function fetch(string $sql, array $params = []): ?array
    {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Retrieve multiple result rows
     *
     * @param string $sql Consulta SQL para obtener datos
     * @param array<string,mixed> $params Parámetros de consulta
     *
     * @return array<int, array<string, mixed>>
     */
    public function fetchAll(string $sql, array $params = []): array
    {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Obtener el último ID insertado
     *
     * @return int El último ID insertado
     */
    public function lastInsertId(): int
    {
        return (int) $this->connection->lastInsertId();
    }

    /**
     * Obtener la conexión actual
     *
     * @return PDO|null La conexión PDO actual
     */
    public function getConnection(): ?\PDO
    {
        return $this->connection;
    }
}
