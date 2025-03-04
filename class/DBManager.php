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
     * @return bool True on success, false on failure.
     * @return array<string, mixed>|null
     * @throws \RuntimeException If the statement preparation fails.
     */
    public function query(string $sql, array $params = []): bool
    {
        $stmt = $this->connection->prepare($sql);
        if (!$stmt) {
            throw new \RuntimeException("Failed to prepare SQL statement: " . $sql);
        }
        return $stmt->execute($params);
    }

    /**
     * Fetch a single result row
     *
     *
     * @param \PDOStatement $stmt The prepared statement to fetch data from.
     * @return array|null The fetched row as an associative array, or null if no data is found.
    */
    public function fetch(\PDOStatement $stmt): ?array
    {
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Retrieve multiple result rows
     * @param \PDOStatement $stmt The prepared statement to fetch data from.
     * @return array|null
     */
    public function fetchAll(\PDOStatement $stmt): array
    {
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Query and Retrieve a single result row
     *
     * @param string $sql Consulta SQL para obtener datos
     * @param array<string, mixed> $params Parámetros de consulta
     *
     * @return array<int, array<string, mixed>>
     */
    public function qfetch(string $sql, array $params = []): ?array
    {
        $stmt = $this->connection->prepare($sql);
        $this->bindParams($stmt, $params);
        $stmt->execute();
        return $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * Query and Retrieve multiple result rows
     *
     * @param string $sql Consulta SQL para obtener datos
     * @param array<string,mixed> $params Parámetros de consulta
     *
     * @return array<int, array<string, mixed>>
     */
    public function qfetchAll(string $sql, array $params = []): array
    {
        $stmt = $this->connection->prepare($sql);
        $this->bindParams($stmt, $params);
        $stmt->execute();
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

    /**
     * Update records in a table
     *
     * @param string $table Table name
     * @param array<string, mixed> $data Key-value pairs of columns and their new values
     * @param string $condition WHERE clause (without "WHERE")
     * @param array<string, mixed> $params Parameters for the WHERE clause
     * @return bool True on success, false on failure
     */
    public function update(string $table, array $data, string $condition, array $params = []): bool
    {
        // Convert bool to int to avoid fail (why need?)
        foreach ($data as $key => $value) {
            if (is_bool($value)) {
                $data[$key] = (int) $value;
            }
        }

        $columns = array_keys($data);
        $setClause = implode(", ", array_map(fn($col) => "$col = :$col", $columns));

        $sql = "UPDATE $table SET $setClause WHERE $condition";
        $stmt = $this->connection->prepare($sql);

        if (!$stmt) {
            throw new \RuntimeException("Failed to prepare SQL statement: " . $sql);
        }

        if (!$stmt->execute($data + $params)) {
            throw new \RuntimeException("Failed to execute SQL statement: " . $sql);
        }

        return true;
    }

    /**
     * Delete records from a table
     *
     * @param string $table Table name
     * @param string $condition WHERE clause (without "WHERE")
     * @param array<string, mixed> $params Parameters for the WHERE clause
     * @return bool True on success, false on failure
     */
    public function delete(string $table, string $condition, array $params = []): bool
    {
        $sql = "DELETE FROM $table WHERE $condition";
        $stmt = $this->connection->prepare($sql);

        if (!$stmt) {
            throw new \RuntimeException("Failed to prepare SQL statement: " . $sql);
        }

        return $stmt->execute($params);
    }

    /**
     * Insert a new record into a table
     *
     * @param string $table Table name
     * @param array<string, mixed> $data Key-value pairs of columns and values to insert
     * @return bool True on success, false on failure
     */
    public function insert(string $table, array $data): bool
    {
        $columns = array_keys($data);
        $placeholders = array_map(fn($col) => ":$col", $columns);

        $sql = "INSERT INTO $table (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $placeholders) . ")";
        $stmt = $this->connection->prepare($sql);

        if (!$stmt) {
            throw new \RuntimeException("Failed to prepare SQL statement: " . $sql);
        }

        return $stmt->execute($data);
    }

    /**
     * Actualiza valores específicos dentro de un campo JSON sin sobrescribirlo.
     *
     * @param string $table  Nombre de la tabla
     * @param string $json_column  Nombre de la columna JSON
     * @param array  $json_data  Datos a actualizar dentro del JSON
     * @param string $condition  Condición WHERE
     * @param array  $params  Parámetros de la condición WHERE
     * @return bool  `true` si se actualizó correctamente, `false` si no
     */
    public function updateJson(string $table, string $json_column, array $json_data, string $condition, array $params): bool
    {
        if (empty($json_data)) {
            throw new \InvalidArgumentException('Los datos JSON no pueden estar vacíos');
        }

        $json_updates = [];
        foreach ($json_data as $key => $value) {
            $json_key = '$.' . $key;
            $param_key = ":json_{$key}";

            // Verificar si es un número o booleano para evitar comillas innecesarias
            if (is_numeric($value) || is_bool($value) || $value === '') {
                $params["json_{$key}"] = $value;
            } else {
                $params["json_{$key}"] = json_encode($value, JSON_UNESCAPED_SLASHES);
            }

            $json_updates[] = "$json_column = JSON_SET($json_column, '{$json_key}', {$param_key})";
        }

        $sql = "UPDATE $table SET " . implode(', ', $json_updates) . " WHERE $condition";

        $stmt = $this->connection->prepare($sql);

        return $stmt->execute($params);
    }

    /**
     * Select records from a table
     *
     * @param string $table Table name
     * @param array<string> $columns Columns to retrieve
     * @param string|null $condition WHERE clause (without "WHERE"), optional
     * @param array<string, mixed> $params Parameters for the WHERE clause
     * @param int|null $limit Maximum number of records to return
     * @return array<int, array<string, mixed>> List of results as associative arrays
     * @throws \RuntimeException If query execution fails
     */
    public function select(
            string $table,
            array $columns = ['*'],
            ?string $condition = null,
            array $params = [],
            ?int $limit = null
    ): array
    {
        $columnList = implode(", ", $columns);
        $sql = "SELECT $columnList FROM $table";

        if ($condition) {
            $sql .= " WHERE $condition";
        }

        if ($limit !== null && $limit > 0) {
            $sql .= ' LIMIT ' . $limit;
        }

        $stmt = $this->connection->prepare($sql);
        if (!$stmt) {
            throw new \RuntimeException("Failed to prepare SQL statement: " . $sql);
        }

        $stmt->execute($params);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Select a single record from a table.
     *
     * @param string $table Table name
     * @param array<string> $columns Columns to select (default: all)
     * @param string|null $condition WHERE clause (without "WHERE")
     * @param array<string, mixed> $params Query parameters
     *
     * @return array<string, mixed>|null The selected record or null if not found
     */
    public function selectOne(
        string $table,
        array $columns = ['*'],
        ?string $condition = null,
        array $params = []
    ): ?array {

        $result = $this->select($table, $columns, $condition, $params, 1);

        return $result[0] ?? null;
    }


    /**
     * Binds parameters dynamically based on their type.
     *
     * @param PDOStatement $stmt
     * @param array<string, mixed> $params
     * @return void
     */
    private function bindParams(PDOStatement $stmt, array $params): void
    {
        foreach ($params as $key => $value) {
            if (is_int($value)) {
                $stmt->bindValue($key, $value, PDO::PARAM_INT);
            } elseif (is_bool($value)) {
                $stmt->bindValue($key, (int) $value, PDO::PARAM_INT);
            } elseif (is_null($value)) {
                $stmt->bindValue($key, null, PDO::PARAM_NULL);
            } else {
                $stmt->bindValue($key, $value, PDO::PARAM_STR);
            }
        }
    }
}
