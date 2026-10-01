<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class School_event_model extends CI_Model
{
    private $table = 'school_events';

    public function get_upcoming($limit = null)
    {
        $query = $this->db
            ->where('status', 'active')
            ->where('ends_at >=', date('Y-m-d H:i:s'))
            ->order_by('starts_at', 'ASC');
        if ($limit !== null) $query->limit((int) $limit);
        return $query->get($this->table)->result();
    }

    public function get_completed()
    {
        return $this->db
            ->where('status', 'active')
            ->where('ends_at <', date('Y-m-d H:i:s'))
            ->order_by('starts_at', 'DESC')
            ->get($this->table)
            ->result();
    }

    public function get_by_id($id)
    {
        return $this->db->where('id', (int) $id)->get($this->table)->row();
    }

    public function save(array $data, $id = null)
    {
        if ($id) {
            return $this->db->where('id', (int) $id)->where('status', 'active')->update($this->table, $data);
        }
        return $this->db->insert($this->table, $data);
    }

    public function cancel($id, $updated_by)
    {
        return $this->db
            ->where('id', (int) $id)
            ->where('status', 'active')
            ->update($this->table, ['status' => 'cancelled', 'updated_by' => $updated_by, 'updated_at' => date('Y-m-d H:i:s')]);
    }
}
