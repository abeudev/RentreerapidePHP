<style>
<?php
    if(!empty($this->session->userdata('opened_lib'))){
        $colors         = $this->db->get_where('supplier_color_settings',['warehouse_id'=>$this->session->userdata('opened_lib')])->row();
        $sup_settings   = $this->db->get_where('supplier_settings',['warehouse_id'=>$this->session->userdata('opened_lib')])->row();
        $font           = $this->db->get_where('fonts',['id'=>$sup_settings->font_id])->row();
        $font_sizes     = $this->com_model->parseArray($this->db->get_where('supplier_font_size',['warehouse_id'=>$this->session->userdata('opened_lib')])->result(),'font_id' , 'font_size');
    }
    else{
        $colors         = $default_colors;
        $sup_settings   = $default_style;
        $font           = $default_font;
        $font_sizes     = $default_font_size;
    }

    $banner_settings = $this->db->select('companies.name , library_name_bg,library_name')
                                    ->join('warehouses','warehouses.email = companies.email')
                                    ->join('supplier_color_settings','supplier_color_settings.warehouse_id = warehouses.id')
                                    ->get('companies')->result();
?>
<?php if(!empty($sup_settings)):;?>
/*IMPORT SUPPLIER FONT*/
/*=====================================================================================================================*/
    <?=$font->url;?>
/*@import url('https://fonts.googleapis.com/css2?family=Baloo+2:wght@400;500&display=swap');*/
body, P, div, h1, h2, h3{
        font-family: <?=$font->code;?> !important;
        font-size: <?=(!empty($font_sizes[$font->id]))?$font_sizes[$font->id] : $font->font_size;?>px !important;
    }
/*=====================================================================================================================*/

/*AJUST FONT SIZE FOR SMALLER FONTS*/
/*=====================================================================================================================*/
    .featured-products .stats-container .product_name a, .featured-products .stats-container .product_price a, .product_name, .product_price, .product-name
    {
        <?php //FONT SIZE AJUSTMENT
        $small_fonts = [ 'Indie Flower' ];
        if(in_array($font->name , $small_fonts)):;?>
            font-size: medium !important;
        <?php else :; ?>
            font-size: small !important;
        <?php endif;?>
    }
/*=====================================================================================================================*/
/*SET BUTTONS AND SEARCH BAR BORDER-RADIUS AND WIDTH*/
/*=====================================================================================================================*/
    .btn , .btn-theme{
        border-radius: <?=$sup_settings->button_border_radius;?>px !important;
    }
    #product-search{
        border-radius: <?=$sup_settings->search_border_radius;?>px 0px 0px <?=$sup_settings->search_border_radius;?>px !important;
    }
    .btn-search{
        border-radius: 0px <?=$sup_settings->search_border_radius;?>px <?=$sup_settings->search_border_radius;?>px 0px !important;
        width: <?=$sup_settings->search_button_width;?>px;
    }
<?php endif;?>
    /*STYLE SPECIFIQUE AUX LIBRAIRIES*/
<?php if(!empty($colors)) : ?>
/*====TOP-HEADER COLORS===*/
/*BG*/
<?php if(!$mobile):;?>
.top-header ,.orange .main-header .btn-search, .orange .navbar .navbar-nav>.active>a, .orange .navbar .navbar-nav>.active>a:active, .orange .navbar .navbar-nav>.active>a:focus, .orange .navbar .navbar-nav>.active>a:hover, .orange .navbar .navbar-nav>.open>a, .orange .navbar .navbar-nav>.open>a:active, .orange .navbar .navbar-nav>.open>a:focus, .orange .navbar .navbar-nav>.open>a:hover, .orange .navbar .navbar-nav>li>a:active, .orange .navbar .navbar-nav>li>a:focus, .orange .navbar .navbar-nav>li>a:hover {
    background-color: <?=$colors->theme;?> !important;
    color: <?=$colors->theme_text;?> !important;
    border-color: <?=$colors->theme;?> !important;
}
<?php endif;?>
/*TEXT*/
.top-header a{color: <?=$colors->theme_text;?> !important;}
/*====NAVIGATIONS AND BUTTONS====*/
/*simple*/
.btn-theme.btn  , .btn-theme {
    background-color: <?=$colors->button_bg;?> !important;
    border-color: <?=$colors->button_bg;?> !important;
    color : <?=$colors->button_text;?> !important;
}
/*on hover ,focus , active*/
.btn-theme:hover ,.orange .top-header ul.list-inline>li .dropdown-toggle:active, .orange .top-header ul.list-inline>li .dropdown-toggle:focus, .orange .top-header ul.list-inline>li .dropdown-toggle:hover, .orange .top-header ul.list-inline>li>a:active, .orange .top-header ul.list-inline>li>a:focus, .orange .top-header ul.list-inline>li>a:hover {
    background-color: <?=$colors->button_bg_hover;?> !important;
    color: <?=$colors->button_text_hover;?> !important;
    border-color: <?=$colors->button_bg_hover;?> !important;
}
/*===NAVIGATION MENU ===*/
nav.navbar{background-color: <?=$colors->navbar_bg;?> !important;}
/*==CATEGORY-LISTING==*/
.category_item{color : <?=$colors->category_list;?> !important;}
/*====PRODUCTS=======*/
/*BG*/
.product ,  .featured-products .stats-container{
    background-color: <?=$colors->product_bg;?>;
    border: 1px solid <?=$colors->product_bg;?>;
}
/*NAME-TEXT*/
.featured-products .stats-container .product_name a, .featured-products .stats-container .product_price a , .product_name, .product_price, .product-price, .product-name{
    color: <?=$colors->product_name;?> !important;
}
/*PRICE-TEXT*/
.product_price , .orange .featured-products .product .btn:hover, .orange .featured-products .product .stats-container .product_price{
    color : <?=$colors->product_price;?> !important;
}
/*CATEGORY*/
.stats-container .link{
    color : <?=$colors->product_category;?> !important;
}
/*LIBRARY-NAME*/
    <?php foreach($banner_settings as $bn):?>
        .featured-products .product .badge.<?=libName($bn->name);?> , .product .badge.<?=libName($bn->name);?>{
            background-color: <?=$bn->library_name_bg;?> !important;
            color: <?=$bn->library_name;?> !important;
        }
    <?php endforeach;?>

.main-header{
    background-color: <?=$colors->main_header;?> !important;
}
<?php endif;?>
.slider-container{
    min-height:<?=($mobile==FALSE)?'28em':'';?>;
}
.orange input[type=checkbox]:checked+span::before, .orange input[type=radio]:checked+span::before{

}
</style>
<link href="<?= $assets ?>css/custom_style.css" rel="stylesheet"/>
<?php if($mobile):;?>
    <link href="<?= $assets ?>css/custom_mobile_style.css" rel="stylesheet"/>
<?php endif;?>