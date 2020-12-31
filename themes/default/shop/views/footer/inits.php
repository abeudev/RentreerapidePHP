<?php /** Created by PhpStorm. User: john Date: 8/27/2020 Time: 9:54 PM */ ?>
<script>
    var dis_qty_ajuster = <?=(!empty($shop_settings->display_qty_ajuster))?true : 0?>;
    var m = '<?= $m; ?>', v = '<?= $v; ?>', products = {}, filters = <?= isset($filters) && !empty($filters) ? json_encode($filters) : '{}'; ?>, shop_color, shop_grid, sorting;
    var cart = <?= isset($cart)                                                          && !empty($cart) ? json_encode($cart) : '{}' ?>;
    var site = {base_url: '<?= base_url(); ?>', site_url: '<?= site_url('/'); ?>', shop_url: '<?= shop_url(); ?>', csrf_token: '<?= $this->security->get_csrf_token_name() ?>', csrf_token_value: '<?= $this->security->get_csrf_hash() ?>', settings: {display_symbol: '<?= $Settings->display_symbol; ?>', symbol: '<?= $Settings->symbol; ?>', decimals: <?= $Settings->decimals; ?>, thousands_sep: '<?= $Settings->thousands_sep; ?>', decimals_sep: '<?= $Settings->decimals_sep; ?>', order_tax_rate: false, products_page: <?= $shop_settings->products_page ? 1 : 0; ?>}, shop_settings: {private: <?= $shop_settings->private ? 1 : 0; ?>, hide_price: <?= $shop_settings->hide_price ? 1 : 0; ?>}}
    const checkout_page = '<?=base_url('cart/checkout');?>';
    const search_tag = '#product-search';


    function isInt(n){
        return Number(n) === n && n % 1 === 0;
    }
    function isFloat(n) {
        return Number(n) === n && n % 1 !== 0;
    }
    function init_product_hover(){
        $(".product").each(function(t,e){$(e).find(".details").hover(function(){$(this).parent().css("z-index","20"),$(this).addClass("animate")},function(){$(this).removeClass("animate"),$(this).parent().css("z-index","1")})});
    }
    $(function(){
        $('body,html').animate({scrollTop:0},500);
        const images = $('img[src]');
        for(var i = 0 ; i<= images.length ; i++){
            var image = $(images)[i];
            if(!$(image).hasClass('lazy')){
                var image_source = $(image).attr('src');
                $(image).addClass('lazy');
                $(image).attr('data-src',image_source);
                $(image).removeAttr('src');
            }
        }
    });
    function limit_string(string , max_length){
        var strlen = string.length;
        var result = '';
        for(i = 0 ; i<max_length ; i++){
            if(string[i] !== undefined){
                result+=string[i];
            }
        }

        if(max_length < strlen){
            result+='...'
        }

        return result;
    }
    function discount(item_price , promo_price){
        var discount = 0;
        discount = (parseFloat(promo_price) * 100) / item_price;

        return Math.round(discount) - 100;
    }
    $(document).ready(function() {
        //SHOP/PRODUCT --- SOCIAL MEDIA SHARING
        <?php if ($m == 'shop' && $v == 'product'):?>
        $('.rrssb-buttons').rrssb({
            title: '<?= $product->slug; ?>',
            url: '<?= site_url('product/' . $product->slug); ?>',
            image: '<?= productImage($product->image); ?>',
            description: '<?= $page_desc; ?>',
            // emailSubject: '',
            // emailBody: '',
        });
        <?php endif;?>

        //SHOW MESSAGE ON FLASHDATA
        <?php if ($message || $warning || $error || $reminder):?>
            <?php if($message):;?>  sa_alert('<?=lang('success'); ?>' , '<?= trim(str_replace(["\r", "\n", "\r\n"], '', addslashes($message))); ?>'); <?php endif;?>
            <?php if($warning):;?>  sa_alert('<?=lang('warning'); ?>' , '<?= trim(str_replace(["\r", "\n", "\r\n"], '', addslashes($warning))); ?>', 'warning'); <?php endif;?>
            <?php if($error):;?>    sa_alert('<?=lang('error'); ?>  ' , '<?= trim(str_replace(["\r", "\n", "\r\n"], '', addslashes($error))); ?>', 'error',5000); <?php endif;?>
            <?php if($reminder):;?> sa_alert('<?=lang('reminder');?>' , '<?= trim(str_replace(["\r", "\n", "\r\n"], '', addslashes($reminder))); ?>', 'info'); <?php endif;?>
        <?php endif ;?>

        init_lazy_load();
        //AUTOCOMPLETE SEARCH PRODUCT
        $("#product-search").autocomplete({
            source: '<?= shop_url('qa_suggestions'); ?>',
            minLength: 1,
            autoFocus: false,
            delay: 250,
            response: function (event, ui) {
                if ($(this).val().length >= 16 && ui.content[0].id == 0) {
                    $(this).removeClass('ui-autocomplete-loading');
                    $(this).removeClass('ui-autocomplete-loading');
                }
                else if (ui.content.length == 1 && ui.content[0].id != 0) {
                    ui.item = ui.content[0];
                    $(this).data('ui-autocomplete')._trigger('select', 'autocompleteselect', ui);
                    $(this).autocomplete('close');
                    $(this).removeClass('ui-autocomplete-loading');
                }
                else if (ui.content.length == 1 && ui.content[0].id == 0) {
//
                    $(this).removeClass('ui-autocomplete-loading');
                }
            },
            select: function (event, ui) {
                event.preventDefault();
                if (ui.item.id !== 0) {
                    $(search_tag).val(ui.item.name);
                    $('.btn-search').click();
                } else {
                    // bootbox.alert('<?= lang('no_match_found') ?>');
                }
            }
        });

        setTimeout(function(){
            if($(pagination_next_button).length === 0){
                $('.footer').show('slow');
            }
        },1000);
    });

    function productImage(image,thumb){
        if(typeof thumb == 'undefined'){thumb = true}
        image = image.trim();
        if(image.search('http') >=0){
            return image;
        }
        else{
            return site.base_url + 'assets/uploads/'+((thumb)?'thumbs':'')+'/'+image
        }
    }

    function libName(name){
        name =  name.replaceAll(' ','_');
        name = name.toLowerCase();
        return name
    }
</script>
