@extends('layouts.dashboard-main')
<!-- Main Banner Starts -->
@section('content')
@section('title', 'Product Videos')
@include('admin.adminsidebar')


<link rel="stylesheet" href="{{ URL::asset('assets/css/order-page.css') }}">


<div class="flex-parent-div bg-light-gray">
    <div class="order-product-page home-section cm-edit-ja">
        <div class="content container">
            <h3 class="buyer-order-heading" style="margin-left: 0px;">
                <div id="loading" class="show-setion" style="display:none;">
                    <img id="loading-image" src="{{ asset('assets/images/Loader-Animation.gif') }}" alt="Loading..." />
                </div>
                <div class="d-flex align-items-center" style="width: 100%; justify-content: space-between;">
                    <div>
                        <span class="me-auto">Product Videos</span>
                        <button id="sync-videos" class="btn btn-outline-secondary ">Sync Videos</button>
                    </div>
                    <!-- Right: Search Form -->
                    <form id="search-form" method="GET" action="#" style="display: flex; align-items: center;">
                        <input type="text" id="search-input" class="form-control" placeholder="Search videos..." style="width: 200px; margin-right: 8px;">
                        <button type="submit" class="btn btn-outline-secondary me-2 mr-2">Search</button>
                        <button type="button" id="clear-search" class="btn btn-outline-danger">Clear</button>
                    </form>
                </div>
            </h3>
            <div id="video-sync-status" class="d-inline-block ms-3">
                <span id="sync-status" class="text-success"></span>
            </div>
            <div>
                <hr>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px; flex-wrap: wrap;">
                    <!-- Left Side: Title + Buttons -->
                    <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                        <h3 style="margin: 0;">
                            <b>Select Videos</b> 
                            <span id="video-count" style="background-color: #ddd; padding: 2px 8px; border-radius: 12px; font-size: 0.9rem; margin-left: 6px; display: inline-block; min-width: 24px; text-align: center;"></span>
                        </h3>
                        <button class="btn btn-primary"><a href="/admin/dashboard">Done</a></button>
                        <!-- <button class="btn filter-btn" data-filter="videos" style="color: black; margin-right: 8px;">Videos</button>
                            <button class="btn filter-btn" data-filter="shorts" style="color: black; margin-right: 8px;">Shorts</button> -->
                    </div>

                    <div style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                        <b>Show:</b>                        
                        <button class="btn btn-secondary filter-btn btn-outline-secondary" data-filter="selected">Selected</button>
                        <button class="btn btn-secondary filter-btn btn-outline-secondary" data-filter="all" style="margin-right: 8px;">All</button>
                    </div>
                    <!-- Right Side: Sort -->
                    <div style="display: flex; align-items: center; gap: 8px;">
                        <label for="sort-select" class="form-label mb-0">Sort By:</label>
                        <select id="sort-select" class="form-select btn border-secondary btn-sm">
                            <option value="newest">Newest</option>
                            <option value="oldest">Oldest</option>
                        </select>
                    </div>
                </div>

                <!-- Video Container -->
                <div class="container">
                    <div class="row" id="video-container"></div>
                </div>

                <!-- Pagination container -->
                <div class="d-flex justify-content-right mt-4" id="pagination-wrapper">
                    <nav>
                        <ul class="pagination" id="pagination"></ul>
                    </nav>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        function getProductIdFromURL() {
            const pathSegments = window.location.pathname.split('/');
            return pathSegments[pathSegments.length - 1];
        }

        const statusSpan = document.getElementById('sync-status');

        function showStatusMessage(message, isError = false) {
            if (statusSpan) {
                statusSpan.textContent = message;
                statusSpan.style.color = isError ? 'red' : 'green';

                // Optional: clear message after 3 seconds
                setTimeout(() => {
                    statusSpan.textContent = '';
                }, 3000);
            }
        }

        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('video-select-checkbox')) {
                const checkbox = e.target;
                const videoId = checkbox.getAttribute('data-video-id');
                const productId = getProductIdFromURL();

                // Instead use:
                console.log(videoId); // ✅

                console.log("Checkbox changed:", {
                    productId: productId,
                    videoId: videoId,
                    isChecked: checkbox.checked
                });

                if (!productId || !videoId) {
                    showStatusMessage("Missing product_id or video_id", true);
                    return;
                }

                const isChecked = checkbox.checked;

                fetch('/api/product-videos-toggle', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({
                            product_id: productId,
                            video_id: videoId,
                            assign: isChecked
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        console.error("API 23 error:", data);
                        showStatusMessage(data.message || "Video assignment updated");
                    })
                    .catch(err => {
                        console.error("API error:", err);
                        showStatusMessage("An error occurred while updating video", true);
                    });
            }
        });
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const syncBtn = document.getElementById('sync-videos');
        const statusSpan = document.getElementById('sync-status');

        syncBtn.addEventListener('click', () => {
            syncBtn.disabled = true;
            statusSpan.textContent = 'Syncing...';
            statusSpan.classList.remove('text-danger', 'text-success');

            fetch('/api/sync-youtube-videos', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content // if needed
                    }
                })
                .then(res => res.json())
                .then(data => {
                    syncBtn.disabled = false;

                    if (data.message) {
                        statusSpan.textContent = `${data.message} (${data.inserted} inserted, ${data.skipped} skipped)`;
                        statusSpan.classList.add('text-success');

                        setTimeout(() => {
                            statusSpan.textContent = '';
                        }, 3000);
                    }
                })
                .catch(error => {
                    syncBtn.disabled = false;
                    statusSpan.textContent = 'Sync failed. Please try again.';
                    statusSpan.classList.add('text-danger');

                    setTimeout(() => {
                        statusSpan.textContent = '';
                    }, 3000);
                });
        });
    });
