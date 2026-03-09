<div class="table-responsive">

    <?php
    if (!empty($resutlt)) {
        ?>
        <table class="table table-striped">
            <thead>
                <tr>
                    <?php if (!empty($resutlt[0]->attribute1)) { ?>
                        <th scope="col">{{ucfirst($resutlt[0]->attribute1)}}</th>
                    <?php } ?>
                    <?php if (!empty($resutlt[0]->attribute2)) { ?>
                        <th scope="col">{{ucfirst($resutlt[0]->attribute2)}}</th>
                    <?php } ?>
                    <?php if (!empty($resutlt[0]->attribute3)) { ?>
                        <th scope="col">{{ucfirst($resutlt[0]->attribute3)}}</th>
    <?php } ?>
                    <th scope="col">Price</th>
                    <th scope="col">Current Stock</th>
                    <th scope="col">Sku</th>
                    <th scope="col">Sales</th>
                  

                </tr>
            </thead>
            <tbody>
                    <?php foreach ($resutlt as $rs) { ?>
                    <tr>
                        <?php if (!empty($rs->option1)) { ?>
                            <td>{{$rs->option1}}</td>
                        <?php } ?>
                        <?php if (!empty($rs->option2)) { ?>
                            <td>{{$rs->option2}}</td>
                        <?php } ?>
                        <?php if (!empty($rs->option3)) { ?>
                            <td>{{$rs->option3}}</td>
                        <?php } ?>
                        <td><?php echo!empty($rs->price) ? $rs->price : ''; ?></td>
                        <td><?php echo!empty($rs->current_stock) ? $rs->current_stock : 0; ?></td>
                        <td><?php echo!empty($rs->sku) ? $rs->sku : 0; ?></td>
                        <td><?php echo!empty($rs->sales) ? $rs->sales : 0; ?></td>
<!--                        <td>&#36;5</td>
                        <td>4644374</td>
                        <td>
                            <label class="form-check-label container-check">
                                <input class="form-check-input" type="checkbox" name="chkrememberme">
                                <span class="checkmark"></span>
                            </label>
                        </td>    -->
                    </tr>
    <?php } ?>
            </tbody>
        </table>
    </div>
<?php }
?>