<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<?php $assets_url = base_url('themes/default/shop/assets/');
      $show_symbol = false;
$this->session->set_flashdata('info','Paiement annulé');
?>
<link rel="stylesheet" href="<?=$assets_url?>preloader/green_preloader.css">
<style>
    .cpButton button.large{
        font-size: 30px !important;
    }
    .swal2-modal{
        position:relative;
        top:-4em;
    }
    .dis-block{display: block !important;}
    .dis-inline-block{display: inline-block !important;}
    .m-r-9{margin-right: 9px !important;}
    .hover-pointer:hover{cursor: pointer !important;}
    .b-r-4{border-radius: 4px !important;}
    #amount_to_pay_error_smg{
        position: absolute;
        left: 43px;
        bottom: 0px;
        display: block;
    }
</style>
<?php
if (!empty($installment_orders)) {
    $pay_progress = 0;
    $total_paid = 0;
    $paid_count = 0;
    foreach ($installment_orders as $io) {
        $sale_id = $io['order_id'];

        for ($i = 1; $i <= 12; $i++) {
            $paid = (!empty($io['w' . $i])) ? $io['w' . $i] : 0;

            if ($paid > 0) {
                $paid_count++;
            }

            $total_paid += $paid;
        }


        $percentage_paid = round(($total_paid * 100) / (float)$io['total'], 2);
        $percentage_paid = ($percentage_paid > 100) ? 100 : $percentage_paid;
//            echo '<br>'.$total_paid.' : '.$percentage_paid.' % ';

        $pay_progress = $percentage_paid;
    }
}
?>

