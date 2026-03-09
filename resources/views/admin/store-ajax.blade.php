
    <?php
    if (!empty($storeData)) {
        foreach ($storeData as $sd) {
            ?>
            <a style="text-decoration: none;" href="/admin/sale-report?type=<?php echo isset($_GET['type']) ? $_GET['type'] : 'yearly'; ?>&date_to=<?php echo request()->get('date_to', '')  ?>&date_from=<?php echo request()->get('date_from', '')  ?>&store_id=<?php echo!empty($sd->store_id) ? $sd->store_id : ""; ?>" class="" data-store-id="<?php echo!empty($sd->store_id) ? $sd->store_id : ""; ?>">
                <div class="row rptstr active pl-0">
                    <div class="col-md-3 pr-0">
                    <input type="checkbox" class="img-checkbox" style="position:absolute; top:0px; left:0px;" value="{{$sd->store_id ? $sd->store_id : ''}}"  {{ request()->filled('store_ids') ? (in_array($sd->store_id, explode(',',request()->get('store_ids'))) ? 'checked' : '') : '' }}>    
                    <img src="<?php echo getAttachmentUrlById((int) $sd->store_logo, 'small'); ?>" alt="<?php echo!empty($sd->store_name) ? $sd->store_name : ""; ?>"  title="<?php echo!empty($sd->store_name) ? $sd->store_name : ""; ?>" onerror="this.onerror=null;this.src='<?php echo URL::asset('assets/images/default-store.png'); ?>';" class="img-fluid" /></div>
                    <div class="col-md-9 pl-0">
                        <p class="mb-0">&nbsp; <?php echo!empty($sd->store_name) ? $sd->store_name : ""; ?></p>
                        <?php
                        $parms = array();
                        $parms['type'] = isset($_GET['type']) ? $_GET['type'] : "yearly";
                        $parms['date_to'] = isset($_GET['date_to']) ? $_GET['date_to'] : "";
                        $parms['date_from'] = isset($_GET['date_from']) ? $_GET['date_from'] : "";
                        $parms['flashsale'] = isset($_GET['flashsale']) ? $_GET['flashsale'] : "";
                        $storeTotalSale = getStoreSale($sd->store_id, $parms);
                        //  print_rr($storeTotalSale);
                        ?>
                        <p class="txt-clr-blue mb-0">&nbsp;Sales: $<?php echo!empty($storeTotalSale['store_sale']) ? number_format($storeTotalSale['store_sale']) : 0; ?>  </p></div> 
                </div>
            </a>
        <?php } ?>
    <?php } else { ?>
        No record found..
    <?php } ?>