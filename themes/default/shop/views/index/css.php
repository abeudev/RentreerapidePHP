<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<link href="<?= $assets ?>shop_stuffs/plugins/OwlCarousel2-2.2.1/owl.carousel.css" rel="stylesheet"/>
<link href="<?= $assets ?>shop_stuffs/plugins/OwlCarousel2-2.2.1/owl.theme.default.css" rel="stylesheet"/>
<link href="<?= $assets ?>shop_stuffs/plugins/OwlCarousel2-2.2.1/animate.css" rel="stylesheet"/>
<link href="<?= $assets ?>shop_stuffs/plugins/slick-1.8.0/slick.css" rel="stylesheet"/>
<link href="<?= $assets ?>shop_stuffs/styles/responsive.css" rel="stylesheet"/>
<link href="<?= $assets ?>css/deal_style.css" rel="stylesheet"/>
<style>
    .list_prod .col-lg-1, .list_prod .col-lg-10, .list_prod .col-lg-11, .list_prod .col-lg-12, .list_prod .col-lg-2, .list_prod .col-lg-3, .list_prod .col-lg-4, .list_prod .col-lg-5, .list_prod .col-lg-6, .list_prod .col-lg-7, .list_prod .col-lg-8, .list_prod .col-lg-9, .list_prod .col-md-1, .list_prod .col-md-10, .list_prod .col-md-11, .list_prod .col-md-12, .list_prod .col-md-2, .list_prod .col-md-3, .list_prod .col-md-4, .list_prod .col-md-5, .list_prod .col-md-6, .list_prod .col-md-7, .list_prod .col-md-8, .list_prod .col-md-9, .list_prod .col-sm-1, .list_prod .col-sm-10, .list_prod .col-sm-11, .list_prod .col-sm-12, .list_prod .col-sm-2, .list_prod .col-sm-3, .list_prod .col-sm-4, .list_prod .col-sm-5, .list_prod .col-sm-6, .list_prod .col-sm-7, .list_prod .col-sm-8, .list_prod .col-sm-9, .list_prod .col-xs-1, .list_prod .col-xs-10, .list_prod .col-xs-11, .list_prod .col-xs-12, .list_prod .col-xs-2, .list_prod .col-xs-3, .list_prod .col-xs-4, .list_prod .col-xs-5, .list_prod .col-xs-6, .list_prod .col-xs-7, .list_prod .col-xs-8, .list_prod .col-xs-9{
        padding: <?=($mobile==FALSE)?'3px':'2px';?>
    }

    <?php if($mobile):;?>
    .col-lg-1, .col-lg-10, .col-lg-11, .col-lg-12, .col-lg-2, .col-lg-3, .col-lg-4, .col-lg-5, .col-lg-6, .col-lg-7, .col-lg-8, .col-lg-9, .col-md-1, .col-md-10, .col-md-11, .col-md-12, .col-md-2, .col-md-3, .col-md-4, .col-md-5, .col-md-6, .col-md-7, .col-md-8, .col-md-9, .col-sm-1, .col-sm-10, .col-sm-11, .col-sm-12, .col-sm-2, .col-sm-3, .col-sm-4, .col-sm-5, .col-sm-6, .col-sm-7, .col-sm-8, .col-sm-9, .col-xs-1, .col-xs-10, .col-xs-11, .col-xs-12, .col-xs-2, .col-xs-3, .col-xs-4, .col-xs-5, .col-xs-6, .col-xs-7, .col-xs-8, .col-xs-9{
        padding-left:5px !important;
        padding-right: 5px !important;
    }
    <?php endif;?>
</style>