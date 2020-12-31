<script src="<?=$assets;?>shop_stuffs/plugins/OwlCarousel2-2.2.1/owl.carousel.js"></script>
<script src="<?=$assets;?>shop_stuffs/plugins/slick-1.8.0/slick.js"></script>

<script>
    var page_called = 0;
    var total_page_to_call = 0;
    var fetched_ids = [];
    <?php if(!empty($fetched_ids)):;?>
        fetched_ids = <?=json_encode(array_unique($fetched_ids));?>;
    <?php endif;?>

    $(document).ready(function()
    {
        var menuActive = false;
        var header = $('.header');


        initDealsSlider();
        initFeaturedSlider();
        featuredSliderZIndex();
        initTimer();
        initBrandsSlider();


        function initDealsSlider()
        {
            if($('.deals_slider').length)
            {
                var dealsSlider = $('.deals_slider');
                dealsSlider.owlCarousel(
                    {
                        items:1,
                        loop:true,
                        navClass:['deals_slider_prev', 'deals_slider_next'],
                        nav:false,
                        dots:false,
                        smartSpeed:1200,
                        margin:30,
                        autoplay:true,
                        autoplayTimeout:5000
                    });

                if($('.deals_slider_prev').length)
                {
                    var prev = $('.deals_slider_prev');
                    prev.on('click', function()
                    {
                        dealsSlider.trigger('prev.owl.carousel');
                    });
                }

                if($('.deals_slider_next').length)
                {
                    var next = $('.deals_slider_next');
                    next.on('click', function()
                    {
                        dealsSlider.trigger('next.owl.carousel');
                    });
                }
            }
        }

        function initFeaturedSlider()
        {
            if($('.featured_slider').length)
            {
                var featuredSliders = $('.featured_slider');
                featuredSliders.each(function()
                {
                    var featuredSlider = $(this);
                    initFSlider(featuredSlider);
                });

            }
        }

        function initFSlider(fs)
        {
            var featuredSlider = fs;
            featuredSlider.on('init', function()
            {
                var activeItems = featuredSlider.find('.slick-slide.slick-active');
                for(var x = 0; x < activeItems.length - 1; x++)
                {
                    var item = $(activeItems[x]);
                    item.find('.border_active').removeClass('active');
                    if(item.hasClass('slick-active'))
                    {
                        item.find('.border_active').addClass('active');
                    }
                }
            }).on(
                {
                    afterChange: function(event, slick, current_slide_index, next_slide_index)
                    {
                        var activeItems = featuredSlider.find('.slick-slide.slick-active');
                        activeItems.find('.border_active').removeClass('active');
                        for(var x = 0; x < activeItems.length - 1; x++)
                        {
                            var item = $(activeItems[x]);
                            item.find('.border_active').removeClass('active');
                            if(item.hasClass('slick-active'))
                            {
                                item.find('.border_active').addClass('active');
                            }
                        }
                    }
                })
                .slick(
                    {
                        rows:2,
                        slidesToShow:4,
                        slidesToScroll:4,
                        infinite:false,
                        arrows:false,
                        dots:true,
                        responsive:
                            [
                                {
                                    breakpoint:768, settings:
                                    {
                                        rows:2,
                                        slidesToShow:3,
                                        slidesToScroll:3,
                                        dots:true
                                    }
                                },
                                {
                                    breakpoint:575, settings:
                                    {
                                        rows:2,
                                        slidesToShow:2,
                                        slidesToScroll:2,
                                        dots:false
                                    }
                                },
                                {
                                    breakpoint:480, settings:
                                    {
                                        rows:1,
                                        slidesToShow:1,
                                        slidesToScroll:1,
                                        dots:false
                                    }
                                }
                            ]
                    });
        }

        function initBrandsSlider()
        {
            if($('.brands_slider').length)
            {
                var brandsSlider = $('.brands_slider');

                brandsSlider.owlCarousel(
                    {
                        loop:true,
                        autoplay:true,
                        autoplayTimeout:5000,
                        nav:false,
                        dots:false,
                        autoWidth:true,
                        items:8,
                        margin:42
                    });

                if($('.brands_prev').length)
                {
                    var prev = $('.brands_prev');
                    prev.on('click', function()
                    {
                        brandsSlider.trigger('prev.owl.carousel');
                    });
                }

                if($('.brands_next').length)
                {
                    var next = $('.brands_next');
                    next.on('click', function()
                    {
                        brandsSlider.trigger('next.owl.carousel');
                    });
                }
            }
        }

        function featuredSliderZIndex()
        {
            // Hide slider dots on item hover
            var items = document.getElementsByClassName('featured_slider_item');

            for(var x = 0; x < items.length; x++)
            {
                var item = items[x];
                item.addEventListener('mouseenter', function()
                {
                    $('.featured_slider .slick-dots').css('display', "none");
                });

                item.addEventListener('mouseleave', function()
                {
                    $('.featured_slider .slick-dots').css('display', "block");
                });
            }
        }

        function initTimer()
        {
            if($('.deals_timer_box').length)
            {
                var timers = $('.deals_timer_box');
                timers.each(function()
                {
                    var timer = $(this);

                    var targetTime;
                    var target_date;

                    // Add a date to data-target-time of the .deals_timer_box
                    // Format: "Feb 17, 2018"
                    if(timer.data('target-time') !== "")
                    {
                        targetTime = timer.data('target-time');
                        target_date = new Date(targetTime).getTime();
                    }
                    else
                    {
                        var date = new Date();
                        date.setDate(date.getDate() + 2);
                        target_date = date.getTime();
                    }

                    // variables for time units
                    var days, hours, minutes, seconds;

                    var h = timer.find('.deals_timer_hr');
                    var m = timer.find('.deals_timer_min');
                    var s = timer.find('.deals_timer_sec');

                    setInterval(function ()
                    {
                        // find the amount of "seconds" between now and target
                        var current_date = new Date().getTime();
                        var seconds_left = (target_date - current_date) / 1000;
                        // console.log(seconds_left);

                        // do some time calculations
                        days = parseInt(seconds_left / 86400);
                        seconds_left = seconds_left % 86400;

                        hours = parseInt(seconds_left / 3600);
                        hours = hours + days * 24;
                        seconds_left = seconds_left % 3600;


                        minutes = parseInt(seconds_left / 60);
                        seconds = parseInt(seconds_left % 60);

                        if(hours.toString().length < 2)
                        {
                            hours = "0" + hours;
                        }
                        if(minutes.toString().length < 2)
                        {
                            minutes = "0" + minutes;
                        }
                        if(seconds.toString().length < 2)
                        {
                            seconds = "0" + seconds;
                        }

                        // display results
                        h.text(hours);
                        m.text(minutes);
                        s.text(seconds);

                    }, 1000);
                });
            }
        }
    });

    //lazy load product
    $(document).ready(function(){
       setTimeout(function(){
           $(window).scroll(function(){
               if((page_called<total_page_to_call) || (total_page_to_call === 0)){
                   const position  = parseInt($(window).scrollTop());
                   const bottom    = parseInt($(document).height() - $(window).height());
                   const rd = bottom - position;

                   if(!mobile){
                       if( rd<=10 ){

                           if(can_add_content === 0){
                               can_add_content = true;
                           }
                       }

                       if( rd <=10 && can_add_content){
                           current_position = position;
                           fetch_product();
                       }
                   }
                   else{
                       console.log('rd : '+rd);
                       if(rd <= 20){
                           if(can_add_content === 0){
                               can_add_content = true;
                           }
                       }

                       if( rd <=20 && can_add_content){
                           current_position = position;
                           fetch_product();
                       }
                   }
               }
           });
       },1000);
    });

    function fetch_product(){
        $('#loading').show();
        console.log('fetch called');
        var t = {};
        t[site.csrf_token] = site.csrf_token_value, t.filters = get_filters(), t.format = "json" , t['fetched_ids'] = fetched_ids , t.filters.page = (page_called+1);
        $.ajax({
            url: site.base_url + "main/search?page=" + t.filters.page,
            type: "POST",
            data: t,
            dataType: "json"
        }).done(function (t) {
            total_page_to_call = t.info.total;
            page_called++;
            products = t.products,
            t.products && (t.pagination && $("#pagination").html(t.pagination),
            t.info && $(".page-info").text(lang.page_info.replace("_page_", t.info.page).replace("_total_", t.info.total))),
                rend_html(products)
        }).always(function () {
            setTimeout(function(){
                $("#loading").hide();
            },300);
        }).always(function () {
            $("#loading").hide();
        });
    }

    function rend_html(products){
        if(products.length > 0){
            products.forEach(function (p , i) {
                var product = '<div class="col-sm-3 col-xs-6 col-md-2 product-item">'+
                '    <div class="product" style="z-index: 1;">'+
                '        <div class="details" style="transition: all 100ms ease-out 0s;">' +
                '<span class="badge badge-left blue '+libName(p.warehouse_name)+'">'+p.warehouse_name+'</span>'+
                '                    '+((p.promotion == '1' && p.promo_price != 0)?'<span class="badge badge-right red">'+discount(p.price , p.promo_price)+' %</span>':'')+''+
                '            <img class="lazy" alt="" data-src="'+productImage(p.image)+'" style="display: inline-block;">'+
                '            <div class="image_overlay"></div>'+
                '            <div class="btn add-to-cart" data-id="'+p.id+'"><i class="fa fa-shopping-basket"></i> '+lang.add_to_cart+'</div>'+
                '            <div class="btn compare-product" data-id="'+p.id+'"><i class="fas fa-exchange-alt"></i> '+lang.compare+'</div>'+
                '            <div class="stats-container">'+
                '                <span class="product_price">'+
                '                    '+((p.promotion == '1' && p.promo_price != 0)?'<del class="text-red">'+p.formated_price+'</del><br> '+p.formated_promo_price+'':''+p.formated_price+'')+''+
                '                </span>'+
                '                <span class="product_name">'+
                '                    <a href="'+site.base_url+'product/'+p.slug+'">'+limit_string(p.name , 55)+'</a>'+
                '                </span>'+
                '                <a href="'+site.base_url+'category/'+p.category_slug+'" class="link dis-none">'+p.category_name+'</a>'+
                    '<div class="more dis-none">'+
                    '    <hr class="simple-hr">'+
                    '    <div data-toggle="tooltip" data-placement="top" title="Plus de details" class="col-xs-4 text-center">'+
                    '        <a href="'+site.base_url+'product/'+p.slug+'"><i class="fas fa-file-alt"></i></a>'+
                    '    </div>' +
                    '<div data-toggle="tooltip" data-placement="top" title="Aperçu rapide" class="col-xs-4 text-center"> <a href="javascript:void(0)" class="quick-preview" data-id="'+p.id+'"><i class="fas fa-eye"></i></a> </div>'+
                    '    <div data-toggle="tooltip" data-placement="top" title="Ajouter au shouhait" class="col-xs-4 text-center">'+
                    '        <a href="javascript:void(0)" class="add-to-wishlist" data-id="'+p.id+'"><i class="fas fa-heart"></i></a>'+
                    '    </div>'+
                    '</div>'+
                    '<span class="link dis-none">-</span>' +
                '                '+((p.brand_name != null)?'<a href="'+site.base_url+'brand/'+p.brand_slug+'" class="link">'+p.brand_name+'</a>':'')+''+
                '            </div>'+
                '            <div class="clearfix"></div>'+
                '        </div>'+
                '    </div>'+
                '</div>';
                $('#result_container').append(product);
                enable_tooltip();
            });

            if(current_position !== null){
                can_add_content = false;
                current_position = null;
                setTimeout(function(){
                    can_add_content = true;
                },1000);
                if($('#pagination a[rel="next"]').length === 0){
                    $('.footer').show('slow');
                }
            }

            init_lazy_load();
            init_product_hover();
        }
    }
</script>