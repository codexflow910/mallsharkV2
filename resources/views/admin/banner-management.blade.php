@extends('layouts.dashboard-main')
    @section('content')
    @section('title', 'Banner Management')
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-colorpicker/2.5.3/css/bootstrap-colorpicker.min.css"
        rel="stylesheet">
    <style>
        #editLinkModal input[type= 'checkbox']{
            width: auto !important;
        }
        .colorpicker-2x .colorpicker-saturation {
            width: 200px;
            height: 200px;
        }

        .colorpicker-2x .colorpicker-hue,
        .colorpicker-2x .colorpicker-alpha {
            width: 30px;
            height: 200px;
        }

        .colorpicker-2x .colorpicker-color,
        .colorpicker-2x .colorpicker-color div {
            height: 30px;
        }

        .colorpicker-element .input-group-addon {
            position: relative;
            top: -2px;
        }

        .colorpicker-element .input-group-addon i {
            width: 40px;
            height: 41px;
        }
        .low-placeholder::placeholder {
            color: #000000;
            opacity: 0.3;
        }
    </style>
    <main>
        <section class="admin-dashboard dshbrdin buyer mt-0 user-management manage-categories-section increased-fnt-cards">

            @include('admin.adminsidebar')
            <div class="dshbrdinadmin list-management w-100">

                <div class="container">
                    <div class="row">
                        <!-- <div class="col-sm-12 mt-40">
                            <h3>Manage Banner Sets</h3>
                            <ul>
                                <li> <a class="addslide-link" href="javascript:void(0)" style="color:#195dfa">Add Slide</a></li>

                            </ul>
                        </div> -->
                        @if (Session::has('message_error'))
                        <div class="col-sm-12">
                            <div class="alert alert-danger">
                                <ul>
                                    <li>{!! Session::get('message_error') !!}</li>
                                </ul>
                            </div>
                        </div>
                        @endif
                        @if (Session::has('message_success'))
                        <div class="col-sm-12">

                            <div class="alert alert-success">
                                <ul>
                                    <li>{!! Session::get('message_success') !!}</li>
                                </ul>
                            </div>
                        </div>
                        @endif

                        <div class="col-sm-6 mt-10 add-slide card shadow-sm" style="display:none;">
                            <div class="card-header">
                                <h5 id="new-set">Create new set </h5>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('addSlideToSet') }}" method="POST" enctype="multipart/form-data" id="add-slide">

                                    <!-- Hidden inputs for backend -->
                                    <input type="hidden" name="slide_set_id" id="slide_set_id">
                                    <input type="hidden" name="slide_set_name" id="slide_set_name">

                                    <div class="form-group">
                                        <p class="small text-muted">
                                            Note: Best dimension for <strong>large slide</strong> is <code>730×380</code>
                                            and for <strong>small slide</strong> is <code>350×380</code>
                                        </p>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text" id="inputGroupFileAddon01">Upload</span>
                                            </div>
                                            <div class="custom-file">
                                                <input type="file" name="slide_image" required class="custom-file-input" id="slide_image"
                                                    aria-describedby="inputGroupFileAddon01">
                                                <label class="custom-file-label" for="slide_image">Choose file</label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="bg_color">Background Color</label>
                                        <div id="cp2" class="input-group colorpicker-component">
                                            <input type="text" value="#001dbb" name="bg_color"
                                                class="form-control" id="bg_color" />
                                            <span class="input-group-addon"><i></i></span>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="slide_url">Slide Url:</label>
                                        <input class="form-control" type="text" id="slide_url" name="slide_url" required>
                                    </div>

                                    <div class="form-group">
                                        <label for="slide_category">Slide Category:</label>
                                        <select class="form-control" name="slide_category" id="slide_category">
                                            <option value="home">Home</option>
                                            <option value="home-small">Home Small</option>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="slide_type">Slide Type:</label>
                                        <select class="form-control" name="slide_type" id="slide_type" required>
                                            <option value="banner">Banner</option>
                                            <option value="slider">Slider</option>
                                        </select>
                                    </div>

                                    {!! csrf_field() !!}
                                    <div class="form-group d-flex justify-content-between">
                                        <input type="submit" value="Save" name="submit" class="btn btn-primary">
                                        <a href="/slides/sets" class="btn btn-secondary">Cancel</a>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="col-sm-6 mt-10 edit-slide card" style="display:none;">
                            <div class="card-header">
                                <h5 id="formTitle">Edit Slide
                                    (<span id="slide_set_name_text"></span>)
                                </h5>
                            </div>
                            <div class="card-body">
                                <form action="{{ route('editSlideToSet') }}" method="POST" enctype="multipart/form-data" id="edit-slide">
                                    @csrf

                                    <!-- Hidden IDs -->
                                    <input type="hidden" id="slide_id_edit" name="slide_id">
                                    <input type="hidden" id="slide_attachment" name="slide_attachment">
                                    <input type="hidden" id="slide_set_id" name="slide_set_id">

                                    <!-- Upload -->
                                    <div class="form-group">
                                        <p class="small text-muted">
                                            <strong>Note:</strong> Best dimension for large slide is <code>730×380</code>
                                            and for small slide is <code>350×380</code>.
                                        </p>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text" id="inputGroupFileAddon02">Upload</span>
                                            </div>
                                            <div class="custom-file">
                                                <input type="file" name="slide_image" class="custom-file-input" id="slide_image_edit"
                                                    aria-describedby="inputGroupFileAddon02">
                                                <label class="custom-file-label" for="slide_image_edit">Choose file</label>
                                            </div>
                                        </div>
                                        <div class="slide-pic-edit mt-2" style="max-width:250px;">
                                            <img id="previewSlideImg" src="" class="img-fluid rounded shadow-sm border" alt="Slide Preview">
                                        </div>
                                    </div>

                                    <!-- Background Color -->
                                    <div class="form-group">
                                        <label for="bg_color_edit">Background Color</label>
                                        <div id="cp3" class="input-group colorpicker-component">
                                            <input type="text" value="#001dbb" name="bg_color" class="form-control" id="bg_color_edit" />
                                            <span class="input-group-addon"><i></i></span>
                                        </div>
                                    </div>

                                    <!-- Slide URL -->
                                    <div class="form-group">
                                        <label for="slide_url_edit">Slide URL:</label>
                                        <input class="form-control" type="text" id="slide_url_edit" name="slide_url" required>
                                    </div>

                                    <!-- Category -->
                                    <div class="form-group">
                                        <label for="slide_category">Slide Category:</label>
                                        <select class="form-control" name="slide_category" id="slide_category">
                                            <option value="home">Home</option>
                                            <option value="home-small">Home Small</option>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="slide_type">Slide Type:</label>
                                        <select class="form-control" name="slide_type" id="slide_type" required>
                                            <option value="banner">Banner</option>
                                            <option value="slider">Slider</option>
                                        </select>
                                    </div>

                                    <!-- Actions -->
                                    <div class="form-group d-flex justify-content-between">
                                        <input type="submit" value="Save" class="btn btn-success">
                                        <a href="/slides/sets" class="btn btn-secondary">Cancel</a>
                                    </div>
                                </form>
                            </div>
                        </div>

                            <?php
                            if ($allBannerSets->count() > 0) {
                                foreach ($allBannerSets as $set) {
                                    // Assume $set->slides is a flat array with category info (web/mobile)
                                    ?>
                                    <div class="col-sm-10 col-lg-10 mt-4 mx-auto">
                                        <div class="card shadow-sm border-0 rounded-3 h-100">
                                            <div class="card-header bg-light d-flex justify-content-between align-items-center">
                                                <h5 class="mb-0">
                                                    <?= htmlspecialchars($set->name) ?> 
                                                    — Status: <?= htmlspecialchars($set->status) ?>
                                                </h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="table-responsive">
                                                    <table class="table table-bordered table-striped align-middle mb-0">
                                                        <thead class="table-light">
                                                            <tr>
                                                                <th scope="col">Box No</th>
                                                                <th scope="col">Home Image</th>
                                                                <th scope="col">Home Small Image</th>
                                                                <th scope="col">Link</th>
                                                                <th scope="col">Actions</th>
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php
                                                            // Group slides by box_no
                                                            // Show all slider type slides one by one first
                                                            $sliderSlides = [];
                                                            $slidesByBox = [];
                                                            foreach ($set->slides as $slide) {
                                                                if ($slide->type === 'slider') {
                                                                    $sliderSlides[] = $slide;
                                                                } else {
                                                                    $boxNo = $slide->box_number;
                                                                    $slidesByBox[$boxNo][$slide->category] = $slide;
                                                                }
                                                            }
                                                            // Show slider slides
                                                            foreach ($sliderSlides as $slide):
                                                                $img = getAttachmentUrlById($slide->attachment_id, 'small');
                                                            ?>
                                                                <tr>
                                                                    <td><?= htmlspecialchars($slide->box_number) ?></td>
                                                                    <td colspan="2">
                                                                        <?php if ($img): ?>
                                                                            <img src="<?= $img ?>" alt="Slider Image" class="img-fluid rounded slide-img" style="max-width:100px; height:auto;">
                                                                        <?php else: ?>
                                                                            <span class="text-muted">—</span>
                                                                        <?php endif; ?>
                                                                    </td>
                                                                    <td>
                                                                        <span id="slide-link-<?= $slide->slide_id ?>" class="slide-link-text">
                                                                            <?= htmlspecialchars($slide->link) ?>
                                                                        </span>
                                                                    </td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" class="btn btn-sm btn-outline-primary edit-slide-link"
                                                                            title="Edit Slide"
                                                                            data-slide_id="<?= $slide->slide_id ?>"
                                                                            data-home_id="<?= $home->slide_id ?? '' ?>"
                                                                            data-category="<?= htmlspecialchars($slide->category) ?>"
                                                                            data-link="<?= htmlspecialchars($slide->link) ?>"
                                                                            data-heading="<?= htmlspecialchars($slide->heading ?? '') ?>"
                                                                            data-sub_heading="<?= htmlspecialchars($slide->sub_heading ?? '') ?>"
                                                                            data-cto="<?= htmlspecialchars($slide->cto ?? '') ?>"
                                                                            data-heading_bold="<?= $slide->heading_bold ?? 0 ?>"
                                                                            data-heading_italic="<?= $slide->heading_italic ?? 0 ?>"
                                                                            data-sub_heading_bold="<?= $slide->sub_heading_bold ?? 0 ?>"
                                                                            data-sub_heading_italic="<?= $slide->sub_heading_italic ?? 0 ?>"
                                                                            data-swipe_headings="<?= $slide->swipe_headings ?? 0 ?>"
                                                                            data-cto_bold="<?= $slide->cto_bold ?? 0 ?>"
                                                                            data-cto_italic="<?= $slide->cto_italic ?? 0 ?>"
                                                                            data-status="<?= $slide->status ?? 0 ?>"
                                                                            data-heading_color="<?= htmlspecialchars($slide->heading_color ?? '') ?>"
                                                                            data-sub_heading_color="<?= htmlspecialchars($slide->sub_heading_color ?? '') ?>"
                                                                            data-button_bg_color="<?= htmlspecialchars($slide->button_bg_color ?? '') ?>">
                                                                            <i class="fa fa-edit"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                            <?php

                                                            // Now show grouped (non-slider) slides
                                                            ksort($slidesByBox);
                                                            foreach ($slidesByBox as $boxNo => $slidesForBox):
                                                                $home = $slidesForBox['home'] ?? null;
                                                                $homeSmall = $slidesForBox['home-small'] ?? null;
                                                                $mainSlide = $home ?? $homeSmall;
                                                                $homeImg = $home ? getAttachmentUrlById($home->attachment_id, 'small') : '';
                                                                $homeSmallImg = $homeSmall ? getAttachmentUrlById($homeSmall->attachment_id, 'small') : '';
                                                            ?>
                                                                <tr>
                                                                    <td><?= htmlspecialchars($boxNo) ?></td>
                                                                    <td>
                                                                        <?php if ($homeImg): ?>
                                                                            <img src="<?= $homeImg ?>" alt="Home Image" class="img-fluid rounded slide-img" style="max-width:100px; height:auto;">
                                                                        <?php else: ?>
                                                                            <span class="text-muted">—</span>
                                                                        <?php endif; ?>
                                                                    </td>
                                                                    <td>
                                                                        <?php if ($homeSmallImg): ?>
                                                                            <img src="<?= $homeSmallImg ?>" alt="Home Small Image" class="img-fluid rounded slide-img" style="max-width:100px; height:auto;">
                                                                        <?php else: ?>
                                                                            <span class="text-muted">—</span>
                                                                        <?php endif; ?>
                                                                    </td>
                                                                    <td>
                                                                        <span id="slide-link-<?= $mainSlide->slide_id ?>" class="slide-link-text">
                                                                            <?= htmlspecialchars($mainSlide->link) ?>
                                                                        </span>
                                                                    </td>
                                                                    <td>
                                                                        <a href="javascript:void(0);" class="btn btn-sm btn-outline-primary edit-slide-link"
                                                                            title="Edit Slide"
                                                                            data-slide_id="<?= $mainSlide->slide_id ?>"
                                                                            data-home_id="<?= $home->slide_id ?? '' ?>"
                                                                            data-home_small_id="<?= $homeSmall->slide_id ?? '' ?>"
                                                                            data-category="<?= htmlspecialchars($mainSlide->category) ?>"
                                                                            data-link="<?= htmlspecialchars($mainSlide->link) ?>"
                                                                            data-heading="<?= htmlspecialchars($mainSlide->heading ?? '') ?>"
                                                                            data-sub_heading="<?= htmlspecialchars($mainSlide->sub_heading ?? '') ?>"
                                                                            data-cto="<?= htmlspecialchars($mainSlide->cto ?? '') ?>"
                                                                            data-heading_bold="<?= $mainSlide->heading_bold ?? 0 ?>"
                                                                            data-heading_italic="<?= $mainSlide->heading_italic ?? 0 ?>"
                                                                            data-sub_heading_bold="<?= $mainSlide->sub_heading_bold ?? 0 ?>"
                                                                            data-sub_heading_italic="<?= $mainSlide->sub_heading_italic ?? 0 ?>"
                                                                            data-swipe_headings="<?= $mainSlide->swipe_headings ?? 0 ?>"
                                                                            data-cto_bold="<?= $mainSlide->cto_bold ?? 0 ?>"
                                                                            data-cto_italic="<?= $mainSlide->cto_italic ?? 0 ?>"
                                                                            data-status="<?= $mainSlide->status ?? 0 ?>"
                                                                            data-heading_color="<?= htmlspecialchars($mainSlide->heading_color ?? '') ?>"
                                                                            data-sub_heading_color="<?= htmlspecialchars($mainSlide->sub_heading_color ?? '') ?>"
                                                                            data-button_bg_color="<?= htmlspecialchars($mainSlide->button_bg_color ?? '') ?>">
                                                                            <i class="fa fa-edit"></i>
                                                                        </a>
                                                                    </td>
                                                                </tr>
                                                            <?php endforeach; ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php
                                }
                            }
                            ?>
                    </div>
                </div>
            </div>
            <!-- dshbrdinadmin -->
        </section>     
        <!-- Modal -->
        <div class="modal fade" id="editLinkModal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document" style="max-width: 600px; padding: 4px;">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Edit Parameters</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                        <input type="hidden" id="link_slide_id">
                        
                        <div class="form-group">
                            <label for="link_slide_link">Link</label>
                            <input type="text" id="link_slide_link" class="form-control">
                            <small class="edit-link-success"></small>
                        </div>

                        <div class="form-group d-flex align-items-center">
                           <input type="checkbox" id="enableFieldsCheckbox">
                            <label class="mb-0 pl-2" for="enableFieldsCheckbox">Enable Heading, Sub Heading, and Button</label>
                        </div>

                        <!-- Heading -->
                        <div class="form-group">
                            
                            <div class="d-flex align-items-center justify-content-between">
                                <div style="width: 330px;">
                                    <label for="link_slide_heading">Heading</label>
                                    <input type="text" id="link_slide_heading" class="form-control">
                                </div>
                                <div>
                                    <label for="link_slide_heading_color" class="">Color</label>
                                    <input type="text" id="link_slide_heading_color" class="form-control low-placeholder" placeholder="#000000">
                                </div>
                            </div>
                            <div class="d-flex align-items-center mt-2">
                                <input type="checkbox" id="heading_bold">
                                <label class="mb-0 pl-2 pr-3" for="heading_bold">Bold</label>
                                <input type="checkbox" id="heading_italic">
                                <label class="mb-0 pl-2" for="heading_italic">Italic</label>
                            </div>
                            <small class="edit-heading-success"></small>
                        </div>
                        
                        <!-- Sub Heading -->
                        <div class="form-group">
                            <div class="d-flex align-items-center justify-content-between">
                                <div style="width: 330px;">
                                    <label for="link_slide_sub_heading">Sub Heading</label>
                                    <input type="text" id="link_slide_sub_heading" class="form-control">
                                </div>
                                <div>
                                    <label for="link_slide_sub_heading_color" class="">Color</label>
                                    <input type="text" id="link_slide_sub_heading_color" class="form-control low-placeholder" placeholder="#000000">
                                </div>
                            </div>
                            <div class="d-flex align-items-center mt-2">
                                <input type="checkbox" id="sub_heading_bold">
                                <label class="mb-0 pl-2 pr-3" for="sub_heading_bold">Bold</label>
                                <input type="checkbox" id="sub_heading_italic">
                                <label class="mb-0 pl-2" for="sub_heading_italic">Italic</label>
                            </div>
                            <small class="edit-sub-heading-success"></small>
                        </div>
                        
                         <!-- Swipe Headings -->
                        <div class="form-group">
                            <div class="d-flex align-items-center mt-2">
                                <input type="checkbox" id="swipe_headings">
                                <label class="mb-0 pl-2 pr-3" for="swipe_headings">Swipe Headings</label>
                            </div>
                            <small class="edit-sub-heading-success"></small>
                        </div>
                        <!-- Button -->
                        <div class="form-group">
                            <div class="d-flex align-items-center justify-content-between">
                                <div style="width: 330px;">
                                    <label for="link_slide_cto">Button</label>
                                    <input type="text" id="link_slide_cto" class="form-control">
                                </div>
                                <div>
                                    <label for="link_slide_button_bg_color" class="mt-5">Background Color</label>
                                    <input type="text" id="link_slide_button_bg_color" class="form-control low-placeholder" placeholder="#000000">
                                </div>
                            </div>
                            <div class="d-flex align-items-center mt-2">
                                <input type="checkbox" id="cto_bold">
                                <label class="mb-0 pl-2 pr-3" for="cto_bold">Bold</label>
                                <input type="checkbox" id="cto_italic">
                                <label class="mb-0 pl-2" for="cto_italic">Italic</label>
                            </div>
                            <small class="edit-cto-success"></small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary" id="saveLinkOnly">Save</button>
                    </div>
                </div>
            </div>
        </div>
    </main>
