<?php

defined('BASEPATH') or exit('No direct script access allowed');

class School_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    public function addSchool($data)
    {
        if ($this->db->insert('schools', $data)) {
            return true;
        } else {
            return false;
        }
    }

    public function deleteSchool($id)
    {
        if ($this->db->delete('schools', ['id' => $id])) {
            return true;
        }
        return false;
    }

    public function getAllSchools()
    {
        $q = $this->db->get('schools');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }

            return $data;
        }
    }

    public function getSchoolByID($id)
    {
        $q = $this->db->get_where('schools', ['id' => $id], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }

        return false;
    }

    public function getSchoolByCode($code= '')
    {
        $q = $this->db->get_where('schools', ['code' => $code], 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }

        return false;
    }

    public function getSchools()
    {
        $date = date('Y-m-d H:i:s', time());
        $this->db->where('from_date <=', $date);
        $this->db->where('till_date >=', $date);
        if (!$this->Owner) {
            if ($this->Supplier) {
                $this->db->where('scope', 4);
            } elseif ($this->Customer) {
                $this->db->where('scope', 1)->or_where('scope', 3);
            } elseif (!$this->Customer && !$this->Supplier) {
                $this->db->where('scope', 2)->or_where('scope', 3);
            }
        }
        $q = $this->db->get('schools');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }

            return $data;
        }
    }

    public function updateSchool($id, $data)
    {
        $this->db->where('id', $id);
        if ($this->db->update('schools', $data)) {
            return true;
        } else {
            return false;
        }
    }
}

/* End of file pts_model.php */
/* Location: ./application/models/pts_types_model.php */
