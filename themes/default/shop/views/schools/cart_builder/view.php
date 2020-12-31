<?php if(!$modal):;?>
<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title><?= $page_title . ' ' . lang('no') . ' ' . $inv->id; ?></title>
    <base href="<?= base_url() ?>"/>
    <meta http-equiv="cache-control" content="max-age=0"/>
    <meta http-equiv="cache-control" content="no-cache"/>
    <meta http-equiv="expires" content="0"/>
    <meta http-equiv="pragma" content="no-cache"/>
    <link rel="shortcut icon" href="<?= $assets ?>images/icon.png"/>
    <link rel="stylesheet" href="<?= $assets ?>styles/theme.css" type="text/css"/>
    <style type="text/css" media="all">
        body {
            color: #000;
        }

        #wrapper {
            max-width: 480px;
            margin: 0 auto;
            padding-top: 20px;
        }

        .btn {
            border-radius: 0;
            margin-bottom: 5px;
        }

        .bootbox .modal-footer {
            border-top: 0;
            text-align: center;
        }

        h3 {
            margin: 5px 0;
        }

        .order_barcodes img {
            float: none !important;
            margin-top: 5px;
        }

        @media print {
            .no-print {
                display: none;
            }

            #wrapper {
                max-width: 480px;
                width: 100%;
                min-width: 250px;
                margin: 0 auto;
            }

            .no-border {
                border: none !important;
            }

            .border-bottom {
                border-bottom: 1px solid #ddd !important;
            }

            table tfoot {
                display: table-row-group;
            }
        }
    </style>
