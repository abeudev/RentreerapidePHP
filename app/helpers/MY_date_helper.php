<?php
if(!function_exists('is_passed_date'))
{
    function is_passed_date($date)
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

if(!function_exists('limit_string'))
{
    function limit_string($string,$max_character = 10)
    {
        $str = '';
        if(!empty($string) and !empty ($max_character))
        {
            for($i = 0;$i<$max_character;$i++)
            {
                if(!empty($string[$i]))
                {
                    $str.=$string[$i];
                }
            }

            if(strlen($string) > $max_character)
            {
                return $str.'...';
            }
            else
            {
                return $str;
            }

        }
    }
}