<!-- Load scripts in correct order - FULL jQuery, then Popper, then Bootstrap -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-colorpicker/2.5.3/js/bootstrap-colorpicker.min.js"></script>

<script type="application/javascript">
    // Colorpicker initialization
    $('#cp2').colorpicker({
        customClass: 'colorpicker-2x',
        sliders: {
            saturation: { maxLeft: 200, maxTop: 200 },
            hue: { maxTop: 200 },
            alpha: { maxTop: 200 }
        }
    });
    
    $('#cp3').colorpicker({
        customClass: 'colorpicker-2x',
        sliders: {
            saturation: { maxLeft: 200, maxTop: 200 },
            hue: { maxTop: 200 },
            alpha: { maxTop: 200 }
        }
    });

    // File input change
    $('input[type="file"]').change(function(e) {
        var fileName = e.target.files[0].name;
        $(this).next('.custom-file-label').html(fileName);
        $('.slide-pic-edit').hide();
    });

    // Add/Edit slide buttons
    $(document).on('click', '.addslide-link', function() {
        $('.add-slide').show();
        $('.edit-slide').hide();
    });

    $(document).on('click', '.editslide-link', function() {
        $('.add-slide').hide();
        $('.edit-slide').show();
        
        var slide_id = $(this).attr('data-slide_id');
        var slide_url = $(this).attr('data-link');
        var img_url = $(this).attr('data-img-url');
        var slide_set_id = $(this).attr('data-slide-set-id');
        var slide_set_name = $(this).attr('data-slide-set-name');
        var slide_cat = $(this).attr('data-cat');
        var slide_type = $(this).attr('data-type');
        var slide_attachment = $(this).attr('data-attachment_id');
        var bgcolor = $(this).attr('data-bgcolor');

        $('#slide_attachment').val(slide_attachment);
        $('#bg_color_edit').val(bgcolor);
        $('#cp3').colorpicker('setValue', bgcolor);
        $('#slide_url_edit').val(slide_url);
        $('#slide_id_edit').val(slide_id);
        $('#slide_category').val(slide_cat);
        $('#slide_set_id').val(slide_set_id);
        $('#slide_set_name').val(slide_set_name);
        $('#slide_set_name_text').text(slide_set_name);
        $('.slide-pic-edit').show();
        $('.slide-pic-edit img').attr('src', img_url);
        $('#slide_type').val(slide_type);

        $(window).scrollTop(0);
    });

    // Auto-hide alerts
    document.addEventListener("DOMContentLoaded", function() {
        setTimeout(function() {
            let alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                alert.style.transition = "opacity 0.5s";
                alert.style.opacity = "0";
                setTimeout(() => alert.remove(), 500);
            });
        }, 3000);
    });

    // Open link edit modal
    $(document).on('click', '.edit-slide-link', function() {
        const slideId = $(this).data('slide_id');
        const homeId = $(this).data('home_id');
        const homeSmallId = $(this).data('home_small_id');
        // Store both IDs in hidden field (comma separated)
        let ids = [];
        if (slideId) ids.push(slideId);
        if (homeId) ids.push(homeId);
        if (homeSmallId && homeSmallId !== homeId) ids.push(homeSmallId);
        $('#link_slide_id').val(ids.join(','));

        // Get link value
        let linkValue = $(this).data('link') || '';
        if (!linkValue || linkValue.trim() === "") {
            linkValue = "<?= env('FRONT_END_URL') ?>";
        }

        // Get text field values
        const heading = $(this).data('heading') || '';
        const subHeading = $(this).data('sub_heading') || '';
        const cto = $(this).data('cto') || '';
        const headingColor = $(this).data('heading_color') || '';
        const subHeadingColor = $(this).data('sub_heading_color') || '';
        const buttonBgColor = $(this).data('button_bg_color') || '';
        
        // Get boolean values for styling
        const headingBold = $(this).data('heading_bold') == '1' || $(this).data('heading_bold') === true;
        const headingItalic = $(this).data('heading_italic') == '1' || $(this).data('heading_italic') === true;
        const subHeadingBold = $(this).data('sub_heading_bold') == '1' || $(this).data('sub_heading_bold') === true;
        const subHeadingItalic = $(this).data('sub_heading_italic') == '1' || $(this).data('sub_heading_italic') === true;
        const swipeHeadings = $(this).data('swipe_headings') == '1' || $(this).data('swipe_headings') === true;
        const ctoBold = $(this).data('cto_bold') == '1' || $(this).data('cto_bold') === true;
        const ctoItalic = $(this).data('cto_italic') == '1' || $(this).data('cto_italic') === true;
        const status = $(this).data('status') || 0;
        
        // Set text inputs
        $('#link_slide_link').val(linkValue);
        $('#link_slide_heading').val(heading);
        $('#link_slide_heading_color').val(headingColor);
        $('#link_slide_sub_heading').val(subHeading);
        $('#link_slide_sub_heading_color').val(subHeadingColor);
        $('#link_slide_cto').val(cto);
        $('#link_slide_button_bg_color').val(buttonBgColor);
        
        // Set style checkboxes
        $('#heading_bold').prop('checked', headingBold);
        $('#heading_italic').prop('checked', headingItalic);
        $('#sub_heading_bold').prop('checked', subHeadingBold);
        $('#sub_heading_italic').prop('checked', subHeadingItalic);
        $('#swipe_headings').prop('checked', swipeHeadings);
        $('#cto_bold').prop('checked', ctoBold);
        $('#cto_italic').prop('checked', ctoItalic);
        $('#enableFieldsCheckbox').prop('checked', status);
        
        // Check if all three fields are empty to auto-check disable checkbox
        const allFieldsEmpty = !heading && !subHeading && !cto;
        
        //    const isChecked = $(this).is(':checked');
        $('#link_slide_heading, #link_slide_sub_heading, #link_slide_cto').prop('disabled', !status);
        $('#heading_bold, #heading_italic, #sub_heading_bold, #sub_heading_italic, #cto_bold, #cto_italic, #swipe_headings').prop('disabled', !status);
    
        // Clear success messages
        $('.edit-link-success, .edit-heading-success, .edit-sub-heading-success, .edit-cto-success').text('');
        
        // Open modal
        $('#editLinkModal').modal('show');
    });

    // Handle disable fields checkbox
    $('#enableFieldsCheckbox').change(function() {
        const isChecked = $(this).is(':checked');
        $('#link_slide_heading, #link_slide_sub_heading, #link_slide_cto, #link_slide_heading_color, #link_slide_sub_heading_color, #link_slide_button_bg_color').prop('disabled', !isChecked);
        $('#heading_bold, #heading_italic, #sub_heading_bold, #sub_heading_italic, #cto_bold, #cto_italic, #swipe_headings').prop('disabled', !isChecked);
    });

    // Save link via AJAX
    document.getElementById('saveLinkOnly').addEventListener('click', function () {
        let slideIds = document.getElementById('link_slide_id').value;
        let newLink = document.getElementById('link_slide_link').value;
        let newHeading = document.getElementById('link_slide_heading').value;
        let newHeadingColor = document.getElementById('link_slide_heading_color').value;
        let newSubHeading = document.getElementById('link_slide_sub_heading').value;
        let newSubHeadingColor = document.getElementById('link_slide_sub_heading_color').value;
        let newCto = document.getElementById('link_slide_cto').value;
        let newButtonBgColor = document.getElementById('link_slide_button_bg_color').value;
        let enableFields = document.getElementById('enableFieldsCheckbox').checked;

        // Get style checkboxes
        let headingBold = document.getElementById('heading_bold').checked;
        let headingItalic = document.getElementById('heading_italic').checked;
        let subHeadingBold = document.getElementById('sub_heading_bold').checked;
        let subHeadingItalic = document.getElementById('sub_heading_italic').checked;
        let swipeHeadings = document.getElementById('swipe_headings').checked;
        let ctoBold = document.getElementById('cto_bold').checked;
        let ctoItalic = document.getElementById('cto_italic').checked;

        let csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Support multiple slide IDs (comma separated)
        let idsArr = slideIds.split(',').map(id => id.trim()).filter(Boolean);
        let updatePromises = idsArr.map(function(id) {
            return fetch(`/slides/${id}/update-link`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    link: newLink || null,
                    heading: newHeading || null,
                    heading_color: newHeadingColor || null,
                    sub_heading: newSubHeading || null,
                    sub_heading_color: newSubHeadingColor || null,
                    cto: newCto || null,
                    enable_fields: enableFields,
                    button_bg_color: newButtonBgColor || null,
                    heading_bold: headingBold,
                    heading_italic: headingItalic,
                    sub_heading_bold: subHeadingBold,
                    sub_heading_italic: subHeadingItalic,
                    swipe_headings: swipeHeadings,
                    cto_bold: ctoBold,
                    cto_italic: ctoItalic,
                }),
            })
            .then(response => {
                if (!response.ok) throw new Error("Network response was not ok");
                return response.json();
            });
        });

        Promise.all(updatePromises)
        .then(responses => {
            // Use the first response for UI update
            let res = responses[0];
            if (res.success) {
                // Update visible link text for all slide IDs
                idsArr.forEach(function(id) {
                    let linkEl = document.getElementById(`slide-link-${id}`);
                    if (linkEl) linkEl.textContent = res.link || '';
                });

                // Update data attributes for all edit buttons
                idsArr.forEach(function(id) {
                    const editBtn = document.querySelector(`.edit-slide-link[data-slide_id="${id}"]`);
                    if (editBtn) {
                        editBtn.setAttribute('data-link', res.link || '');
                        editBtn.setAttribute('data-heading', res.heading || '');
                        editBtn.setAttribute('data-heading_color', res.heading_color || '');
                        editBtn.setAttribute('data-sub_heading', res.sub_heading || '');
                        editBtn.setAttribute('data-sub_heading_color', res.sub_heading_color || '');
                        editBtn.setAttribute('data-cto', res.cto || '');
                        editBtn.setAttribute('data-button_bg_color', res.button_bg_color || '');
                        editBtn.setAttribute('data-heading_bold', res.heading_bold ? '1' : '0');
                        editBtn.setAttribute('data-heading_italic', res.heading_italic ? '1' : '0');
                        editBtn.setAttribute('data-sub_heading_bold', res.sub_heading_bold ? '1' : '0');
                        editBtn.setAttribute('data-sub_heading_italic', res.sub_heading_italic ? '1' : '0');
                        editBtn.setAttribute('data-swipe_headings', res.swipe_headings ? '1' : '0');
                        editBtn.setAttribute('data-cto_bold', res.cto_bold ? '1' : '0');
                        editBtn.setAttribute('data-cto_italic', res.cto_italic ? '1' : '0');
                        editBtn.setAttribute('data-status', res.status ? '1' : '0');
                    }
                });

                // Success messages
                const showSuccess = (selector, msg) => {
                    const el = document.querySelector(selector);
                    if (el) {
                        el.textContent = msg;
                        el.style.color = 'green';
                        setTimeout(() => el.textContent = '', 2000);
                    }
                };
                
                showSuccess('.edit-link-success', 'Link saved ✓');
                showSuccess('.edit-heading-success', 'Heading saved ✓');
                showSuccess('.edit-sub-heading-success', 'Sub-heading saved ✓');
                showSuccess('.edit-cto-success', 'CTO saved ✓');

                // Close modal after short delay
                setTimeout(() => {
                    $('#editLinkModal').modal('hide');
                    window.location.reload();
                }, 1500);
            }
        })
        .catch(error => {
            console.error("Error:", error);
            $('#editLinkModal').modal('hide');
            alert('Error updating slide: ' + error.message);
        });
    });
</script>

@endsection