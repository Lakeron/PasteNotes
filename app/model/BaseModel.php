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

    public function getActive($code)
    {
        return $this->db->select('n.*, p.id pool_id, p.name pool_name')->from('notes n')->join('pools p')->on('p.note_id = n.id')->where('n.code = %s AND p.isActive = 1', $code)->fetch();
    }

    public function addPool($code) {
        $note = $this->db->select('*')->from('notes')->where('code = %s', $code)->fetch();
        if($note) {
            $this->db->insert('pools', array(
                'note_id' => $note->id,
                'name' => 'New pool'
            ))->execute();
        } else {
           return false;
        }
    }

    public function getDefaultPools($code) {
        $note = $this->db->select('*')->from('notes')->where('code = %s', $code)->fetch();
        if(!$note) {
            $this->db->insert('notes', array(
                'code' => $code,
                'request' => serialize($_REQUEST)
            ))->execute();
            $note_id = $this->db->getInsertId();

            $this->db->insert('pools', array(
                'note_id' => $note_id,
                'name' => 'Done',
                'isDone' => 1
            ))->execute();

            $this->db->insert('pools', array(
                'note_id' => $note_id,
                'name' => 'Active pool',
                'isActive' => 1
            ))->execute();

            $this->db->insert('pools', array(
                'note_id' => $note_id,
                'name' => 'Deleted',
                'isDeleted' => 1
            ))->execute();

            $this->db->insert('pools', array(
                'note_id' => $note_id,
                'name' => "Today todo's",
            ))->execute();
        } else {
            $note_id = $note->id;
        }
        return $this->db->select('*')
                    ->from('pools p')
                    ->where('note_id = %i AND (isActive = 1 || isDone = 1 || isDeleted = 1)', $note_id)
                    ->orderBy('isDone DESC')
                    ->fetchAll();

    }

    public function getUserPools($code) {
        $note = $this->db->select('*')->from('notes')->where('code = %s', $code)->fetch();
        if($note) {
            return $this->db->select('*')
                ->from('pools p')
                ->where('note_id = %i AND (isActive = 0 && isDone = 0 && isDeleted = 0)', $note->id)
                ->fetchAll();
        } else {
            return false;
        }

    }

    public function getTasks($code, $pool_id) {
        $note = $this->db->select('*')->from('notes')->where('code = %s', $code)->fetch();
        if($note) {
            return $this->db->select('*')
                        ->from('tasks')
                        ->where('note_id = %i AND pool_id = %i', $note->id, $pool_id)
                        ->orderBy('position, id ASC')
                        ->fetchAll();
        } else {
            return false;
        }
    }

    public function changePool($task_id, $pool_id, $position = 0) {
        $this->reorderPool($pool_id, $position);
        return $this->db->update('tasks', array(
                            'pool_id' => $pool_id,
                            'position' => $position
                        ))
                        ->where('id = %i', $task_id)
                        ->execute();
    }

    public function saveNote($note_id, $pool_id, array $tasks) {

        $this->reorderPool($pool_id, 1);
        foreach($tasks as $key => $task) {
            $this->db->insert('tasks', array(
                'note_id' => $note_id,
                'pool_id' => $pool_id,
                'text' => $task,
                'position' => $key+1
            ))->execute();
        }
    }

    public function reorderPool($pool_id, $position = 0) {
        $this->db->query('SET @position = %i', $position);
        $this->db->query('
            UPDATE tasks t
            JOIN (SELECT @position := @position + 1 AS new_position, id FROM tasks WHERE pool_id = %i AND position >= %i  ORDER BY position ASC) t2 ON t2.id = t.id
            SET t.position = t2.new_position WHERE t.pool_id = %i',
            $pool_id,
            $position,
            $pool_id
        );

    }
}