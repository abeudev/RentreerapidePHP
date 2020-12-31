<?php
header("X-Frame-Options: ALLOW");
?>
<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php $assets_url = base_url('themes/default/shop/assets/'); ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <script type="text/javascript">if (parent.frames.length !== 0) { top.location = '<?= site_url(); ?>'; }</script>
    <title><?= $page_title; ?></title>
    <meta name="description" content="<?= $page_desc; ?>">
    <link rel="shortcut icon" href="<?= $assets; ?>images/icon.png">
    <link href="<?= $assets; ?>css/fontawesome-all.css" rel="stylesheet">
    <link href="<?= $assets; ?>css/libs2.min.css" rel="stylesheet">
    <link href="<?= $assets; ?>css/sweetalert2.css" rel="stylesheet">
    <link href="<?= $assets; ?>css/styles.min.css" rel="stylesheet">
    <link href="<?= $assets; ?>preloader/preloader.css" rel="stylesheet">
    <link href="<?= $assets; ?>css/jquery-ui.css" rel="stylesheet">
    <meta property="og:url" content="<?= isset($product) && !empty($product) ? site_url('product/' . $product->slug) : site_url(); ?>" />
    <meta property="og:type" content="website" />
    <meta property="og:title" content="<?= $page_title; ?>" />
    <meta property="og:description" content="<?= $page_desc; ?>" />
    <meta property="og:image" content="<?= isset($product) && !empty($product) ? productImage($product->image,false) : base_url('assets/uploads/logos/' . $shop_settings->logo); ?>" />
    <script src="<?= $assets; ?>js/libs.min.js"></script>
    <link rel="stylesheet" href="<?=$assets_url?>preloader/green_preloader.css">
    <?php include_once('style.php'); ?>

    <script>
        var base_url = '<?=base_url();?>';
        function init_lazy_load(){
            $('.lazy[data-src] , [data-src]').lazy({});
        }
    </script>