<section class="page-contents">
    <div class="container">
        <div class="row">
            <div class="col-xs-12">
                <div class="row">
                    <div class="col-sm-9 col-md-10">

                        <div class="panel panel-default margin-top-lg">
                            <div class="panel-heading text-bold">
                                <i class="fa fa-list-alt margin-right-sm"></i> <?= lang('view_order') . ($inv ? ' (' . $inv->reference_no . ')' : ''); ?>
                                <?= $this->loggedIn ? '<a href="' . shop_url('orders') . '" class="pull-right"><i class="fa fa-share"></i> ' . lang('my_orders') . '</a>' : ''; ?>
                                <a href="<?= shop_url('orders?download=' . $inv->id . ($this->loggedIn ? '' : '&hash=' . $inv->hash)); ?>"
                                   class="pull-right" style="margin-right:10px;"><i
                                            class="fa fa-download"></i> <?= lang('download'); ?></a>
                            </div>
                            <div class="panel-body mprint">

                                <div class="text-center biller-header print" style="margin-bottom:20px;">
                                    <img src="<?= base_url() . 'assets/uploads/logos/' . $biller->logo; ?>"
                                         alt="<?= $biller->company && $biller->company != '-' ? $biller->company : $biller->name; ?>">
                                    <h2 style="margin-top:10px;"><?= $biller->company && $biller->company != '-' ? $biller->company : $biller->name; ?></h2>
                                    <?= $biller->company ? '' : 'Attn: ' . $biller->name ?>

                                    <?php
                                    echo $biller->address . ' ' . $biller->city . ' ' . $biller->postal_code . ' ' . $biller->state . ' ' . $biller->country;

                                    echo '<br>';

                                    if ($biller->vat_no != '-' && $biller->vat_no != '') {
                                        echo lang('vat_no') . ': ' . $biller->vat_no;
                                    }
                                    if ($biller->cf1 != '-' && $biller->cf1 != '') {
                                        echo ', ' . lang('bcf1') . ': ' . $biller->cf1;
                                    }
                                    if ($biller->cf2 != '-' && $biller->cf2 != '') {
                                        echo ', ' . lang('bcf2') . ': ' . $biller->cf2;
                                    }
                                    if ($biller->cf3 != '-' && $biller->cf3 != '') {
                                        echo ', ' . lang('bcf3') . ': ' . $biller->cf3;
                                    }
                                    if ($biller->cf4 != '-' && $biller->cf4 != '') {
                                        echo ', ' . lang('bcf4') . ': ' . $biller->cf4;
                                    }
                                    if ($biller->cf5 != '-' && $biller->cf5 != '') {
                                        echo ', ' . lang('bcf5') . ': ' . $biller->cf5;
                                    }
                                    if ($biller->cf6 != '-' && $biller->cf6 != '') {
                                        echo ', ' . lang('bcf6') . ': ' . $biller->cf6;
                                    }

                                    echo '<br>';
                                    echo lang('tel') . ': ' . $biller->phone . ' ' . lang('email') . ': ' . $biller->email;
                                    ?>
                                </div>

                                <div class="well well-sm">
                                    <div class="row bold">
                                        <div class="col-xs-5">
                                            <p style="margin-bottom:0;">
                                                <?= lang('date'); ?>: <?= $this->sma->hrld($inv->date); ?><br>
                                                <?= lang('ref'); ?>: <?= $inv->reference_no; ?><br>
                                                <?php if (!empty($inv->return_sale_ref)) {
                                                    echo lang('return_ref') . ': ' . $inv->return_sale_ref;
                                                    if ($inv->return_id) {
                                                        echo ' <a data-target="#myModal2" data-toggle="modal" href="' . admin_url('sales/modal_view/' . $inv->return_id) . '"><i class="fa fa-external-link no-print"></i></a><br>';
                                                    } else {
                                                        echo '<br>';
                                                    }
                                                } ?>
                                                <?= lang('sale_status'); ?>: <?= lang($inv->sale_status); ?><br>
                                                <?= lang('payment_status'); ?>: <?= lang($inv->payment_status); ?>
                                            </p>
                                        </div>
                                        <div class="col-xs-7 text-right order_barcodes">
                                            <img src="<?= admin_url('misc/barcode/' . $this->sma->base64url_encode($inv->reference_no) . '/code128/74/0/1'); ?>"
                                                 alt="<?= $inv->reference_no; ?>" class="bcimg"/>
                                            <?= $this->sma->qrcode('link', urlencode(shop_url('orders/' . $inv->id)), 2); ?>
                                        </div>
                                        <div class="clearfix"></div>
                                    </div>
                                    <div class="clearfix"></div>
                                </div>

                                <div class="row" style="margin-bottom:15px;">

                                    <div class="col-xs-6">
                                        <?php echo $this->lang->line('billing'); ?>:<br/>
                                        <h3 style="margin-top:10px; display:inline-block"><?=$customer->name;?> </h3>
                                        <br>
                                        <?php
                                        echo $customer->address . '<br>' . $customer->city . ' ' . $customer->postal_code . ' ' . $customer->state . '<br>' . $customer->country;

                                        echo '<p>';

                                        if ($customer->vat_no != '-' && $customer->vat_no != '') {
                                            echo '<br>' . lang('vat_no') . ': ' . $customer->vat_no;
                                        }
                                        if ($customer->cf1 != '-' && $customer->cf1 != '') {
                                            echo '<br>' . lang('ccf1') . ': ' . $customer->cf1;
                                        }
                                        if ($customer->cf2 != '-' && $customer->cf2 != '') {
                                            echo '<br>' . lang('ccf2') . ': ' . $customer->cf2;
                                        }
                                        if ($customer->cf3 != '-' && $customer->cf3 != '') {
                                            echo '<br>' . lang('ccf3') . ': ' . $customer->cf3;
                                        }
                                        if ($customer->cf4 != '-' && $customer->cf4 != '') {
                                            echo '<br>' . lang('ccf4') . ': ' . $customer->cf4;
                                        }
                                        if ($customer->cf5 != '-' && $customer->cf5 != '') {
                                            echo '<br>' . lang('ccf5') . ': ' . $customer->cf5;
                                        }
                                        if ($customer->cf6 != '-' && $customer->cf6 != '') {
                                            echo '<br>' . lang('ccf6') . ': ' . $customer->cf6;
                                        }

                                        echo '</p>';
                                        echo lang('tel') . ': ' . $customer->phone . '<br>' . lang('email') . ': ' . $customer->email;
                                        ?>
                                    </div>
                                    <?php if ($address) {
                                        ?>
                                        <div class="col-xs-6">
                                            <?php echo $this->lang->line('shipping'); ?>:
                                            <h2 style="margin-top:10px;"><?=$customer->name; ?></h2>
                                            <?= $customer->company ? '' : 'Attn: ' . $customer->name ?>
                                            <p>
                                                <?= $address->line1; ?><br>
                                                <?= $address->line2; ?><br>
                                                <?= $address->city; ?> <?= $address->state; ?><br>
                                                <?= $address->postal_code; ?> <?= $address->country; ?><br>
                                                <?= lang('phone') . ': ' . $address->phone; ?>
                                            </p>
                                        </div>
                                        <?php
                                    } ?>

                                </div>

                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover table-striped print-table order-table">

                                        <thead>

                                        <tr>
                                            <th><?= lang('no'); ?></th>
                                            <th><?=lang('image')?></th>
                                            <th><?= lang('description'); ?></th>
                                            <?php if ($Settings->indian_gst) {
                                                ?>
                                                <th><?= lang('hsn_code'); ?></th>
                                                <?php
                                            } ?>
                                            <th><?= lang('quantity'); ?></th>
                                            <th><?= lang('unit_price'); ?></th>
                                            <?php
                                            if ($Settings->tax1 && $inv->product_tax > 0) {
                                                echo '<th>' . lang('tax') . '</th>';
                                            }
                                            if ($Settings->product_discount && $inv->product_discount != 0) {
                                                echo '<th>' . lang('discount') . '</th>';
                                            }
                                            ?>
                                            <th><?= lang('subtotal'); ?></th>
                                        </tr>

                                        </thead>

                                        <tbody>

                                        <?php $r = 1;
                                        $tax_summary = [];
                                        foreach ($rows as $row):
                                            ?>
                                            <tr>
                                                <td style="text-align:center; width:40px; vertical-align:middle;"><?= $r; ?></td>
                                                <td style="text-align:center; width:40px; vertical-align:middle;">
                                                    <img class="img-thumbnail preview" alt="" src="<?= productImage($row->image);?>">
                                                </td>
                                                <td style="vertical-align:middle;">
                                                    <?= $row->product_code . ' - ' . $row->product_name . ($row->variant ? ' (' . $row->variant . ')' : ''); ?>
                                                    <?= $row->second_name ? '<br>' . $row->second_name : ''; ?>
                                                    <?= $row->details ? '<br>' . $row->details : ''; ?>
                                                    <?= $row->serial_no ? '<br>' . $row->serial_no : ''; ?>
                                                </td>
                                                <?php if ($Settings->indian_gst) {
                                                    ?>
                                                    <td style="width: 85px; text-align:center; vertical-align:middle;"><?= $row->hsn_code; ?></td>
                                                    <?php
                                                } ?>
                                                <td style="width: 80px; text-align:center; vertical-align:middle;"><?= $this->sma->formatQuantity($row->unit_quantity); ?></td>
                                                <td style="text-align:right; width:100px;"><?= $this->sma->formatMoney($row->real_unit_price); ?></td>
                                                <?php
                                                if ($Settings->tax1 && $inv->product_tax > 0) {
                                                    echo '<td style="width: 100px; text-align:right; vertical-align:middle;">' . ($row->item_tax != 0 && $row->tax_code ? '<small>(' . $row->tax_code . ')</small>' : '') . ' ' . $this->sma->formatMoney($row->item_tax) . '</td>';
                                                }
                                                if ($Settings->product_discount && $inv->product_discount != 0) {
                                                    echo '<td style="width: 100px; text-align:right; vertical-align:middle;">' . ($row->discount != 0 ? '<small>(' . $row->discount . ')</small> ' : '') . $this->sma->formatMoney($row->item_discount) . '</td>';
                                                }
                                                ?>
                                                <td style="text-align:right; width:120px;"><?= $this->sma->formatMoney($row->subtotal); ?></td>
                                            </tr>
                                            <?php
                                            $r++;
                                        endforeach;
                                        if ($return_rows) {
                                            echo '<tr class="warning"><td colspan="100%" class="no-border"><strong>' . lang('returned_items') . '</strong></td></tr>';
                                            foreach ($return_rows as $row):
                                                ?>
                                                <tr class="warning">
                                                    <td style="text-align:center; width:40px; vertical-align:middle;"><?= $r; ?></td>
                                                    <td style="vertical-align:middle;">
                                                        <?= $row->product_code . ' - ' . $row->product_name . ($row->variant ? ' (' . $row->variant . ')' : ''); ?>
                                                        <?= $row->details ? '<br>' . $row->details : ''; ?>
                                                        <?= $row->serial_no ? '<br>' . $row->serial_no : ''; ?>
                                                    </td>
                                                    <?php if ($Settings->indian_gst) {
                                                        ?>
                                                        <td style="width: 85px; text-align:center; vertical-align:middle;"><?= $row->hsn_code; ?></td>
                                                        <?php
                                                    } ?>
                                                    <td style="width: 80px; text-align:center; vertical-align:middle;"><?= $this->sma->formatQuantity($row->quantity) . ' ' . $row->product_unit_code; ?></td>
                                                    <td style="text-align:right; width:100px;"><?= $this->sma->formatMoney($row->real_unit_price); ?></td>
                                                    <?php
                                                    if ($Settings->tax1 && $inv->product_tax > 0) {
                                                        echo '<td style="width: 100px; text-align:right; vertical-align:middle;">' . ($row->item_tax != 0 && $row->tax_code ? '<small>(' . $row->tax_code . ')</small>' : '') . ' ' . $this->sma->formatMoney($row->item_tax) . '</td>';
                                                    }
                                                    if ($Settings->product_discount && $inv->product_discount != 0) {
                                                        echo '<td style="width: 100px; text-align:right; vertical-align:middle;">' . ($row->discount != 0 ? '<small>(' . $row->discount . ')</small> ' : '') . $this->sma->formatMoney($row->item_discount) . '</td>';
                                                    } ?>
                                                    <td style="text-align:right; width:120px;"><?= $this->sma->formatMoney($row->subtotal); ?></td>
                                                </tr>
                                                <?php
                                                $r++;
                                            endforeach;
                                        }
                                        ?>
                                        </tbody>

                                        <tfoot>
                                        <?php
                                        $col = $Settings->indian_gst ? 6 : 5;
                                        if ($Settings->product_discount && $inv->product_discount != 0) {
                                            $col++;
                                        }
                                        if ($Settings->tax1 && $inv->product_tax > 0) {
                                            $col++;
                                        }
                                        if ($Settings->product_discount && $inv->product_discount != 0 && $Settings->tax1 && $inv->product_tax > 0) {
                                            $tcol = $col - 2;
                                        } elseif ($Settings->product_discount && $inv->product_discount != 0) {
                                            $tcol = $col - 1;
                                        } elseif ($Settings->tax1 && $inv->product_tax > 0) {
                                            $tcol = $col - 1;
                                        } else {
                                            $tcol = $col;
                                        }
                                        ?>
                                        <?php if ($inv->grand_total != $inv->total) {
                                            ?>
                                            <tr>
                                                <td colspan="<?= $tcol; ?>"
                                                    style="text-align:right; padding-right:10px;"><?= lang('Total_Item_Cost'); ?>
                                                    <?=($show_symbol)?'('.$this->Settings->symbol.')':null?>
                                                </td>
                                                <?php
                                                if ($Settings->tax1 && $inv->product_tax > 0) {
                                                    echo '<td style="text-align:right;">' . $this->sma->formatMoney($return_sale ? ($inv->product_tax + $return_sale->product_tax) : $inv->product_tax) . '</td>';
                                                }
                                                if ($Settings->product_discount && $inv->product_discount != 0) {
                                                    echo '<td style="text-align:right;">' . $this->sma->formatMoney($return_sale ? ($inv->product_discount + $return_sale->product_discount) : $inv->product_discount) . '</td>';
                                                } ?>
                                                <?php
                                                    $total_item_cost = $return_sale ? (($inv->total + $inv->product_tax) + ($return_sale->total + $return_sale->product_tax)) : ($inv->total + $inv->product_tax);
                                                ?>
                                                <td style="text-align:right; padding-right:10px;"><?= $this->sma->formatMoney($total_item_cost); ?></td>
                                            </tr>
                                            <?php
                                        } ?>
                                        <?php
                                          $grand_total = $return_sale ? ($inv->grand_total + $return_sale->grand_total) : $inv->grand_total;
                                        ?>
                                        <?php if ($Settings->indian_gst) {
                                            if ($inv->cgst > 0) {
                                                $cgst = $return_sale ? $inv->cgst + $return_sale->cgst : $inv->cgst;
                                                echo '<tr><td colspan="' . $col . '" style="text-align:right; padding-right:10px; font-weight:bold;">' . lang('cgst'). (($show_symbol)?'('.$this->Settings->symbol:null).'</td><td style="text-align:right; padding-right:10px; font-weight:bold;">' . ($Settings->format_gst ? $this->sma->formatMoney($cgst) : $cgst) . '</td></tr>';
                                            }
                                            if ($inv->sgst > 0) {
                                                $sgst = $return_sale ? $inv->sgst + $return_sale->sgst : $inv->sgst;
                                                echo '<tr><td colspan="' . $col . '" style="text-align:right; padding-right:10px; font-weight:bold;">' . lang('sgst'). (($show_symbol)?'('.$this->Settings->symbol:null).'</td><td style="text-align:right; padding-right:10px; font-weight:bold;">' . ($Settings->format_gst ? $this->sma->formatMoney($sgst) : $sgst) . '</td></tr>';
                                            }
                                            if ($inv->igst > 0) {
                                                $igst = $return_sale ? $inv->igst + $return_sale->igst : $inv->igst;
                                                echo '<tr><td colspan="' . $col . '" style="text-align:right; padding-right:10px; font-weight:bold;">' . lang('igst'). (($show_symbol)?'('.$this->Settings->symbol:null).'</td><td style="text-align:right; padding-right:10px; font-weight:bold;">' . ($Settings->format_gst ? $this->sma->formatMoney($igst) : $igst) . '</td></tr>';
                                            }
                                        } ?>
                                        <?php
                                        if ($return_sale) {
                                            echo '<tr><td colspan="' . $col . '" style="text-align:right; padding-right:10px;;">' . lang('return_total'). (($show_symbol)?'('.$this->Settings->symbol:null).'</td><td style="text-align:right; padding-right:10px;">' . $this->sma->formatMoney($return_sale->grand_total) . '</td></tr>';
                                        }
                                        if ($inv->surcharge != 0) {
                                            echo '<tr><td colspan="' . $col . '" style="text-align:right; padding-right:10px;;">' . lang('return_surcharge'). (($show_symbol)?'('.$this->Settings->symbol:null).'</td><td style="text-align:right; padding-right:10px;">' . $this->sma->formatMoney($inv->surcharge) . '</td></tr>';
                                        }
                                        ?>
                                        <?php if ($inv->order_discount != 0) {
                                            echo '<tr><td colspan="' . $col . '" style="text-align:right; padding-right:10px;;">' . lang('order_discount'). (($show_symbol)?'('.$this->Settings->symbol:null).'</td><td style="text-align:right; padding-right:10px;">' . ($inv->order_discount_id ? '<small>(' . $inv->order_discount_id . ')</small> ' : '') . $this->sma->formatMoney($return_sale ? ($inv->order_discount + $return_sale->order_discount) : $inv->order_discount) . '</td></tr>';
                                        }
                                        ?>
                                        <?php if ($Settings->tax2 && $inv->order_tax != 0) {
                                            echo '<tr><td colspan="' . $col . '" style="text-align:right; padding-right:10px;">' . lang('order_tax'). (($show_symbol)?'('.$this->Settings->symbol:null).'</td><td style="text-align:right; padding-right:10px;">' . $this->sma->formatMoney($return_sale ? ($inv->order_tax + $return_sale->order_tax) : $inv->order_tax) . '</td></tr>';
                                        }
                                        ?>
                                        <?php if ($inv->shipping != 0) {
                                            echo '<tr><td colspan="' . $col . '" style="text-align:right; padding-right:10px;;">' . lang('Delivery_fee'). (($show_symbol)?'('.$this->Settings->symbol:null).'</td><td style="text-align:right; padding-right:10px;">' . $this->sma->formatMoney($inv->shipping) . '</td></tr>';

                                            if($payment_method =='installment'){
                                                echo '<tr><td colspan="' . $col . '" style="text-align:right; padding-right:10px;;">' . lang('Installment_fee'). (($show_symbol)?'('.$this->Settings->symbol:null).'</td><td style="text-align:right; padding-right:10px;">' . $this->sma->formatMoney($grand_total - ($total_item_cost+$inv->shipping)) . '</td></tr>';
                                            }
                                        }
                                        ?>
                                        <tr>
                                            <td colspan="<?= $col; ?>"
                                                style="text-align:right; font-weight:bold;"><?= lang('total'); ?>
                                                <?=($show_symbol)?'('.$this->Settings->symbol.')':null?>
                                            </td>
                                            <td style="text-align:right; padding-right:10px; font-weight:bold;"><?= $this->sma->formatMoney($grand_total); ?></td>
                                        </tr>
                                        <tr>
                                            <td colspan="<?= $col; ?>"
                                                style="text-align:right; font-weight:bold;"><?= lang('paid'); ?>
                                                <?php if ($payment_method == 'installment'):; ?>
                                                    (<?= $pay_progress ?> %) <span class="margin-left-sm"><?= $paid_count ?> /<?= $installment_orders[0]['num_of_week']; ?></span>
                                                <?php else : ; ?>
                                                   <?=($show_symbol)?'('.$this->Settings->symbol.')':null?>
                                                <?php endif; ?>

                                            </td>
                                            <td style="text-align:right; font-weight:bold;">
                                                <?=$this->sma->formatMoney($return_sale ? ($inv->paid + $return_sale->paid) : $inv->paid);?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td colspan="<?= $col; ?>"
                                                style="text-align:right; font-weight:bold;"><?= lang('balance'); ?>
                                               <?=($show_symbol)?'('.$this->Settings->symbol.')':null?>
                                            </td>
                                            <td style="text-align:right; font-weight:bold;"><?= $this->sma->formatMoney(($return_sale ? ($inv->grand_total + $return_sale->grand_total) : $inv->grand_total) - ($return_sale ? ($inv->paid + $return_sale->paid) : $inv->paid)); ?></td>
                                        </tr>
                                        
                                        <tr>
                                            <td colspan="<?=$col;?>"  style="text-align:right; font-weight:bold;"><?=lang('payment_method');?></td>
                                            <td>
                                                <?=lang($payment_method);?>
                                            </td>
                                        </tr>

                                        </tfoot>
                                    </table>
                                </div>

                                <div class="row">
                                    <div class="col-xs-12">
                                        <?php
                                        if ($inv->note || $inv->note != '') {
                                            ?>
                                            <div class="well well-sm" style="margin-bottom:0;">
                                                <p class="bold"><?= lang('note'); ?>:</p>
                                                <div><?= $this->sma->decode_html($inv->note); ?></div>
                                            </div>
                                            <?php
                                        } ?>
                                    </div>

                                    <?php if ($customer->award_points != 0 && $Settings->each_spent > 0) {
                                        ?>
                                        <div class="col-xs-5 pull-left">
                                            <div class="well well-sm" style="margin-bottom:0;">
                                                <?=
                                                '<p>' . lang('this_sale') . ': ' . floor(($inv->grand_total / $Settings->each_spent) * $Settings->ca_point)
                                                . '<br>' .
                                                lang('total') . ' ' . lang('award_points') . ': ' . $customer->award_points . '</p>'; ?>
                                            </div>
                                        </div>
                                        <?php
                                    } ?>
                                </div>
                                <?php
                                if ($inv->grand_total > $inv->paid && !$inv->attachment) {
                                    echo '<div class="no-print well well-sm" style="margin:20px 0 0 0;">';
                                    if (!empty($shop_settings->bank_details)) {
                                        echo '<div class="text-center">';
                                        echo $shop_settings->bank_details;
                                        echo shop_form_open_multipart('manual_payment/' . $inv->id);
                                        echo '<input type="file" name="payment_receipt" id="file" class="file" />';
                                        echo '<label for="file" class="btn btn-default"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="17" viewBox="0 0 20 17"><path d="M10 0l-5.2 4.9h3.3v5.1h3.8v-5.1h3.3l-5.2-4.9zm9.3 11.5l-3.2-2.1h-2l3.4 2.6h-3.5c-.1 0-.2.1-.2.1l-.8 2.3h-6l-.8-2.2c-.1-.1-.1-.2-.2-.2h-3.6l3.4-2.6h-2l-3.2 2.1c-.4.3-.7 1-.6 1.5l.6 3.1c.1.5.7.9 1.2.9h16.3c.6 0 1.1-.4 1.3-.9l.6-3.1c.1-.5-.2-1.2-.7-1.5z"/></svg> <span>' . lang('select_file') . '&hellip;</span></label>';
                                        echo '<span id="submit-container">' . form_submit('upload', lang('upload'), 'id="upload-file" class="btn btn-theme"') . '</span>';
                                        echo form_close();
                                        echo '</div><hr class="divider or">';
                                    }
                                    $btn_code = '<div id="payment_buttons" class="text-center margin010">';
                                    ?>
                                    <div class="text-center">
                                        <a href="##" class="btn batre btn-theme make_payment_btn"><?= lang('complete_payment'); ?></a>
                                    </div>
                                    <?php
                                    $btn_code .= '<div class="clearfix"></div></div>';
                                    echo $btn_code;
                                    echo '</div>';
                                }
                                if ($inv->payment_status != 'paid' && $inv->attachment) {
                                    echo '<div class="alert alert-info" style="margin-top:15px;">' . lang('payment_under_review') . '</div>';
                                }
                                ?>

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
</section>


