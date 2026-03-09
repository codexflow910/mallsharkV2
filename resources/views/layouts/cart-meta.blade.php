<meta property="og:title" content="<?php if(isset($title) ? 'Cart': 'Cart');  ?>">
<meta property="og:description" content=" <?php if(isset($short_description) ? $short_description: '');  ?>">
<meta property="og:type" content="product" />
<meta property="og:url" content="{{url()->current()}}">
<meta property="og:image" content="<?php if(isset($featureImage) ? $featureImage: '');  ?> ">
<meta name="twitter:card" content="summary_large_image">
<link rel='canonical' href="{{url()->current()}}" />