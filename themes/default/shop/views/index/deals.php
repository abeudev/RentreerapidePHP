<?php if(!empty($week_deals)):;?>
    <div class="col-xs-12 col-sm-6 col-md-3" <?=($mobile==FALSE)?'style="padding: 0px;"':'';?>>
    <div class="deals_featured">
        <div class="col d-flex flex-lg-row flex-column align-items-center justify-content-start">
            <div class="deals" style="background: white">
                <div class="deals_title">Deals de la semaine</div>
                <div class="deals_slider_container">
                    <!-- Deals Slider -->
                    <div class="owl-carousel owl-theme deals_slider">
                        <?php foreach($week_deals as $deal):?>
                            <?php
                            $deal_date = strtotime($deal->ending_date);
                            ?>
                            <?php if(!is_passed_date($deal_date)):;?>
                                <div class="owl-item deals_item text-center">
                                    <div class="deals_image product">
                                        <a  href="<?= site_url('product/' . $deal->product_id); ?>" data-id="<?=$deal->product_id;?>">
                                            <div style="width: 100%;max-width: 236px; height: 100%; background-size: contain; background-repeat: no-repeat;background-position: center center;" class="lazy" data-src="<?= productImage($deal->image,false); ?>" alt="week deal"></div>
                                        </a>
                                    </div>
                                    <div class="deals_content">
                                        <div class="deals_info_line d-flex flex-row justify-content-start">
                                            <div class="deals_item_category"><a href="#"><?php echo $deal->category_name ;?></a></div>
                                            <div class="deals_item_price_a ml-auto"><?php echo $this->sma->convertMoney($deal->price) ;?></div>
                                        </div>
                                        <div class="deals_info_line d-flex flex-row justify-content-start">
                                            <div class="deals_item_name"><?php echo $deal->deal_title ;?></div>
                                            <div class="deals_item_price ml-auto"><?php echo $deal->promotion_price ;?></div>
                                        </div>
                                        <div class="available">
                                            <div class="available_line d-flex flex-row justify-content-start">
                                                <div class="available_title">Disponible: <span><?php echo $deal->quantity - $deal->total_sold ;?></span></div>
                                                <div class="sold_title ml-auto">Vendus :  <span><?php echo $deal->total_sold ;?></span></div>
                                            </div>
                                            <?php
                                            $total_available = $deal->quantity + $deal->total_sold;

                                            $available_percentage = ($deal->quantity * 100) / $total_available;

                                            ?>
                                            <div class="available_bar" style="position: relative"><span style="width:<?php echo $available_percentage ;?>%"></span></div>
                                        </div>
                                        <div class="deals_timer d-flex flex-row align-items-center justify-content-start">
                                            <div class="deals_timer_title_container">
                                                <div class="deals_timer_title">Dépêchez-vous</div>
                                                <div class="deals_timer_subtitle">L'offre prend fin dans:</div>
                                            </div>
                                            <div class="deals_timer_content ml-auto">
                                                <div class="deals_timer_box clearfix" data-target-time="<?php echo $deal->ending_date ;?>">
                                                    <div class="deals_timer_unit">
                                                        <div id="deals_timer<?php echo $deal->id ;?>_hr" class="deals_timer_hr"></div>
                                                        <span>Heures</span>
                                                    </div>
                                                    <div class="deals_timer_unit">
                                                        <div id="deals_timer<?php echo $deal->id ;?>_min" class="deals_timer_min"></div>
                                                        <span>min</span>
                                                    </div>
                                                    <div class="deals_timer_unit">
                                                        <div id="deals_timer<?php echo $deal->id ;?>_sec" class="deals_timer_sec"></div>
                                                        <span>secs</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif;?>
                        <?php endforeach;?>
                    </div>
                </div>

                <div class="deals_slider_nav_container">
                    <div class="deals_slider_prev deals_slider_nav"><i class="fa fa-chevron-left ml-auto"></i></div>
                    <div class="deals_slider_next deals_slider_nav"><i class="fa fa-chevron-right ml-auto"></i></div>
                </div>
            </div>
        </div>
    </div>
    <?=($mobile==TRUE)?'<hr>':'';?>
    </div>
<?php endif;?>