<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php $assets_url = base_url('themes/default/shop/assets/'); ?>
<script src="<?= $assets;?>js/jquery.lazy.min.js"></script>
<?php require('footer/inits.php'); ?>
<?php require('footer/footer.php'); ?>
<?php include_once('footer/modals.php'); ?>
<?php require('footer/accept_cookies.php'); ?>
<?php require('footer/js_lang.php'); ?>
<?php require('debug_script.php'); ?>
<!--<script src="--><?//= $assets; ?><!--js/products.js"></script>-->
<script src="<?= $assets;?>js/extra/toastr.min.js"></script>
<script src="<?= $assets;?>js/extra/sweetalert2.all.js"></script>
<script src="<?= $assets;?>js/extra/is_mobile.js"></script>
<script src="<?= $assets;?>tooltip/custom.min.js"></script>
<script src="<?= $assets;?>tooltip/popper.min.js"></script>
<script src="<?= $assets;?>js/extra/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="<?= $assets ?>js/jquery-ui.min.js"></script>
<script src="<?= $assets;?>js/extra/dp.min.js"></script>
<!--<script src="--><?//= $assets;?><!--js//libs.min.js"></script>-->

<script type="text/javascript">
    //SHOP/INSTALLMENT
    <?php if($this->router->fetch_class() == 'shop' && $this->router->fetch_method() == 'installment'):;?>
        const total = Number('<?=$sales->grand_total?>');
        $(document).on('click','[name="num_of_month"]',{passive:true},function () {
            const period = this.value;
            const num_of_weeks = 4;
            const div = num_of_weeks * period;



            var tx = 0;
            switch (period){
                case '1':tx = m1p; break;
                case '2':tx = m2p; break;
                case '3':tx = m3p; break;
            }


            const tx_p_w = tx / div;
            var to_pay = (total / div) + tx_p_w;


            to_pay = ((isInt(to_pay))?to_pay : parseFloat(to_pay).toFixed(4));

            var all_pay = to_pay * div;
            all_pay = Math.ceil(all_pay);
            all_pay = parseFloat(all_pay).toFixed(3);

            if(to_pay * div < all_pay){
                var dif =  all_pay - (to_pay * div);
                dif = parseFloat(dif).toFixed(2);
                dif = ((dif<= 0)? 0.1 : dif);

                console.log(''+to_pay+' + ('+dif+' / '+div+' )');
                to_pay = Number(to_pay) + (Number(dif) / div);
            }

            to_pay = ((isInt(to_pay))?to_pay : parseFloat(to_pay).toFixed(2));

            all_pay = to_pay * div;
            all_pay = parseFloat(all_pay).toFixed(4);



            $('#to_payPerWeek').text(to_pay+'');
            $('#to_pay').val(to_pay);

            $('.numOfWeeks').text(div+' <?=lang('weeks');?>');
            $('#all_to_pay').text(''+all_pay+'');
            $('#old_all').addClass('text-strike');
            $('#num_of_week').val(div);

            $('.save_param_btn').removeClass('dis-none');

        });
    <?php endif;?>

    function set_flashdata(key , value){
        localStorage.setItem(key , value);
    }
    function get_flashdata(key){
        var data = localStorage.getItem(key);
        localStorage.removeItem(key);
        return data;
    }

    $(document).on('click','.buy-now , .buy-now-2',{passive:true},function () {
        show_loader();
           setTimeout(function(){
               location.href=checkout_page;
           },300);
    });
    $(document).on('click','.buy-on-refresh',{passive:true},function () {
        set_flashdata('checkout','make_checkout');
    });
    $(document).ready(function () {
        var checkout = get_flashdata('checkout');
        if(checkout !== null){
            setTimeout(function(){
                location.href = checkout_page;
            },1500);
        }

        $('.three-col').click();
    });
    $(document).on('click','.load_stuffs',{passive:true},function () {
       <?php if($mobile):;?>
        close_drawer();
       <?php endif;?>
        show_loader();
        const school_id = $(this).attr('data-school_id');
        const class_id = $(this).attr('data-class_id');

        console.log(school_id+' : '+class_id);
        aoData = [
            {name:'<?= $this->security->get_csrf_token_name() ?>' , value:'<?=$this->security->get_csrf_hash() ?>'},
            {name:'school_id',value:school_id},
            {name:'class_id',value:class_id},
        ];
        $.ajax({
            url: base_url + 'shop/opened_bills',
            type: 'POST',
            data: aoData,
            error: function() {
                show_message('error','Une erreur est survenue');
            },
            success: function(response) {
                sweetDialog(response , {width:'<?=($mobile==FALSE)?'50%':'100%';?>'});
            }
        });
    });
    $(document).on('click','.sus_sale,.sus_sale2',{passive:true},function () {
        if($(this).hasClass('.sus_sale')){
            swal.showLoading();
        }
        else{
            sweetDialog(loader);
        }
        const btn = $(this);


        const id = $(this).attr('id');

        aoData = [
            {name:'<?= $this->security->get_csrf_token_name() ?>' , value:'<?=$this->security->get_csrf_hash() ?>'},
            {name:'sus_id',value:id},
        ];
        $.ajax({
            url: base_url + 'shop/build_cart',
            type: 'POST',
            data: aoData,
            error: function() {
              show_message('error' , 'Une erreur est survenue')
            },
            success: function(response) {
                show_loader();
                swal.fire({
                    title:'Liste de fourniture ajouter au panier avec succès',
                    icon:'success',
                    timer:2000
                })
                update_mini_cart(response);
            }
        });
    });
    $(document).on('click','.sus_receip',{passive:true},function () {
        const id = $(this).attr('id');

        aoData = [
            {name:'<?= $this->security->get_csrf_token_name() ?>' , value:'<?=$this->security->get_csrf_hash() ?>'},
            {name:'sus_id',value:id},
        ];
        $.ajax({
            url: base_url + 'shop/view_cart_receip/'+id,
            type: 'POST',
            data: aoData,
            error: function() {
                show_message('error' , 'Une erreur est survenue');
            },
            success: function(response) {
               hideSwal2Loading();
                sweetAlertTwo({
                    html:response,
                    width:'<?=($mobile==FALSE)?'50%':'100%';?>',
                })
            }
        });
    });
    $(document).on('click','.add-to-cart',{passive:true},function () {
//        setTimeout(function(){
//
//        },500);
    });
    $(document).on('click','.compare-product',{passive:true},function () {
        show_loader();
        const id = $(this).attr('data-id');
        $.post(site.shop_url+'compare/'+id , {'token':site.csrf_token_value} , function (response) {
           if(response.status === true){
               sweetDialog(response.products , {size:'<?=($mobile==FALSE)?'60%':'100%';?>'});
               init_lazy_load();
           }
           else{
               show_message('info',response.message)
           }
        })
    });

    //MOBILE SCRIPT FOR DRAWER
    <?php if($mobile):;?>
        function close_drawer(){
            $('.toggle_drawer.fa-times').click();
        }
        function open_drawer(){
            $('.toggle_drawer.fa-bars').click();
        }
        const drawer_container = '.drawer_container';
        //close drawer
        $(document).on('click','.toggle_drawer.fa-times',{passive:true},function () {
            const drawer = $('.drawer');
            $(drawer_container).removeClass('drawer_backdrop');
            $(drawer).addClass('close_drawer');
            $(drawer).removeClass('drawer');

            setTimeout(function(){
                $(drawer).removeClass('close_drawer');
                $(drawer).addClass('drawer');
            },300);
        });
        //open_drawer
        $(document).on('click','.toggle_drawer.fa-bars',{passive:true},function () {
            const drawer = $('.drawer');
            $(drawer).addClass('drawer');
            $(drawer).removeClass('close_drawer');
            setTimeout(function(){
                $(drawer_container).addClass('drawer_backdrop');
            },100);
        });
        //toggle_drawer_icon
        $(document).on('click','.toggle_drawer',{passive:true},function () {
            const icon = $(this);
            if($(this).hasClass('collapsed')){
                $(icon).removeClass('fa-times');
                $(icon).addClass('fa-bars');
            }
            else{
                $(icon).removeClass('fa-bars');
                $(icon).addClass('fa-times');
            }

        });
        //close_drawer on backdrop click
        $(document).on('click','.drawer_backdrop , .drawer_container',{passive:true},function () {
        console.log('close the fucking drawer');
        close_drawer();
    });
    <?php endif;?>

    $(document).on('click','.quick-preview',{passive:true},function () {
        show_loader();
        const product_id = $(this).attr('data-id');
        aoData = [
            {name:'<?= $this->security->get_csrf_token_name() ?>' , value:'<?=$this->security->get_csrf_hash() ?>'},
            {name:'product_id',value:product_id},
        ];

        $.get(site.base_url+'shop/preview_product/'+product_id , {} , function (response) {
            hide_loader();
            var product = $.parseJSON(response.product);
            product.description = response.description;
            sweetModal1({html:response.page , size:'70%' , top:'-27px' , title:response.title});
            enable_tooltip();
            initShareButtons(product);
            setTimeout(function(){
                init_lazy_load();
            },300);
        });
    });

    function initShareButtons(product){
        $('.rrssb-buttons').rrssb({
            title: product.name,
            url: site.base_url+'product/'+product.slug,
            image: productImage(product.image),
            description: product.description,
        });
    }
</script>

</body>
</html>
