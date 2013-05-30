<?php
/**
 * Created by JetBrains PhpStorm.
 * User: lakeron
 * Date: 3/30/13
 * Time: 11:30 PM
 * To change this template use File | Settings | File Templates.
 */

namespace Model;

use Nette\Object;

class BaseModel extends Object {

    /** @var DibiConnection */
    public $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function saveNote($code, array $tasks) {
        $note = $this->db->select('*')->from('notes')->where('code = %s', $code)->fetch();
        if(!$note) {
            $note_id = $this->db->insert('notes', array(
                'code' => $code,
                'request' => serialize($_REQUEST)
            ))->execute();
        } else {
            $note_id = $note->id;
        }

        foreach($tasks as $task) {
            $this->db->insert('tasks', array(
                'note_id' => $note_id,
                'text' => $task
            ))->execute();
        }
    }

    public function getNotes($code, $status_id) {
        $note = $this->db->select('*')->from('notes')->where('code = %s', $code)->fetch();
        if($note) {
            return $this->db->select('*')
                        ->from('tasks')
                        ->where('note_id = %i AND status_id = %i', $note->id, $status_id)
                        ->orderBy('position, id DESC')
                        ->fetchAll();
        } else {
            return false;
        }
    }

    public function changeStatus($note_id, $status_id) {
        return $this->db->update('tasks', array('status_id' => $status_id))
                        ->where('id = %i', $note_id)
                        ->execute();
    }
}