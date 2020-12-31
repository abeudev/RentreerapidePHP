<?php /** Created by PhpStorm. User: john Date: 8/27/2020 Time: 10:08 PM */?>
<?php if(!get_cookie('shop_use_cookie') && get_cookie('shop_use_cookie') != 'accepted' && !empty($shop_settings->cookie_message)):;?>
    <div class="cookie-warning">
        <div class="bounceInLeft alert alert-info">
            <a href="<?= site_url('main/cookie/accepted'); ?>" class="btn btn-sm btn-primary" style="float: right;"><?= lang('i_accept'); ?></a>
            <p>
                <?= $shop_settings->cookie_message; ?>
                <?php if (!empty($shop_settings->cookie_link)) {
                    ?>
                    <a href="<?= site_url('page/' . $shop_settings->cookie_link); ?>"><?= lang('read_more'); ?></a>
                    <?php
                } ?>
            </p>
        </div>
    </div>
<?php endif;?>
