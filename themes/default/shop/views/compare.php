<?php /** Created by PhpStorm. User: john Date: 8/28/2020 Time: 5:33 AM */?>

<table id="zero_config" class="text-left table table-striped table-hover" width="100%">
    <thead>
        <tr>
            <th>Image</th>
            <th>Nom</th>
            <th>Prix</th>
            <th>Librairie</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($products as $product):?>
            <tr>
                <td>
                    <a href="<?=base_url('product/'.$product->slug);?>"><img class="lazy" data-src="<?= productImage($product->image); ?>" alt="<?=$product->slug;?>"></a>
                </td>
                <td><?=$product->name;?></td>
                <td>
                    <?php if(!empty($product->promotion) and !empty($product->promo_price)):;?>
                        <del class="text-red"><?=$this->sma->convertMoney($product->price);?></del> <?=$this->sma->convertMoney($product->promo_price);?>
                    <?php else :; ?>
                        <?=$this->sma->convertMoney($product->price);?>
                    <?php endif;?>
                </td>
                <td><span class="label green"><?=$product->warehouse_name;?></span></td>
                <td>
                    <button class="btn add-to-cart btn-primary" data-id="<?=$product->id;?>"><i class="fa fa-shopping-basket"></i> <?=lang('add_to_cart');?></button>
                </td>
            </tr>
        <?php endforeach;?>
    </tbody>
</table>
