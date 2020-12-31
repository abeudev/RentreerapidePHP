<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Com_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        if(empty($this->loggedIn)){
            $this->loggedIn = $this->loggedIn();
        }
    }

    private function loggedIn(){
        return !empty($_SESSION['user_id']) && !empty($_SESSION['email']);
    }

    public function insert_or_update($table = '' , $where = [] , $insert_data = [] , $update_data = []){
        $db_data = $this->db->get_where($table , $where);
        if($db_data->num_rows() > 0){
            $this->db->where($where)->update($table , $update_data);
        }
        else{
            $this->db->insert($table , $insert_data);
        }
    }

    public function insert_if_not_exist($table = '' , $where = [] , $insert_data = [] , $return_type = 'row'){
        $db_data = $this->db->get_where($table , $where);
        if($db_data->num_rows() > 0){
            return $db_data->$return_type();
        }
        else{
            $this->db->insert($table , $insert_data);
            return $this->db->get_where($table , $where)->$return_type();
        }
    }

    public function parseArray($std_class = [] , $key = '' , $value = ''){
        $array = [];
        if(!empty($std_class)){
            foreach ($std_class as $class){
                $array[$class->$key] = $class->$value;
            }
        }

        return $array;
    }

}