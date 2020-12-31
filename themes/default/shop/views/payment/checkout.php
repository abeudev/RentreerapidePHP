<?php /** Created by PhpStorm. User: john Date: 8/31/2020 Time: 11:08 AM */?>
<?php $assets_url = base_url('themes/default/shop/assets/'); ?>
<link href="<?= $assets; ?>preloader/preloader.css" rel="stylesheet">
<script src="<?= $assets; ?>js/libs.min.js"></script>
<div id="page_preloader" class="green_preloader"></div>
<form action="<?=$action;?>" method="post" id="checkout_form">
    <input type="hidden" name="currency" value="<?=$currency;?>">
    <input type="hidden" name="name" value="<?=$name;?>" >
    <input type="hidden" name="operation_token" value="<?=$operation_token;?>" >
    <input type="hidden" name="order" value="<?=$order;?>" class="form-control">
    <input type="hidden" name="transaction_amount" value="<?=$transaction_amount;?>" >
    <input type="hidden" name="jwt" value="<?=$jwt;?>">
    <input type="submit" value="send" style="display: none;">
</form>
<script>
    $(document).ready(function () {
        $('#checkout_form').submit();
    });
</script>
