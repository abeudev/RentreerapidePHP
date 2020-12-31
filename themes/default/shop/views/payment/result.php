<?php /** Created by PhpStorm. User: john Date: 8/31/2020 Time: 11:08 AM */?>
<?php $assets_url = base_url('themes/default/shop/assets/'); ?>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Paiment</title>
    <link href="<?= $assets; ?>preloader/preloader.css" rel="stylesheet">
    <link href="<?= $assets; ?>css/sweetalert2.css" rel="stylesheet">
    <script src="<?= $assets; ?>js/libs.min.js"></script>
    <script src="<?= $assets;?>js/extra/sweetalert2.all.js"></script>
</head>
<body>
<script>
    function set_flashdata(key , value){
        localStorage.setItem(key , value);
    }
    const status_id = Number('<?=$status_id;?>');
    const sale_id   = '<?=$sale_id;?>';
    $(document).ready(function () {
        var html = '';
        var icon  = '';
        switch(status_id){
            case '1': html = 'Paiement effectué avec succès' ;break;
            case '2': html = 'Vous ne disposez pas d’assez de fond pour effectuer le paiement'; icon = 'success'; break;
            case '3': html = 'Echec de l’opération veillez réessayer'; icon = 'error'; break;
            default : html = 'Echec de l’opération veillez réessayer'; icon= 'error' ;break;
        }
        Swal.fire({
            icon: icon,
            title : html,
            width: 600,
            allowOutsideClick:false,
            showCancelButton: false,
            showConfirmButton:true,
            confirmButtonText:'OK',
            cancelButtonText: 'Ok',
            reverseButtons: true,
            padding: '3em',
            backdrop: 'rgba(0,0,123,0.4)',
            showCloseButton:false,
            timer : 5000
        });

        setTimeout(function(){
            set_flashdata('payment_status_'+sale_id+'' , status_id);
            window.close();
        },2000);

    });
</script>
</body>
</html>

