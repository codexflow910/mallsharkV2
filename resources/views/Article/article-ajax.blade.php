

@foreach ($posts as $item)
        
    <div class="listing-blocks lightBlue " >
        <h2 class="listing-blocksheading">
        {{$item->title}}
        </h2>
        <p class="listing-blocksContent">
        {!! $item->description !!}
        </p>
        <?php
            $imgeUrl = "";
            $style = 'none';
            if (isset($item->blog_image) && $item->blog_image != "") {
                $style = 'block';
                $imgeUrl = getAttachmentUrlById((int) $item->blog_image, 'media');
                // echo 'Data: <pre>' .print_r($imgeUrl,true). '</pre>'; die();
            }
            ?>
        <button > Read More &#x2192;</button>
        @isset($item->blog_image)
            
        <div class="imageBlock">
            <img src="{{$imgeUrl}}" alt="" srcset="">
        </div>
        @endisset
    </div>

    @endforeach  