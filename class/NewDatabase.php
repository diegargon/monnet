<?php

/*
 * replacement class
 */

class NewDatabase
{
    private ?\PDO $connection = null;
    private string $dsn;
    private string $username;
    private string $password;

    /**
     * @param array $config Arreglo de configuración de la base de datos
     * @throws InvalidArgumentException Si el tipo de base de datos no es soportado
     */
    public function __construct(array $config)
    {
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
     * @param array $params Parámetros de consulta
     * @return bool Verdadero si la consulta se ejecutó con éxito
     */
    public function query(string $sql, array $params = []): bool
    {
        $stmt = $this->connection->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * Obtener una fila de resultados
     *
     * @param string $sql Consulta SQL para obtener datos
     * @param array $params Parámetros de consulta
     * @return array|null Un arreglo asociativo con los datos obtenidos o null si no hay resultados
     */
    public function fetchOne(string $sql, array $params = []): ?array
    {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Obtener múltiples filas de resultados
     *
     * @param string $sql Consulta SQL para obtener datos
     * @param array $params Parámetros de consulta
     * @return array Un arreglo de arreglos asociativos con los datos obtenidos
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
