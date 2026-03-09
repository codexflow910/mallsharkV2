<?php
if (Session::has('userData')) { 
    $userId = isset(Session::get('userData')['user_id']) ? Session::get('userData')['user_id']:'';
$res = getuserNotifications();
$counter = count($res['notification']);
$class = "";
if (isset($res['unread']) && $res['unread'] <= 0) {
    $class = "zero";
}
?>
<li class="nav-item dropdown noman">
    <a class="nav-link  left-minu110 notify-anchor" href="#" id="navbardrop1" data-toggle="dropdown">
        <img class="headermobileicon" src="<?php echo asset('assets/images/bell.svg') ?>"
            alt="cart icon" title="cart icon" width="36" height="34" alt="alert" />
        <!-- <span class="badge badge-primary" id="cart_counter"><?php //echo $counter; ?></span> -->

        <span class="badge badge-success badge-num arrow-explore <?php echo $class; ?>" id="mark-read">
            <div class=" entypo-bell" href="javascript:;"><?php echo isset($res['unread']) ? $res['unread'] : 0; ?></div>
        </span>  
    </a>
    <div class="dropdown-menu cartmnu" id="navbardrop-dropdown">
        <div class="cartmenu-notification">
            <span class="trangl"><img
                    src="{{ URL::asset('assets/images/trangl.svg')}}"></span>
            <h5 style="margin-bottom:3px">Notifications</h5>
            <ul class="ntfc">
                <?php
            if ($counter > 0) {
                foreach ($res['notification'] as $nf) { ?>

                <li><span> <a
                            href="<?php echo $nf['url_link']; ?>"><?php  echo $nf['subject'];  ?></a></span>
                    <span><?php  echo time_ago_in_php($nf['created_at']); ?></span></li>

                <?php }
             if ($counter >= 10) {
                ?>
                <li><a class="btn btn-primary"
                        href="<?php echo URL::to('/all-notifications'); ?>">View all</a>
                </li>
                <?php
            }
            }else{ ?>
                <li>
                    <p>you have no message.</p>
                </li>
                <?php }
            ?>

            </ul>
        </div>
    </div>
</li>

     
<?php   
} ?>
<script>
  $(".notify-anchor").on('click', function () {
        var data = {};
        sendreq(data, 'user', 'read', 'callbackreq');
    });
function callbackreq() {
    $(".entypo-bell").html("0");
    $("#new-noti").addClass("zero");
}
</script>