@extends('layouts.dashboard-main')

@section('content')
<!-- Bootstrap 4 CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
.video-card { 
    transition: transform .2s; 
    display: flex; 
    flex-direction: column; 
    justify-content: space-between;
}
.video-card:hover { 
    transform: translateY(-3px); 
    box-shadow: 0 4px 16px rgba(0,0,0,.08); 
}

/* FIXED: Better TikTok embed container */
.video-container { 
    position: relative; 
    width: 100%; 
    padding-top: 177.78%; /* 9:16 aspect ratio (16/9 * 100) */
    background: #f8f9fa;
    overflow: hidden;
}

.video-container .embed-wrapper {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
}

/* TikTok blockquote styling */
.video-container blockquote.tiktok-embed {
    margin: 0 !important;
    max-width: none !important;
    min-width: 100% !important;
}

/* Product preview styling */
.product-preview {
    margin-top: 10px;
    padding: 10px;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    background-color: #f8f9fa;
    display: none;
}

.product-preview.show {
    display: block;
}

.product-info {
    display: flex;
    align-items: center;
    gap: 15px;
}

.product-info img {
    width: 80px;
    height: 80px;
    object-fit: cover;
    border-radius: 4px;
}

.product-info-details h5 {
    margin: 0 0 5px 0;
    font-size: 14px;
    font-weight: 600;
}

.product-info-details p {
    margin: 0;
    font-size: 13px;
    color: #6c757d;
}

.product-info-details .price {
    color: #28a745;
    font-weight: 600;
    font-size: 16px;
}

.product-loading {
    text-align: center;
    padding: 10px;
}
</style>

