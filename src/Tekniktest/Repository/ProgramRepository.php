<?php

namespace Tekniktest\Repository;

use Doctrine\DBAL\Connection;


class ProgramRepository
{
    protected $db;
    protected $tableName = 'Programs';
 
    public function __construct(Connection $db)
    {
        $this->db = $db;
    }
 
    public function findAll()
    {
        return $this->db->fetchAll('SELECT rowid as id, * FROM ' . $this->tableName);
    }

    public function findById($id)
    {
        $statement = $this->db->executeQuery('SELECT rowid as id, * FROM ' . $this->tableName . ' WHERE ROWID = ?', array($id));
        return $statement ->fetch();
    }

    public function deleteById($id)
    {   
        return $this->db->delete($this->tableName , array('ROWID' => $id));
    }

    public function save($program)
    {
        $this->db->insert($this->tableName, $program);
        return $this->db->lastInsertId();
    }
}