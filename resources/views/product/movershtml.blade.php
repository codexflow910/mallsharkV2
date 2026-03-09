 <?php
    if(!empty($availablbeTransporter)){
    foreach ($availablbeTransporter as $at) { ?>
    <div class="alert-listing row">
        <div class="col-sm-3 no-padd-left">
            <p class="services-provider-names"><?php echo $at->name; ?></p>
        </div>
        <div class="col-sm-3 text-center">
            <p class="services-provider-number"><?php echo $at->contact_detail; ?></p>
        </div>
        <div class="col-sm-3 text-center">
            <p class="services-provider-number"><?php echo $at->email; ?></p>
        </div>
        <div class="col-sm-3 no-padd-right">
            <p class="services-provider-detial"><a href="<?php echo 'http://'.$at->website; ?>" target="_blank">View
                    Details</a></p>
        </div>
    </div>
    <?php
    }
    }else{?>
   <?php } ?>
</div>