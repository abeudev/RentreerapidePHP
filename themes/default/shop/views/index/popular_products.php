<div class="col-xs-12 <?=(!empty($week_deals))?'col-md-9':'col-md-12';?>">

    <?php  $r = 0; foreach(array_chunk($featured_products, 8) as $fps):?>
        <div class="">
            <div class="featured-products list_prod">
                <?php foreach($fps as $fp):?>
                    <div class="col-sm-6 col-xs-6 product-item <?=(empty($week_deals))?'col-md-2':'col-md-3';?>">
                        <div class="product" style="z-index: 1;">
                            <div class="details" style="transition: all 100ms ease-out 0s;">
                                <span class="badge badge-left blue <?=libName($fp->warehouse_name);?>"><?=$fp->warehouse_name;?></span>
                                <?php
                                if ($fp->promotion) {
                                    ?>
                                    <span class="badge badge-right green"><?=discount($fp->price , $fp->promo_price);?> %</span>
                                    <?php
                                } ?>
                                <?php if($mobile):;?>
                                    <a href="<?= site_url('product/' . $fp->slug); ?>">
                                        <img class="lazy" data-src="<?=productImage($fp->image);?>" alt="">
                                    </a>
                                <?php else :; ?>
                                    <img class="lazy" data-src="<?= productImage($fp->image); ?>" alt="">
                                <?php endif;?>
                                <?php if (!$shop_settings->hide_price) {
                                    ?>
                                    <div class="image_overlay"></div>
                                    <div class="btn add-to-cart" data-id="<?= $fp->id; ?>"><i class="fa fa-shopping-basket"></i> <?= lang('add_to_cart'); ?></div>
                                    <div class="btn compare-product" data-id="<?= $fp->id; ?>"><i class="fas fa-exchange-alt"></i> <?= lang('compare'); ?></div>
                                    <?php
                                } ?>
                                <div class="stats-container">
                                    <?php if (!$shop_settings->hide_price) {
                                        ?>
                                        <span class="product_price">
                                            <?php
                                            if ($fp->promotion) {
                                                echo '<del class="text-red">' . $this->sma->convertMoney(isset($fp->special_price) && !empty(isset($fp->special_price)) ? $fp->special_price : $fp->price) . '</del><br>';
                                                echo $this->sma->convertMoney($fp->promo_price);
                                            } else {
                                                echo $this->sma->convertMoney(isset($fp->special_price) && !empty(isset($fp->special_price)) ? $fp->special_price : $fp->price);
                                            } ?>
                                        </span>
                                        <?php
                                    } ?>
                                    <span class="product_name"> <a href="<?= site_url('product/' . $fp->slug); ?>"><?=limit_string($fp->name , 55); ?></a> </span>
                                    <a href="<?= site_url('category/' . $fp->category_slug); ?>" class="link dis-none"><?= $fp->category_name; ?></a>
                                    <div class="more dis-none">
                                        <hr class="simple-hr">
                                        <div data-toggle="tooltip" data-placement="top" title="Plus de details" class="col-xs-4 text-center">
                                            <a href="<?=base_url('product/'.$fp->slug);?>"><i class="fas fa-file-alt"></i></a>
                                        </div>

                                        <div data-toggle="tooltip" data-placement="top" title="Aperçu rapide" class="col-xs-4 text-center"> <a href="javascript:void(0)" class="quick-preview" data-id="<?= $fp->id; ?>"><i class="fas fa-eye"></i></a> </div>

                                        <div data-toggle="tooltip" data-placement="top" title="Ajouter aux shouhaits" class="col-xs-4 text-center">
                                            <a href="javascript:void(0)" class="add-to-wishlist" data-id="<?=$fp->id;?>"><i class="fas fa-heart"></i></a>
                                        </div>
                                    </div>
                                    <?php
                                    if ($fp->brand_name) {
                                        ?>
                                        <span class="link dis-none">-</span>
                                        <a href="<?= site_url('brand/' . $fp->brand_slug); ?>" class="link dis-none"><?= $fp->brand_name; ?></a>
                                        <?php
                                    } ?>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                        </div>
                    </div>
                <?php endforeach;?>
            </div>
        </div>
    <?php $r++; endforeach;?>
</div>