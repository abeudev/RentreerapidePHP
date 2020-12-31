<?php
function json_output($data){
    $res =  json_encode($data);
    $res = str_replace('"{','{',$res);
    $res = str_replace('}"','}',$res);
    $res = str_replace('\"',"'",$res);

    return $res;
}

print_r($response);
?>