<script src="<?=$assets_url;?>js/extra/toastr.min.js"></script>
<!--<script src="--><?//=$assets_url;?><!--js/extra/utils.js"></script>-->


<script type="text/javascript">

    const pay_opt_url = 'https://app.slydepay.com.gh/api/merchant/invoice/payoptions';
    const pay_opt = [
        {
            "name": "MTN Mobile Money",
            "shortName": "MTN_MM",
            "maximumAmount": 999999999,
            "active": true,
            "reason": null,
            "logourl": "<?=$assets_url;?>images/mtn_mm.png"
        },
        {
            "name": "AIRTEL Mobile Money",
            "shortName": "AIRTEL_MM",
            "maximumAmount": 999999999,
            "active": true,
            "reason": null,
            "logourl": "<?=$assets_url;?>images/airtel_mm.png"
        },
        {
            "name": "TIGO Cash",
            "shortName": "TIGO_CASH",
            "maximumAmount": 999999999,
            "active": true,
            "reason": null,
            "logourl": "<?=$assets_url;?>images/tigo_mm.png"
        },
        {
            "name": "VODAFONE Cash",
            "shortName": "VODAFONE_CASH",
            "maximumAmount": 999999999,
            "active": true,
            "reason": null,
            "logourl": "<?=$assets_url;?>images/voda_mm.png"
        },
        {
            "name": "Visa Card",
            "shortName": "card",
            "maximumAmount": 999999999,
            "active": true,
            "reason": null,
            "logourl": "<?=$assets_url;?>images/visa.jpg"
        }
    ];
    const payment_method = '<?=$payment_method;?>';
    const weekly_payment = '<?=(!empty($installment_orders[0]))?$installment_orders[0]['weekly_pay'] : null;?>';
    const max_amount = '<?= (($return_sale ? ($inv->grand_total + $return_sale->grand_total) : $inv->grand_total) - ($return_sale ? ($inv->paid + $return_sale->paid) : $inv->paid)); ?>';

    var payment_option = null;
    var mobile_number = null;
    var chosen_amount_to_pay= max_amount;
    const sale_id = '<?=$inv->id;?>';
    var lookup_interval = null;

    $(document).on('click','.set_as_payotion',{passive:true},function ()    {
        const pay_option = $(this).attr('data-value');
        payment_option = pay_option;
        console.log(pay_option)
    });

    $(document).on('click','.finish_and_pay',{passive:true},function () {
        if(payment_method === 'installment'){
            if(validate_form('payment_mode_form')){
                chosen_amount_to_pay = $('#amount_to_pay').val();
                closeModal();
                $('.make_payment_btn').click();
            }
        }
        else{
            closeModal();
            $('.make_payment_btn').click();
        }
    });

    $(document).on('click','.make_payment_btn',{passive:true},function () {
       make_payment();
    });

    function make_payment(){
        show_loader();
        if(lookup_interval !== null){
            clearInterval(lookup_interval);
        }
        const reference_no = '<?=str_replace('/','_' , $inv->reference_no);?>';
        const amount = max_amount;
        const url = ''+site.base_url+'payment/make_payment/'+sale_id+'--'+reference_no+'/'+amount+'';

        $.get(url , {} , function (response) {
            show_loader('<b style="font-size: large">vous allez être redirigé automatiquement</b>'+response);
            setTimeout(function(){
                $('.cpButton').click();
            },300);

//            setTimeout(function(){
//                Swal.fire({html:'Vous allez etre redirigé',timer:3000});
//                Swal.showLoading();
//            },1000);
//
//            setTimeout(function(){
//                sweetDialog(response);
//            },3100);
        });

//        windowPopupCenter(url,'Paiement');
//        setTimeout(function(){
//            begin_lookup(sale_id);
//        },10000);
    }

    function make_payment_old(){
        if(lookup_interval !== null){
            clearInterval(lookup_interval);
        }
        const reference_no = '<?=str_replace('/','_' , $inv->reference_no);?>';
        const amount = 10;
        const url = ''+site.base_url+'shop/make_payment/'+sale_id+'--'+reference_no+'/'+amount+'';

        windowPopupCenter(url,'Paiement');

//        window.open(url,'_blank', 'directories=no,titlebar=no,toolbar=no,location=no,status=no,menubar=no,scrollbars=no,resizable=no,width=800, height=600');

        setTimeout(function(){
            begin_lookup(sale_id);
        },10000);
    }

    $(document).on('blur keyup','#amount_to_pay',{passive:true},function () {
        const min = Number($(this).attr('min'));
        const max = Number($(this).attr('max'));
        const val = Number($(this).val());
        const tag = $(this);
        const error_msg_tag = '#amount_to_pay_error_smg';

        $(error_msg_tag).remove();

        if(val >= min && val <=max){
            $(error_msg_tag).remove();
            $(tag).removeClass('is-invalid');
            $(tag).addClass('is-valid');
        }
        else{
            const error_msg = '<small id="amount_to_pay_error_smg" class="text-danger">Amount must be between '+min+' and '+max+'</small>';
            $(tag).after(error_msg);
            $(tag).removeClass('is-valid');
            $(tag).addClass('is-invalid');
        }
    });

    function show_pay_opt(){

        var html = '<form id="payment_mode_form"><div class="text-center">';

        if(payment_method === 'installment'){
            html = '<form id="payment_mode_form"><div class="text-center">' +
                '<div id="amount_panel">' +
                '   <label for="amount">Enter the amount you want to Pay</label>' +
                '   <input style="display: inline-block;max-width: 70%" type="text" min="'+weekly_payment+'" max="'+max_amount+'" id="amount_to_pay" value="'+weekly_payment+'" required name="amount_to_pay" class="form-control num-only" placeholder="Enter the amount you want to Pay" />' +
                '<button class="btn btn-warning" id="show_momo_panel" type=button">Next</button>' +
                '</div>' +
                '<hr>' +
                '<div id="momo_panel" style="display: none">';
        }


        html+= '<div><label for="mobile-number">Numéro de télephone :</label> <input class="from-control" id="mobile-number" type="text" required></div> <hr>';
        pay_opt.forEach(function (payment, index) {
            var link = '<label class="text-center m-r-9 hover-pointer" for="pay_'+payment.shortName+'">' +
                '<img class="dis-block b-r-4" height="50px" width="50px" src="'+payment.logourl+'" alt="'+payment.shortName+'" data-toggle="tooltip" data-placement="top" title="'+payment.name+'">' +
                '<input class="set_as_payotion" type="radio" name="payoption" id="pay_'+payment.shortName+'" data-value="'+payment.shortName+'" style="display: inline-block !important; width: 27px; height: 27px">' +
                '</label>';
            html+=link;
        });

        html+='<hr>';
        html+='<div class="text-center">' +
            '   <button type="button" class="btn btn-success btn-lg btn-block finish_and_pay">Pay</button>' +
            '</div>'



        if(payment_method === 'installment'){
            html+='<br><button class="btn btn-warning" id="show_payment_amount_panel" type=button">Back</button>' +
                '</div>' +
                '</div>' +
                '</form>';
        }
        else{
            html+= '</div>' +
                '</div>' +
                '</form>';
        }


        return html;
    }

    function begin_lookup(sale_id){
        console.log('begingin lookup');
        show_loader();
        lookup_interval = setInterval(function () {
            check_transaction_status(sale_id)
        },2000);
    }

    function check_transaction_status(sale_id){
        setTimeout(function(){
            const status = get_flashdata('payment_status_'+sale_id+'');
            console.log(status);
            if(status !== null){
                var html = '';
                var icon  = '';
                switch(status){
                    case '1': html = 'Paiement effectué avec succès' ;break;
                    case '2': html = 'Vous ne disposez pas d’assez de fond pour effectuer le paiement'; icon = 'success'; break;
                    case '3': html = 'Echec de l’opération veillez réessayer'; icon = 'error'; break;
                    default : html = 'Echec de l’opération veillez réessayer'; icon= 'error' ;break;
                }
                Swal.fire({
                    icon: icon,
                    title : html,
                    width: 600,
                    allowOutsideClick:false,
                    showCancelButton: false,
                    showConfirmButton:true,
                    confirmButtonText:'OK',
                    cancelButtonText: 'Ok',
                    reverseButtons: true,
                    padding: '3em',
                    backdrop: 'rgba(0,0,123,0.4)',
                    showCloseButton:false,
                    timer : 5000
                });

                setTimeout(function(){
                    refresh();
                },5000);
            }
        },500);
    }
</script>