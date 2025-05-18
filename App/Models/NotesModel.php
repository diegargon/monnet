<?php

/**
 *
 * @author diego/@/envigo.net
 * @copyright Copyright CC BY-NC-ND 4.0 @ 2020 - 2025 Diego Garcia (diego/@/envigo.net)
 */

namespace App\Models;


use App\Core\DBManager;
/**
 * Modelo para la tabla notes.
 * Campos: id, uid, host_id, update, content
 */
class NotesModel
{
    /** @var DBManager */
    private DBManager $db;

    /**
     * @param DBManager $db Instancia de DBManager para acceso a base de datos
     */
    public function __construct(DBManager $db)
    {
        $this->db = $db;
    }

    /**
     * Crea una nota vacía para un host.
     * @param int $host_id
     * @param string $content
     * @param int $uid
     * @return bool
     */
    public function createForHost(int $host_id, string $content = '', int $uid = 0): bool
    {
        $data = [
            'uid' => $uid,
            'host_id' => $host_id,
            'content' => $content,
            // 'update' se autogenera por la base de datos
        ];
        return $this->db->insert('notes', $data);
    }

    /**
     * Obtiene todas las notas de un host.
     * @param int $host_id
     * @return array<int, array<string, mixed>>
     */
    public function getNotesByHost(int $host_id): array
    {
        return $this->db->select(
            'notes',
            ['id', 'uid', 'host_id', 'update', 'content'],
            'host_id = :host_id',
            ['host_id' => $host_id]
        );
    }

    /**
     * Actualiza el contenido de una nota.
     * @param int $note_id
     * @param string $content
     * @return bool
     */
    public function updateNote(int $note_id, string $content): bool
    {
        return $this->db->update(
            'notes',
            ['content' => $content],
            'id = :id',
            ['id' => $note_id]
        );
    }

    /**
     * Elimina una nota por id.
     * @param int $note_id
     * @return bool
     */
    public function deleteNote(int $note_id): bool
    {
        return $this->db->delete('notes', 'id = :id', ['id' => $note_id]);
    }
}
