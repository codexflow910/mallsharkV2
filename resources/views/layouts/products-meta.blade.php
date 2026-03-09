<?php 
$store_info = getStoreInfoById($productDetail['store_id']); 
$storename =  $storeInfo[0]->store_name;
$description = $PshortDes;
$keyword = ''.$productDetail['title'].', buy '.$productDetail['title'].'';
$featureImage = getAttachmentUrlById($featureImage);
?>


<meta property="og:site_name" content="MallShark">
<meta property="og:title" content="{{$productDetail['title']}}">
<meta property="og:description" content="{{$description}}">
<meta property="og:type" content="product" />
<meta property="og:url" content="{{URL::to('product/'.$productDetail['slug'])}}">
<meta property="og:image" content="{{isset($featureImage) ? $featureImage: '' }}">
<meta property="og:image:secure_url" content="{{isset($featureImage) ? $featureImage : ''}}">
<meta property="og:image:width" content="620">
<meta property="og:image:height" content="860">
<meta property="og:price:amount" content="{{$productDetail['price']}}">
<meta property="og:price:currency" content="USD">


<meta name="twitter:site" content="@https://twitter.com/ShopMallShark">
<meta name="twitter:title" content="{{$productDetail['title']}}">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:description" content="{{$description}}">
<meta name="twitter:url" content="{{URL::to('product/'.$productDetail['slug'])}}">
<meta property="twitter:image" content="{{isset($featureImage) ? $featureImage: '' }}">
<meta name="twitter:card" content="summary">
<link rel='canonical' href="{{url()->current()}}" />