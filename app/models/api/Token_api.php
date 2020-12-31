<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Token_api extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
        $this->load->helper('cookie');
    }

    public function validate_token($token = ''){
        if(!empty($token)){
            $tk = $this->db->get_where('tokens',['token'=>$token]);

            if($tk->num_rows() > 0){
                if($this->is_passed_date($tk->row()->expire_at)){
                    return false;
                }
                else{
                    return true;
                }
            }
            else{
                return false;
            }
        }
        else return false;
    }

    public function set_token_session($token = ''){
        if(!empty($token)){
            $tk = $this->db->get_where('tokens',['token'=>$token]);
            if($tk->num_rows() > 0){
                //update session_details
                $this->db->where(['id'=>$tk->row()->id])->update('tokens',[
                    'session_details'=>json_encode($_SESSION),
                    'cookie_details'=>json_encode($_COOKIE),
                ]);
            }
        }
    }

    public function get_token_session($token = ''){
        if(!empty($token)){
            $tk = $this->db->get_where('tokens',['token'=>$token]);
            if($tk->num_rows() > 0){
                $tke = $tk->row();
                $_SESSION   = json_decode($tke->session_details , true);
                $_COOKIE    = json_decode($tke->cookie_details , true);
            }
        }
    }

    private function is_passed_date($date)
    {
        if(date('d-m-Y H:i') == date('d-m-Y H:i' , $date)){
            return false;
        }
        else
        {
            //same year
            if(date('Y') == date('Y',$date))
            {
                //same month
                if(date('m') == date('m',$date))
                {
                    // same date
                    if(date('d') == date('d',$date))
                    {
                        //same hour
                        if(date('H') == date('H',$date)){
                            //same minute
                            if(date('i') == date('i',$date)){
                                return false;
                            }
                            else{
                                if(date('i') > date('i',$date)){
                                    return true;
                                }else{return false;}
                            }
                        }
                        else{
                            if(date('H') > date('H',$date)){
                                return true;
                            }else{return false;}
                        }
                    }
                    else{
                        if(date('d') > date('d',$date)){
                            return true;
                        }else{return false;}
                    }
                }
                else
                {
                    //different_month
                    //passed month
                    if(date('m') > date('m',$date))
                    { return true;
                    } else{ return false; }
                }
            }

            //different year
            else{
                //passed year
                if(date('Y') > date('Y',$date)){
                    return true;
                }else return false;
            }
        }


    }
}
