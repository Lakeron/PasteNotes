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
            $this->reorderPools($note->id, 1, 1000000000);
            $this->db->insert('pools', array(
                'note_id' => $note->id,
                'name' => 'New pool',
                'position' => 1
            ))->execute();
        } else {
           return false;
        }
    }

    public function updatePool($pool_id, $code, $data) {
        $pool = $this->db->select('p.*')
                     ->from('pools p')
                     ->leftJoin('notes n')->on('p.note_id = n.id')
                     ->where('p.id = %i AND n.code = %s', $pool_id, $code)->fetch();
        if($pool) {
            if(isset($data['position'])) {
                $this->reorderPools($pool->note_id, $data['position'], $pool->position);
            }
            return $this->db->update('pools', $data)
                ->where('id = %i', $pool->id)
                ->execute();
        } else {
            return false;
        }
    }

    public function deletePool($pool_id, $code) {
        $pool = $this->db->select('p.*')
                     ->from('pools p')
                     ->leftJoin('notes n')->on('p.note_id = n.id')
                     ->where('p.id = %i AND n.code = %s', $pool_id, $code)->fetch();
        if($pool) {
            return $this->db->delete('pools')->where('id = %i', $pool->id)->execute();
        } else {
            return false;
        }
    }

    public function reorderPools($note_id, $position, $old_position) {
        if($position > $old_position) {
            $this->db->query('UPDATE pools
                SET position = position - 1
                WHERE note_id = %i AND position BETWEEN %i AND %i',
                    $note_id,
                    $old_position,
                    $position
            );
        } else {
            $this->db->query('UPDATE pools
                SET position = position + 1
                WHERE note_id = %i AND position BETWEEN %i AND %i',
                    $note_id,
                    $position,
                    $old_position
            );
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
                'name' => 'Activity pool',
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
                ->orderBy('position')
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

    public function saveTask($note_id, $pool_id, array $tasks) {
        $this->reorderTasks($pool_id, 1, 1000000000);
        foreach($tasks as $key => $task) {
            $this->db->insert('tasks', array(
                'note_id' => $note_id,
                'pool_id' => $pool_id,
                'text' => $task,
                'position' => $key+1
            ))->execute();
        }
    }

    public function changeTask($task_id, $pool_id, $position, $code)
    {
        $task = $this->db->select('t.*')
            ->from('tasks t')
            ->leftJoin('notes n')->on('t.note_id = n.id')
            ->where('t.id = %i AND n.code = %s', $task_id, $code)->fetch();
        if($task) {
            // move in the same pool
            if($pool_id == $task->pool_id) {
                $this->reorderTasks($pool_id, $position, $task->position);
                return $this->db->update('tasks', array(
                    'pool_id' => $pool_id,
                    'position' => $position
                ))->where('id = %i', $task_id)->execute();
            } else {
                $this->reorderTasks($pool_id, $position, 1000000000);
                return $this->db->update('tasks', array(
                            'pool_id' => $pool_id,
                            'position' => $position
                       ))->where('id = %i', $task_id)->execute();
            }
        } else {
            false;
        }
    }

    public function reorderTasks($pool_id, $position, $old_position) {
        if($position > $old_position) {
            $this->db->query('UPDATE tasks
                SET position = position - 1
                WHERE pool_id = %i AND position BETWEEN %i AND %i',
                    $pool_id,
                    $old_position,
                    $position
            );
        } else {
            $this->db->query('UPDATE tasks
                SET position = position + 1
                WHERE pool_id = %i AND position BETWEEN %i AND %i',
                    $pool_id,
                    $position,
                    $old_position
            );
        }
    }
}