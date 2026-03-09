<?php
 if (isset($name)) {
    $singleCategory = $singleCategory1;
    $imageUrl = isset($singleCategory->image_id) && !empty($singleCategory->image_id) ? getAttachmentUrlById($singleCategory->image_id) : '';
    // Set defaults if meta title or description are missing
    $metaTitle = isset($singleCategory->meta_title) ? $singleCategory->meta_title : 'Shop Mallshark - Find Your Online Products';
    $metaDescription = isset($singleCategory->meta_description) ? $singleCategory->meta_description : 'Discover a wide range of products on Mallshark. Browse through the best options available online, perfect for your needs. Shop now and enjoy our secure payment options.';
    ?>

    <meta property="og:title" content="{{$metaTitle}}">
    <meta property="og:description" content="{{$metaDescription}}">
    <meta property="og:image" content="{{isset($imageUrl) ? $imageUrl: '' }}">
    <meta name="description" content="{{$metaDescription}}">
    <meta name="twitter:title" content="{{$metaTitle}}">
    <meta name="twitter:description" content="{{$metaDescription}}">
    <meta property="twitter:image" content="{{isset($imageUrl) ? $imageUrl: '' }}">

<?php } else { 
    $metaKeyword = isset($_GET['keyword']) ? $_GET['keyword'] : '';
    ?>
    <meta property="og:title" content="{{$metaKeyword != '' ? $metaKeyword : 'Shop Mallshark - Find Your Online Products'}}">
    <meta property="og:description" content="{{$metaKeyword != '' ? "$metaKeyword - Find a $metaKeyword" : 'Discover a wide range of products on Mallshark. Browse through the best options available online, perfect for your needs. Shop now and enjoy our secure payment options.'}}">
    <meta property="og:image" content="https://dev.mallshark.net/assets/images/MallShark-Beta-new.svg">
    <meta name="keywords" content="{{$metaKeyword}}">
    <meta name="description" content="{{$metaKeyword != '' ? "$metaKeyword - Find a $metaKeyword" : 'Discover a wide range of products on Mallshark. Browse through the best options available online, perfect for your needs. Shop now and enjoy our secure payment options.'}}">
    <meta name="twitter:title" content="{{$metaKeyword != '' ? $metaKeyword : 'Shop Mallshark - Find Your Online Products'}}">
    <meta name="twitter:description" content="{{$metaKeyword != '' ? "$metaKeyword - Find a $metaKeyword" : 'Discover a wide range of products on Mallshark. Browse through the best options available online, perfect for your needs. Shop now and enjoy our secure payment options.'}}">
    <meta property="twitter:image" content="https://dev.mallshark.net/assets/images/MallShark-Beta-new.svg">
<?php } ?>
<meta property="og:url" content="{{url()->full()}}">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:url" content="{{Request::url()}}">
