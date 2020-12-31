<?php
$cic = [];
foreach($product_categories_count as $cc){
    $cic["{$cc->name}"] = $cc->total_items;
}
?>

<?php include_once('index/css.php'); ?>

<?php include_once('index/slider.php'); ?>

<section class="page-contents">
    <div class="container" style="min-width: 95%">
        <div class="row">
            <h3 class="margin-top-no text-size-lg"> <?= lang('featured_products'); ?> </h3>
            <hr>
            <?php include_once('index/deals.php'); ?>
            <?php include_once('index/popular_products.php'); ?>
        </div>
    </div>

    <?php include_once('index/categories.php');?>

    <?php include_once('index/librairies.php');?>

    <div class="<?=($mobile==FALSE)?'container':'container';?>" style="<?=($mobile==FALSE)?'min-width: 95%;':'min-width:100% margin-bottom:30px';?>">
        <div id="result_container" class="featured-products list_prod">

        </div>
    </div>

    <div id="loading" style="position: relative !important;">
        <div class="wave">
            <div class="rect rect1"></div>
            <div class="rect rect2"></div>
            <div class="rect rect3"></div>
            <div class="rect rect4"></div>
            <div class="rect rect5"></div>
        </div>
    </div>

    <div class="col-md-6">
        <div id="pagination" class="pagination-right"></div>
    </div>
</section>


<?php include_once('index/scripts.php'); ?>