

<?php
$count = 0;

foreach ($storeIcons as $si) {
    $chk = "";
    if ($count == 0) {
        $chk = "checked";
    }
    ?>
    <label class="smlicn" data-toggle="tooltip"  data-placement="top" title="<?php echo $si->name; ?>">  
        <input type="radio" name="icon-pack" data-icon-name="<?php echo $si->name; ?>" data-icon-description="<?php echo $si->description; ?>" value="<?php echo $si->icon_id; ?>" <?php echo $chk; ?> />
        <img src="<?php echo getAttachmentUrlById((int) $si->image_id, $size = 'icons') ?>" alt="icon image" title="icon image">
    </label>
    <?php
    $count++;
}
?>
<div class="">
    <?php
    $q = [];
    if (isset($_GET['status'])) {
        $q['status'] = $_GET['status'];
    }
    if (isset($_GET['order'])) {
        $q['order'] = $_GET['order'];
    }
    echo $storeIcons->appends($q)->links('pagination.defaultpagination');
    ?>
</div>