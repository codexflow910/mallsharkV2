@extends('layouts.dashboard-main')
@section('content')
@section('title', 'Banner Management')
<main>
    <section class="admin-dashboard dshbrdin buyer mt-0 user-management manage-categories-section increased-fnt-cards">
        @include('admin.adminsidebar')
        <!-- @dump($allBannerSets) -->
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <meta name="csrf-token" content="{{ csrf_token() }}">
            <title>Banner & Sliders Sets</title>
            <!-- Bootstrap 4 CSS -->
            <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
            <link rel="stylesheet" href="styles.css">
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
        </head>

        <body class="bg-white p-4">
            <div class="container-fluid px-3">
                <div class=" mt-3 card">
                    <div class="row px-2">
                        <!-- Left Column -->
                        <div class="col-md-6 border-right pr-4 pb-3">
                            <h5 class="font-weight-bold mb-3 p-2">Banner & Sliders Sets</h5>
                            <div id="setsContainer"></div>
                            <button id="addNewBtn" class="btn btn-dark font-weight-bold">Add New</button>
                        </div>
    
                        <!-- Right Column -->
                        <div class="col-md-6 pl-4 pb-3 d-none" id="rightSide"> <!-- d-none -->
                            <form id="setForm" enctype="multipart/form-data" method="POST">
    
                                <!-- Set Name field (added above gray grid) -->
                                <div class="form-group">
                                    <label for="setName">Set Name</label>
                                    <input type="text" id="setName" class="form-control" placeholder="Enter set name" />
                                </div>
    
                                <h6 class="font-weight-bold" id="editorTitle">Set Slider</h6>
    
                                <!-- Large Preview -->
                                <div id="mainPreview" class="bg-secondary rounded mb-3" style="height:250px;"></div>
    
                                <!-- Thumbnails (gray grid, same UI) -->
                                <div class="row no-gutters mb-3" id="thumbGrid">
                                    <div class="col-3 upload-box bg-secondary rounded d-flex justify-content-center align-items-center m-1"
                                        style="height:100px; cursor:pointer;" id="addThumbBox">
                                        <span class="h2 text-light">+</span>
                                        <input type="file" id="thumbInput" accept="image/*" />
                                    </div>
                                </div>
    
                                <!-- Blue custom grid -->
                                <div class="custom-grid mb-3">
                                    <div class="box box1 upload-box">1<input type="file" name="box1" class="file-input"
                                            accept="image/*" /></div>
                                    <div class="box box2 upload-box">2<input type="file" name="box2" class="file-input"
                                            accept="image/*" /></div>
                                    <div class="box slider-box"></div>
                                    <div class="box box3 upload-box">3<input type="file" name="box3" class="file-input"
                                            accept="image/*" /></div>
                                    <div class="box box4 upload-box">4<input type="file" name="box4" class="file-input"
                                            accept="image/*" /></div>
                                    <div class="box box5 upload-box">5<input type="file" name="box5" class="file-input"
                                            accept="image/*" /></div>
                                </div>
                                <!-- Mobile Banners Grid -->
                                <div class="mobile-banners-section mb-3">
                                    <h6 class="font-weight-bold mt-3" id="mobileBannersTitle">Mobile Sliders</h6>
                                    <div class="d-flex flex-wrap space-2 mobile-banners-grid" id="mobileBannersGrid">
                                        <div class="mblbox mblbox1 upload-box col-3 bg-secondary rounded d-flex justify-content-center align-items-center text-light m-1 p-0" 
                                            style="height:100px; cursor:pointer;">
                                            1 p-0
                                            <input type="file" name="mblbox1" class="file-input" accept="image/*" />
                                        </div>
                                        <div class="mblbox mblbox2 upload-box col-3 bg-secondary rounded d-flex justify-content-center align-items-center text-light m-1 p-0" 
                                            style="height:100px; cursor:pointer;">
                                            2
                                            <input type="file" name="mblbox2" class="file-input" accept="image/*" />
                                        </div>
                                        <div class="mblbox mblbox3 upload-box col-3 bg-secondary rounded d-flex justify-content-center align-items-center text-light m-1 p-0" 
                                            style="height:100px; cursor:pointer;">
                                            3
                                            <input type="file" name="mblbox3" class="file-input" accept="image/*" />
                                        </div>
                                        <div class="mblbox mblbox4 upload-box col-3 bg-secondary rounded d-flex justify-content-center align-items-center text-light m-1 p-0" 
                                            style="height:100px; cursor:pointer;">
                                            4
                                            <input type="file" name="mblbox4" class="file-input" accept="image/*" />
                                        </div>
                                        <div class="mblbox mblbox5 upload-box col-3 bg-secondary rounded d-flex justify-content-center align-items-center text-light m-1 p-0" 
                                            style="height:100px; cursor:pointer;">
                                            5
                                            <input type="file" name="mblbox5" class="file-input" accept="image/*" />
                                        </div>
                                    </div>
                                </div>
                                <!-- Start date + Save -->
                                <div class="d-flex justify-content-between align-items-center">
                                    <div class="form-inline">
                                        <label class="mr-2">Start date</label>
                                        <!-- <input type="date" id="startDate" class="form-control" /> -->
                                        <input type="text" id="startDate" placeholder="Select Start Date" />

                                    </div>
                                    <button type="submit" id="saveBtn" class="btn btn-dark">Save</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bootstrap 4 JS -->
            <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
            <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
            <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

        </body>

        <script>
            const resData = @json($allBannerSets);
            console.log("Response Data: ", resData);
            const selectedDates = @json($selectedDates);
            // console.log("Selected Dates from backend:", selectedDates);
            // Application State
            let setCount = 0;
            let editingSetId = null;
            let isEditMode = false;
            const maxThumbs = 8;

            // Track attachment IDs for existing images
            const attachmentIds = {
                slider: [],
                boxes: {},
            };
            const slide_IDs = {
                slider: [],
                boxes: {},
            };
            const mobileFiles = {};

            const addNewBtn = document.getElementById("addNewBtn");
            const rightSide = document.getElementById("rightSide");
            const setsContainer = document.getElementById("setsContainer");
            const startDateInput = document.getElementById("startDate");
            const mainPreview = document.getElementById("mainPreview");
            const setForm = document.getElementById("setForm");
            const thumbGrid = document.getElementById("thumbGrid");
            const addThumbBox = document.getElementById("addThumbBox");
            const thumbInput = document.getElementById("thumbInput");
            const mobileBanners = document.getElementById("mobileBannersGrid");

            // Initialize flatpickr and store the instance
            let flatpickrInstance = null;

            // Wait for DOM to be ready and flatpickr to be loaded
            document.addEventListener("DOMContentLoaded", () => {
                if (typeof flatpickr !== 'undefined') {
                    flatpickrInstance = flatpickr("#startDate", {
                        dateFormat: "Y-m-d",
                        disable: selectedDates,
                        minDate: "today",
                        onChange: function(selectedDatesArr, dateStr) {
                            console.log("Selected date:", dateStr);
                        }
                    });
                } else {
                    console.error("Flatpickr library not loaded");
                }
            });


            // Memory storage for thumbnail files and their types (new file vs existing)
            const thumbFiles = [];

            // Utility function to create file keys
            const fileKey = (f) => `${f.name}|${f.size}|${f.lastModified}`;

            // Initialize the application
            document.addEventListener("DOMContentLoaded", () => {
                renderSets(resData);
                initializeBlueGrid();
                initializeMobileGrid();
            });

            // Show editor for new set
            addNewBtn.addEventListener("click", () => {
                setCount++;
                editingSetId = null;
                isEditMode = false;
                document.getElementById("editorTitle").innerText = `Set Slider`;
                document.getElementById("saveBtn").innerText = "Save";
                rightSide.classList.remove("d-none");
                resetForm();
            });

            // Render sets in left sidebar
            function renderSets(data) {
                setsContainer.innerHTML = "";
                data.forEach((set) => {
                    
                    const setDiv = document.createElement("div");
                    const statusText = getStatusText(set.status);
                    setDiv.className =
                        "d-flex justify-content-between align-items-start border p-3 mb-3";
                    setDiv.dataset.setId = set.id;
                    setDiv.innerHTML = `
                        <div>
                            <span class="font-weight-bold set-title">${set.name}</span>
                            <br/>
                            <div class="d-flex">
                                <p class="p-0 mr-1 mb-0">Started: </p>
                                <p class="font-weight-bold set-date p-0 mr-1 mb-0 ${statusText === "Active" ? "text-success" : ""}">${
                                set.start_date
                                }</p>
                            </div>
                            <div class="d-flex">
                                <p class="p-0 mr-1 mb-0">Status:</p>
                                <p class="p-0 mr-1 mb-0 ${statusText === "Active" ? "text-success font-weight-bold" : ""}">${statusText}</p>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <button class="btn btn-link p-0 mr-3 btn-edit" data-set-id="${
                            set.id
                            }">Edit</button>
                            <button class="btn btn-link text-danger p-0 btn-delete ${set.status === "active" ? "disabled" : ""}" data-set-id="${
                            set.id
                            }">Delete</button>
                        </div>
                    `;

                    // Edit button event listener
                    setDiv.querySelector(".btn-edit").addEventListener("click", (e) => {
                        const setId = parseInt(e.target.getAttribute("data-set-id"));
                        editSet(setId);
                    });

                    // Delete button event listener
                    setDiv.querySelector(".btn-delete").addEventListener("click", (e) => {
                        const setId = parseInt(e.target.getAttribute("data-set-id"));
                        deleteSet(setId, setDiv, set.status);
                    });

                    // Activate button event listener - ONLY if button exists
                    const activateBtn = setDiv.querySelector(".btn-activate");
                    if (activateBtn) {
                        activateBtn.addEventListener("click", (e) => {
                            const setId = parseInt(e.target.getAttribute("data-set-id"));
                            activateSet(setId);
                        });
                    }

                    setsContainer.appendChild(setDiv);
                });
            }

            // Edit set functionality - Fixed
            function editSet(setId) {
                console.log("Editing set with ID:", setId);

                editingSetId = setId;
                isEditMode = true;

                // Use the first set's ID (14) to load enhanced sample data
                const setData = resData.find(s => s.id === setId);

                if (!setData) {
                    console.error("Set data not found for ID:", setId);
                    alert("Set data not found!");
                    return;
                }

                document.getElementById("editorTitle").innerText = `Editing ${setData.name}`;
                document.getElementById("saveBtn").innerText = "Update";
                rightSide.classList.remove("d-none");

                fillFormWithSetData(setData);
            }

            // ========================================
            // 1. GLOBAL validateMobileImage Function
            // ========================================
            function validateMobileImage(file) {
                return new Promise((resolve) => {
                    const img = new Image();
                    img.onload = function () {
                        const requiredWidth = 420;
                        const requiredHeight = 270;

                        const sizeMatch =
                            this.width >= requiredWidth && this.height >= requiredHeight;

                        resolve({
                            isValid: sizeMatch,
                            sizeMatch,
                            actualSize: `${this.width}x${this.height}`,
                            requiredSize: `${requiredWidth}x${requiredHeight}`,
                        });
                    };

                    img.onerror = function () {
                        resolve({
                            isValid: false,
                            error: "Could not load image"
                        });
                    };

                    img.src = URL.createObjectURL(file);
                });
            }

            // ========================================
            // 2. Add Editable Mobile Banner
            // ========================================
            function addEditableMobileBanner(imgSrc, slideId, boxName, boxNumber) {
                const box = document.querySelector(`.mobile-banners-grid .upload-box input[name="${boxName}"]`)?.closest('.upload-box');
                
                if (!box) {
                    console.warn("Mobile box not found for:", boxName);
                    return;
                }
                
                const input = box.querySelector('.file-input');
                
                // Set box HTML with controls
                box.innerHTML = `
                    <img src="${imgSrc}" alt="Mobile Banner ${boxNumber}" style="width:100%; height:100%; object-fit:cover; cursor:pointer;">
                    <div class="thumbnail-controls position-absolute" style="top:2px; right:2px; display:none;">
                        <button class="btn btn-sm btn-danger btn-delete-mobile" type="button" style="padding:2px 6px; font-size:10px;" title="Delete">×</button>
                    </div>
                    <div class="thumbnail-controls position-absolute" style="top:2px; left:2px; display:none;">
                        <button class="btn btn-sm btn-primary btn-replace-mobile" type="button" style="padding:2px 6px; font-size:10px;" title="Replace">↻</button>
                    </div>
                `;
                
                // Re-append original input
                if (input) {
                    box.appendChild(input);
                }
                
                box.dataset.slideId = slideId || "";
                box.dataset.boxName = boxName;
                
                // Setup controls
                setupMobileBannerControls(box, boxName);
                
                // Show controls on hover
                box.addEventListener("mouseenter", () => {
                    box.querySelectorAll(".thumbnail-controls").forEach((control) => {
                        control.style.display = "block";
                    });
                });
                
                box.addEventListener("mouseleave", () => {
                    box.querySelectorAll(".thumbnail-controls").forEach((control) => {
                        control.style.display = "none";
                    });
                });
            }

            // ========================================
            // 3. Setup Mobile Banner Controls
            // ========================================
            function setupMobileBannerControls(box, boxName) {
                const deleteBtn = box.querySelector(".btn-delete-mobile");
                const replaceBtn = box.querySelector(".btn-replace-mobile");
                const fileInput = box.querySelector(".file-input");
                const boxNumber = boxName.replace('mblbox', '');
                
                // Delete mobile banner
                deleteBtn.addEventListener("click", (e) => {
                    e.stopPropagation();
                    if (confirm("Are you sure you want to delete this mobile banner?")) {
                        // Clear the box and restore original state
                        box.innerHTML = `${boxNumber}<input type="file" name="${boxName}" class="file-input" accept="image/*" />`;
                        
                        // Clear attachment ID
                        delete attachmentIds.boxes[boxName];
                        delete slide_IDs.boxes[boxName];
                        delete mobileFiles[boxName]; // Clear from tracking
                        
                        // Re-initialize this specific box with controls
                        initializeSingleMobileBox(box, boxName, boxNumber);
                    }
                });
                
                // Replace mobile banner
                replaceBtn.addEventListener("click", (e) => {
                    e.stopPropagation();
                    fileInput.click();
                });
                
                fileInput.addEventListener("change", async (e) => {
                    const file = e.target.files && e.target.files[0];
                    if (!file) return;
                    
                    // Validate file type
                    if (!file.type.startsWith('image/')) {
                        alert("Please select a valid image file");
                        e.target.value = "";
                        return;
                    }
                    
                    try {
                        // Validate size (768x459)
                        const validation = await validateMobileImage(file);
                        
                        if (validation.error) {
                            alert(validation.error);
                            e.target.value = "";
                            return;
                        }
                        
                        if (!validation.isValid) {
                            alert(`Image size must be min 420 x 270.\n- Expected size: ${validation.requiredSize}\n- Got size: ${validation.actualSize}`);
                            e.target.value = "";
                            return;
                        }

                        // ✅ Track the new file (mark as new, not existing)
                        mobileFiles[boxName] = {
                            file: file,
                            isExisting: false,
                            attachmentId: null,
                            slideId: slide_IDs.boxes[boxName] || null
                        };
                        
                        // Update image (no need to re-add controls, they're already there)
                        const reader = new FileReader();
                        reader.onload = (ev) => {
                            const img = box.querySelector("img");
                            if (img) {
                                img.src = ev.target.result;
                            }
                        };
                        reader.readAsDataURL(file);
                        
                    } catch (error) {
                        console.error('Error validating mobile image:', error);
                        alert("Error validating image. Please try again.");
                        e.target.value = "";
                    }
                    
                    e.target.value = "";
                });
            }

            // ========================================
            // 4. Initialize Single Mobile Box (After Delete)
            // ========================================
            function initializeSingleMobileBox(box, boxName, boxNumber) {
                const newInput = box.querySelector('.file-input');
                
                // Remove any existing click listeners by cloning
                const newBox = box.cloneNode(true);
                box.parentNode.replaceChild(newBox, box);
                
                const finalInput = newBox.querySelector('.file-input');
                
                newBox.addEventListener("click", () => finalInput.click());
                
                finalInput.addEventListener("change", async (e) => {
                    const f = e.target.files && e.target.files[0];
                    if (!f) return;
                    
                    // Validate file type
                    if (!f.type.startsWith("image/")) {
                        alert("Please select a valid image file.");
                        finalInput.value = "";
                        return;
                    }
                    
                    try {
                        // Validate size (768x459)
                        const validation = await validateMobileImage(f);
                        
                        if (validation.error) {
                            alert(validation.error);
                            finalInput.value = "";
                            return;
                        }
                        
                        if (!validation.isValid) {
                            alert(`Image size must be min 420 x 270.\n- Expected size: ${validation.requiredSize}\n- Got size: ${validation.actualSize}`);
                            finalInput.value = "";
                            return;
                        }

                        // ✅ Track the new file
                        mobileFiles[boxName] = {
                            file: f,
                            isExisting: false,
                            attachmentId: null,
                            slideId: null
                        };
                        
                        // ✅ Display image with controls using addEditableMobileBanner
                        const reader = new FileReader();
                        reader.onload = (ev) => {
                            addEditableMobileBanner(ev.target.result, null, boxName, boxNumber);
                        };
                        reader.readAsDataURL(f);
                        
                    } catch (error) {
                        console.error('Error validating mobile image:', error);
                        alert("Error validating image. Please try again.");
                        finalInput.value = "";
                    }
                });
            }

            // ========================================
            // 5. Initialize Mobile Grid (For New Sets)
            // ========================================
            function initializeMobileGrid() {
                const mobileInputs = Array.from(
                    document.querySelectorAll(
                        ".mobile-banners-grid .upload-box .file-input[name]:not([disabled])"
                    )
                );

                function isMobileDuplicate(currentInput, file) {
                    const k = fileKey(file);
                    return mobileInputs.some(
                        (inp) =>
                        inp !== currentInput &&
                        inp.files &&
                        inp.files[0] &&
                        fileKey(inp.files[0]) === k
                    );
                }

                mobileInputs.forEach((input) => {
                    const box = input.closest(".upload-box");
                    const boxName = input.getAttribute("name"); // mblbox1, mblbox2, etc.
                    const boxNumber = boxName.replace('mblbox', ''); // Extract number
                    
                    // Remove existing event listeners to prevent duplicates
                    const newBox = box.cloneNode(true);
                    box.parentNode.replaceChild(newBox, box);

                    const newInput = newBox.querySelector(".file-input");

                    newBox.addEventListener("click", () => newInput.click());

                    newInput.addEventListener("change", async (e) => {
                        const f = e.target.files && e.target.files[0];
                        if (!f) return;

                        if (isMobileDuplicate(newInput, f)) {
                            alert("This image is already selected.");
                            newInput.value = "";
                            return;
                        }

                    // Validate file type
                        if (!f.type.startsWith("image/")) {
                            alert("Please select a valid image file.");
                            newInput.value = "";
                            return;
                        }

                        try {
                            // Validate size
                            const validation = await validateMobileImage(f);

                            if (validation.error) {
                                alert(validation.error);
                                newInput.value = "";
                                return;
                            }

                            if (!validation.isValid) {
                                let errorMsg = `Image size must be min 420 x 270.\n`;
                                if (!validation.sizeMatch) {
                                    errorMsg += `- Expected size: ${validation.requiredSize}\n- Got size: ${validation.actualSize}\n`;
                                }
                                alert(errorMsg);
                                newInput.value = "";
                                return;
                            }

                            // ✅ Track the new file
                            mobileFiles[boxName] = {
                                file: f,
                                isExisting: false,
                                attachmentId: null,
                                slideId: null
                            };

                            // ✅ If validation passes, use addEditableMobileBanner to add controls
                            const reader = new FileReader();
                            reader.onload = (ev) => {
                                addEditableMobileBanner(ev.target.result, null, boxName, boxNumber);
                            };
                            reader.readAsDataURL(f);

                        } catch (error) {
                            console.error('Error validating mobile image size:', error);
                            alert("Error validating image. Please try again.");
                            newInput.value = "";
                            return;
                        }
                    });
                });
            }


            // Fill form with existing set data
            function fillFormWithSetData(setData) {
                console.log("Filling form with data:", setData);
                const mobileSlides = setData.slides.filter((s) => s.type === "mbl");
                console.log("mobile slides:", mobileSlides);

                // Reset attachment IDs
                attachmentIds.slider = [];
                attachmentIds.boxes = {};

                // Reset slide IDs
                slide_IDs.slider = [];
                slide_IDs.boxes = {};

                // Reset mobile files tracking
                Object.keys(mobileFiles).forEach(key => delete mobileFiles[key]);

                // Set name and date
                document.getElementById("setName").value = setData.name || "";
                startDateInput.value = setData.start_date;
                
                if(setData.status === "active") {
                    console.log("set active | start date value: ", setData.status, setData.start_date);
                    startDateInput.disabled = true;
                    startDateInput.style.cursor = 'not-allowed';
                    startDateInput.style.backgroundColor = '#e9ecef';
                    startDateInput.title = 'Cannot change date for active set';
                } else {
                    console.log("set active | start date value: ", setData.status, setData.start_date);
                    startDateInput.disabled = false;
                    startDateInput.style.cursor = 'pointer';
                    startDateInput.style.backgroundColor = '#fff';
                    startDateInput.title = 'Select start date';
                }

                // Reset form
                resetThumbs();
                mainPreview.innerHTML = "";
                resetBlueGrid();
                resetMobileGrid(); // Important: Reset mobile grid first

                // Fill gray grid (thumbnails) with slider images - now editable
                const sliderSlides = setData.slides.filter((s) => s.type === "slider");
                console.log("Slider slides:", sliderSlides);

                sliderSlides.forEach((slide, index) => {
                    if (index >= maxThumbs) return;

                    const imgUrl = slide.image_url;
                    console.log("Adding editable thumbnail:", imgUrl);
                    const slide_ID = slide.slide_id;
                    console.log("Slide ID:", slide_ID);

                    addEditableThumbnail(imgUrl, slide_ID, index);

                    // Store attachment ID and slide ID
                    attachmentIds.slider[index] = slide.attachment_id;
                    slide_IDs.slider[index] = slide_ID;

                    // Add dummy file to memory for validation (will be replaced if user uploads new image)
                    thumbFiles.push({
                        file: createDummyFile(`slide_${slide.slide_id}.jpg`),
                        isExisting: true,
                        attachmentId: slide.attachment_id,
                        slideId: slide.slide_id,
                    });

                    // Show first image in main preview
                    if (index === 0) {
                        mainPreview.innerHTML = `<img src="${imgUrl}" class="img-fluid h-100 w-100" style="object-fit:cover;" alt="Main Preview">`;
                    }
                });

                // Hide add button if max reached
                if (thumbFiles.length >= maxThumbs) {
                    addThumbBox.style.display = "none";
                }

                // Fill blue grid boxes with banner images
                const boxMap = {
                    1: "box1",
                    2: "box2",
                    3: "box3",
                    4: "box4",
                    5: "box5"
                };

                setData.slides.forEach((slide) => {
                    if (slide.type === "banner" && boxMap[slide.box_number]) {
                        const boxClass = boxMap[slide.box_number];
                        const box = document.querySelector(`.custom-grid .${boxClass}`);

                        if (!box) {
                            console.warn("Box not found for class:", boxClass);
                            return;
                        }

                        // Preserve the input element
                        const input = box.querySelector("input.file-input");
                        const imgUrl =
                            slide.image_url ||
                            `https://via.placeholder.com/150?text=Box+${slide.box_number}`;

                        console.log("Adding banner to box:", boxClass, imgUrl);

                        // Set image
                        box.innerHTML = `<img src="${imgUrl}" alt="Banner ${slide.box_number}">`;

                        // Store attachment ID for existing box image
                        attachmentIds.boxes[boxClass] = slide.attachment_id;
                        slide_IDs.boxes[boxClass] = slide.slide_id;

                        // Re-append input
                        if (input) {
                            box.appendChild(input);
                        }
                    }
                });

                // Fill mobile grid boxes with mobile banner images
                mobileSlides.forEach((slide) => {
                    if (slide.type === "mbl" && slide.box_number) {
                        const boxName = `mblbox${slide.box_number}`;
                        const slide_ID = slide.slide_id;
                        const imgUrl = slide.image_url || `https://via.placeholder.com/150?text=Mobile+${slide.box_number}`;

                        addEditableMobileBanner(imgUrl, slide_ID, boxName, slide.box_number);

                        // Store attachment ID for existing mobile box image
                        attachmentIds.boxes[boxName] = slide.attachment_id;
                        slide_IDs.boxes[boxName] = slide.slide_id;

                        // ✅ Track existing mobile file
                        mobileFiles[boxName] = {
                            file: createDummyFile(`mobile_${slide_ID}.jpg`),
                            isExisting: true,
                            attachmentId: slide.attachment_id,
                            slideId: slide_ID
                        };
                    }
                });
            }

            // Delete set functionality
            async function deleteSet(setId, setDiv, status) {
                // Double-check status before confirming
                if (status === "active") {
                    alert("⚠️ Cannot delete active set!");
                    return;
                }
                if (!confirm("Are you sure you want to delete this set?")) return;

                try {
                    const response = await fetch(`/slides/delete-set/${setId}`, {
                        method: "DELETE",
                        headers: {
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')?.content,
                            "Accept": "application/json"
                        }
                    });

                    let data = {};
                    try {
                        data = await response.json();
                    } catch {
                        // fallback if no JSON returned
                        window.location.reload();
                        data = { success: true, message: "Slide set deleted " };
                    }

                    if (!response.ok) {
                        throw new Error(data.message || `HTTP ${response.status}`);
                    }

                    // ✅ Assume success if 200, even if success flag missing
                    if (response.ok && (data.success || Object.keys(data).length === 0)) {
                        window.location.reload();
                        setDiv.remove();

                        if (editingSetId === setId) {
                            rightSide.classList.add("d-none");
                            resetForm();
                        }

                        console.log("Delete response:", data);
                    } else {
                        throw new Error(data.message || "Delete failed");
                    }

                } catch (error) {
                    console.error("❌ Error deleting set:", error);
                    alert(error.message || "Something went wrong. Please try again later.");
                }
            }            

            // Handle form submission
            setForm.addEventListener("submit", (e) => {
                e.preventDefault();

                const setName = document.getElementById("setName").value;
                const startDate = startDateInput.value;
                const today = new Date().toISOString().split("T")[0]; // YYYY-MM-DD

                // Validation
                if (!setName || setName.length < 3) {
                    alert("Set name should be at least 3 characters long.");
                    return;
                }

                // ❌ No spaces allowed in set name
                if (!/^[^\s]+(\s[^\s]+)*$/.test(setName)) {
                    alert("Set name can contain single spaces only between words.");
                    return;
                }


                if (thumbFiles.length === 0) {
                    alert("Please add at least one thumbnail image.");
                    return;
                }

                // Validate blue boxes (only for new sets or if images are changed)
                if (!isEditMode && !validateBlueBoxes()) {
                    return;
                }

                if (!startDate) {
                    alert("Please select a start date.");
                    return;
                }

                if(!isEditMode){
                    // ✅ Prevent selecting past date
                    if (startDate < today) {
                        alert("Please select a future date.");
                        return;
                    }
    
                    // ✅ Prevent selecting restricted dates from backend
                    if (selectedDates.includes(startDate)) {
                        alert("Please select another date.");
                        return;
                    }
                }

                // Build FormData
                const formData = buildFormData(setName, startDate);

                // Submit to backend
                submitFormData(formData);
            });

            // Validate blue boxes
            function validateBlueBoxes() {
                const allBlueBoxes = document.querySelectorAll(
                    ".custom-grid .upload-box input[type=file]"
                );

                for (const inp of allBlueBoxes) {
                    const nm = inp.getAttribute("name");
                    if (!nm || inp.disabled) continue;

                    // Check if box has image (either uploaded or existing)
                    const box = inp.closest(".upload-box");
                    const hasExistingImage = box.querySelector("img");
                    const hasNewFile = inp.files && inp.files.length > 0;

                    if (!hasExistingImage && !hasNewFile) {
                        alert(`Please select image for ${nm}.`);
                        return false;
                    }
                }
                return true;
            }

            function buildFormData(setName, startDate) {
                const formData = new FormData();

                if(isEditMode && editingSetId) {
                    thumbFiles.forEach((thumb, index) => {
                        // Ensure slide_id is always available and never undefined
                        let slideId = thumb.slideId;

                        // If slideId is null/undefined, generate or get from slide_IDs array
                        if (!slideId) {
                        slideId = slide_IDs.slider[index]; //
                        }

                        if (thumb.file.size > 0) {
                            if(slideId){
                                formData.append(`slider[${slideId}]`, thumb.file);
                            }else{
                                formData.append(`slider[new][${index}]`, thumb.file);
                            }
                        } else if (thumb.isExisting && thumb.attachmentId) {
                            formData.append(`slider[${slideId}]`, thumb.attachmentId.toString());
                        }
                    });

                    // Add blue grid images
                    const allBlueBoxes = document.querySelectorAll(
                        ".custom-grid .upload-box input[type=file]"
                    );
                    allBlueBoxes.forEach((inp) => {
                        const nm = inp.getAttribute("name");
                        if (!nm || inp.disabled) return;

                        if (inp.files && inp.files[0]) {
                        formData.append(`${nm}`, inp.files[0]);
                        } else if (attachmentIds.boxes[nm]) {
                        formData.append(`${nm}`, attachmentIds.boxes[nm].toString());
                        }
                    });

                    Object.keys(mobileFiles).forEach((boxName) => {
                        const mobileFile = mobileFiles[boxName];
                        
                        if (mobileFile.file.size > 0) {
                            // New file uploaded
                            formData.append(`${boxName}[new]`, mobileFile.file);
                        } else if (mobileFile.isExisting && mobileFile.attachmentId) {
                            // Existing file, send attachment ID
                            formData.append(`${boxName}`, mobileFile.attachmentId.toString());
                        }
                    });
                } else {
                    thumbFiles.forEach((thumb, index) => {
                        if (thumb.file.size > 0) {
                        formData.append(`slider[${index}]`, thumb.file);
                        }
                    });

                    // Add blue grid images
                    const allBlueBoxes = document.querySelectorAll(
                        ".custom-grid .upload-box input[type=file]"
                    );
                    allBlueBoxes.forEach((inp) => {
                        const nm = inp.getAttribute("name");
                        if (!nm || inp.disabled) return;

                        if (inp.files && inp.files[0]) {
                        formData.append(`${nm}`, inp.files[0]);
                        }
                    });
                    Object.keys(mobileFiles).forEach((boxName) => {
                        const mobileFile = mobileFiles[boxName];
                        
                        if (mobileFile.file.size > 0) {
                            formData.append(`${boxName}`, mobileFile.file);
                        }
                    });
                }

                // Add metadata
                formData.append("name", setName);
                formData.append("startDate", startDate);
                formData.append("slider_count", thumbFiles.length.toString());

                if (isEditMode && editingSetId) {
                    formData.append("set_id", editingSetId.toString());
                    formData.append("_method", "PUT"); // Laravel method override for update
                }

                return formData;
            }

            // Submit form data to backend
            async function submitFormData(formData) {
                console.log("FormData payload:", [...formData.entries()]);

                saveBtn.disabled = true;
                if(isEditMode){
                    saveBtn.innerText = "Updating...";
                }else{
                    saveBtn.innerText = "Saving...";
                }

                const url = isEditMode
                    ? `/slides/update-set/${editingSetId}`
                    : "/slides/create-set";

                if (isEditMode) formData.append("_method", "POST");

                const csrfToken =
                    document.querySelector('meta[name="csrf-token"]')?.content ||
                    (document.cookie.match(/XSRF-TOKEN=([^;]+)/)?.[1] ?? "");

                try {
                    const response = await fetch(url, {
                        method: "POST",
                        body: formData,
                        headers: { "X-CSRF-TOKEN": csrfToken }
                    });

                    const data = await response.json().catch(() => ({}));

                    if (!response.ok) {
                        let message = `HTTP ${response.status}`;
                        switch (response.status) {
                            case 405: message = "Method Not Allowed – check your route & controller."; break;
                            case 404: message = "API endpoint not found."; break;
                            case 419: message = "CSRF token mismatch – refresh and try again."; break;
                            case 422: message = `Validation failed: ${JSON.stringify(data.errors || data.message)}`; break;
                        }
                        throw new Error(message);
                    }

                    // ✅ Success
                    alert(data.message || `Set ${isEditMode ? "updated" : "created"} successfully!`);
                    console.log("✅ Backend response:", data);
                    window.location.reload();
                    // Update UI using backend’s returned slide_set
                    // updateUIAfterSubmit(data.slide_set);

                    rightSide.classList.add("d-none");
                    resetForm();

                } catch (err) {
                    console.error("❌ Submit error:", err);
                    alert(err.message || "Unexpected error occurred");
                    saveBtn.disabled = false;
                    if(isEditMode){
                        saveBtn.innerText = "Update";
                    }else{
                        saveBtn.innerText = "Save";
                    }
                }
            }

            // Reset form and state
            function resetForm() {
                setForm.reset();
                resetThumbs();
                resetBlueGrid();
                resetMobileGrid();
                mainPreview.innerHTML = "";
                thumbFiles.length = 0;
                editingSetId = null;
                isEditMode = false;

                // Reset attachment IDs
                attachmentIds.slider = [];
                attachmentIds.boxes = {};

                // Reset slide IDs
                slide_IDs.slider = [];
                slide_IDs.boxes = {};
                // Reset mobile files tracking
                Object.keys(mobileFiles).forEach(key => delete mobileFiles[key]);

                // Clear date input and flatpickr
                startDateInput.value = "";
                if (flatpickrInstance) {
                    flatpickrInstance.clear();
                }
            }

            // Reset thumbnails grid
            function resetThumbs() {
                thumbGrid.querySelectorAll(".upload-box").forEach((box) => {
                    if (box.id !== "addThumbBox") box.remove();
                });
                addThumbBox.style.display = "flex";
                thumbInput.value = "";
                thumbFiles.length = 0;
                mainPreview.innerHTML = "";     // Clear main preview   //
            }

            // Reset blue grid to original state
            function resetBlueGrid() {
                const boxMap = {
                    1: "box1",
                    2: "box2",
                    3: "box3",
                    4: "box4",
                    5: "box5"
                };

                Object.entries(boxMap).forEach(([num, boxClass]) => {
                    const box = document.querySelector(`.custom-grid .${boxClass}`);
                    if (box) {
                        box.innerHTML = `${num}<input type="file" name="${boxClass}" class="file-input" accept="image/*" />`;
                    }
                });

                // Re-initialize blue grid event listeners
                initializeBlueGrid();
            }

            // Reset Mobile grid to original state
            function resetMobileGrid() {
                const mobileBoxes = mobileBanners.querySelectorAll(
                    ".mobile-banners-grid .upload-box"
                );
                mobileBoxes.forEach((box, index) => {
                    const boxNum = index + 1;
                    box.innerHTML = `${boxNum}<input type="file" name="mblbox${boxNum}" class="file-input" accept="image/*" />`;
                });
                // Re-initialize mobile grid event listeners
                initializeMobileGrid();
            }

            // Get status text
            function getStatusText(status) {
                switch (status) {
                    case "inactive":
                        return "Inactive";
                    case "expired":
                        return "Expired";
                    case "active":
                        return "Active";
                    default:
                        return status;
                }
            }

            // Thumbnail upload functionality
            addThumbBox.addEventListener("click", () => thumbInput.click());

            thumbInput.addEventListener("change", async (e) => {
                const file = e.target.files && e.target.files[0];
                if (!file) return;

                 // Validate file type
                if (!file.type.startsWith('image/')) {
                    alert("Please select a valid image file");
                    e.target.value = "";
                    return;
                }

                // Prevent duplicates
                const key = fileKey(file);
                if (thumbFiles.some((f) => fileKey(f.file) === key)) {
                    alert("This thumbnail is already added.");
                    e.target.value = "";
                    return;
                }

                if (thumbFiles.length >= maxThumbs) {
                    alert("Maximum thumbnails reached.");
                    e.target.value = "";
                    return;
                }

                try {
                    // Validate aspect ratio (5:3)
                    const validation = await validateSliderAspectRatio(file);
                    
                    if (validation.error) {
                        alert(validation.error);
                        e.target.value = "";
                        return;
                    }

                    if (!validation.isValid) {
                        alert(`Please upload an image with ratio of 5:3 and size 768 x 459.\n\nCurrent image: ${validation.actualSize} (${validation.imageRatio.toFixed(2)}:1)`);
                        e.target.value = "";
                        return;
                    }

                    // If validation passes, add thumbnail
                    thumbFiles.push({
                        file: file,
                        isExisting: false,
                        attachmentId: null,
                    });

                    console.log("Thumbnail files:", thumbFiles);

                    // Create preview
                    const reader = new FileReader();
                    reader.onload = (ev) => {
                        addEditableThumbnail(ev.target.result, null, thumbFiles.length - 1);

                        // Update main preview with latest image
                        mainPreview.innerHTML = `<img src="${ev.target.result}" class="img-fluid h-100 w-100" style="object-fit:cover;" alt="Main Preview">`;

                        if (thumbFiles.length >= maxThumbs) {
                            addThumbBox.style.display = "none";
                        }
                    };
                    reader.readAsDataURL(file);

                } catch (error) {
                    console.error('Error validating slider aspect ratio:', error);
                    alert("Error validating image. Please try again.");
                    e.target.value = "";
                }

                e.target.value = "";
            });

            // Function to validate slider thumbnail aspect ratio (5:3)
            function validateSliderAspectRatio(file) {
                return new Promise((resolve) => {
                    const img = new Image();
                    img.onload = function() {
                        const imageRatio = this.width / this.height;
                        const requiredRatio = 5/3; // 5:3 ratio
                        const tolerance = 0.05; // 5% tolerance
                        const requiredWidth = 768;
                        const requiredHeight = 459;
                        
                        // Check if ratio matches within tolerance
                        const ratioMatch = Math.abs(imageRatio - requiredRatio) / requiredRatio <= tolerance;
                        const sizeMatch = this.width === requiredWidth && this.height === requiredHeight;
                        resolve({
                            isValid: ratioMatch && sizeMatch,
                            ratioMatch,
                            sizeMatch,
                            imageRatio: imageRatio,
                            requiredRatio: requiredRatio,
                            actualSize: `${this.width}x${this.height}`,
                            requiredSize: `${requiredWidth}x${requiredHeight}`,
                        });
                    };
                    
                    img.onerror = function() {
                        resolve({
                            isValid: false,
                            error: "Could not load image"
                        });
                    };
                    
                    // Create object URL to load the image
                    img.src = URL.createObjectURL(file);
                });
            }
            // Add editable thumbnail with delete, replace, and reorder functionality
            function addEditableThumbnail(imgSrc, slideId = null, index) {
                const thumb = document.createElement("div");
                thumb.className =
                    "col-3 upload-box bg-secondary rounded m-1 position-relative";
                thumb.style.cssText = "height:100px; overflow:hidden;";
                thumb.dataset.index = index;
                thumb.dataset.slideId = slideId || "";

                thumb.innerHTML = `
                        <img src="${imgSrc}" alt="Thumbnail ${
                    index + 1
                }" style="width:100%; height:100%; object-fit:cover; cursor:pointer;">
                        <div class="thumbnail-controls position-absolute" style="top:2px; right:2px; display:none;">
                            <button class="btn btn-sm btn-danger btn-delete-thumb" type="button" style="padding:2px 6px; font-size:10px;" title="Delete">×</button>
                        </div>
                        <div class="thumbnail-controls position-absolute" style="top:2px; left:2px; display:none;">
                            <button class="btn btn-sm btn-primary btn-replace-thumb" type="button" style="padding:2px 6px; font-size:10px;" title="Replace">↻</button>
                        </div>
                        <input type="file" class="thumb-replace-input" style="display:none;" accept="image/*">
                    `;

                thumbGrid.insertBefore(thumb, addThumbBox);

                // Add event listeners for controls
                setupThumbnailControls(thumb, index);

                // Show controls on hover
                thumb.addEventListener("mouseenter", () => {
                    thumb.querySelectorAll(".thumbnail-controls").forEach((control) => {
                        control.style.display = "block";
                    });
                });

                thumb.addEventListener("mouseleave", () => {
                    thumb.querySelectorAll(".thumbnail-controls").forEach((control) => {
                        control.style.display = "none";
                    });
                });

                // Click to preview in main view
                const img = thumb.querySelector("img");
                img.addEventListener("click", (e) => {
                    e.stopPropagation();
                    mainPreview.innerHTML = `<img src="${imgSrc}" class="img-fluid h-100 w-100" style="object-fit:cover;" alt="Main Preview">`;

                    // Highlight selected thumbnail
                    document.querySelectorAll(".upload-box").forEach((box) => {
                        box.style.border = "";
                    });
                    thumb.style.border = "3px solid #007bff";
                });
            }

            // Setup thumbnail control buttons
            function setupThumbnailControls(thumb, index) {
                const deleteBtn = thumb.querySelector(".btn-delete-thumb");
                const replaceBtn = thumb.querySelector(".btn-replace-thumb");
                const replaceInput = thumb.querySelector(".thumb-replace-input");

                // Delete thumbnail
                deleteBtn.addEventListener("click", (e) => {
                    e.stopPropagation();
                    if (confirm("Are you sure you want to delete this thumbnail?")) {
                        const thumbIndex = parseInt(thumb.dataset.index);
                        thumbFiles.splice(thumbIndex, 1);
                        thumb.remove();
                        updateThumbnailIndices();

                        if (thumbFiles.length < maxThumbs) {
                            addThumbBox.style.display = "flex";
                        }

                        // Update main preview if this was the selected image
                        if (thumbFiles.length > 0) {
                            const firstThumb = thumbGrid.querySelector(".upload-box img");
                            if (firstThumb) {
                                mainPreview.innerHTML = `<img src="${firstThumb.src}" class="img-fluid h-100 w-100" style="object-fit:cover;" alt="Main Preview">`;
                            }
                        } else {
                            mainPreview.innerHTML = "";
                        }
                    }
                });

                // Replace thumbnail - updated to handle attachment IDs
                replaceBtn.addEventListener("click", (e) => {
                    e.stopPropagation();
                    replaceInput.click();
                });

                replaceInput.addEventListener("change", async (e) => {
                    const file = e.target.files && e.target.files[0];
                    if (!file) return;

                    // Validate file type
                    if (!file.type.startsWith('image/')) {
                        alert("Please select a valid image file");
                        e.target.value = "";
                        return;
                    }

                    try {
                        // Validate aspect ratio (5:3)
                        const validation = await validateSliderAspectRatio(file);
                        
                        if (validation.error) {
                            alert(validation.error);
                            e.target.value = "";
                            return;
                        }

                        if (!validation.isValid) {
                            let errorMsg = `❌ Invalid slider image.\n`;
                            if (!validation.ratioMatch) {
                                errorMsg += `- Expected ratio: 5:3\n- Got ratio: ${validation.imageRatio.toFixed(
                                    2
                                )}:1\n`;
                            }
                            if (!validation.sizeMatch) {
                                errorMsg += `- Expected size: ${validation.requiredSize}\n- Got size: ${validation.actualSize}\n`;
                            }
                            alert(errorMsg);
                            e.target.value = "";
                            return;
                        }

                        // If validation passes, replace the thumbnail
                        const thumbIndex = parseInt(thumb.dataset.index);

                        // Update the file and mark as new (not existing)
                        thumbFiles[thumbIndex] = {
                            file: file,
                            isExisting: false,
                            attachmentId: null,
                        };

                        const reader = new FileReader();
                        reader.onload = (ev) => {
                            const img = thumb.querySelector("img");
                            img.src = ev.target.result;

                            // Update main preview if this thumbnail is currently shown
                            const mainImg = mainPreview.querySelector("img");
                            if (mainImg && mainImg.src === img.src) {
                                mainPreview.innerHTML = `<img src="${ev.target.result}" class="img-fluid h-100 w-100" style="object-fit:cover;" alt="Main Preview">`;
                            }
                        };
                        reader.readAsDataURL(file);

                    } catch (error) {
                        console.error('Error validating slider aspect ratio:', error);
                        alert("Error validating image. Please try again.");
                        e.target.value = "";
                    }

                    e.target.value = "";
                });
            }

            // Update thumbnail indices after reordering or deletion
            function updateThumbnailIndices() {
                const thumbnails = thumbGrid.querySelectorAll(
                    ".upload-box:not(#addThumbBox)"
                );
                thumbnails.forEach((thumb, index) => {
                    thumb.dataset.index = index;

                    const img = thumb.querySelector("img");         //
                        if (img) {
                            img.alt = `Thumbnail ${index + 1}`;
                        }
                    });
            }

            // Create dummy file for existing images (used for validation)
            function createDummyFile(filename) {
                return new File([], filename, {
                    type: "image/jpeg"
                });
            }

            // Blue grid functionality
            function initializeBlueGrid() {
                // Define required aspect ratios + sizes for each box
                const requiredSpecs = {
                    box1: { ratio: 16 / 9, ratioName: "16:9", width: 376, height: 221 },
                    box2: { ratio: 9 / 11, ratioName: "9:11", width: 376, height: 459 },
                    box3: { ratio: 9 / 11, ratioName: "9:11", width: 376, height: 459 },
                    box4: { ratio: 32 / 9, ratioName: "32:9", width: 768, height: 220 },
                    box5: { ratio: 16 / 9, ratioName: "16:9", width: 376, height: 221 }
                };
                
                // Tolerance for aspect ratio matching (5%)
                const tolerance = 0.05;
                const blueInputs = Array.from(
                    document.querySelectorAll(
                        ".custom-grid .upload-box .file-input[name]:not([disabled])"
                    )
                );

                function isBlueDuplicate(currentInput, file) {
                    const k = fileKey(file);
                    return blueInputs.some(
                        (inp) =>
                        inp !== currentInput &&
                        inp.files &&
                        inp.files[0] &&
                        fileKey(inp.files[0]) === k
                    );
                }
                // Function to validate aspect ratio + size
                function validateImage(file, boxName) {
                    return new Promise((resolve) => {
                        const img = new Image();
                        img.onload = function () {
                            const imageRatio = this.width / this.height;
                            const spec = requiredSpecs[boxName];
                            if (!spec) {
                                resolve({ isValid: true });
                                return;
                            }

                            const ratioMatch =
                                Math.abs(imageRatio - spec.ratio) / spec.ratio <= tolerance;

                            const sizeMatch =
                                this.width === spec.width && this.height === spec.height;

                            resolve({
                                isValid: ratioMatch && sizeMatch,
                                ratioMatch,
                                sizeMatch,
                                imageRatio,
                                requiredRatio: spec.ratio,
                                ratioName: spec.ratioName,
                                actualSize: `${this.width}x${this.height}`,
                                requiredSize: `${spec.width}x${spec.height}`,
                            });
                        };

                        img.onerror = function () {
                            resolve({
                                isValid: false,
                                error: "Could not load image"
                            });
                        };

                        img.src = URL.createObjectURL(file);
                    });
                }
                blueInputs.forEach((input) => {
                    const box = input.closest(".upload-box");
                    const boxName = input.getAttribute("name"); // box1, box2, etc.
                    // Remove existing event listeners to prevent duplicates
                    const newBox = box.cloneNode(true);
                    box.parentNode.replaceChild(newBox, box);

                    const newInput = newBox.querySelector(".file-input");

                    newBox.addEventListener("click", () => newInput.click());

                    newInput.addEventListener("change", async (e) => {
                        const f = e.target.files && e.target.files[0];
                        if (!f) return;

                        if (isBlueDuplicate(newInput, f)) {
                            alert("This image is already selected.");
                            newInput.value = "";
                            return;
                        }

                       // Validate file type
                        if (!f.type.startsWith("image/")) {
                            alert("Please select a valid image file.");
                            newInput.value = "";
                            return;
                        }

                        try {
                            // Validate aspect ratio
                            const validation = await validateImage(f, boxName);

                            if (validation.error) {
                                alert(validation.error);
                                newInput.value = "";
                                return;
                            }

                            if (!validation.isValid) {
                                let errorMsg = `❌ Invalid image for ${boxName.toUpperCase()}\n`;
                                if (!validation.ratioMatch) {
                                    errorMsg += `- Expected ratio: ${validation.ratioName}\n- Got ratio: ${validation.imageRatio.toFixed(2)}:1\n`;
                                }
                                if (!validation.sizeMatch) {
                                    errorMsg += `- Expected size: ${validation.requiredSize}\n- Got size: ${validation.actualSize}\n`;
                                }
                                alert(errorMsg);
                                newInput.value = "";
                                return;
                            }

                            // If validation passes, display the image
                            const reader = new FileReader();
                            reader.onload = (ev) => {
                                newBox.innerHTML = `<img src="${ev.target.result}" alt="Banner Image">`;
                                newBox.appendChild(newInput);
                            };
                            reader.readAsDataURL(f);

                        } catch (error) {
                            console.error('Error validating aspect ratio:', error);
                            alert("Error validating image. Please try again.");
                            newInput.value = "";
                        }
                    });
                });
            }
        </script>

        <!-- Custom Grid Style -->
        <style>
            /* Custom Grid Style */
            .custom-grid {
                display: grid;
                grid-template-columns: 2fr 4fr 2fr;
                grid-template-rows: repeat(3, 150px);
                gap: 10px;
            }

            .box {
                background-color: #007bff;
                border-radius: 8px;
                color: white;
                display: flex;
                justify-content: center;
                align-items: center;
                font-weight: bold;
                font-size: 1.5rem;
                cursor: pointer;
            }

            /* Grid placement */
            .box1 {
                grid-column: 1 / 2;
                grid-row: 1 / 2;
            }

            .box2 {
                grid-column: 1 / 2;
                grid-row: 2 / 4;
            }

            /* spans 2 rows */
            .slider-box {
                grid-column: 2 / 3;
                grid-row: 1 / 3;
                cursor: no-drop;
                background-color: #c7c7c7;
            }

            .box3 {
                grid-column: 3 / 4;
                grid-row: 1 / 3;
            }

            .box4 {
                grid-column: 2 / 3;
                grid-row: 3 / 4;
            }

            .box5 {
                grid-column: 3 / 4;
                grid-row: 3 / 4;
            }

            .upload-box img {
                width: 100%;
                height: 100%;
                object-fit: cover;
                border-radius: 5px;
            }

            .upload-box input[type="file"] {
                display: none;
            }

            /* Thumbnail editing styles */
            .upload-box {
                transition: all 0.2s ease;
            }

            .upload-box:hover {
                transform: scale(1.02);
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            }

            .thumbnail-controls {
                opacity: 0;
                transition: opacity 0.2s ease;
                z-index: 10;
            }

            .upload-box:hover .thumbnail-controls {
                opacity: 1;
            }

            .thumbnail-controls button {
                border: none !important;
                border-radius: 3px !important;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3) !important;
                line-height: 1 !important;
                min-width: auto !important;
            }

            .thumbnail-controls .btn-danger {
                background-color: #dc3545 !important;
            }

            .thumbnail-controls .btn-primary {
                background-color: #007bff !important;
            }

            .thumbnail-controls .btn-secondary {
                background-color: #6c757d !important;
            }

            .thumbnail-controls button:hover {
                transform: scale(1.1);
            }

            /* Selected thumbnail border */
            .upload-box.selected {
                border: 3px solid #007bff !important;
                border-radius: 8px !important;
            }

            /* Drag and drop visual feedback */
            .upload-box.drag-over {
                border: 2px dashed #007bff !important;
                background-color: rgba(0, 123, 255, 0.1) !important;
            }

            /* Animation for thumbnail operations */
            .upload-box {
                animation: thumbnailAdd 0.3s ease;
            }

            @keyframes thumbnailAdd {
                from {
                    opacity: 0;
                    transform: scale(0.8);
                }

                to {
                    opacity: 1;
                    transform: scale(1);
                }
            }

            /* Mobile responsive adjustments */
            @media (max-width: 768px) {
                .thumbnail-controls button {
                    padding: 1px 4px !important;
                    font-size: 8px !important;
                }

                .upload-box {
                    margin: 2px !important;
                }
            }
        </style>
    </section>
</main>
@endsection