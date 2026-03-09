
    <?php
    if (!empty($productData)) {
        foreach ($productData as $pd) {

            ?>
            <a style="text-decoration: none;" href="/admin/sale-report?type=<?php echo isset($_GET['type']) ? $_GET['type'] : 'yearly'; ?>&product_id=&date_to=<?php echo isset($_GET['date_to']) ? $_GET['date_to'] : ''; ?>&date_from=<?php echo isset($_GET['date_from']) ? $_GET['date_from'] : ''; ?>&product_id=<?php echo!empty($pd->product_id) ? $pd->product_id : ""; ?>&flashsale=<?php echo!empty($_GET['flashsale']) ? 'on' : ""; ?>&product_name=<?php echo!empty($pd->title) ? $pd->title : ""; ?>" class="" data-store-id="<?php echo!empty($pd->product_id) ? $pd->product_id : ""; ?>">
                <div class="row rptstr active pl-0">
                    <div class="col-md-3 pr-0">
                    <input type="checkbox" class="img-checkbox" style="position:absolute; top:0px; left:0px;" value="{{$pd->product_id ? $pd->product_id : ''}}"  {{ request()->filled('product_ids') ? (in_array($pd->product_id, explode(',',request()->get('product_ids'))) ? 'checked' : '') : '' }}>    
                    <img src="<?php echo Packages\Order\Contracts\V2\OrderHelpers::getOrderItemImage($pd->item_image); ?>" alt="<?php echo!empty($pd->title) ? $pd->title : ""; ?>"  title="<?php echo!empty($pd->title) ? $pd->title : ""; ?>" onerror="this.onerror=null;this.src='<?php echo URL::asset('assets/images/default-store.png'); ?>';" class="img-fluid" /></div>
                    <div class="col-md-9 pl-0">
                        <p class="mb-0">&nbsp; <?php echo!empty($pd->title) ? $pd->title : ""; ?></p>
                        <?php
                        $parms = array();
                        $parms['type'] = isset($_GET['type']) ? $_GET['type'] : "yearly";
                        $parms['date_to'] = isset($_GET['date_to']) ? $_GET['date_to'] : "";
                        $parms['date_from'] = isset($_GET['date_from']) ? $_GET['date_from'] : "";
                        $parms['flashsale'] = isset($_GET['flashsale']) ? $_GET['flashsale'] : "";
                        //  print_rr($storeTotalSale);
                        ?>
                        <p class="txt-clr-blue mb-0">&nbsp;Sales: $<?php echo!empty($pd->total_sale) ? number_format($pd->total_sale) : 0; ?>  </p></div> 
                </div>
            </a>
        <?php } ?>
    <?php } else { ?>
        No record found..
    <?php } ?>