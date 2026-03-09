<?php
$res = getmess();
?>
<li class="nav-item dropdown notifi-email" style="display:none;">
    <a href="#" id="menu1" data-toggle="dropdown">
    <img src="{{ asset('assets/images/msg.svg',env('HTTPS_STATUS')) }}"
    class="img-fluid cart-icon-nv shake-icon" alt="user icon" title="cart icon" width="26px" />
        <span class="badge badge-success"><?php echo count($res); ?></span>
    </a>
    <div class="dropdown-menu left-position" id="">
        <ul>
            <?php
            if (!empty($res)) {
                foreach ($res as $e) {
                    $userData = getUserByIdchat($e);
                    $url = URL::to('/chat/' . $userData->id);
                    ?>
                    <li>
                        <p>You have received message from <a href="#"
                                                             onclick="return popitup('<?php echo $url; ?>')"
                                                             class="message-received"><?php echo $userData->name; ?>

                            </a></p>
                    </li>

                    <?php
                }
            } else {
                ?> 
                <li>No unread messages</li>
            <?php } ?>
        </ul>
    </div>
</li>





<?php
$class = "";
if (isset($count) && $count <= 0) {
    $class = "zero";
}
?>
<li class="nav-item dropdown notify notifi-message-nav <?php echo $class; ?>" id="new-noti">
    <a href="#" class="notify-anchor" id="" data-toggle="dropdown">
    <img src="{{ asset('assets/images/bell.svg',env('HTTPS_STATUS')) }}"
    class="img-fluid cart-icon-nv shake-icon" alt="user icon" title="cart icon" width="26px" />
        <span class="badge badge-success badge-num arrow-explore" id="mark-read">
            <div class="entypo-bell" href="javascript:;"><?php echo!empty($count) ? $count : 0; ?></div>
        </span>                                   
    </a>
    <div class="dropdown-menu dropdown-menu-right">
        <ul class="dropdown-menu-list" id="show-noti" onclick="event.stopPropagation();">

            <?php
            if (isset($notification['notification'])) {
                $classStyle = "";
                foreach ($notification['notification'] as $nf) {
                    if ($nf['status'] == 0) {
                        $classStyle = "new-notifcation";
                    }
                    ?>
                    <li>
                        <div> <span class="time <?php echo $classStyle; ?>"><?php echo time_ago_in_php($nf['created_at']); ?></span> <span class="details">  <b></b><a href="<?php echo $nf['url_link']; ?>"><?php echo $nf['subject']; ?></a>. </span> </div>
                    </li>
                    <?php
                }
            } else {
                ?>
                <li>you have no notification yet</li>
            <?php } ?>
        </ul>
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