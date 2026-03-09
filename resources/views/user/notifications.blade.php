<?php
// $res = getmess();
?>
<!-- <li class="nav-item dropdown notifi-email">
    <a href="#" id="menu1" data-toggle="dropdown">
    <img src="{{ asset('assets/images/msg.svg',env('HTTPS_STATUS')) }}"
    class="img-fluid cart-icon-nv shake-icon" alt="user icon" title="cart icon" width="26px" />
        <span class="badge badge-success"><?php //  echo count($res); ?></span>
    </a>
    <div class="dropdown-menu left-position" id="">
        <ul>
            <?php
            // if (!empty($res)) {
            //     foreach ($res as $e) {
            //         $userData = getUserByIdchat($e);
            //         $url = URL::to('/chat/' . $userData->id);
                    ?>
                    <li>
                        <p>You have received message from <a href="#"
                                                             onclick="return popitup('<?php //echo $url; ?>')"
                                                             class="message-received"><?php //echo $userData->name; ?>

                            </a></p>
                    </li>

                    <?php
            //     }
            // } else {
                ?> 
                <li>No unread messages</li>
            <?php // } ?>
        </ul>
    </div>
</li> -->
<?php
$res = getuserNotifications();
$class = "";
if (isset($res['unread']) && $res['unread'] <= 0) {
    $class = "zero";
}
?>
<li class="nav-item dropdown notify notifi-message-nav <?php $class; ?>" id="new-noti">
    <a href="#" class="notify-anchor" id="" data-toggle="dropdown">
    <img src="{{ asset('assets/images/bell.svg',env('HTTPS_STATUS')) }}"
    class="img-fluid cart-icon-nv shake-icon" alt="user icon" title="cart icon" width="26px" />
        <span class="badge badge-success badge-num arrow-explore <?php echo $class; ?>" id="mark-read">
            <div class="entypo-bell" href="javascript:;"><?php echo isset($res['unread']) ? $res['unread'] : 0; ?></div>
        </span>                                   
    </a>
    <div class="dropdown-menu dropdown-menu-right cartmnu">
    <div class="cartmenu-notification">
        <span class="trangl"><img src="{{ URL::asset('assets/images/trangl.svg')}}"></span>
        <ul class="dropdown-menu-list ntfc" id="show-noti" onclick="event.stopPropagation();">

            <?php
            $counter = count($res['notification']);

            if ($counter > 0) {
                foreach ($res['notification'] as $nf) {
                    ?>
                    <li>
                        <div> <span class="time"><?php echo time_ago_in_php($nf['created_at']); ?></span> <span class="details">  <b> </b><a href="<?php echo $nf['url_link']; ?>"><?php echo $nf['subject']; ?></a>. </span> </div>
                    </li>
                    <?php
                }
                if ($counter >= 10) {
                    ?>
                    <li><a href="<?php echo URL::to('/all-notifications'); ?>">View all</a></li>
                    <?php
                }
            } else {
                ?>
                <li> <div><span class="details">No new notifications. </span> </div></li>
                <?php } ?>
        </ul>
    </div>
    </div>
</li>
<script>
  $(".msg-noti .notify-anchor").on('click', function () {
        var data = {};
        sendreq(data, 'user', 'read', 'callbackreq');
    });
function callbackreq() {
    $(".entypo-bell").html("0");
    $("#new-noti").addClass("zero");
}
</script>