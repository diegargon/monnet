<?php

/**
 * Mysql
 *
 * Database class
 *
 * @author diego////@////envigo.net
 * @package ProjectBase
 * @subpackage CORE
 * @copyright Copyright @ 2016 - 2021 Diego Garcia (diego////@////envigo.net)
 */
!defined('IN_WEB') ? exit : true;

class Database
{
    /**
     * table prefix
     * @var string
     */
    public $db_prefix;

    /**
     * charset
     * @var string
     */
    public $charset;

    /**
     * Min search char default: 2 (have setter)
     * @var int
     */
    private $min_search_char = 2;

    /**
     * Avoid print errors
     * @var boolean
     */
    private $silent = false;

    /**
     * db link object
     * @var object|null
     */
    protected $dblink = null;

    /**
     * host
     * @var string
     */
    protected $dbhost;

    /**
     * db name
     * @var string
     */
    protected $dbname;

    /**
     * db user
     * @var string
     */
    protected $dbuser;

    /**
     * db password
     * @var string
     */
    protected $dbpassword;
    //Logging

    /**
     * hold number querys
     * @var int
     */
    private $query_stats = 0;

    /**
     * hold query history
     * @var array<string>
     */
    private $query_history = [];

    /**
     *
     * @var int
     */
    private $query_affected = 0;

    /** @var bool */
    private $isConnected = false;
    /**
     * Set connection details, defaults, and init
     * @param array<string> $cfg_db
     */
    public function __construct(array $cfg_db)
    {
        $this->db_prefix = $cfg_db['dbprefix'];
        $this->charset = $cfg_db['dbcharset'];
        $this->dbhost = $cfg_db['dbhost'];
        $this->dbname = $cfg_db['dbname'];
        $this->dbuser = $cfg_db['dbuser'];
        $this->dbpassword = $cfg_db['dbpassword'];
    }

    /**
     * Destruct
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * Init connection
     *
     * @return boolean
     */
    public function connect(): bool
    {
        $this->dblink = new mysqli($this->dbhost, $this->dbuser, $this->dbpassword, $this->dbname);
        if ($this->dblink->connect_errno) {
            $this->isConnected = false;
            printf("Failed to connect to database: %s\n ", $this->dblink->connect_error);
            exit();
        }
        //$this->query('SET NAMES ' . $this->charset);
        $this->isConnected = true;
        $this->dblink->query('SET NAMES ' . $this->charset);
        return true;
    }

    /**
     *
     * @return bool
     */
    public function isConn(): bool
    {
        if ($this->isConnected && $this->dblink->ping()) :
            return true;
        endif;

        return $this->connect();
    }

    /**
     * prefix setter
     * @param string $prefix
     */
    public function setPrefix(string $prefix): void
    {
        $this->db_prefix = $prefix;
    }

    /**
     * charset setter
     * @param string $charset
     */
    public function setCharset(string $charset): void
    {
        $this->charset = $charset;
        $this->dblink->set_charset($this->charset);
    }

    /**
     * Min char setter
     * @param int $value
     */
    public function setMinCharSearch(int $value): void
    {
        $this->min_search_char = $value;
    }

    /**
     * Silent errors
     * @param boolean $value
     */
    public function silent(bool $value = true): void
    {
        $this->silent = $value;
    }

    /**
     * Query
     *
     * Std query wrap, add history and stats
     *
     * @param string $query
     *
     * @return mysqli_result|bool
     */
    public function query(string $query): mysqli_result|bool
    {
        $this->query_stats++;
        $this->query_history[] = $query;
        $result = $this->dblink->query($query);
        if (!$result && !$this->silent) {
            $this->dbdie($query);
        }
        $this->query_affected = $this->dblink->affected_rows;

        return $result;
    }

    /**
     *
     * @return int
     */
    public function getAffected(): int
    {
        return $this->query_affected;
    }
    /**
     * fetch wrap
     *
     * @param mysqli_result $result
     *
     * @return array<string,string>|bool
     */
    public function fetch(mysqli_result $result): array|bool
    {
        $row = $result->fetch_assoc();

        return $row ? $row : false;
    }

    /**
     * fetch all
     *
     * @param mysqli_result $result
     *
     * @return array<int, array<string,string>>
     */
    public function fetchAll(mysqli_result $result): array
    {
        $return_ary = [];
        if ($this->numRows($result) > 0) {
            while ($row = $this->fetch($result)) {
                $return_ary[] = $row;
            }
        }
        return $return_ary;
    }

