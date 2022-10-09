<?php

namespace App\Models;

use CodeIgniter\Model;

class GroupModel extends Model
{
    protected $table = 'groups';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $allowedFields = ['name', 'code'];

    public function getGroupsWithFewestUsers(array $groupIds): array
    {
        $builder = $this->builder();
        $builder->select(['group_id', 'COUNT(id) AS countIds']);
        $builder->from('users');
        $builder->where('group_id', $groupIds);
        $builder->groupBy('group_id');
        $builder->orderBy('countIds', 'DESC');

        return $builder->get()->getResult('array');
    }
}
