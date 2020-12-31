<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<link rel="stylesheet" type="text/css" href="<?=$assets;?>jquery-minicolors/jquery.minicolors.css">
<script src="<?=$assets;?>jquery-minicolors/jquery.minicolors.min.js"></script>
<style>
    .minicolors-theme-bootstrap .minicolors-input{ text-align: right;}
    .minicolors-theme-bootstrap .minicolors-swatch{width: 60% !important;}
</style>
<div class="box">
    <div class="box-header">
        <h2 class="blue"><i class="fa-fw fa fa-cogs"></i>COULEUR DU SITE</h2>
    </div>
</div>

<?php $attrib = ['data-toggle' => 'validator', 'role' => 'form'];
echo admin_form_open_multipart('shop_settings/site_colors', $attrib); ?>
<div class="box">
    <div class="box-header">
        <h2 class="blue">Theme</h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="form-group col-sm-6">
                <label for="theme" class="bmd-label-floating"><b>Theme (fond)</b></label>
                <input type="text" name="theme" id="theme" class="form-control minicolor" data-control="hue" value="<?=$colors->theme;?>">
            </div>

            <div class="form-group col-sm-6">
                <label for="theme_text" class="bmd-label-floating"><b>Theme (text)</b></label>
                <input type="text" name="theme_text" id="theme_text" class="form-control minicolor" data-control="hue" value="<?=$colors->theme_text;?>">
            </div>
        </div>
    </div>
</div>

<div class="box">
    <div class="box-header">
        <h2 class="blue">Entête</h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="form-group col-sm-4">
                <label for="main_header" class="bmd-label-floating"><b>Arriere Plan entete</b></label>
                <input type="text" name="main_header" id="main_header" class="form-control minicolor" data-control="hue" value="<?=$colors->main_header;?>">
            </div>
            <div class="form-group col-sm-6">
                <label for="navbar_bg" class="bmd-label-floating"><b>Arriere plan navigation</b></label>
                <input type="text" name="navbar_bg" id="navbar_bg" class="form-control minicolor" data-control="hue" value="<?=$colors->navbar_bg;?>">
            </div>
        </div>
    </div>
</div>

<div class="box">
    <div class="box-header">
        <h2 class="blue">Produits</h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="form-group col-sm-3">
                <label for="product_bg" class="bmd-label-floating"><b>Arriere Plan du produit</b></label>
                <input type="text" name="product_bg" id="product_bg" class="form-control minicolor" data-control="hue" value="<?=$colors->product_bg;?>">
            </div>

            <div class="form-group col-sm-3">
                <label for="product_bg_hover" class="bmd-label-floating"><b>Fond du produit au survol</b></label>
                <input type="text" name="product_bg_hover" id="product_bg_hover" class="form-control minicolor" data-control="hue" value="<?=$colors->product_bg_hover;?>">
            </div>

            <div class="form-group col-sm-3">
                <label for="product_name" class="bmd-label-floating"><b>Nom du produit</b></label>
                <input type="text" name="product_name" id="product_name" class="form-control minicolor" data-control="hue" value="<?=$colors->product_name;?>">
            </div>

            <div class="form-group col-sm-3">
                <label for="product_price" class="bmd-label-floating"><b>Prix du produit</b></label>
                <input type="text" name="product_price" id="product_price" class="form-control minicolor" data-control="hue" value="<?=$colors->product_price;?>">
            </div>

            <div class="form-group col-sm-3">
                <label for="old__product_price" class="bmd-label-floating"><b>Ancient prix du produit</b></label>
                <input type="text" name="old__product_price" id="old__product_price" class="form-control minicolor" data-control="hue" value="<?=$colors->old__product_price;?>">
            </div>

            <div class="form-group col-sm-3">
                <label for="discount_text" class="bmd-label-floating"><b>pourcentage (%) Reduction</b></label>
                <input type="text" name="discount_text" id="discount_text" class="form-control minicolor" data-control="hue" value="<?=$colors->discount_text;?>">
            </div>

            <div class="form-group col-sm-3">
                <label for="discount_bg" class="bmd-label-floating"><b>Arriere plan (%)reduction</b></label>
                <input type="text" name="discount_bg" id="discount_bg" class="form-control minicolor" data-control="hue" value="<?=$colors->discount_bg;?>">
            </div>
            <div class="form-group col-sm-3">
                <label for="product_category" class="bmd-label-floating"><b>Categorie</b></label>
                <input type="text" name="product_category" id="product_category" class="form-control minicolor" data-control="hue" value="<?=$colors->product_category ;?>">
            </div>

            <div class="form-group col-sm-6">
                <label for="library_name_bg" class="bmd-label-floating"><b>Arriere plan nom librairie</b></label>
                <input type="text" name="library_name_bg" id="library_name_bg" class="form-control minicolor" data-control="hue" value="<?=$colors->library_name_bg;?>">
            </div>
            <div class="form-group col-sm-6">
                <label for="library_name" class="bmd-label-floating"><b>Nom de la librairie</b></label>
                <input type="text" name="library_name" id="library_name" class="form-control minicolor" data-control="hue" value="<?=$colors->library_name;?>">
            </div>
        </div>
    </div>
</div>

<div class="box">
    <div class="box-header">
        <h2 class="blue">Boutons</h2>
    </div>
    <div class="box-content">
        <div class="row">
            <div class="form-group col-sm-3">
                <label for="button_bg_hover" class="bmd-label-floating"><b>Arriere plan bouton au survol</b></label>
                <input type="text" name="button_bg_hover" id="button_bg_hover" class="form-control minicolor" data-control="hue" value="<?=$colors->button_bg_hover;?>">
            </div>

            <div class="form-group col-sm-3">
                <label for="button_text_hover" class="bmd-label-floating"><b>text bouton au survol</b></label>
                <input type="text" name="button_text_hover" id="button_text_hover" class="form-control minicolor" data-control="hue" value="<?=$colors->button_text_hover;?>">
            </div>

            <div class="form-group col-sm-3">
                <label for="category_list" class="bmd-label-floating"><b>liste Categories</b></label>
                <input type="text" name="category_list" id="category_list" class="form-control minicolor" data-control="hue" value="<?=$colors->category_list;?>">
            </div>

            <div class="form-group col-sm-3">
                <label for="button_bg" class="bmd-label-floating"><b>Arriere plan Boutons</b></label>
                <input type="text" name="button_bg" id="button_bg" class="form-control minicolor" data-control="hue" value="<?=$colors->button_bg;?>">
            </div>

            <div class="form-group col-sm-3">
                <label for="button_text" class="bmd-label-floating"><b>Text Boutons</b></label>
                <input type="text" name="button_text" id="button_text" class="form-control minicolor" data-control="hue" value="<?=$colors->button_text ;?>">
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-12">
    <button type="submit" class="btn btn-primary pull-right save_colors"><?=lang('save');?></button>
    </div>
</div>
</form>

<div class="clearfix" style="padding-top: 20px;"></div>

<script>

    $(function () {
        /*colorpicker*/
        $('.minicolor').each(function() {
            $(this).minicolors({
                control: $(this).attr('data-control') || 'hue',
                position: $(this).attr('data-position') || 'bottom left',

                change: function(value, opacity) {
                    if (!value) return;
                    if (opacity) value += ', ' + opacity;
                    if (typeof console === 'object') {
//                        console.log(value);
                    }
                },
                theme: 'bootstrap'
            });

        });
    });
</script>