<?php

/**
 * Class DBManager
 *
 * Handles database connections and operations.
 *
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
*/

namespace App\Core;

class DBManager
{
    private AppContext $ctx;
    private ?\PDO $connection = null;
    private string $dsn;
    private string $username;
    private string $password;

    /**
     * DBManager constructor.
     *
     * Initializes the database connection using the provided application context.
     *
     * @param AppContext $ctx Application context containing configuration data.
     * @throws \InvalidArgumentException If the database type is unsupported.
     */
    public function __construct(AppContext $ctx)
    {
        $this->ctx = $ctx;
        $config = $ctx->get('cfg');

        $dbType = $config['dbtype'];
        $host = $config['dbhost'];
        $port = $config['dbport'] ?? null; // Optional port
        $dbName = $config['dbname'];
        $username = $config['dbuser'];
        $password = $config['dbpassword'];

        // Build DSN depending on the database type
        switch ($dbType) {
            case 'mysql':
            case 'mysqli':
                $port = $port ?? '3306';
                $this->dsn = "mysql:host=$host;port=$port;dbname=$dbName;charset=utf8";
                break;
            case 'pgsql':
                $port = $port ?? '5432';
                $this->dsn = "pgsql:host=$host;port=$port;dbname=$dbName";
                break;
            case 'sqlite':
                $this->dsn = "sqlite:$dbName"; // For SQLite, dbName must be a file path
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
     * Destructor.
     *
     * Disconnects from the database and cleans up resources.
     */
    public function __destruct()
    {
        $this->disconnect();
        unset($this->connection, $this->ctx);
    }

    /**
     * Connect to the database.
     *
     * @throws \RuntimeException If the connection fails.
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
     * Disconnect from the database.
     */
    public function disconnect(): void
    {
        $this->connection = null;
    }

    /**
     * Executes an SQL query and returns the prepared statement.
     *
     * @param string $sql The SQL query to execute.
     * @param array<string, mixed> $params The parameters to bind to the query.
     * @return \PDOStatement The prepared statement after execution.
     * @throws \RuntimeException If the query fails.
     */
    public function query(string $sql, array $params = []): \PDOStatement
    {
        try {
            $stmt = $this->connection->prepare($sql);
            if (!$stmt) {
                throw new \RuntimeException("Failed to prepare SQL statement: " . $sql);
            }

            $stmt->execute($params);

            return $stmt;
        } catch (\PDOException $e) {
            throw new \RuntimeException("Query failed: " . $e->getMessage() . " [SQL: $sql]", 0, $e);
        }
    }

    /**
     * Fetch a single result row.
     *
     * @param \PDOStatement $stmt The prepared statement to fetch data from.
     * @return array<string, mixed>|null An associative array for a single row, or null if no rows found.
     */
    public function fetch(\PDOStatement $stmt): ?array
    {
        $result = $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        $stmt->closeCursor();

        return $result;
    }

    /**
     * Retrieve multiple result rows.
     *
     * @param \PDOStatement $stmt The prepared statement to fetch data from.
     * @return array<int, array<string, mixed>> An array of associative arrays, or an empty array if no results.
     */
    public function fetchAll(\PDOStatement $stmt): array
    {
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC) ;
        $stmt->closeCursor();

        return $result;
    }

    /**
     * Query and retrieve a single result row.
     *
     * @param string $sql SQL query to fetch data.
     * @param array<string, mixed> $params Query parameters.
     * @return array<string, mixed>|null An associative array for a single row, or null if no rows found.
     */
    public function qfetch(string $sql, array $params = []): ?array
    {
        $stmt = $this->connection->prepare($sql);
        $this->bindParams($stmt, $params);
        $stmt->execute();
        $result = $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
        $stmt->closeCursor();

        return $result;
    }

    /**
     * Query and retrieve multiple result rows.
     *
     * @param string $sql SQL query to fetch data.
     * @param array<string, mixed> $params Query parameters.
     * @return array<int, array<string, mixed>> An array of associative arrays.
     */
    public function qfetchAll(string $sql, array $params = []): array
    {
        $stmt = $this->connection->prepare($sql);
        $this->bindParams($stmt, $params);
        $stmt->execute();
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        return $result;
    }

    /**
     * Get the last inserted ID.
     *
     * @return int The last inserted ID.
     * @throws \RuntimeException If there is no active database connection.
     */
    public function lastInsertId(): int
    {
        if (!$this->connection) {
            throw new \RuntimeException("No active database connection");
        }
        return (int)$this->connection->lastInsertId();
    }

    /**
     * Get the current database connection.
     *
     * @return \PDO|null The current PDO connection, or null if not connected.
     */
    public function getConnection(): ?\PDO
    {
        return $this->connection;
    }

    /**
     * Update records in a table.
     *
     * @param string $table The name of the table.
     * @param array<string, mixed> $data The data to update as key-value pairs.
     * @param ?string $condition The WHERE clause for the update.
     * @param array<string, mixed> $params The parameters for the WHERE clause.
     * @return bool True if the update was successful, false otherwise.
     * @throws \RuntimeException If the update fails.
     */
    public function update(string $table, array $data, ?string $condition, array $params = []): bool
    {
        // Convert bool to int to avoid fail (why need?)
        foreach ($data as $key => $value) {
            if (is_bool($value)) {
                $data[$key] = (int) $value;
            }
        }

        $columns = array_keys($data);
        $setClause = implode(", ", array_map(fn($col) => "$col = :$col", $columns));

        $sql = "UPDATE $table SET $setClause";

        if ($condition) {
            $sql .= " WHERE $condition";
        }

        $stmt = $this->connection->prepare($sql);

        if (!$stmt) {
            throw new \RuntimeException("Failed to prepare SQL statement: " . $sql);
        }

        if (!$stmt->execute($data + $params)) {
            throw new \RuntimeException("Failed to execute SQL statement: " . $sql);
        }
        $stmt->closeCursor();

        return true;
    }

    /**
     * Delete records from a table.
     *
     * @param string $table Table name.
     * @param string $condition WHERE clause.
     * @param array<string, mixed> $params Optional parameters for the WHERE clause.
     * @return bool True on success, false on failure.
     */
    public function delete(string $table, string $condition, array $params = []): bool
    {
        $sql = "DELETE FROM $table WHERE $condition";
        $stmt = $this->connection->prepare($sql);

        if (!$stmt) {
            throw new \RuntimeException("Failed to prepare SQL statement: " . $sql);
        }
        $this->bindParams($stmt, $params);
        $result = $stmt->execute();
        $stmt->closeCursor();

        return $result;
    }

    /**
     * Inserts a new record into a table.
     *
     * @param string $table The name of the table.
     * @param array<string, mixed> $data The data to insert as key-value pairs.
     * @return bool True if the insertion was successful, false otherwise.
     * @throws \RuntimeException If the insertion fails.
     */
    public function insert(string $table, array $data): bool
    {
        if (empty($data)) {
            return false;
        }
        $columns = array_keys($data);
        $placeholders = array_map(fn($col) => ":$col", $columns);

        $sql = "INSERT INTO $table (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $placeholders) . ")";
        $stmt = $this->connection->prepare($sql);

        if (!$stmt) {
            throw new \RuntimeException("Failed to prepare SQL statement: " . $sql);
        }

        $this->bindParams($stmt, $data);
        $result = $stmt->execute();
        $stmt->closeCursor();

        return $result;
    }