    /**
     * Escape string
     *
     * @param string $var
     *
     * @return string
     */
    public function escape(string $var): string
    {
        return $this->dblink->real_escape_string($var);
    }

    /**
     * Escape and Strip tags
     *
     * @param string $var
     * @return string
     */
    public function escapeStrip(string $var)
    {
        return $this->dblink->real_escape_string(strip_tags($var));
    }

    /**
     * Return num rows
     *
     * @param object $result
     * @return int
     */
    public function numRows(object $result): int
    {
        return $result->num_rows;
    }

    /**
     *  Close dblink
     */
    public function close(): void
    {
        $this->dblink?->close();
    }

    /**
     * Print db error and exit
     *
     * @param string $query
     */
    private function dbdie(string $query): void
    {
        printf('<b>Error: Unable to retrieve information.</b>');
        printf("\n<br>%s", $query);
        printf("\n<br>reported: %s", $this->dblink->error);
        $this->close();
        exit;
    }

    /**
     * Return next table id
     *
     * @return int
     */
    public function insertID(): int
    {
        if (!($id = $this->dblink->insert_id)) {
            die('Could not connect: ' . $this->dblink->error);
        }

        return $id;
    }

    /**
     * free
     *
     * @param object $query
     */
    public function free(object &$query): void
    {
        $query->free();
    }

