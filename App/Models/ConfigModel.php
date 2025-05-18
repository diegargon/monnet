<?php

namespace App\Models;

class ConfigModel
{
    private $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Devuelve todas las filas de la tabla config.
     */
    public function getAll(): array
    {
        $query = "SELECT ckey, cvalue, ctype, ccat FROM config";
        $stmt = $this->db->query($query);
        return $this->db->fetchAll($stmt);
    }

    /**
     * Devuelve todas las filas editables.
     */
    public function getAllEditable(): array
    {
        $query = "SELECT ckey, cvalue, ctype, ccat, cdesc FROM config WHERE ccat > 0";
        $stmt = $this->db->query($query);
        return $this->db->fetchAll($stmt);
    }

    /**
     * Guarda los cambios en la base de datos.
     * @param array<string, mixed> $modifiedKeys
     */
    public function saveChanges(array $modifiedKeys): void
    {
        if (empty($modifiedKeys)) {
            return;
        }
        $values = [];
        foreach ($modifiedKeys as $key => $value) {
            // DBManager does not have escape, so use parameterized queries instead
            $values[] = [
                'ckey' => $key,
                'cvalue' => json_encode($value['value'], JSON_HEX_APOS | JSON_HEX_QUOT)
            ];
        }
        // Build a multi-insert with ON DUPLICATE KEY UPDATE
        $sql = "INSERT INTO config (ckey, cvalue) VALUES ";
        $placeholders = [];
        $params = [];
        foreach ($values as $i => $row) {
            $ckey = ":ckey_$i";
            $cvalue = ":cvalue_$i";
            $placeholders[] = "($ckey, $cvalue)";
            $params["ckey_$i"] = $row['ckey'];
            $params["cvalue_$i"] = $row['cvalue'];
        }
        $sql .= implode(', ', $placeholders);
        $sql .= " ON DUPLICATE KEY UPDATE cvalue = VALUES(cvalue)";
        $this->db->query($sql, $params);
    }
}