    /**
     * Update specific values within a JSON field without overwriting it.
     *
     * @param string $table Table name.
     * @param string $json_column JSON column name.
     * @param array<string, mixed> $json_data Data to update within the JSON.
     * @param string $condition WHERE clause.
     * @param array<string, mixed> $params Parameters for the WHERE clause.
     * @return bool True if updated successfully, false otherwise.
     * @throws \InvalidArgumentException If JSON data is invalid or empty.
     * @throws \RuntimeException For general errors.
     */
    public function updateJson(
        string $table,
        string $json_column,
        array $json_data,
        string $condition,
        array $params
    ): bool {
        if (empty($json_data)) {
            throw new \InvalidArgumentException('JSON data cannot be empty');
        }

        $json_updates = [];
        foreach ($json_data as $key => $value) {
            $json_key = '$.' . $key;
            $param_key = ":json_{$key}";

            // Avoid double quote on strings
            if (is_null($value) || is_numeric($value) || is_bool($value)) {
                $params["json_{$key}"] = $value;
            } else {
                $params["json_{$key}"] = (string) $value;
            }

            $json_updates[] = "$json_column = JSON_SET($json_column, '{$json_key}', {$param_key})";
        }

        $sql = "UPDATE $table SET " . implode(', ', $json_updates) . " WHERE $condition";

        $this->connection->beginTransaction();
        try {
            $stmt = $this->connection->prepare($sql);

            if (!$stmt) {
                throw new \RuntimeException("Error preparing SQL query");
            }

            $this->bindParams($stmt, $params);
            $success = $stmt->execute();

            if (!$success) {
                throw new \RuntimeException("Error executing JSON update");
            }

            $this->connection->commit();

            return true;
        } catch (\Exception $e) {
            $this->connection->rollBack();
            throw new \RuntimeException("Error in updateJson: " . $e->getMessage(), 0, $e);
        }

        return false;
    }