</head>
<body>
<?php endif;?>
<div id="wrapper">
    <div id="receiptData">
        <div id="receipt-data">
            <div class="text-center">
                <?= !empty($biller->logo) ? '<img src="' . base_url('assets/uploads/logos/' . $biller->logo) . '" alt="">' : ''; ?>
                <h3 style="text-transform:uppercase;"><?= $biller->company && $biller->company != '-' ? $biller->company : $biller->name; ?></h3>
                <?php
                echo '<p>' . $biller->address . ' ' . $biller->city . ' ' . $biller->postal_code . ' ' . $biller->state . ' ' . $biller->country .
                    '<br>' . lang('tel') . ': ' . $biller->phone;
                ?>
            </div>
            <div>
                <p>Ecole : <strong><?=$school->name;?></strong></p>
                <p>Classe : <strong><?=$class->level;?></strong></p>
            </div>

            <div class="col-sm-12 text-center border-bottom">
                <h4 style="font-weight:bold;"><?=$note;?></h4>
            </div>
            <div style="clear:both;"></div>
            <table class="table table-condensed ">
                <tbody>
                <?php
                $r = 1;
                $category = 0;
                $tax_summary = [];
                foreach ($rows as $row) {
                    if ($pos_settings->item_order == 1 && $category != $row->category_id) {
                        $category = $row->category_id;
                        echo '<tr><td colspan="100%" class="no-border"><strong>' . $row->category_name . '</strong></td></tr>';
                    }
                    echo '<tr><td colspan="2" class="no-border">#' . $r . ': &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<strong>' . product_name($row->product_name, ($printer ? $printer->char_per_line : null)) . ($row->variant ? ' (' . $row->variant . ')' : '') . '</strong><span class="pull-right">' . ($row->tax_code ? '*' . $row->tax_code : '') . '</span></td></tr>';
                    if (!empty($row->second_name)) {
                        echo '<tr><td colspan="2" class="no-border">' . $row->second_name . '</td></tr>';
                    }
                    echo '<tr><td class="no-border border-bottom">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.$this->sma->formatQuantity($row->unit_quantity).' &nbsp;&nbsp;&nbsp;X &nbsp;&nbsp;&nbsp;'. $this->sma->formatMoney($row->unit_price) . ($row->item_tax != 0 ? ' - ' . lang('tax') . ' <small>(' . ($Settings->indian_gst ? $row->tax : $row->tax_code) . ')</small> ' . $this->sma->formatMoney($row->item_tax) . ($row->hsn_code ? ' (' . lang('hsn_code') . ': ' . $row->hsn_code . ')' : '') : '') . '</td><td class="no-border border-bottom text-right">' . $this->sma->formatMoney($row->subtotal) . '</td></tr>';

                    $r++;
                }
                if ($return_rows) {
                    echo '<tr class="warning"><td colspan="100%" class="no-border"><strong>' . lang('returned_items') . '</strong></td></tr>';
                    foreach ($return_rows as $row) {
                        if ($pos_settings->item_order == 1 && $category != $row->category_id) {
                            $category = $row->category_id;
                            echo '<tr><td colspan="100%" class="no-border"><strong>' . $row->category_name . '</strong></td></tr>';
                        }
                        echo '<tr><td colspan="2" class="no-border">#' . $r . ': &nbsp;&nbsp;' . product_name($row->product_name, ($printer ? $printer->char_per_line : null)) . ($row->variant ? ' (' . $row->variant . ')' : '') . '<span class="pull-right">' . ($row->tax_code ? '*' . $row->tax_code : '') . '</span></td></tr>';
                        echo '<tr><td class="no-border border-bottom">' . $this->sma->formatQuantity($row->unit_quantity) . ' x ' . $this->sma->formatMoney($row->unit_price) . ($row->item_tax != 0 ? ' - ' . lang('tax') . ' <small>(' . ($Settings->indian_gst ? $row->tax : $row->tax_code) . ')</small> ' . $this->sma->formatMoney($row->item_tax) . ($row->hsn_code ? ' (' . lang('hsn_code') . ': ' . $row->hsn_code . ')' : '') : '') . '</td><td class="no-border border-bottom text-right">' . $this->sma->formatMoney($row->subtotal) . '</td></tr>';

                        // echo '<tr><td class="no-border border-bottom">' . $this->sma->formatQuantity($row->quantity) . ' x ';
                        // if ($row->item_discount != 0) {
                        //     echo '<del>' . $this->sma->formatMoney($row->net_unit_price + ($row->item_discount / $row->quantity) + ($row->item_tax / $row->quantity)) . '</del> ';
                        // }
                        // echo $this->sma->formatMoney($row->net_unit_price + ($row->item_tax / $row->quantity)) . '</td><td class="no-border border-bottom text-right">' . $this->sma->formatMoney($row->subtotal) . '</td></tr>';
                        $r++;
                    }
                }

                ?>
                </tbody>
                <tfoot>
                <tr>
                    <th><?= lang('total'); ?></th>
                    <th class="text-right"><?= $this->sma->formatMoney($return_sale ? (($inv->total + $inv->product_tax) + ($return_sale->total + $return_sale->product_tax)) : ($inv->total + $inv->product_tax)); ?></th>
                </tr>
                <?php
                if ($inv->order_tax != 0) {
                    echo '<tr><th>' . lang('tax') . '</th><th class="text-right">' . $this->sma->formatMoney($return_sale ? ($inv->order_tax + $return_sale->order_tax) : $inv->order_tax) . '</th></tr>';
                }
                if ($inv->order_discount != 0) {
                    echo '<tr><th>' . lang('order_discount') . '</th><th class="text-right">' . $this->sma->formatMoney($inv->order_discount) . '</th></tr>';
                }

                if ($inv->shipping != 0) {
                    echo '<tr><th>' . lang('shipping') . '</th><th class="text-right">' . $this->sma->formatMoney($inv->shipping) . '</th></tr>';
                }

                if ($return_sale) {
                    if ($return_sale->surcharge != 0) {
                        echo '<tr><th>' . lang('order_discount') . '</th><th class="text-right">' . $this->sma->formatMoney($return_sale->surcharge) . '</th></tr>';
                    }
                }


                if ($pos_settings->rounding || $inv->rounding != 0) {
                    ?>
                    <tr>
                        <th><?= lang('rounding'); ?></th>
                        <th class="text-right"><?= $this->sma->formatMoney($inv->rounding); ?></th>
                    </tr>
                    <?php
                }
                ?>
                </tfoot>
            </table>
        </div>

        <div class="order_barcodes text-center">
            <?= $this->sma->qrcode('link', urlencode(admin_url('sales/view/' . $inv->id)), 2); ?>
        </div>
        <div style="clear:both;"></div>
    </div>

    <div id="buttons" style="padding-top:10px; text-transform:uppercase;" class="no-print text-center">
        <?php
        if ($pos->remote_printing == 1) {
            echo '<button onclick="window.print();" class="btn btn-primary"><i class="fa fa-print"></i> ' . lang('print') . ' </button>';
        } else {
            echo '<button onclick="return printReceipt()" class="btn btn-block btn-primary"><i class="fa fa-print"></i> ' . lang('print') . '</button>';
            echo '<button onclick="return openCashDrawer()" class="btn btn-block btn-default"><i class="fa fa-print"></i> ' . lang('open_cash_drawer') . ' </button>';
        } ?>
            <button class="btn btn-success" id="email"><i class="fa fa-envelope"></i> <?= lang('Envoyer par Mail'); ?></button>
            <button  class="btn btn-warning sus_sale2" id="<?=$sus_id;?>"><i class="fa fa-cart-plus"></i> <?= lang('Ajouter la liste au panier'); ?></button>


        <div style="clear:both;"></div>
    </div>
</div>