<div class="container-fluid">
    <div class="row my-3">
        <div class="col d-flex justify-content-between align-items-center">
            <h4 class="mb-0">TikTok Videos</h4>
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-success mr-2" id="addNewVideo">
                    <i class="fas fa-plus"></i> Add New Video
                </button>
                <button type="button" class="btn btn-primary" id="saveSelected">
                    <i class="fas fa-save"></i> Save Selected
                </button>
            </div>
        </div>
    </div>
    <div class="mb-3">
        <div class="custom-control custom-checkbox">
            <input type="checkbox" class="custom-control-input" id="selectAll">
            <label class="custom-control-label" for="selectAll">Select All</label>
        </div>
    </div>

    <form id="tiktokVideosForm" method="POST" action="{{ route('admin.tiktok.videos.save') }}">
        @csrf
        <div class="row d-flex flex-wrap align-items-stretch">
            @forelse($results as $video)
                <div class="col-md-4 mb-4 d-flex">
                    <div class="card h-100 video-card flex-fill">
                        <div class="card-header d-flex align-items-center justify-content-between py-2">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox"
                                       class="custom-control-input video-checkbox"
                                       id="video_{{ $video->id }}"
                                       name="selected_videos[]"
                                       value="{{ $video->id }}"
                                       {{ !empty($video->is_selected) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="video_{{ $video->id }}">Select</label>
                            </div>

                            <button type="button"
                                    class="btn btn-sm btn-outline-primary edit-video-btn"
                                    data-video-id="{{ $video->id }}"
                                    data-embed-code="{{ htmlspecialchars($video->embed_code ?? '', ENT_QUOTES) }}"
                                    data-product-link="{{ $video->product_link ?? '' }}">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                        </div>

                        <div class="card-body">
                            <div class="video-container mb-3">
                                @php
                                    $embedCode = $video->embed_code ?? null;
                                    $url = $video->url ?? null;
                                    
                                    // If no embed code, try to get it from oEmbed
                                    if (!$embedCode && $url) {
                                        try {
                                            $oembed = Http::get('https://www.tiktok.com/oembed', ['url' => $url])->json();
                                            $embedCode = $oembed['html'] ?? null;
                                        } catch (\Exception $e) {
                                            $embedCode = null;
                                        }
                                    }
                                @endphp

                                @if($embedCode)
                                    <div class="embed-wrapper" data-tiktok-embed>
                                        {!! $embedCode !!}
                                    </div>
                                @elseif($url)
                                    <div class="alert alert-light small mb-0 text-center" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 90%;">
                                        Unable to load embed. <a href="{{ $url }}" target="_blank">View on TikTok</a>
                                    </div>
                                @else
                                    <div class="alert alert-light small mb-0 text-center" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 90%;">
                                        No video data available.
                                    </div>
                                @endif
                            </div>

                            @if(!empty($video->description))
                                <p class="mb-2">
                                    <strong>Description:</strong>
                                    {{ \Illuminate\Support\Str::limit($video->description, 120) }}
                                </p>
                            @endif

                            @if(isset($video->likes) || isset($video->views))
                                <p class="text-muted small mb-0">
                                    @if(isset($video->likes))
                                        <strong>Likes:</strong> {{ number_format((int) $video->likes) }}
                                    @endif
                                    @if(isset($video->views))
                                        <span class="ml-2"><strong>Views:</strong> {{ number_format((int) $video->views) }}</span>
                                    @endif
                                </p>
                            @endif
                        </div>

                        <div class="card-footer text-muted py-2">
                            <small id="product_link_display_{{ $video->id }}">
                                @if(!empty($video->product_link))
                                    <i class="fas fa-link"></i>
                                    Linked:
                                    <a href="{{ $video->product_link }}" target="_blank">
                                        {{ \Illuminate\Support\Str::limit($video->product_link, 40) }}
                                    </a>
                                @else
                                    No product link
                                @endif
                            </small>
                        </div>

                        <input type="hidden"
                               name="embed_codes[{{ $video->id }}]"
                               id="hidden_embed_{{ $video->id }}"
                               value="{{ htmlspecialchars($video->embed_code ?? '', ENT_QUOTES) }}">
                        
                        <input type="hidden"
                               name="product_links[{{ $video->id }}]"
                               id="hidden_link_{{ $video->id }}"
                               value="{{ $video->product_link ?? '' }}">
                    </div>
                </div>
            @empty
                <div class="col-12">
                    <div class="alert alert-info mb-0">
                        <i class="fas fa-info-circle"></i> No TikTok videos found.
                    </div>
                </div>
            @endforelse
        </div>

        @if(method_exists($results, 'links'))
            <div class="row">
                <div class="col-12 d-flex flex-column align-items-center">
                    <div class="mb-2 text-muted small">
                        Page {{ $results->currentPage() }} of {{ $results->lastPage() }}
                    </div>
                    {{ $results->onEachSide(2)->links('pagination::bootstrap-4') }}
                </div>
            </div>
        @endif
    </form>
</div>

<!-- Modal: Add/Edit Video -->
<div class="modal fade" id="videoModal" tabindex="-1" role="dialog" aria-labelledby="videoModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header py-2">
        <h6 class="modal-title" id="videoModalLabel">Add TikTok Video</h6>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body pt-3">
        <input type="hidden" id="modalVideoId" value="">
        
        <div class="form-group mb-3">
            <label for="modalEmbedCode" class="mb-1">
                <strong>TikTok Embed Code</strong>
                <i class="fas fa-info-circle text-muted ml-1" 
                   data-toggle="tooltip" 
                   title="Paste the embed code from TikTok (e.g., <blockquote class='tiktok-embed'...>)"></i>
            </label>
            <textarea class="form-control" 
                      id="modalEmbedCode" 
                      rows="6" 
                      placeholder='<blockquote class="tiktok-embed" cite="https://www.tiktok.com/@user/video/123" data-video-id="123">...</blockquote>'></textarea>
            <small class="form-text text-muted">
                Go to TikTok video → Click Share → Click Embed → Copy the code and paste it here
            </small>
        </div>

        <div class="form-group mb-3">
            <label for="modalProductLink" class="mb-1"><strong>Product Link</strong></label>
            <input type="url" 
                   class="form-control" 
                   id="modalProductLink" 
                   placeholder="https://example.com/product/123">
            <small class="form-text text-muted">Enter the product URL you want to link with this video</small>
            
            <!-- Product Preview Container -->
            <div id="productPreview" class="product-preview">
                <!-- Product info will be injected here -->
            </div>
        </div>

        <div class="custom-control custom-checkbox mt-2">
            <input type="checkbox" class="custom-control-input" id="modalAlsoSelect">
            <label class="custom-control-label" for="modalAlsoSelect">Also select this video</label>
        </div>
      </div>
      <div class="modal-footer py-2">
        <button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button>
        <button type="button" id="saveVideoBtn" class="btn btn-primary">
            <i class="fas fa-save"></i> Save Video
        </button>
      </div>
    </div>
  </div>
</div>

<!-- jQuery 3.x (required by Bootstrap 4) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJHvC4Jv8bm7TCDuaLy/Pn2vMl8dOGRFDe4mM=" crossorigin="anonymous"></script>
<!-- Popper.js (required by Bootstrap 4 dropdowns/modals) -->
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
<!-- Bootstrap 4 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.min.js" integrity="sha384-+sLIOodYLS7CIrQpBjl+C7nPvqq+FbNUBDunl/OZv93DB7Ln/533i8e/mZXLi/P+" crossorigin="anonymous"></script>

<script>
$(function () {
    // Initialize tooltips
    $('[data-toggle="tooltip"]').tooltip();

    // Select/Deselect all
    $('#selectAll').on('change', function () {
        $('.video-checkbox').prop('checked', $(this).is(':checked'));
    });

    // Open modal for adding new video
    $('#addNewVideo').on('click', function () {
        $('#modalVideoId').val('');
        $('#videoModalLabel').text('Add TikTok Video');
        $('#modalEmbedCode').val('');
        $('#modalProductLink').val('');
        $('#productPreview').removeClass('show').html('');
        $('#modalAlsoSelect').prop('checked', true);
        $('#videoModal').modal('show');
    });

    // Open modal for editing existing video
    $(document).on('click', '.edit-video-btn', function () {
        const videoId = $(this).data('video-id');
        const embedCode = $(this).data('embed-code') || $('#hidden_embed_' + videoId).val() || '';
        const productLink = $(this).data('product-link') || $('#hidden_link_' + videoId).val() || '';
        
        $('#modalVideoId').val(videoId);
        $('#videoModalLabel').text('Edit TikTok Video');
        $('#modalEmbedCode').val(embedCode);
        $('#modalProductLink').val(productLink);
        $('#productPreview').removeClass('show').html('');
        $('#modalAlsoSelect').prop('checked', $('#video_' + videoId).is(':checked'));
        $('#videoModal').modal('show');
        
        // Fetch product metadata if product link exists
        if (productLink) {
            fetchProductMetadata(productLink);
        }
    });

    // Fetch product metadata when user enters URL
    $('#modalProductLink').on('blur', function() {
        const url = $(this).val().trim();
        
        if (!url) {
            $('#productPreview').removeClass('show').html('');
            return;
        }
        
        fetchProductMetadata(url);
    });

    // Save video from modal
    $('#saveVideoBtn').on('click', function () {
        const videoId = $('#modalVideoId').val();
        const embedCode = ($('#modalEmbedCode').val() || '').trim();
        const productLink = ($('#modalProductLink').val() || '').trim();

        // Validate embed code
        if (!embedCode) {
            alert('Please enter the TikTok embed code.');
            return;
        }

        // Basic validation for TikTok embed code
        if (!embedCode.includes('tiktok-embed') && !embedCode.includes('tiktok.com')) {
            alert('Please enter a valid TikTok embed code.');
            return;
        }

        // Validate product link if provided
        if (productLink.length && !/^https?:\/\/.+/i.test(productLink)) {
            alert('Please enter a valid URL starting with http or https.');
            return;
        }

        if (videoId) {
            // Update existing video
            $('#hidden_embed_' + videoId).val(embedCode);
            $('#hidden_link_' + videoId).val(productLink);

            // Update the video preview
            const videoCard = $('#video_' + videoId).closest('.card');
            const embedWrapper = videoCard.find('.embed-wrapper');
            if (embedWrapper.length) {
                embedWrapper.html(embedCode);
            }

            // Update product link display
            updateProductLinkDisplay(videoId, productLink);

            // Select checkbox if option is checked
            if ($('#modalAlsoSelect').is(':checked')) {
                $('#video_' + videoId).prop('checked', true);
            }

            // Reinitialize TikTok embeds
            loadTikTokEmbeds();
        } else {
            // Add new video via AJAX
            const saveBtn = $('#saveVideoBtn');
            saveBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

            const csrfToken = $('meta[name="csrf-token"]').attr('content') || 
                             $('input[name="_token"]').val();

            $.ajax({  
                url: '/admin/tiktok-videos/store', 
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                },
                data: {
                    embed_code: embedCode,
                    product_link: productLink,
                },
                success: function(response) {
                    if (response.success) {
                        alert(response.message);
                        $('#videoModal').modal('hide');
                        window.location.reload();
                    } else {
                        alert(response.message || 'Error adding video.');
                        saveBtn.prop('disabled', false).html('<i class="fas fa-save"></i> Save Video');
                    }
                },
                error: function(xhr) {
                    console.error('AJAX Error:', xhr);
                    let errorMsg = 'Error adding video.';
                    
                    if (xhr.status === 404) {
                        errorMsg = 'Route not found. Please check your route configuration.';
                    } else if (xhr.status === 419) {
                        errorMsg = 'Session expired. Please refresh the page.';
                    } else if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    } else if (xhr.responseText) {
                        errorMsg = xhr.responseText;
                    }
                    
                    alert(errorMsg);
                    saveBtn.prop('disabled', false).html('<i class="fas fa-save"></i> Save Video');
                }
            });
        }

        $('#videoModal').modal('hide');
    });

    // Save selected
    $('#saveSelected').on('click', function () {
        if ($('.video-checkbox:checked').length === 0) {
            alert('Please select at least one video.');
            return;
        }
        $('#tiktokVideosForm').submit();
    });

    // Load TikTok embed script and reinitialize embeds
    loadTikTokEmbeds();
});
function fetchProductMetadata(url) {
    const preview = $('#productPreview');
    
    preview.addClass('show').html(`
        <div class="product-loading">
            <i class="fas fa-spinner fa-spin"></i> Loading product information...
        </div>
    `);
    
    const csrfToken = $('meta[name="csrf-token"]').attr('content');
    
    if (!csrfToken) {
        preview.html(`
            <div class="alert alert-danger mb-0 small">
                <i class="fas fa-times-circle"></i> CSRF token not found. Please refresh the page.
            </div>
        `);
        return;
    }
    
    $.ajax({
        url: '{{ route("admin.product.metadata") }}',
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        data: JSON.stringify({ url: url }),
        contentType: 'application/json',
        success: function(result) {
            if (result.success && result.data) {
                const data = result.data;
                let html = '<div class="product-info">';
                
                if (data.image) {
                    html += `<img src="${data.image}" alt="${data.title || 'Product'}" onerror="this.style.display='none'">`;
                }
                
                html += '<div class="product-info-details">';
                
                if (data.title) {
                    html += `<h5>${escapeHtml(data.title)}</h5>`;
                }
                
                if (data.price) {
                    html += `<p class="price">$${data.price}</p>`;
                }
                
                html += '</div></div>';
                preview.html(html);
            } else {
                const message = result.message || 'Product not found';
                preview.html(`
                    <div class="alert alert-warning mb-0 small">
                        <i class="fas fa-exclamation-triangle"></i> ${escapeHtml(message)}
                    </div>
                `);
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', xhr);
            console.error('Status:', status);
            console.error('Error:', error);
            console.error('Response:', xhr.responseText);
            
            let errorMessage = 'Error loading product information';
            
            if (xhr.status === 404) {
                errorMessage = 'Route not found. Check your routes/web.php file.';
            } else if (xhr.status === 419) {
                errorMessage = 'Session expired. Please refresh the page.';
            } else if (xhr.status === 500) {
                errorMessage = 'Server error. Check storage/logs/laravel.log';
            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                errorMessage = xhr.responseJSON.message;
            }
            
            preview.html(`
                <div class="alert alert-danger mb-0 small">
                    <i class="fas fa-times-circle"></i> ${escapeHtml(errorMessage)}
                    <br><small>Status: ${xhr.status} - Check browser console for details</small>
                </div>
            `);
        }
    });
}

// Helper function to escape HTML
function escapeHtml(text) {
    if (!text) return '';
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return String(text).replace(/[&<>"']/g, m => map[m]);
}

// Function to update product link display
function updateProductLinkDisplay(videoId, link) {
    const display = $('#product_link_display_' + videoId);
    if (link.length) {
        const short = link.length > 40 ? link.substring(0, 37) + '...' : link;
        display.html('<i class="fas fa-link"></i> Linked: <a href="'+ link +'" target="_blank">'+ short +'</a>');
    } else {
        display.text('No product link');
    }
}

// Function to load and initialize TikTok embeds
function loadTikTokEmbeds() {
    if (window.tiktokEmbed) {
        window.tiktokEmbed.lib.render();
    } else {
        const script = document.createElement('script');
        script.src = 'https://www.tiktok.com/embed.js';
        script.async = true;
        script.onload = function() {
            console.log('TikTok embed script loaded');
            setTimeout(() => {
                if (window.tiktokEmbed) {
                    window.tiktokEmbed.lib.render();
                }
            }, 500);
        };
        document.body.appendChild(script);
    }
}
</script>
@endsection