</script>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        let currentPage = 1;
        let currentFilter = 'all';
        let currentSearch = '';
        let currentSort = 'newest';
        
        function getProductIdFromURL() {
            const pathSegments = window.location.pathname.split('/');
            return pathSegments[pathSegments.length - 1];
        }

        function updateVideoCount() {
            const productId = getProductIdFromURL();
            const countSpan = document.getElementById('video-count');

            if (!productId || !countSpan) return;

            fetch(`/api/product-videos/${productId}/assigned`)
                .then(res => res.json())
                .then(videoIds => {
                    countSpan.textContent = videoIds.length;
                })
                .catch(err => {
                    console.error('Error fetching assigned videos:', err);
                    countSpan.textContent = '0';
                });
        }

        const productId = getProductIdFromURL();

        function loadVideos() {
            const perPage = 12;

            fetch(`/api/youtube-videos?search=${encodeURIComponent(currentSearch)}&filter=${currentFilter}&page=${currentPage}&per_page=${perPage}&sort=${currentSort}&product_id=${productId}`)
                .then(res => res.json())
                .then(res => {
                    const videos = res.data;
                    const container = document.getElementById('video-container');
                    container.innerHTML = '';

                    if (videos.length === 0) {
                        container.innerHTML = '<div class="col-12 text-center"><p>No videos found.</p></div>';
                        document.getElementById('pagination').innerHTML = '';
                        return;
                    }

                    const selectedVideoIds = res.selected_videos || [];
                    
                    videos.forEach(video => {
                        const isSelected = selectedVideoIds.includes(video.id);
                        container.innerHTML += `
                            <div class="col-md-3 mb-3 p-2">
                                <div class="card h-100 border border-dark border-2" style="background-color: #f8f9fa;">
                                    <div class="video mb-1">
                                        <iframe
                                            class="video-frame w-100 p-1"
                                            src="https://www.youtube.com/embed/${video.video_id}"
                                            title="${video.title}"
                                            frameborder="0"
                                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                            referrerpolicy="strict-origin-when-cross-origin"
                                            allowfullscreen>
                                        </iframe> 
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center px-2" style="font-size: 0.9rem; color: gray;">
                                        <small>Published on: ${new Date(video.published_at).toLocaleDateString()}</small>
                                        <input 
                                            type="checkbox" 
                                            class="video-select-checkbox" 
                                            style="width: 20px; height: 20px;" 
                                            data-video-id="${video.id}" 
                                            ${isSelected ? 'checked' : ''}>
                                    </div>
                                    <div class="media-body title px-2 pb-2">${video.title}</div>
                                </div>
                            </div>
                            `

                        ;
                    });

                    document.querySelectorAll('.video-select-checkbox').forEach(checkbox => {
                        checkbox.addEventListener('change', () => {
                            updateVideoCount();
                        });
                    });
                    updateVideoCount();
                    renderPagination(res.current_page, res.last_page);
                });
        }

        function renderPagination(current, last) {
            const pagination = document.getElementById('pagination');
            pagination.innerHTML = '';

            const maxPagesToShow = 5;
            let start = Math.max(1, current - Math.floor(maxPagesToShow / 2));
            let end = Math.min(last, start + maxPagesToShow - 1);

            if (end - start < maxPagesToShow - 1) {
                start = Math.max(1, end - maxPagesToShow + 1);
            }

            // Previous
            pagination.innerHTML += `
                <li class="page-item ${current === 1 ? 'disabled' : ''}">
                    <a class="page-link" href="#" data-page="${current - 1}">Previous</a>
                </li>`;

            // First page and ellipsis
            if (start > 1) {
                pagination.innerHTML += `
                    <li class="page-item">
                        <a class="page-link" href="#" data-page="1">1</a>
                    </li>`;
                if (start > 2) {
                    pagination.innerHTML += `
                    <li class="page-item disabled"><span class="page-link">...</span></li>`;
                }
            }

            // Page numbers
            for (let i = start; i <= end; i++) {
                pagination.innerHTML += `
                    <li class="page-item ${current === i ? 'active' : ''}">
                        <a class="page-link" href="#" data-page="${i}">${i}</a>
                    </li>`;
            }

            // Ellipsis and last page
            if (end < last) {
                if (end < last - 1) {
                    pagination.innerHTML += `
                    <li class="page-item disabled"><span class="page-link">...</span></li>`;
                }
                pagination.innerHTML += `
                    <li class="page-item">
                        <a class="page-link" href="#" data-page="${last}">${last}</a>
                    </li>`;
            }

            // Next
            pagination.innerHTML += `
                <li class="page-item ${current === last ? 'disabled' : ''}">
                    <a class="page-link" href="#" data-page="${current + 1}">Next</a>
                </li>`;

            // Handle page link clicks
            document.querySelectorAll('#pagination a.page-link').forEach(link => {
                link.addEventListener('click', e => {
                    e.preventDefault();
                    const targetPage = parseInt(e.target.getAttribute('data-page'));
                    if (!isNaN(targetPage) && targetPage >= 1 && targetPage <= last) {
                        currentPage = targetPage;
                        loadVideos();
                        window.scrollTo({
                            top: 0,
                            behavior: 'smooth'
                        }); // optional scroll to top
                    }
                });
            });
        }

        // Search handler
        document.getElementById('search-form').addEventListener('submit', function(e) {
            e.preventDefault();
            currentSearch = document.getElementById('search-input').value;
            currentPage = 1;
            loadVideos();
        });

        // Clear search
        document.getElementById('clear-search').addEventListener('click', () => {
            document.getElementById('search-input').value = '';
            currentSearch = '';
            currentPage = 1;
            loadVideos();
        });

        // Filter buttons
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                currentFilter = btn.dataset.filter;
                currentPage = 1;
                loadVideos();
            });
        });

        // Sort select
        document.getElementById('sort-select').addEventListener('change', function() {
            currentSort = this.value;
            currentPage = 1;
            loadVideos();
        });
        // Initial load
        loadVideos();
    });
</script>



<style>
    .video-box-sec {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 1rem;
    }

    .box {
        border-radius: 1rem;
        /* background-color: aqua; */
        display: flex;
        flex-direction: column;
    }

    .video {
        position: relative;
        top: 0;
        width: 100%;
        padding-top: 56.25%;
        /* 16:9 Aspect Ratio (9/16 = 0.5625) */
        border-radius: 1rem 1rem 0 0;
        overflow: hidden;
    }

    .video-frame {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        border: none;
        border-radius: 0.8rem;
    }

    .title {
        display: flex;
        align-items: center;
        border-radius: 0 0 1rem 1rem;
        padding: 12px 8px 2px 8px;
        font-weight: 700;
        /* background-color: antiquewhite; */
    }
</style>

@endsection