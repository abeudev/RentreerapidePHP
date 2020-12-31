<?php /** Created by PhpStorm. User: john Date: 8/27/2020 Time: 10:02 PM */?>
<section class="footer" style="display: none;">
    <div class="container padding-bottom-md">
        <div class="row">
            <div class="col-md-6 col-sm-12">
                <div class="title-footer"><span><?= lang('about_us'); ?></span></div>
                <p>
                    <?= $shop_settings->description; ?> <a href="<?= site_url('page/' . $shop_settings->about_link); ?>"><?= lang('read_more'); ?></a>
                </p>
                <p>
                    <i class="fa fa-phone"></i> <span class="margin-left-md"><?= $shop_settings->phone; ?></span>
                    <i class="fa fa-envelope margin-left-xl"></i> <span class="margin-left-md"><?= $shop_settings->email; ?></span>
                </p>
                <ul class="list-inline">
                    <li><a href="<?= site_url('page/' . $shop_settings->privacy_link); ?>"><?= lang('privacy_policy'); ?></a></li>
                    <li><a href="<?= site_url('page/' . $shop_settings->terms_link); ?>"><?= lang('terms_conditions'); ?></a></li>
                    <li><a href="<?= site_url('page/' . $shop_settings->contact_link); ?>"><?= lang('contact_us'); ?></a></li>
                </ul>
            </div>

            <div class="clearfix visible-sm-block"></div>
            <div class="col-md-3 col-sm-6">
                <div class="title-footer"><span><?= lang('payment_methods'); ?></span></div>
                <p><?= $shop_settings->payment_text; ?></p>
                <img class="img-responsive" src="<?= $assets; ?>/images/payment-methods.png" alt="Payment Methods">
            </div>

            <div class="col-md-3 col-sm-6">
                <div class="title-footer"><span><?= lang('follow_us'); ?></span></div>
                <p><?= $shop_settings->follow_text; ?></p>
                <ul class="follow-us">
                    <?php if (!empty($shop_settings->facebook)) {
                        ?>
                        <li><a target="_blank" href="<?= $shop_settings->facebook; ?>"><i class="fa fa-facebook"></i></a></li>
                        <?php
                    } if (!empty($shop_settings->twitter)) {
                        ?>
                        <li><a target="_blank" href="<?= $shop_settings->twitter; ?>"><i class="fa fa-twitter"></i></a></li>
                        <?php
                    } if (!empty($shop_settings->google_plus)) {
                        ?>
                        <li><a target="_blank" href="<?= $shop_settings->google_plus; ?>"><i class="fa fa-google-plus"></i></a></li>
                        <?php
                    } if (!empty($shop_settings->instagram)) {
                        ?>
                        <li><a target="_blank" href="<?= $shop_settings->instagram; ?>"><i class="fa fa-instagram"></i></a></li>
                        <?php
                    } ?>
                </ul>
            </div>

        </div>
    </div>
    <div class="footer-bottom">
        <div class="container">
            <div class="copyright line-height-lg">
                &copy; <?= date('Y'); ?> <?= $shop_settings->shop_name; ?>. <?= lang('all_rights_reserved'); ?>
            </div>
            <ul class="list-inline pull-right line-height-md" style="display: none">
                <li class="padding-x-no text-size-lg">
                    <a href="#" class="theme-color text-blue" data-color="blue"><i class="fa fa-square"></i></a>
                </li>
                <li class="padding-x-no text-size-lg">
                    <a href="#" class="theme-color text-blue-grey" data-color="blue-grey"><i class="fa fa-square"></i></a>
                </li>
                <li class="padding-x-no text-size-lg">
                    <a href="#" class="theme-color text-brown" data-color="brown"><i class="fa fa-square"></i></a>
                </li>
                <li class="padding-x-no text-size-lg">
                    <a href="#" class="theme-color text-cyan" data-color="cyan"><i class="fa fa-square"></i></a>
                </li>
                <li class="padding-x-no text-size-lg">
                    <a href="#" class="theme-color text-green" data-color="green"><i class="fa fa-square"></i></a>
                </li>
                <li class="padding-x-no text-size-lg">
                    <a href="#" class="theme-color text-grey" data-color="grey"><i class="fa fa-square"></i></a>
                </li>
                <li class="padding-x-no text-size-lg">
                    <a href="#" class="theme-color text-purple" data-color="purple"><i class="fa fa-square"></i></a>
                </li>
                <li class="padding-x-no text-size-lg">
                    <a href="#" class="theme-color text-orange" data-color="orange"><i class="fa fa-square"></i></a>
                </li>
                <li class="padding-x-no text-size-lg">
                    <a href="#" class="theme-color text-pink" data-color="pink"><i class="fa fa-square"></i></a>
                </li>
                <li class="padding-x-no text-size-lg">
                    <a href="#" class="theme-color text-red" data-color="red"><i class="fa fa-square"></i></a>
                </li>
                <li class="padding-x-no text-size-lg">
                    <a href="#" class="theme-color text-teal" data-color="teal"><i class="fa fa-square"></i></a>
                </li>
            </ul>
            <div class="clearfix"></div>
        </div>
    </div>
</section>

<!--scroll to top-->
<a href="#" class="back-to-top text-center" onclick="$('body,html').animate({scrollTop:0},500); return false">
    <i class="fa fa-angle-double-up"></i>
</a>

</section><!-- /wrapper-->