    /**
     * Update specific values within a JSON field without overwriting it (MySQL 8 variant).
     *
     * @param string $table Table name.
     * @param string $json_column JSON column name.
     * @param array<string, mixed> $json_data Data to update within the JSON.
     * @param string $condition WHERE clause.
     * @param array<string, mixed> $params Parameters for the WHERE clause.
     * @return bool True if updated successfully, false otherwise.
     */
    public function mysql8_updateJson(
        string $table,
        string $json_column,
        array $json_data,
        string $condition,
        array $params
    ): bool {
        if (empty($json_data)) {
            throw new \InvalidArgumentException('JSON data cannot be empty');
        }

        // Prepare the patch (only the keys to update)
        $patch = [];
        foreach ($json_data as $key => $value) {
            if (!preg_match('/^[a-z0-9_]+$/i', $key)) {
                throw new \InvalidArgumentException("Invalid JSON key: $key");
            }
            $patch[$key] = $value; // Without "$." because JSON_MERGE_PATCH uses flat notation
        }

        $params[':json_patch'] = json_encode($patch);
        $sql = "UPDATE $table
                SET $json_column = JSON_MERGE_PATCH($json_column, :json_patch)
                WHERE $condition";

        $this->connection->beginTransaction();
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            $this->connection->commit();
            return true;
        } catch (\Exception $e) {
            $this->connection->rollBack();
            throw $e;
        }
    }

    /**
     * Select records from a table.
     *
     * @param string $table Table name.
     * @param array<string> $columns Columns to retrieve.
     * @param string|null $condition WHERE clause (without "WHERE"), optional.
     * @param array<string, mixed> $params Parameters for the WHERE clause.
     * @param int|null $limit Maximum number of records to return.
     * @param string|null $extra Additional SQL clauses.
     * @return array<int, array<string, mixed>> List of results as associative arrays.
     * @throws \RuntimeException If query execution fails.
     */
    public function select(
        string $table,
        array $columns = ['*'],
        ?string $condition = null,
        array $params = [],
        ?int $limit = null,
        ?string $extra = null
    ): array {
        $columnList = implode(", ", $columns);
        $sql = "SELECT $columnList FROM $table";

        if ($condition) {
            $sql .= " WHERE $condition";
        }

        if ($extra !== null) {
            $sql .= $extra;
        }

        if ($limit !== null && $limit > 0) {
            $sql .= ' LIMIT ' . $limit;
        }

        $stmt = $this->connection->prepare($sql);
        if (!$stmt) {
            throw new \RuntimeException("Failed to prepare SQL statement: " . $sql);
        }

        $stmt->execute($params);
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $stmt->closeCursor();

        return $result;
    }

    /**
     * Select a single record from a table.
     *
     * @param string $table Table name.
     * @param array<string> $columns Columns to select (default: all).
     * @param string|null $condition WHERE clause (without "WHERE").
     * @param array<string, mixed> $params Query parameters.
     * @return array<string, mixed>|null The selected record or null if not found.
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
     * Insert or update a record in a table (UPSERT).
     *
     * @param string $table Table name.
     * @param array<string, mixed> $data Key-value pairs of columns and values.
     * @param array<string> $uniqueKeys Columns that determine uniqueness (for ON DUPLICATE KEY UPDATE in MySQL).
     * @return bool True on success, false on failure.
     */
    public function upsert(string $table, array $data, array $uniqueKeys): bool
    {
        if (empty($data) || empty($uniqueKeys)) {
            throw new \InvalidArgumentException("Data and unique keys cannot be empty");
        }

        $columns = array_keys($data);
        $placeholders = array_map(fn($col) => ":$col", $columns);

        $updates = [];
        foreach ($columns as $col) {
            if (!in_array($col, $uniqueKeys, true)) {
                $updates[] = "$col = VALUES($col)";
            }
        }

        $sql = "INSERT INTO $table (" . implode(", ", $columns) . ")
                VALUES (" . implode(", ", $placeholders) . ")
                ON DUPLICATE KEY UPDATE " . implode(", ", $updates);

        $stmt = $this->connection->prepare($sql);
        if (!$stmt) {
            throw new \RuntimeException("Failed to prepare SQL statement: " . $sql);
        }

        $result = $stmt->execute($data);
        $stmt->closeCursor();

        return $result;
    }

    /**
     * Start a database transaction.
     *
     * @return void
     * @throws \RuntimeException If the transaction fails to start.
     */
    public function beginTransaction(): void
    {
        if (!$this->connection->beginTransaction()) {
            throw new \RuntimeException("Failed to start transaction");
        }
    }

    /**
     * Commit a database transaction.
     *
     * @return void
     * @throws \RuntimeException If the transaction fails to commit.
     */
    public function commit(): void
    {
        if (!$this->connection->commit()) {
            throw new \RuntimeException("Failed to commit transaction");
        }
    }

    /**
     * Roll back a database transaction.
     *
     * @return void
     * @throws \RuntimeException If the transaction fails to roll back.
     */
    public function rollBack(): void
    {
        if (!$this->connection->rollBack()) {
            throw new \RuntimeException("Failed to rollback transaction");
        }
    }

    /**
     * Bind parameters dynamically based on their type.
     *
     * @param \PDOStatement $stmt The prepared statement.
     * @param array<string, mixed> $params Parameters to bind.
     * @throws \RuntimeException If the statement is invalid.
     */
    private function bindParams(\PDOStatement $stmt, array $params): void
    {

        if (!$stmt) {
            throw new \RuntimeException("Invalid PDOStatement");
        }
        foreach ($params as $key => $value) {
            if (is_int($value)) {
                $stmt->bindValue($key, $value, \PDO::PARAM_INT);
                continue;
            } elseif (is_bool($value)) {
                $stmt->bindValue($key, (int) $value, \PDO::PARAM_INT);
                continue;
            } elseif (is_null($value)) {
                $stmt->bindValue($key, null, \PDO::PARAM_NULL);
                continue;
            }

            $stmt->bindValue($key, $value, \PDO::PARAM_STR);
        }
    }

    /**
     * Check if the database connection is active.
     *
     * @return bool True if connected, false otherwise.
     */
    public function isConnected(): bool
    {
        return $this->connection instanceof \PDO;
    }
}
