<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>redirect</title>
</head>
<body>
    <h1>vous alez etre rediriger dans <span id="seconds">5</span> seconde</h1>
    <?php
        if(empty($this->session->userdata('user_id'))){
            $session_data = $this->db->get_where('transaction_session',['sale_id'=>$sale_id]);
            if($session_data->num_rows() > 0){
                $session_data = $session_data->row();
                //RESTORE SESSION
                $_SESSION = json_decode($session_data->session_data , true);
            }
        }
    ?>
    <script>
        var seconds = 5;
        var t = setInterval(function () {
            seconds--;
          document.getElementById('seconds').innerText = ''+seconds+'';
            if(seconds===0){
                clearInterval(t);
                location.href='<?=base_url('shop/orders');?>';
            }
        },1000)
    </script>
</body>
</html>