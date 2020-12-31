<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
    $available_quantity = $product->quantity - $product->alert_quantity;
?>
<section class="page-contents">
    <div class="container">
        <div class="row">
            <div class="col-xs-12">

                <div class="row">
                    <div class="col-sm-9 col-md-10">

                        <div class="panel panel-default margin-top-lg">
                            <div class="panel-heading text-bold">
                                <i class="fa fa-list-alt margin-right-sm"></i> <?= $product->name . ' (' . $product->warehouse_name . ')'; ?>
                                <a href="<?= shop_url('products'); ?>" class="pull-right"><i class="fa fa-share"></i> <?= lang('products'); ?></a>
                            </div>
                            <div class="panel-body mprint">

                                <div class="row">
                                    <div class="col-sm-5">

                                        <div class="photo-slider">
                                            <div class="carousel slide article-slide" id="photo-carousel">

                                                <div class="carousel-inner cont-slider">
                                                    <div class="item active">
                                                        <a href="#" data-toggle="modal" data-target="#lightbox">
                                                            <img data-src="<?= productImage($product->image,false) ?>" alt="<?= $product->name ?>" class="img-responsive img-thumbnail lazy"/>
                                                        </a>
                                                    </div>
                                                    <?php
                                                    if (!empty($images)) {
                                                        foreach ($images as $ph) {
                                                            echo '<div class="item"><a href="#" data-toggle="modal" data-target="#lightbox"><img class="img-responsive img-thumbnail lazy" data-src="' . productImage($ph->photo) . '" alt="' . $ph->photo . '" /></a></div>';
                                                        }
                                                    }
                                                    ?>
                                                </div>

                                                <ol class="carousel-indicators">
                                                    <li class="active" data-slide-to="0" data-target="#photo-carousel">
                                                        <img class="img-thumbnail lazy" alt="" data-src="<?= productImage($product->image);?>">
                                                    </li>
                                                    <?php
                                                    $r = 1;
                                                    if (!empty($images)) {
                                                        foreach ($images as $ph) {
                                                            echo '<li class="" data-slide-to="' . $r . '" data-target="#photo-carousel"><img class="img-thumbnail" alt="" src="' . productImage($ph->photo) . '"></li>';
                                                            $r++;
                                                        }
                                                    }
                                                    ?>

                                                </ol>
                                            </div>
                                            <div class="clearfix"></div>
                                        </div>

                                        <?php if (!$shop_settings->hide_price) {
                                                        ?>
                                        <?php if (($product->type != 'standard' || $available_quantity > 0) || $Settings->overselling) {
                                                            ?>
                                        <?= form_open('cart/add/' . $product->id, 'class="ajax-form" onsubmit="ajax_submit_form_callback = update_mini_cart"'); ?>
                                        <div class="form-group">
                                            <?php
                                            if ($variants) {
                                                foreach ($variants as $variant) {
                                                    $opts[$variant->id] = $variant->name . ($variant->price > 0 ? ' (+' . $this->sma->convertMoney($variant->price, true, false) . ')' : ($variant->price == 0 ? '' : ' (+' . $this->sma->convertMoney($variant->price, true, false) . ')'));
                                                }
                                                echo form_dropdown('option', $opts, '', 'class="form-control selectpicker mobile-device" required="required"');
                                            } ?>
                                        </div>

                                        <div class="form-group">
                                            <div class="input-group">
                                                <span class="input-group-addon pointer btn-minus"><span class="fa fa-minus"></span></span>
                                                <input type="text" name="quantity" class="form-control text-center quantity-input" value="1" required="required">
                                                <span class="input-group-addon pointer btn-plus"><span class="fa fa-plus"></span></span>
                                                <input type="hidden" name="is_ajax" value="yes">
                                            </div>
                                        </div>
                                        <!-- <input type="hidden" name="quantity" class="form-control text-center" value="1"> -->

                                        <div class="form-group">
                                            <div class="btn-group" role="group" aria-label="...">
                                                <button class="btn btn-info btn-lg add-to-wishlist" data-id="<?= $product->id; ?>"><i class="fa fa-heart"></i></button>
                                                <button type="submit" class="btn btn-theme btn-lg"><i class="fa fa-shopping-cart padding-right-md"></i> <?= lang('add_to_cart'); ?></button>
                                                <button type="submit" class="btn btn-theme btn-lg pull-right buy-on-refresh" style="margin-right: 1px" data-id="<?= $product->id; ?>"><i class="fa fa-shopping-cart"></i> <?= lang('buy_now'); ?></button>
                                            </div>
                                            <div class="btn-group">
                                            </div>
                                        </div>
                                        <?= form_close(); ?>
                                        <?php
                                                        } else {
                                                            echo '<div class="well well-sm"><strong>' . lang('item_out_of_stock') . '</strong></div>';
                                                        } ?>
                                        <?php
                                                    } ?>
                                    </div>

                                    <div class="col-sm-7">
                                        <div class="clearfix"></div>
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-striped dfTable table-right-left">
                                                <tbody>
                                                    <tr>
                                                        <td width="50%"><?= lang('company'); ?></td>
                                                        <td width="50%"><?= $product->warehouse_name; ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td width="50%"><?= lang('name'); ?></td>
                                                        <td width="50%"><?= $product->name; ?></td>
                                                    </tr>
                                                    <?php if (!empty($product->second_name)) {
                                                        ?>
                                                    <tr>
                                                        <td width="50%"><?= lang('secondary_name'); ?></td>
                                                        <td width="50%"><?= $product->second_name; ?></td>
                                                    </tr>
                                                    <?php
                                                    } ?>
                                                    <tr>
                                                        <td><?= lang('code'); ?></td>
                                                        <td><?= $product->code; ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td><?= lang('type'); ?></td>
                                                        <td><?= lang($product->type); ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td><b><?= lang('stock'); ?></b></td>
                                                        <td><b class="text-success"><?=$available_quantity?></b></td>
                                                    </tr>
                                                    <tr>
                                                        <td><?= lang('brand'); ?></td>
                                                        <td><?= $brand ? '<a href="' . site_url('brand/' . $brand->slug) . '" class="line-height-lg">' . $brand->name . '</a>' : ''; ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td><?= lang('category'); ?></td>
                                                        <td><?= '<a href="' . site_url('category/' . $category->slug) . '" class="line-height-lg">' . $category->name . '</a>'; ?></td>
                                                    </tr>
                                                    <?php if ($product->subcategory_id) {
                                                        ?>
                                                    <tr>
                                                        <td><?= lang('subcategory'); ?></td>
                                                        <td><?= '<a href="' . site_url('category/' . $category->slug . '/' . $subcategory->slug) . '" class="line-height-lg">' . $subcategory->name . '</a>'; ?></td>
                                                    </tr>
                                                    <?php
                                                    } ?>

                                                    <?php if (!$shop_settings->hide_price) {
                                                        ?>
                                                    <tr>
                                                        <td><?= lang('price'); ?></td>
                                                        <td><?= $this->sma->convertMoney(isset($product->special_price) ? $product->special_price : $product->price); ?></td>
                                                    </tr>
                                                    <?php
                                                    } ?>

                                                    <?php
                                                    if ($product->promotion) {
                                                        echo '<tr><td>' . lang('promotion') . '</td><td><strong>' . $this->sma->convertMoney($product->promo_price) . '</strong><br>' . ($product->start_date && $product->start_date != '0000-00-00' ? lang('start_date') . ': <strong>' . $this->sma->hrsd($product->start_date) . '</strong><br>' : '') . ($product->end_date && $product->end_date != '0000-00-00' ? lang('end_date') . ': <strong>' . $this->sma->hrsd($product->end_date) . '</strong>' : '') . '</td></tr>';
                                                    }
                                                    ?>

                                                    <?php if ($product->tax_rate) {
                                                        ?>
                                                    <tr>
                                                        <td><?= lang('tax_rate'); ?></td>
                                                        <td><?= $tax_rate->name; ?></td>
                                                    </tr>
                                                    <tr>
                                                        <td><?= lang('tax_method'); ?></td>
                                                        <td><?= $product->tax_method == 0 ? lang('inclusive') : lang('exclusive'); ?></td>
                                                    </tr>
                                                    <?php
                                                    } ?>

                                                    <tr>
                                                        <td><?= lang('unit'); ?></td>
                                                        <td><?= $unit ? $unit->name . ' (' . $unit->code . ')' : ''; ?></td>
                                                    </tr>
                                                    <?php if (!empty($warehouse) && $product->type == 'standard') {
                                                        ?>
                                                    <tr>
                                                        <td><?= lang('in_stock'); ?></td>
                                                        <td><?= $this->sma->formatQuantity($warehouse->quantity); ?></td>
                                                    </tr>
                                                    <?php
                                                    } ?>

                                                    <?php if ($variants) {
                                                        ?>
                                                    <tr>
                                                        <td><?= lang('product_variants'); ?></td>
                                                        <td><?php foreach ($variants as $variant) {
                                                            echo '<span class="label label-primary">' . $variant->name . '</span> ';
                                                        } ?></td>
                                                    </tr>
                                                    <?php
                                                    } ?>

                                                    <?php if (!empty($options)) {
                                                        foreach ($options as $option) {
                                                            if ($option->wh_qty != 0) {
                                                                echo '<tr><td colspan="2" class="bg-primary">' . $option->name . '</td></tr>';
                                                                echo '<td>' . lang('in_stock') . ': ' . $this->sma->formatQuantity($option->wh_qty) . '</td>';
                                                                echo '<td>' . lang('price') . ': ' . $this->sma->convertMoney(($product->special_price ? $product->special_price : $product->price) + $option->price) . '</td>';
                                                                echo '</tr>';
                                                            }
                                                        }
                                                    } ?>

                                                    <?php if ($product->cf1 || $product->cf2 || $product->cf3 || $product->cf4 || $product->cf5 || $product->cf6) {
                                                        if ($product->cf1) {
                                                            echo '<tr><td>' . lang('pcf1') . '</td><td>' . $product->cf1 . '</td></tr>';
                                                        }
                                                        if ($product->cf2) {
                                                            echo '<tr><td>' . lang('pcf2') . '</td><td>' . $product->cf2 . '</td></tr>';
                                                        }
                                                        if ($product->cf3) {
                                                            echo '<tr><td>' . lang('pcf3') . '</td><td>' . $product->cf3 . '</td></tr>';
                                                        }
                                                        if ($product->cf4) {
                                                            echo '<tr><td>' . lang('pcf4') . '</td><td>' . $product->cf4 . '</td></tr>';
                                                        }
                                                        if ($product->cf5) {
                                                            echo '<tr><td>' . lang('pcf5') . '</td><td>' . $product->cf5 . '</td></tr>';
                                                        }
                                                        if ($product->cf6) {
                                                            echo '<tr><td>' . lang('pcf6') . '</td><td>' . $product->cf6 . '</td></tr>';
                                                        }
                                                    } ?>


                                                </tbody>
                                            </table>
                                            <?php if ($product->type == 'combo') {
                                                        ?>
                                            <strong><?= lang('combo_items') ?></strong>
                                            <div class="table-responsive">
                                                <table
                                                class="table table-bordered table-striped table-condensed dfTable two-columns">
                                                <thead>
                                                    <tr>
                                                        <th><?= lang('product_name') ?></th>
                                                        <th><?= lang('quantity') ?></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($combo_items as $combo_item) {
                                                            echo '<tr><td>' . $combo_item->name . ' (' . $combo_item->code . ') </td><td>' . $this->sma->formatQuantity($combo_item->qty) . '</td></tr>';
                                                        } ?>
                                                </tbody>
                                            </table>
                                        </div>
                                        <?php
                                                    } ?>
                                    </div>
                                </div>
                                <div class="clearfix"></div>

                                <div class="col-xs-12">

                                    <?= $product->details ? '<div class="panel panel-info"><div class="panel-heading">' . lang('product_details_for_invoice') . '</div><div class="panel-body">' . $product->details . '</div></div>' : ''; ?>
                                    <?= $product->product_details ? '<div class="panel panel-default"><div class="panel-heading">' . lang('product_details') . '</div><div class="panel-body">' . $product->product_details . '</div></div>' : ''; ?>

                                </div>
                            </div>

                            <?php include 'share.php'; ?>
                        </div>
                    </div>
                </div>

                <div class="col-sm-3 col-md-2">
                    <?php include 'sidebar2.php'; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="container">
    <div class="featured-products list_prod">
    <div class="row">
        <?php
        if (!empty($other_products)) {
            ?>
            <div class="col-xs-12">
                <h3 class="margin-top-no text-size-lg">
                    <?= lang('other_products'); ?>
                </h3>
            </div>
            <div class="row">
            <div class="col-xs-12">
                <?php
                foreach ($other_products as $fp) {
                    ?>
                    <div class="col-sm-3 col-xs-6 col-md-2 product-item">
                        <div class="product" style="z-index: 1;">
                            <div class="details" style="transition: all 100ms ease-out 0s;">
                                <span class="badge badge-left blue <?=libName($fp->warehouse_name);?>"><?=$fp->warehouse_name;?></span>
                                <?php
                                if (!empty($fp->promotion)) {
                                    ?>
                                    <span class="badge badge-right green"><?=discount($fp->price , $fp->promo_price);?> %</span>
                                    <?php
                                } ?>
                                <?php if($mobile):;?>
                                    <a href="<?= site_url('product/' . $fp->slug); ?>">
                                        <img class="lazy" data-src="<?= productImage($fp->image,false); ?>" alt="">
                                    </a>
                                <?php else :; ?>
                                    <img class="lazy" data-src="<?= productImage($fp->image,false); ?>" alt="">
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
                                    <span class="product_name">
                                                                <a href="<?= site_url('product/' . $fp->slug); ?>"><?= limit_string($fp->name,55); ?></a>
                                                            </span>
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
                                        <a href="<?= site_url('brand/' . $fp->brand_slug); ?>" class="link"><?= $fp->brand_name; ?></a>
                                        <?php
                                    } ?>
                                </div>
                                <div class="clearfix"></div>
                            </div>
                        </div>
                    </div>
                    <?php
                } ?>
            </div>
        </div>
            <?php
        }
        ?>
    </div>
    </div>
</div>
</section>

<div id="lightbox" class="modal" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-middle">
        <div class="modal-content">
            <button type="button" class="close hidden" data-dismiss="modal" aria-hidden="true">×</button>
            <div class="modal-body">
                <img src="" alt="" />
            </div>
        </div>
    </div>
</div>
