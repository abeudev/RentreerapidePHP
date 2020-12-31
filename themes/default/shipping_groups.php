<div class="container" style="max-width: 30em">
    <?php $attrib = ['class'=>'ajax-form']; echo admin_form_open('shop_settings/shipping_settings/set_shipping_fee', $attrib); ?>
    <div class="row">
        <table id="zero_config" class="text-center table table-striped table-bordered">
            <thead>
            <tr>
                <th>Group</th>
                <th>Shipping fee</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach($groups as $group):?>
                <tr>
                    <td><?=strtoupper($group->group_name)?></td>
                    <td>
                        <div class="input-group">
                            <input type="text" name="sheeping_<?=$group->id;?>" value="<?=(!empty($group_fees["group_{$group->id}"]))?$group_fees["group_{$group->id}"]:'';?>" class="form-control" required>
                            <span class="input-group-addon">GHC</span>
                        </div>

                    </td>
                </tr>
            <?php endforeach;?>
            </tbody>
        </table>
        <input type="hidden" name="region_id" value="<?=$region_id;?>">
        <div class="col-sm-12 text-right">
            <button type="submit" class="btn btn-primary">Save</button>
        </div>
    </div>
    </form>

</div>