    /**
     * Check if table exists
     *
     * @param string $table
     * @return boolean
     */
    public function tableExists(string $table): bool
    {
        $query = 'SHOW TABLES LIKE \'' . $table . '\'';
        $result = $this->query($query);
        if ($this->numRows($result) == 1) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check if a value exists in a table.
     *
     * @param string $table The name of the table to check.
     * @param string $field The name of the field to check for the value.
     * @param mixed $value The value to check for existence in the table.
     * @return bool True if the value exists, false otherwise.
     */
    public function valueExists(string $table, string $field, $value): bool
    {
        $table = $this->fieldQuote($table);
        $field = $this->fieldQuote($field);
        $value = $this->valQuote($value);

        $query = "SELECT $field FROM $table WHERE $field = $value";
        $result = $this->query($query);

        return $this->numRows($result) > 0;
    }

    /**
     *
     * @param string $query
     * @return bool
     */
    public function queryExists(string $query): bool
    {
        $result = $this->query($query);

        return $this->numRows($result) > 0;
    }

    /**
     *
     * @param mixed $value
     * @return string
     */
    public function valQuote(mixed $value): string
    {
        return '\'' . $this->escape($value) . '\'';
    }

    /**
     *
     * @param string $value
     * @return string
     */
    public function fieldQuote(string $value): string
    {
        return '`' . $value . '`';
    }

    /**
     * Calculate next row number
     *
     * @param string $table
     * @param string $field
     * @return int|boolean
     */
    public function getNextNum(string $table, string $field): int|bool
    {

        if (empty($table) || empty($field)) {
            return false;
        }
        $table = $this->db_prefix . $table;
        $query = "SELECT MAX( $field ) AS max FROM `$table`;";
        $result = $this->query($query);
        $row = $this->fetch($result);

        return ++$row['max'];
    }

    /*
     * $db->selectAll("users", ['uid' => 1, 'username' => "myname"], "LIMIT 1");
     * Especify operator default '=';
     * $query = $db->selectAll("news", ["frontpage" => ["value"=> 1, "op" => "="], "moderation" => 0, "disabled" => 0]);
     * extra not array
     */
    /**
     * Select all fields
     *
     * @param string $table
     * @param array<string,int|string> $where
     * @param string $extra
     * @param string $logic
     * @return mysqli_result|bool
     */
    public function selectAll(
        string $table,
        array $where = null,
        string $extra = null,
        string $logic = 'AND'
    ): mysqli_result|bool {
        if (empty($table)) {
            return false;
        }
        $query = 'SELECT * FROM ' . $this->db_prefix . $table;

        if (!empty($where)) {
            $query .= ' WHERE ';
            $query .= $this->whereProcess($where, $logic);
        }
        !empty($extra) ? $query .= " $extra" : null;

        return $this->query($query);
    }

    /**
     * Select specific fields using $what
     *
     * @param string $table
     * @param string $what comma field separated
     * @param array<string,int|string> $where
     * @param string $extra
     * @param string $logic
     * @return mysqli_result|bool
     */
    public function select(
        string $table,
        $what = '*',
        array $where = null,
        $extra = null,
        $logic = 'AND'
    ): mysqli_result|bool {
        if (empty($table) || empty($what)) {
            return false;
        }

//FIXME TODO arreglo a correr para evitar fallo de palabras reservadas en mysql8 (groups/lead) revisar
        $what_filtered = '';

        if ($what !== '*') {
            $what_ary = explode(",", $what);
            $end_what = end($what_ary);

            foreach ($what_ary as $_what) {
                $what_filtered .= "`" . trim($_what) . "`";
                if ($_what != $end_what) {
                    $what_filtered .= ",";
                }
            }
        } else {
            $what_filtered = '*';
        }

        $query = 'SELECT ' . $what_filtered . ' FROM ' . $this->db_prefix . $table;

        if (!empty($where)) {
            $query .= ' WHERE ';
            $query .= $this->whereProcess($where, $logic);
        }
        !empty($extra) ? $query .= " $extra" : null;

        return $this->query($query);
    }

    /**
     * Search databse
     *
     * @param string $table
     * @param string $s_fields
     * @param string $searchText
     * @param array<string,int|string> $where
     * @param string $extra
     * @return mysqli_result|bool
     */
    public function search(
        string $table,
        string $s_fields,
        string $searchText,
        array $where = null,
        string $extra = null
    ): mysqli_result|bool {
        $s_words_ary = explode(' ', $searchText);
        $fields_ary = explode(' ', $s_fields);

        $where_s_fields = '';
        $where_s_tmp = '';
        $query = 'SELECT * FROM ' . $this->db_prefix . $table . ' WHERE ';

        if (!empty($where)) {
            $query .= $this->whereProcess($where, $logic = 'AND');
            $query .= ' AND ';
        }

        foreach ($fields_ary as $field) {
            !empty($where_s_fields) ? $where_s_fields .= ' OR ' : null;

            foreach ($s_words_ary as $s_word) {
                if (mb_strlen($s_word, $this->charset) > $this->min_search_char) {
                    !empty($where_s_tmp) ? $where_s_tmp .= ' AND ' : null;
                    $where_s_tmp .= " $field LIKE '%$s_word%' ";
                }
            }
            !empty($where_s_tmp) ? $where_s_fields .= $where_s_tmp : null;
            $where_s_tmp = "";
        }

        if (!empty($where_s_fields)) {
            $query .= '(' . $where_s_fields . ')';
        } else {
            return false;
        }
        !empty($extra) ? $query .= " $extra " : null;

        return $this->query($query);
    }

    /**
     * Update database
     *
     * @param string $table
     * @param array<string, mixed> $set
     * @param array<string, mixed> $where
     * @param string $extra
     * @param string $logic
     * @return mysqli_result|boolean
     */
    public function update(
        string $table,
        array $set,
        array $where = null,
        string $extra = null,
        string $logic = 'AND'
    ): mysqli_result|bool {
        if (empty($set) || empty($table)) {
            return false;
        }
        $query = 'UPDATE ' . $this->db_prefix . $table . ' SET ';
        $query .= $this->setProcess($set);

        if (!empty($where)) {
            $query .= ' WHERE ' . $this->whereProcess($where, $logic);
        }
        !empty($extra) ? $query .= " $extra" : null;
        return $this->query($query);
    }

    /**
     * Sum field +1
     * @param string $table
     * @param string $field
     * @param array<string,int|string> $where
     * @param string $logic
     * @return mysqli_result|bool
     */
    public function plusOne(
        string $table,
        string $field,
        array $where = null,
        string $extra = null,
        string $logic = 'AND'
    ): mysqli_result|bool {

        if (empty($field) || empty($table)) {
            return false;
        }
        $query = 'UPDATE ' . $this->db_prefix . $table . ' SET ' . $field . ' = ' . $field . ' +1';
        if (!empty($where)) {
            $query .= ' WHERE ' . $this->whereProcess($where, $logic);
        }
        !empty($extra) ? $query .= " $extra" : null;

        return $this->query($query);
    }

    /**
     * Toggle field 1/0
     *
     * @param string $table
     * @param string $field
     * @param array<string,int|string> $where
     * @param string $logic
     * @return array|bool
     */
    public function toggleField(
        string $table,
        string $field,
        array $where = null,
        string $logic = 'AND'
    ): array|bool {

        if (empty($field) || empty($table)) {
            return false;
        }
        $query = 'UPDATE ' . $this->db_prefix . $table . ' SET `' . $field . '` =  ' . '!' . '`' . $field . '`';
        if (!empty($where)) {
            $query .= ' WHERE ' . $this->whereProcess($where, $logic);
        }

        return $this->query($query);
    }

    /**
     * Insert
     *
     * @param string $table
     * @param array<string,int|string> $insert_data
     * @param string $extra
     * @return array|bool
     */
    public function insert(string $table, array $insert_data, string $extra = null): array|bool
    {

        if (empty($table) || empty($insert_data)) {
            return false;
        }
        $insert_ary = $this->insertProcess($insert_data);
        $query = "INSERT INTO " .
            $this->db_prefix . $table
            . " ( {$insert_ary['fields']} ) VALUES ( {$insert_ary['values']} ) $extra";

        return $this->query($query);
    }

    /**
     * Delete
     *
     * @param string $table
     * @param array<string,int|string> $where
     * @param string $extra
     * @param string $logic
     * @return array|bool
     */
    public function delete(string $table, array $where, string $extra = null, string $logic = 'AND'): array|bool
    {

        if (empty($table) || empty($where)) {
            return false;
        }
        $query = 'DELETE FROM ' . $this->db_prefix . $table . ' WHERE ';
        $query .= $this->whereProcess($where, $logic);
        !empty($extra) ? $query .= " $extra" : null;

        return $this->query($query);
    }

    /**
     * Insert or update if exists
     *
     * @param string $table
     * @param array<string,int|string> $set_ary
     * @param array<string,int|string> $where_ary
     */
    public function upsert(string $table, array $set_ary, array $where_ary): void
    {
        $insert_data = array_merge($where_ary, $set_ary);
        $set_data = $this->setProcess($set_ary);
        $this->insert($table, $insert_data, "ON DUPLICATE KEY UPDATE $set_data");
    }

    /**
     * Return number of executed querys
     *
     * @return int
     */
    public function numQuerys(): int
    {
        return $this->query_stats;
    }

    /**
     * return query history
     *
     * @return array
     */
    public function getQueryHistory(): array
    {
        return $this->query_history;
    }

    /**
     * Insert Processs
     *
     * @param array<string,int|string> $insert_data
     * @return array<string,int|string>
     */
    private function insertProcess(array $insert_data): array
    {
        $values_ary = [];
        $fields_ary = [];

        foreach ($insert_data as $field => $value) {
            $value = (is_string($value)) ? $value = $this->escape($value) : $value;
            //TODO FIXME correccion rapida para evitar errores en mysql 8 con groups lead (palabras reservadas)
            $fields_ary[] = '`' . $field . '`';
            $values_ary[] = "'" . $value . "'";
        }
        $insert['fields'] = implode(', ', $fields_ary);
        $insert['values'] = implode(', ', $values_ary);

        return $insert;
    }

    /**
     * Set process
     *
     * @param array<string,int|string> $set
     * @return string
     */
    private function setProcess(array $set): string
    {
        $newset = [];
        foreach ($set as $field => $value) {
            $value = $this->escape($value);
            $newset[] = "`$field` = " . "'" . $value . "'";
        }
        $query = implode(',', $newset);
        return $query;
    }

    /**
     * Where process
     *
     * @param array<string,int|string> $where
     * @param string $logic
     * @return string
     */
    private function whereProcess(array $where, string $logic): string
    {
        $q_where_fields = [];

        foreach ($where as $field => $value) {
            if (!is_array($value)) {
                $q_where_fields[] = "`$field` = " . "'" . $value . "'";
            } else {
                !isset($value['op']) ? $value['op'] = '=' : null;
                //$q_where_fields[] = "$field {$value['op']} '" . $value['value'] . "'";
                //$q_where_fields[] = "`$field` {$value['op']} " . $value['value']; //CHANGE 100818
                $q_where_fields[] = "`$field` {$value['op']} '" . $value['value'] . "'"; //CHANGE AGAIN
            }
        }
        $query = implode(" $logic ", $q_where_fields);
        return $query;
    }
}