</head>
<body>
<div id="page_preloader" class="green_preloader"></div>
    <section id="wrapper" class="orange">
        <header>
            <!-- Top Header -->
            <section class="top-header">
                <div class="container">
                    <div class="row">
                        <div class="col-xs-12 topbar">
                            <?php if($mobile):;?>
                                <ul class="list-inline nav pull-left">
                                    <nav class="navbar navbar-default" role="navigation">
                                        <div class="navbar-header">
                                            <a href="##" type="button" style="font-size: 35px" class="fa fa-bars toggle_drawer navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-ex1-collapse"> </a>
                                            <a href="<?= site_url('cart'); ?>" class="btn-cart-xs visible-xs pull-right shopping-cart">
                                                <i class="fa fa-shopping-cart"></i> <span class="cart-total-items"></span>
                                            </a>
                                        </div>
                                        <div class="drawer_container">
                                            <div class="collapse navbar-collapse drawer" id="navbar-ex1-collapse">
                                                <ul class="nav navbar-nav">
                                                    <li>
                                                        <div class="logo" style="border-bottom: solid thin gainsboro;">
                                                            <?php if(!empty($this->session->userdata('opened_lib_logo'))):;?>
                                                                <a href="<?= site_url(); ?>">
                                                                    <img style="height:50px" alt="<?= $shop_settings->shop_name; ?>" src="<?=base_url('assets/uploads/company_logo/thumbs/'.$this->session->userdata('opened_lib_logo'));?>" class="img-responsive" />
                                                                </a>
                                                            <?php else :; ?>
                                                                <a href="<?= site_url(); ?>">
                                                                    <img alt="<?= $shop_settings->shop_name; ?>" src="<?= base_url('assets/uploads/logos/' . $shop_settings->logo); ?>" class="img-responsive" />
                                                                </a>
                                                            <?php endif;?>

                                                        </div>
                                                    </li>

                                                    <li>
                                                        <a href="<?=site_url('library/all');?>">
                                                            <i class="fas fa-sign-out-alt"></i> Sortir
                                                        </a>
                                                    </li>

                                                    <li class="<?= $m == 'main' && $v == 'index' ? 'active' : ''; ?>">
                                                        <a href="<?= base_url(); ?>"><i class="fa fa-home"></i> <?= lang('home'); ?></a>
                                                    </li>


                                                    <?php if ($isPromo) {
                                                        ?>
                                                        <li class="<?= $m == 'shop' && $v == 'products' && $this->input->get('promo') == 'yes' ? 'active' : ''; ?>"><a href="<?= shop_url('products?promo=yes'); ?>"><i class="fa fa-gift"></i> <?= lang('promotions'); ?></a></li>
                                                        <?php
                                                    } ?>
                                                    <li class="<?= $m == 'shop' && $v == 'products' && $this->input->get('promo') != 'yes' ? 'active' : ''; ?>"><a href="<?= shop_url('products'); ?>"> <i class="fa fa-cubes"></i> <?= lang('products'); ?></a></li>


                                                    <li class="dropdown">
                                                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                                                            <i class="fa fa-align-left"></i> <?= lang('categories'); ?> <span class="fa fa-angle-right pull-right"></span>
                                                        </a>
                                                        <ul class="dropdown-menu">
                                                            <?php
                                                            foreach ($categories as $pc) {
                                                                echo '<li class="' . ($pc->subcategories ? 'dropdown dropdown-submenu' : '') . '">';
                                                                echo '<a ' . ($pc->subcategories ? 'class="dropdown-toggle" data-toggle="dropdown"' : '') . ' href="' . site_url('category/' . $pc->slug) . '">' . $pc->name . '</a>';
                                                                if ($pc->subcategories) {
                                                                    echo '<ul class="dropdown-menu">';
                                                                    foreach ($pc->subcategories as $sc) {
                                                                        echo '<li><a href="' . site_url('category/' . $pc->slug . '/' . $sc->slug) . '">' . $sc->name . '</a></li>';
                                                                    }
                                                                    echo '<li class="divider"></li>';
                                                                    echo '<li><a href="' . site_url('category/' . $pc->slug) . '">' . lang('all_products') . '</a></li>';
                                                                    echo '</ul>';
                                                                }
                                                                echo '</li>';
                                                            }
                                                            ?>
                                                        </ul>
                                                    </li>

                                                    <li class="dropdown<?= (count($brands) > 20) ? ' mega-menu' : ''; ?>">
                                                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                                                            <i class="fa fa-bookmark"></i> <?= lang('brands'); ?> <span class="fa fa-angle-right pull-right"></span>
                                                        </a>
                                                        <?php
                                                        if (count($brands) <= 10) {
                                                            ?>
                                                            <ul class="dropdown-menu">
                                                                <?php
                                                                foreach ($brands as $brand) {
                                                                    echo '<li><a href="' . site_url('brand/' . $brand->slug) . '" class="line-height-lg">' . $brand->name . '</a></li>';
                                                                } ?>
                                                            </ul>
                                                            <?php
                                                        } elseif (count($brands) <= 20) {
                                                            ?>
                                                            <div class="dropdown-menu dropdown-menu-2x">
                                                                <div class="dropdown-menu-content">
                                                                    <?php
                                                                    $brands_chunks = array_chunk($brands, 10);
                                                                    foreach ($brands_chunks as $brands) {
                                                                        ?>
                                                                        <div class="col-xs-6 padding-x-no line-height-md">
                                                                            <ul class="nav">
                                                                                <?php
                                                                                foreach ($brands as $brand) {
                                                                                    echo '<li><a href="' . site_url('brand/' . $brand->slug) . '" class="line-height-lg">' . $brand->name . '</a></li>';
                                                                                } ?>
                                                                            </ul>
                                                                        </div>
                                                                        <?php
                                                                    } ?>
                                                                </div>
                                                            </div>
                                                            <?php
                                                        } elseif (count($brands) > 20) {
                                                            ?>
                                                            <ul class="dropdown-menu">
                                                                <li>
                                                                    <div class="mega-menu-content">
                                                                        <div class="row">
                                                                            <?php
                                                                            $brands_chunks = array_chunk($brands, ceil(count($brands) / 4));
                                                                            foreach ($brands_chunks as $brands) {
                                                                                ?>
                                                                                <div class="col-sm-3">
                                                                                    <ul class="list-unstyled">
                                                                                        <?php
                                                                                        foreach ($brands as $brand) {
                                                                                            echo '<li><a href="' . site_url('brand/' . $brand->slug) . '" class="line-height-lg">' . $brand->name . '</a></li>';
                                                                                        } ?>
                                                                                    </ul>
                                                                                </div>
                                                                                <?php
                                                                            } ?>
                                                                        </div>
                                                                    </div>
                                                                </li>
                                                            </ul>
                                                            <?php
                                                        }
                                                        ?>
                                                    </li>


                                                    <?php if (!$shop_settings->hide_price) {
                                                        ?>
                                                        <li class="<?= $m == 'cart_ajax' && $v == 'index' ? 'active' : ''; ?>"> <a href="<?= site_url('cart'); ?>"><i class="fa fa-shopping-cart"></i> <?= lang('shopping_cart'); ?></a></li>
                                                        <li class="<?= $m == 'cart_ajax' && $v == 'checout' ? 'active' : ''; ?>"><a href="<?= site_url('cart/checkout'); ?>"> <i class="fa fa-money"></i> <?= lang('checkout'); ?></a></li>
                                                        <?php
                                                    } ?>
                                                    <li class="<?= $m == 'shop' && $v == 'products' && $this->input->get('promo') != 'yes' ? 'active' : ''; ?>"><a href="<?= shop_url('products'); ?>"> <i class="fa fa-cubes"></i> <?= lang('products'); ?></a></li>

                                                    <li class="<?= $m == 'shop' && $v == 'schools' ? 'active' : ''; ?>"><a href="<?= shop_url('schools'); ?>"><i class="fa fa-certificate"></i><?= lang('schools'); ?></a></li>

                                                    <li class="<?= $m == 'shop' && $v == 'suppliers' ? 'active' : ''; ?>"><a href="<?= shop_url('suppliers'); ?>"><i class="fa fa-book"></i><?= lang('companies'); ?></a></li>


                                                </ul>
                                            </div>
                                        </div>
                                    </nav>
                                </ul>
                            <?php endif;?>

                        <?php
                        if (!empty($pages)) {
                            echo '<ul class="list-inline nav pull-left hidden-xs">';
                            foreach ($pages as $page) {
                                echo '<li><a href="' . site_url('page/' . $page->slug) . '">' . $page->name . '</a></li>';
                            }
                            echo '</ul>';
                        }
                        ?>

                            <ul class="list-inline nav pull-right">
                                <?php if(!empty($this->session->userdata('opened_lib'))):;?>
                                    <li class="hidden-xs hidden-sm">
                                        <a href="<?=site_url('library/'.$this->session->userdata('opened_lib'));?>" class="">
                                            <?php if(!empty($this->session->userdata('opened_lib_logo'))):;?>
                                                <img width="26px" src="<?=base_url('assets/uploads/company_logo/thumbs/'.$this->session->userdata('opened_lib_logo'));?>" alt="">
                                            <?php endif;?>
                                            <?=strtoupper($this->session->userdata('opened_lib_name'));?>
                                        </a>
                                    </li>
                                <?php endif;?>
                                <?= $loggedIn && $Staff ? '<li class="hidden-xs"><a href="' . admin_url() . '"><i class="fa fa-dashboard"></i> ' . lang('admin_area') . '</a></li>' : ''; ?>
                                <li class="dropdown dis-none">
                                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                                    <img src="<?= base_url('assets/images/' . $Settings->user_language . '.png'); ?>" alt="">
                                    <span class="hidden-xs">&nbsp;&nbsp;<?= ucwords(lang($Settings->user_language)); ?></span>
                                 </a>
                                 <ul class="dropdown-menu dropdown-menu-right">
                                    <?php $scanned_lang_dir = array_map(function ($path) {
                                    return basename($path);
                                }, glob(APPPATH . 'language/english', GLOB_ONLYDIR));
                                    foreach ($scanned_lang_dir as $entry) {
                                        if (file_exists(APPPATH . 'language' . DIRECTORY_SEPARATOR . $entry . DIRECTORY_SEPARATOR . 'shop' . DIRECTORY_SEPARATOR . 'shop_lang.php')) {
                                            ?>
                                    <li>
                                        <a href="<?= site_url('main/language/' . $entry); ?>">
                                            <img src="<?= base_url('assets/images/' . $entry . '.png'); ?>" class="language-img">
                                            &nbsp;&nbsp;<?= ucwords($entry); ?>
                                        </a>
                                    </li>
                                    <?php
                                        }
                                    } ?>
                                </ul>
                            </li>
                            <?php if (!$shop_settings->hide_price && !empty($currencies)) {
                                        ?>
                            <li class="dropdown dis-none">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                                    <?= $selected_currency->symbol . ' ' . $selected_currency->code; ?>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-right">
                                    <?php
                                    foreach ($currencies as $currency) {
                                        echo '<li><a href="' . site_url('main/currency/' . $currency->code) . '">' . $currency->symbol . ' ' . $currency->code . '</a></li>';
                                    } ?>
                                </ul>
                            </li>
                            <?php
                                    } ?>
                                <?php
                                if ($loggedIn) {
                                    ?>
                                    <?php if (!$shop_settings->hide_price) {
                                        ?>
                                    <li class="hidden-xs"><a href="<?= shop_url('wishlist'); ?>"><i class="fa fa-heart"></i> <?= lang('wishlist'); ?> (<span id="total-wishlist"><?= $wishlist; ?></span>)</a></li>
                                    <?php
                                    } ?>
                                    <li class="dropdown">
                                        <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                                            <?= lang('hi') . ' ' . $loggedInUser->first_name; ?> <span class="caret"></span>
                                        </a>
                                        <ul class="dropdown-menu dropdown-menu-right">
                                            <li class=""><a href="<?= site_url('profile'); ?>"><i class="mi fa fa-user"></i> <?= lang('profile'); ?></a></li>
                                            <li class=""><a href="<?= shop_url('orders'); ?>"><i class="mi fas fa-box-open"></i> <?= lang('orders'); ?></a></li>
                                            <li class=""><a href="<?= shop_url('wishlist'); ?>"><i class="mi fa fa-heart"></i> <?= lang('wishlist'); ?></a></li>
                                            <li class=""><a href="<?= shop_url('quotes'); ?>"><i class="mi fa fa-file-text-o"></i> <?= lang('quotes'); ?></a></li>
                                            <li class=""><a href="<?= shop_url('downloads'); ?>"><i class="mi fa fa-download"></i> <?= lang('downloads'); ?></a></li>
                                            <li class=""><a href="<?= shop_url('addresses'); ?>"><i class="mi fa fa-building"></i> <?= lang('addresses'); ?></a></li>
                                            <li class="divider"></li>
                                            <li class=""><a href="<?= site_url('logout'); ?>"><i class="mi fas fa-sign-out-alt"></i> <?= lang('logout'); ?></a></li>
                                        </ul>
                                    </li>
                                    <?php
                                } else {
                                    ?>
                                    <li>
                                        <div class="dropdown">
                                            <button class="btn dropdown-toggle" type="button" id="dropdownLogin" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                                <i class="fa fa-user-circle-o"></i> <?= lang('login'); ?> <span class="caret"></span>
                                            </button>
                                            <div class="dropdown-menu dropdown-menu-right dropdown-menu-login" aria-labelledby="dropdownLogin" data-dropdown-in="zoomIn" data-dropdown-out="fadeOut">
                                                <?php  include FCPATH . 'themes' . DIRECTORY_SEPARATOR . $Settings->theme . DIRECTORY_SEPARATOR . 'shop' . DIRECTORY_SEPARATOR . 'views' . DIRECTORY_SEPARATOR . 'user' . DIRECTORY_SEPARATOR . 'login_form.php'; ?>
                                            </div>
                                        </div>
                                    </li>
                                    <?php
                                }
                                ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </section>
            <!-- End Top Header -->

            <!-- Main Header -->
            <section class="main-header">
                <div class="container padding-y-md" style="<?=($mobile==TRUE)?'margin-top:20px':'';?> min-width: 98%">
                    <div class="row">

                        <?php if(!$mobile):;?>
                            <div class="col-sm-4 logo">
                                <div class="display-inline-block" style="vertical-align: middle">
                                    <?php if(!empty($this->session->userdata('opened_lib_logo'))):;?>
                                        <a href="<?= site_url(); ?>">
                                            <img style="height:64px" alt="<?= $shop_settings->shop_name; ?>" src="<?=base_url('assets/uploads/company_logo/thumbs/'.$this->session->userdata('opened_lib_logo'));?>" class="img-responsive" />
                                        </a>
                                    <?php else :; ?>
                                        <a href="<?= site_url(); ?>">
                                            <img alt="<?= $shop_settings->shop_name; ?>" src="<?= base_url('assets/uploads/logos/' . $shop_settings->logo); ?>" class="img-responsive" />
                                        </a>
                                    <?php endif;?>
                                </div>

                                <div class="display-inline-block" style="vertical-align: middle">
                                    <?php if($this->session->userdata('opened_lib_logo')):;
                                        $suppliers = $this->db->select('warehouses.id as id , companies.id as company_id , companies.name , companies.logo')
                                            ->where(['group_name'=>'supplier'])
                                            ->join('warehouses','warehouses.email = companies.email')
                                            ->order_by('warehouses.ordering_id','ASC')->get('companies')->result();
                                    ?>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-theme dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <?=limit_string($this->session->userdata('opened_lib_name') , 17);?>
                                            </button>
                                            <div class="dropdown-menu">
                                                <?php foreach($suppliers as $supplier):?>
                                                    <li><a class="dropdown-item" href="<?=site_url('library/' . $supplier->id);?>">
                                                            <?php if(libName($this->session->userdata('opened_lib_name')) != libName($supplier->name)):;?>
                                                                <?= limit_string(str_replace('LIBRAIRIE','',$supplier->name) , 20);?>
                                                            <?php endif;?>
                                                        </a></li>
                                                <?php endforeach;?>

                                            </div>
                                        </div>
                                        <a href="<?=site_url('library/all');?>" class="btn-theme btn" data-toggle="tooltip" data-placement="top" title="Sortir">
                                            <i class="fas fa-sign-out-alt"></i>
                                        </a>

                                    <?php endif;?>
                                </div>

                            </div>
                        <?php endif;?>

                        <div class="col-sm-8 margin-top-lg">
                            <div class="<?=($mobile==TRUE)?'container m-t-30':'row';?>">
                                <div class="<?= (!$shop_settings->hide_price) ? 'col-sm-8 col-md-6 col-md-offset-3' : 'col-md-6 col-md-offset-6'; ?> search-box">
                                    <?= shop_form_open('products', 'id="product-search-form"'); ?>
                                    <div class="input-group">
                                        <input name="query" type="text" class="form-control" id="product-search" aria-label="Search..." placeholder="Rechercher...">
                                        <div class="input-group-btn">
                                            <button type="submit" class="btn btn-default btn-search">
                                                <?php if(!empty($this->session->userdata('opened_lib'))):;?>
                                                    <?=$sup_settings->search_button_content;?>
                                                    <?php else :; ?>
                                                    <?=$default_style->search_button_content;?>
                                                <?php endif;?>
                                            </button>
                                        </div>
                                    </div>
                                    <?= form_close(); ?>
                                </div>

                                <?php if (!$shop_settings->hide_price) {
                                    ?>
                                <div class="col-sm-4 col-md-3 cart-btn hidden-xs">
                                    <button type="button" class="btn btn-theme btn-block dropdown-toggle shopping-cart" id="dropdown-cart" data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                        <i class="fa fa-shopping-cart margin-right-md"></i>
                                        <span class="cart-total-items"></span>
                                        <!-- <i class="fa fa-caret-down margin-left-md"></i> -->
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdown-cart">
                                        <div id="cart-contents">
                                            <table class="table table-condensed table-striped table-cart" id="cart-items"></table>
                                            <div id="cart-links" class="text-center margin-bottom-md">
                                                <div class="btn-group btn-group-justified" role="group" aria-label="View Cart and Checkout Button">
                                                    <div class="btn-group">
                                                        <a class="btn btn-default btn-sm" href="<?= site_url('cart'); ?>"><i class="fa fa-shopping-cart"></i> <?= lang('view_cart'); ?></a>
                                                    </div>
                                                    <div class="btn-group">
                                                        <a class="btn btn-default btn-sm" href="<?= site_url('cart/checkout'); ?>"><i class="fa fa-check"></i> <?= lang('checkout'); ?></a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div id="cart-empty"><?= lang('please_add_item_to_cart'); ?></div>
                                    </div>
                                </div>
                                <?php
                                } ?>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <!-- End Main Header -->

            <!-- Nav Bar -->
            <?php if(!$mobile):;?>
                <nav class="navbar navbar-default" role="navigation">
                    <div class="container">
                        <div class="navbar-header">
                            <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-ex1-collapse">
                                <?= lang('navigation'); ?>
                            </button>
                            <a href="<?= site_url('cart'); ?>" class="btn btn-default btn-cart-xs visible-xs pull-right shopping-cart">
                                <i class="fa fa-shopping-cart"></i> <span class="cart-total-items"></span>
                            </a>
                        </div>
                        <div class="collapse navbar-collapse" id="navbar-ex1-collapse">
                            <ul class="nav navbar-nav">
                                <li class="<?= $m == 'main' && $v == 'index' ? 'active' : ''; ?>"><a href="<?= base_url(); ?>"><?= lang('home'); ?></a></li>
                                <?php if ($isPromo) {
                                    ?>
                                    <li class="<?= $m == 'shop' && $v == 'products' && $this->input->get('promo') == 'yes' ? 'active' : ''; ?>"><a href="<?= shop_url('products?promo=yes'); ?>"><?= lang('promotions'); ?></a></li>
                                    <?php
                                } ?>
                                <li class="<?= $m == 'shop' && $v == 'products' && $this->input->get('promo') != 'yes' ? 'active' : ''; ?>"><a href="<?= shop_url('products'); ?>"><?= lang('products'); ?></a></li>

                                <li class="dropdown">
                                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                                        <?= lang('categories'); ?> <span class="caret"></span>
                                    </a>
                                    <ul class="dropdown-menu">
                                        <?php
                                        foreach ($categories as $pc) {
                                            echo '<li class="' . ($pc->subcategories ? 'dropdown dropdown-submenu' : '') . '">';
                                            echo '<a ' . ($pc->subcategories ? 'class="dropdown-toggle" data-toggle="dropdown"' : '') . ' href="' . site_url('category/' . $pc->slug) . '">' . $pc->name . '</a>';
                                            if ($pc->subcategories) {
                                                echo '<ul class="dropdown-menu">';
                                                foreach ($pc->subcategories as $sc) {
                                                    echo '<li><a href="' . site_url('category/' . $pc->slug . '/' . $sc->slug) . '">' . $sc->name . '</a></li>';
                                                }
                                                echo '<li class="divider"></li>';
                                                echo '<li><a href="' . site_url('category/' . $pc->slug) . '">' . lang('all_products') . '</a></li>';
                                                echo '</ul>';
                                            }
                                            echo '</li>';
                                        }
                                        ?>
                                    </ul>
                                </li>

                                <li class="dropdown<?= (count($brands) > 20) ? ' mega-menu' : ''; ?>">
                                    <a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">
                                        <?= lang('brands'); ?> <span class="caret"></span>
                                    </a>
                                    <?php
                                    if (count($brands) <= 10) {
                                        ?>
                                        <ul class="dropdown-menu">
                                            <?php
                                            foreach ($brands as $brand) {
                                                echo '<li><a href="' . site_url('brand/' . $brand->slug) . '" class="line-height-lg">' . $brand->name . '</a></li>';
                                            } ?>
                                        </ul>
                                        <?php
                                    } elseif (count($brands) <= 20) {
                                        ?>
                                        <div class="dropdown-menu dropdown-menu-2x">
                                            <div class="dropdown-menu-content">
                                                <?php
                                                $brands_chunks = array_chunk($brands, 10);
                                                foreach ($brands_chunks as $brands) {
                                                    ?>
                                                    <div class="col-xs-6 padding-x-no line-height-md">
                                                        <ul class="nav">
                                                            <?php
                                                            foreach ($brands as $brand) {
                                                                echo '<li><a href="' . site_url('brand/' . $brand->slug) . '" class="line-height-lg">' . $brand->name . '</a></li>';
                                                            } ?>
                                                        </ul>
                                                    </div>
                                                    <?php
                                                } ?>
                                            </div>
                                        </div>
                                        <?php
                                    } elseif (count($brands) > 20) {
                                        ?>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <div class="mega-menu-content">
                                                    <div class="row">
                                                        <?php
                                                        $brands_chunks = array_chunk($brands, ceil(count($brands) / 4));
                                                        foreach ($brands_chunks as $brands) {
                                                            ?>
                                                            <div class="col-sm-3">
                                                                <ul class="list-unstyled">
                                                                    <?php
                                                                    foreach ($brands as $brand) {
                                                                        echo '<li><a href="' . site_url('brand/' . $brand->slug) . '" class="line-height-lg">' . $brand->name . '</a></li>';
                                                                    } ?>
                                                                </ul>
                                                            </div>
                                                            <?php
                                                        } ?>
                                                    </div>
                                                </div>
                                            </li>
                                        </ul>
                                        <?php
                                    }
                                    ?>
                                </li>

                                <?php if (!$shop_settings->hide_price) {
                                    ?>
                                    <li class="<?= $m == 'cart_ajax' && $v == 'index' ? 'active' : ''; ?>"><a href="<?= site_url('cart'); ?>"><?= lang('shopping_cart'); ?></a></li>
                                    <li class="<?= $m == 'cart_ajax' && $v == 'checout' ? 'active' : ''; ?>"><a href="<?= site_url('cart/checkout'); ?>"><?= lang('checkout'); ?></a></li>
                                    <?php
                                } ?>

                                <li class="<?= $m == 'shop' && $v == 'schools' ? 'active' : ''; ?>"><a href="<?= shop_url('schools'); ?>"><?= lang('schools'); ?></a></li>

                                <li class="<?= $m == 'shop' && $v == 'suppliers' ? 'active' : ''; ?>"><a href="<?= shop_url('suppliers'); ?>"><?= lang('companies'); ?></a></li>

                            </ul>
                        </div>
                    </div>
                </nav>
            <?php endif;?>
            <!-- End Nav Bar -->
        </header>
        <?php
//            var_dump($colors);
        ?>


