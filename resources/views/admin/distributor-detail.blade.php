@extends('layouts.dashboard-main')
@section('content')
@section('title', 'Distributor Documents')
<main>
    <!-- <section class="admin-dashboard dshbrdin buyer mt-0 d-flex"> -->
    <section class="admin-dashboard dshbrdin buyer mt-0 increased-fnt-cards">
        @include('admin.adminsidebar')
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <meta name="csrf-token" content="{{ csrf_token() }}">
            <title>Distributor Documents</title>
            <!-- Bootstrap 4 CSS -->
            <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
            <link rel="stylesheet" href="styles.css">
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
        </head>

        <body class="bg-white p-4">
            <div class="container-fluid">
                <div class="container my-2">
                    <div class="bg-white rounded-sm shadow-sm p-4">
                        <div class="mb-3 d-flex align-items-center justify-content-between">
                            <p class="mb-0 font-30">Distributor</p>
                            <button class="btn bg-custom-blue text-white btn-sm" onclick="window.history.back()">Back</button>
                        </div>
                        @if ($distributors && $distributors != null)
                        <div class="">
                            <div class="d-flex align-items-center">
                                <div class="rounded-circle brand-logo-circle">
                                    <img id="brandLogo" src="" alt="" class="w-100 h-100 rounded-circle object-fit-cover">
                                </div>
                                <div class="d-flex justify-content-between flex-grow-1">
                                    <div>
                                        <p id="brandName" class="brand-name font-weight-bold ml-2 mb-0 font-28"></p>
                                    </div>
                                    <div class="validity d-flex justify-content-between align-items-center">
                                        <p class="font-18 mb-0">Valid Till:</p>
                                        <p id="expiryDate" class="expiry-date font-18 mb-0 ml-1"></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="ml-120">
                            <div class="mt-2">
                                <p class="font-18">Dcouments Provided</p>
                                <div class="container">
                                    <div id="documentsContainer" class="brand-dcuments row row-cols-4">
                                        
                                    </div>
                                </div>
                                <div>
                                    <div class="visit-brand d-flex justify-content-between align-items-center mt-2 bg-transparent p-2 border rounded-pill">
                                        <div class="d-flex">
                                            <div class="d-flex align-items-center">
                                                <button class="btn-visit-brand line-height-20 rounded-pill py-2 px-4 bg-white">Visit</button>
                                                <p id="brandUrl" class="visit-brand-link font-16 mb-0 pl-3 cursor-pointer"></p>
                                            </div>
                                        </div>
                                        <div class="d-flex">
                                            @if ($distributors->brand_distributor_id == 0 || $distributors->brand_distributor_id == null) 
                                            @endif
                                                <div id="assignBrandBtns" class="brand-available mr-1 d-flex align-items-center"></div>
                                        </div>
                                    </div>
                                </div>
                                <div>
                                    <div class="mt-3 border rounded p-3">
                                        <p class="mb-0">Note for Seller:</p>
                                        <textarea class="form-control border-0 doc-note-textarea" rows="2" placeholder=""></textarea>
                                    </div>
                                </div>
                                <div>
                                    <div class="approve-reject-btn mt-3">
                                        @if ($distributors->status == 0)
                                        <div class="d-flex align-items-center">
                                            <button class="btn-brand-name-approve px-5 py-2 rounded-pill text-white border-0">Approve</button>
                                            <button class="btn-brand-name-reject px-5 py-2 rounded-pill text-white border-0 ml-2">Reject</button>
                                        </div>
                                        @elseif($distributors->status == 1)
                                        <div class="text-center d-flex align-items-center justify-content-center">
                                            <div class="btn-brand-name-approve rounded-lg px-5 py-3"><h4 class="mb-0 text-white">Distributor Approved</h4></div>
                                        </div>
                                        @elseif($distributors->status == 2)
                                        <div class="text-center d-flex align-items-center justify-content-center">
                                            <div class="btn-brand-name-reject rounded-lg px-5 py-3"><h4 class="mb-0 text-white">Distributor Rejected</h4></div>
                                        </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div> 
                        @else
                        <div class="text-center">
                            <p>Distributor documnets not found</p>
                        </div>
                        @endif                           
                    </div>
                </div>
            </div>

            <!-- Assign Brand Modal -->
            <div class="modal fade" id="attachAnotheBrandModal" tabindex="-1" role="dialog" aria-labelledby="attachAnotheBrandModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-md" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title font-30" id="attachAnotheBrandModalLabel">Assign Brand</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div id="brandsLoadingSpinner" class="text-center py-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="sr-only">Loading...</span>
                                </div>
                                <p class="mt-2">Loading brands...</p>
                            </div>
                            <div id="brandsContainer" class="flex-col" style="display: none; flex-direction: column; cursor: pointer; overflow-y:auto; max-height:300px;">
                                <!-- Brand cards will be dynamically inserted here -->
                            </div>
                            <div id="brandsError" class="alert alert-danger" role="alert" style="display: none;">
                                Failed to load brands. Please try again.
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                            <button type="button" class="btn btn-primary" id="saveBrandAssignment" disabled>Save</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Message Modal -->
            <div class="modal fade" id="messageModal" tabindex="-1" role="dialog" aria-labelledby="messageModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered" role="document">
                    <div class="modal-content border-0">
                        <div class="modal-header">
                            <h5 class="modal-title" id="messageModalLabel">Message</h5>
                            <button type="button" class="close" id="closeModalBtn" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body text-center">
                            <div id="messageText" class="font-weight-bold"></div>
                        </div>
                        <div class="modal-footer justify-content-center">
                            <button type="button" class="btn btn-primary" id="okModalBtn" data-dismiss="modal">OK</button>
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

        <!-- Bootstrap 4 JS -->
        <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

        <script>
            const distributor = @json($distributors);
            const brandsList = @json($brands);
            console.log("detail page data: ",distributor);
            console.log("brandsList data: ", brandsList);
            const distributorDocumentId = distributor.id;
            const distributorId = distributor.distributor_id;
            const distributorName = distributor.brand_name;
            const distributorLogo = distributor.distributor_logo;
            const distributorBrandUrl = distributor.brand_url;
            const brandDistributorId = distributor.brand_distributor_id;
            let selectedBrandId = null;
            let brandsData = [];
            let documentStatuses = {};
            brandURL = 'https://'+ distributorBrandUrl;
            //Initialize document statuses
            if (distributor && distributor.documents_data) {
                distributor.documents_data.forEach(doc => {
                    documentStatuses[doc.id] = doc.status;
                });
            }

            //Function to check and update approve button state
            function checkButtonsState() {
                const approveBtn = document.querySelector(".btn-brand-name-approve");
                const confirmBtn = document.querySelector(".confirm-avl-brand-btn");
                const createNewBtn = document.querySelector(".create-new-brand-btn");
                const attachAnotherBrandBtn = document.querySelector(".attach-another-brand-btn");
                // if (!approveBtn) return;

                // Check if any document has status 0 (pending) or 2 (rejected)
                const hasInvalidStatus = Object.values(documentStatuses).some(
                    status => status === 0 || status === 2
                );
                const hasBrandAssigned = distributor.brand_distributor_id !== 0;
                const isRejected = distributor.status === 2;
                const isApproved = distributor.status === 1;
                
                console.log("check hasInvalidStatus: ",hasInvalidStatus);
                console.log("check hasBrandAssigned: ",hasBrandAssigned);
                console.log("check isRejected: ",isRejected);

                //Check for Approve Btn
                if (approveBtn){
                    if (hasInvalidStatus || !hasBrandAssigned) {
                        // Disable button
                        approveBtn.disabled = true;
                        approveBtn.classList.add("disabled");
                        approveBtn.style.opacity = "0.6";
                        approveBtn.style.cursor = "not-allowed";
                    } else {
                        // Enable button
                        approveBtn.disabled = false;
                        approveBtn.classList.remove("disabled");
                        approveBtn.style.opacity = "1";
                        approveBtn.style.cursor = "pointer";
                    }
                }

                // Check for confirm btn 
                if (confirmBtn){
                    if (hasInvalidStatus || hasBrandAssigned) {
                        // Disable button
                        confirmBtn.disabled = true;
                        confirmBtn.classList.add("disabled");
                        confirmBtn.style.opacity = "0.6";
                        confirmBtn.style.cursor = "not-allowed";
                    } else {
                        // Enable button
                        confirmBtn.disabled = false;
                        confirmBtn.classList.remove("disabled");
                        confirmBtn.style.opacity = "1";
                        confirmBtn.style.cursor = "pointer";
                    }
                }

                // Check for Create new btn
                if (createNewBtn){
                    if (hasInvalidStatus || hasBrandAssigned) {
                        // Disable button
                        createNewBtn.disabled = true;
                        console.log("execution");
                        createNewBtn.classList.add("disabled");
                        createNewBtn.style.opacity = "0.6";
                        createNewBtn.style.cursor = "not-allowed";
                    } else {
                        // Enable button
                        createNewBtn.disabled = false;
                        createNewBtn.classList.remove("disabled");
                        createNewBtn.style.opacity = "1";
                        createNewBtn.style.cursor = "pointer";
                    }
                }

                if (attachAnotherBrandBtn){
                    if (hasInvalidStatus || isRejected || isApproved || true) {
                        // Disable button
                        attachAnotherBrandBtn.disabled = true;
                        attachAnotherBrandBtn.classList.add("disabled");
                        attachAnotherBrandBtn.style.opacity = "0.6";
                        attachAnotherBrandBtn.style.cursor = "not-allowed";
                    } else {
                        // Enable button
                        attachAnotherBrandBtn.disabled = false;
                        attachAnotherBrandBtn.classList.remove("disabled");
                        attachAnotherBrandBtn.style.opacity = "1";
                        attachAnotherBrandBtn.style.cursor = "pointer";
                    }
                }
            }
            
            function showMessage(message, type = 'success') {
                const messageText = document.getElementById('messageText');
                const modalTitle = document.getElementById('messageModalLabel');

                // Update modal text and style
                messageText.textContent = message;
                messageText.className = `font-weight-bold ${type === 'success' ? 'text-success' : 'text-danger'}`;
                modalTitle.textContent = type === 'success' ? 'Success' : 'Error';

                // Show modal using Bootstrap 4 jQuery method
                $('#messageModal').modal('show');

                // Ensure modal closes on buttons
                $('#okModalBtn').on('click', function () {
                    $('#messageModal').modal('hide');
                     window.location.reload();
                });

                $('#closeModalBtn').on('click', function () {
                    $('#messageModal').modal('hide');
                });
            }

            async function fetchBrands() {
                try {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    
                    const response = await fetch(`/admin/distributor/brands?distributor_id=${distributorId}`, {
                        method: 'GET',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        }
                    });

                    if (!response) {
                        throw new Error('Network response was not ok');
                    }

                    const result = await response.json();
                    console.log('Brands fetched successfully:', result.data);

                    if (result && result.data) {
                        brandsData = result.data;
                        const filterBrands = brandsData.filter((b)=> b.name.toLowerCase() !== distributor.brand_name.toLowerCase());
                        console.log("filter brands: ", filterBrands);
                        renderBrands(filterBrands);
                        checkBrandInList(brandsData);

                        setTimeout(() => {
                            checkButtonsState();
                        }, 100);
                    } else {
                        throw new Error('Invalid response format');
                    }
                } catch (error) {
                    console.error('Error fetching brands:', error);
                    document.getElementById('brandsLoadingSpinner').style.display = 'none';
                    document.getElementById('brandsError').style.display = 'block';
                }
            }

            // Function to render brands in modal
            function renderBrands(brands) {
                const container = document.getElementById('brandsContainer');
                const loadingSpinner = document.getElementById('brandsLoadingSpinner');
                
                container.innerHTML = '';
                
                if (brands.length === 0) {
                    container.innerHTML = '<div class="col-12 text-center py-4"><p>No brands available</p></div>';
                    loadingSpinner.style.display = 'none';
                    container.style.display = 'block';
                    return;
                }

                brands.forEach(brand => {
                    const brandCard = createBrandCard(brand);
                    container.appendChild(brandCard);
                });

                loadingSpinner.style.display = 'none';
                container.style.display = 'flex';
            }

            // Function to create brand card element
            function createBrandCard(brand) {
                const colDiv = document.createElement('div');
                colDiv.className = 'brand-name-drpdwn';
                
                colDiv.innerHTML = `
                    <div class="cursor-pointer" data-brand-id="${brand.id}">
                        <div class="d-flex align-items-center">
                            <h6 class="brand-card-name font-weight-bold mb-0">${brand.name}</h6>
                        </div>
                    </div>
                `;

                return colDiv;
            }

            // Function to assign brand to distributor
            async function assignBrandToDistributor(brandId, distributorDocId) {
                console.log("Brand Id & Distributor Id from confirm: ", brandId, distributorDocId);
                try {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    const response = await fetch('/admin/distributor/assign-brand', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({
                            brand_id: brandId,
                            distributor_document_id: distributorDocId
                        })
                    });

                    if (!response) {
                        throw new Error('Network response was not ok');
                    }

                    const data = await response.json();
                    console.log('Brand assigned successfully:', data);
                    
                    $('#attachAnotheBrandModal').modal('hide');
                    // Show success message
                    showMessage('Brand assigned successfully!', 'success');
                    // Reset selection
                    selectedBrandId = null;

                    document.querySelectorAll('.brand-name-drpdwn').forEach(card => {
                        card.classList.remove('selected');
                    });
                    
                    return data;
                } catch (error) {
                    console.error('Error assigning brand:', error);
                    const errorMsg = "Brand not assigned";
                    showMessage(errorMsg, 'error');
                }
            }

            // Function to format date
            function formatDate(dateString) {
                const date = new Date(dateString);
                const options = { day: 'numeric', month: 'short', year: 'numeric' };
                return date.toLocaleDateString('en-GB', options);
            }

            // Function to render distributor data
            function renderDistributorData() {
                if (!distributor || typeof distributor !== 'object') {
                    console.error('No distributor data available');
                    return;
                }
                // Set brand logo
                document.getElementById('brandLogo').src = distributor.distributor_logo_url || '';
                // Set brand name
                document.getElementById('brandName').textContent = distributor.brand_name || '';
                // Set expiry date
                document.getElementById('expiryDate').textContent = formatDate(distributor.expiry_date);
                // Set brand URL
                document.getElementById('brandUrl').textContent = brandURL || '';
                // Render documents
                renderDocuments(distributor.documents_data || []);
                // checkButtonsState();
            }

            //Display btns & brand name if available
            function checkBrandInList(brands){
                const container = document.getElementById('assignBrandBtns');
                console.log("Brands from fetchBrands: ", brands);
                const availableBrandInfetchBrands = brands.find((b)=> b?.url?.toLowerCase() === distributor.brand_url.toLowerCase());
                const availableBrandInMainList = brandsList.find((b)=> b?.url?.toLowerCase() === distributor.brand_url.toLowerCase());
                console.log("is availableBrandInfetchBrands: ", availableBrandInfetchBrands);
                console.log("is availableBrandInMainList: ", availableBrandInMainList);
                const brandName = distributor.brand_name?.toLowerCase();
                const brandUrl = distributor.brand_url?.toLowerCase();
                const avlBrandNameInfetchBrands = availableBrandInfetchBrands?.name?.toLowerCase();
                const avlBrandNameInMainList = availableBrandInMainList?.name?.toLowerCase();
                const avlBrandUrlInfetchBrands = availableBrandInfetchBrands?.url?.toLowerCase();
                const avlBrandUrlInMainList = availableBrandInMainList?.url?.toLowerCase();
                console.log("Brand Name: ", brandName);
                console.log("Available Brand Name in fetch brands: ", avlBrandNameInfetchBrands);
                console.log("Available Brand Name in main list: ", avlBrandNameInMainList);

                if (brandUrl === avlBrandUrlInfetchBrands || brandUrl === avlBrandUrlInMainList){
                    container.innerHTML = `
                        <div class="d-flex flex-column align-items-start justify-content-center">
                            <p class="mb-0 font-12">Brand Found:</p>
                            <div class="check-brand">${availableBrandInMainList?.name}</div>
                        </div>
                        <button class="confirm-avl-brand-btn bg-custom-blue rounded-pill py-2 px-4 bg-[#001183] text-white ml-2">Confirm</button>
                        <button class="attach-another-brand-btn bg-white rounded-pill py-2 px-4 ml-2">Attach Another</button>
                    `;
                } else {
                    container.innerHTML = `
                        <button class="create-new-brand-btn bg-custom-blue rounded-pill py-2 px-4 bg-[#001183] border-0 text-white ml-2">Create New</button>
                    `;
                }
            }

            // Function to render documents
            function renderDocuments(documents) {
                const container = document.getElementById('documentsContainer');
                container.innerHTML = '';

                documents.forEach((doc, index) => {
                    const docElement = createDocumentElement(doc);
                    container.appendChild(docElement);
                });

                // Initialize tooltips for newly added elements
                $('[data-toggle="tooltip"]').tooltip();
            }

            // Function to create document element
            function createDocumentElement(doc) {
                const colDiv = document.createElement('div');
                colDiv.className = 'brand-doc col mb-3';
                colDiv.setAttribute('data-doc-id', doc.id);

                // Check file extension to determine if it's an image or PDF
                const isPDF = doc.url.toLowerCase().endsWith('.pdf');
                
                let imgHTML = '';
                if (isPDF) {
                    imgHTML = `
                                <div class="position-relative">
                                    <div class="pdf-icon-wrapper cursor-pointer" data-pdf-url="${doc.url}" data-pdf-name="document-${doc.id}.pdf">
                                        <svg class="pdf-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                            <polyline points="14 2 14 8 20 8"></polyline>
                                            <line x1="16" y1="13" x2="8" y2="13"></line>
                                            <line x1="16" y1="17" x2="8" y2="17"></line>
                                            <polyline points="10 9 9 9 8 9"></polyline>
                                        </svg>
                                        <p class="pdf-label mt-2 mb-0">PDF Document</p>
                                    </div>
                                    <!-- Download Icon with Tooltip -->
                                    <div class="icon-overlay" data-toggle="tooltip" data-placement="top" title="Download">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                            <polyline points="7 10 12 15 17 10"></polyline>
                                            <line x1="12" y1="15" x2="12" y2="3"></line>
                                        </svg>
                                    </div>
                                </div>
                            `;
                } else {
                    imgHTML = `
                                <div class="position-relative">
                                    <img src="${doc.url}" alt="Document" class="brand-doc-img cursor-pointer" id="brandDocImg-${doc.id}">
                                    <!-- Zoom Icon with Tooltip -->
                                    <div class="icon-overlay" data-toggle="tooltip" data-placement="top" title="Zoom">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="11" cy="11" r="8"></circle>
                                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                            <line x1="11" y1="8" x2="11" y2="14"></line>
                                            <line x1="8" y1="11" x2="14" y2="11"></line>
                                        </svg>
                                    </div>
                                </div>
                            `;
                }

                let statusHTML = '';
                if (doc.status === 0) {
                    // Pending - show both buttons
                    statusHTML = `
                        <div class="doc-accept-reject-btns d-flex justify-content-between mt-2">
                            <button class="btn-doc-accept rounded-pill py-1 px-4 bg-white border border-success text-success">Accept</button>
                            <button class="btn-doc-reject rounded-pill py-1 px-4 bg-white border border-danger text-danger">Reject</button>
                        </div>
                    `;
                } else if (doc.status === 1) {
                    // Accepted
                    statusHTML = `
                        <div class="doc-status text-center">
                            <span class="accept-status rounded text-white px-4 py-2 font-16">Accepted</span>
                        </div>
                    `;
                } else if (doc.status === 2) {
                    // Rejected
                    statusHTML = `
                        <div class="doc-status text-center">
                            <span class="reject-status rounded text-white px-4 py-2 font-16">Rejected</span>
                        </div>
                    `;
                }

                colDiv.innerHTML = `
                    <div class="brand-doc-img-container p-3 border rounded text-center bg-light-gray">
                        ${imgHTML}
                    </div>
                    <div class="mt-2">
                        ${statusHTML}
                    </div>
                `;

                return colDiv;
            }

            // Function to handle document status change
            async function changeDocumentStatus(documentId, status) {
                try {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    
                    const response = await fetch('/admin/distributor/document/change-status', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({
                            document_id: documentId,
                            status: status
                        })
                    });

                    if (!response) {
                        throw new Error('Network response was not ok');
                    }

                    const data = await response.json();
                    console.log('Status changed successfully:', data);

                    updateDocumentUI(documentId, status);
                    
                    return data;
                } catch (error) {
                    console.error('Error changing document status:', error);
                    alert('Failed to update document status. Please try again.');
                }
            }

            async function changeDistributorStatus(distributorDocId, status, note) {
                console.log("distributor Id: ", distributorDocId);
                console.log("Status: ", status);
                console.log("Note: ", note);

                try {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                    const response = await fetch('/admin/distributor/change-status', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({
                            distributor_document_id: distributorDocId,
                            status: status,
                            note: note
                        })
                    });

                    if (!response) {
                        throw new Error('Network response was not ok');
                    }

                    const data = await response.json();
                    console.log('Distributor status changed successfully:', data);

                    // Update the UI
                    updateDistributorUI(status);
                    if(status === 'approved'){
                        window.location.href = "/admin/distributors/list?status=active";
                    } else if (status === 'rejected'){
                        window.location.href = "/admin/distributors/list";
                    }
                    return data;
                } catch (error) {
                    console.error('Error changing distributor status:', error);
                    showMessage('Failed to update distributor status. Please try again.', 'error');
                }
            }

            async function createNewBrand(distributorName, distributorLogo, distributorDocId, distributorBrandUrl) {
                try {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                    const response = await fetch('/admin/distributor/register-brand', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({
                            name: distributorName,
                            logo: distributorLogo,
                            distributor_document_id: distributorDocId,
                            url: distributorBrandUrl
                        })
                    });

                    if (!response) {
                        throw new Error('Network response was not ok');
                    }

                    const data = await response.json();

                    if (data.status === true) {
                        showMessage('Brand registered successfully!', 'success');
                    } else {
                        const errorMsg = data.errors?.url?.join(', ') || 'Something went wrong';
                        showMessage(errorMsg, 'error');
                    }
                    console.log('Brand registered response: ', data);
                    return data;

                } catch (error) {
                    console.error('Error changing distributor status:', error);
                    alert('Failed to update distributor status. Please try again.');
                }
            }

            // Function to update document UI after status change
            function updateDocumentUI(documentId, status) {
                const docElement = document.querySelector(`[data-doc-id="${documentId}"]`);
                if (!docElement) return;

                const buttonsContainer = docElement.querySelector('.doc-accept-reject-btns');
                if (!buttonsContainer) return;

                //Update the status in our tracking object
                if (status === 'approved') {
                    documentStatuses[documentId] = 1;
                } else if (status === 'rejected') {
                    documentStatuses[documentId] = 2;
                }

                let statusHTML = '';
                if (status === 'approved') {
                    statusHTML = `
                        <div class="doc-status mt-2 text-center">
                            <span class="accept-status rounded text-white px-4 py-2 font-16">Accepted</span>
                        </div>
                    `;
                } else if (status === 'rejected') {
                    statusHTML = `
                        <div class="doc-status mt-2 text-center">
                            <span class="reject-status rounded text-white px-4 py-2 font-16">Rejected</span>
                        </div>
                    `;
                }

                buttonsContainer.outerHTML = statusHTML;

                //Re-check if approve button should be enabled
                checkButtonsState();
            }

            // Function to update distributor UI after approval/rejection
            function updateDistributorUI(status) {
                const approveRejectContainer = document.querySelector('.approve-reject-btn');
                if (!approveRejectContainer) return;

                let statusHTML = '';
                if (status === 'approved') {
                    statusHTML = `
                        <div class="text-center d-flex align-items-center justify-content-center">
                            <span class="btn-brand-name-approve rounded-lg px-5 py-3 font-24 text-white">Distributor Approved</span>
                        </div>
                    `;
                } else if (status === 'rejected') {
                    statusHTML = `
                        <div class="text-center d-flex align-items-center justify-content-center">
                            <div class="btn-brand-name-reject rounded-lg px-5 py-3 font-24 text-white">Distributor Rejected</div>
                        </div>
                    `;
                }

                approveRejectContainer.innerHTML = statusHTML;
            }

            // Event delegation for accept/reject buttons
            document.addEventListener('click', function(e) {
                const distributor = @json($distributors);
                
                // Brand card selection
                // if (e.target.closest('.brand-name-drpdwn')) {
                //     const brandCard = e.target.closest('.brand-name-drpdwn');
                //     const brandId = brandCard.getAttribute('data-brand-id');
                    
                //     // Remove selection from all cards
                //     document.querySelectorAll('.brand-name-drpdwn').forEach(card => {
                //         card.classList.remove('selected');
                //     });
                    
                //     // Add selection to clicked card
                //     brandCard.classList.add('selected');
                //     selectedBrandId = brandId;
                    
                //     // Enable save button
                //     document.getElementById('saveBrandAssignment').disabled = false;
                // }
                
                if (e.target.classList.contains('btn-doc-accept')) {
                    const docElement = e.target.closest('.brand-doc');
                    const documentId = docElement.getAttribute('data-doc-id');
                    changeDocumentStatus(documentId, 'approved');
                }

                if (e.target.classList.contains('btn-doc-reject')) {
                    const docElement = e.target.closest('.brand-doc');
                    const documentId = docElement.getAttribute('data-doc-id');
                    changeDocumentStatus(documentId, 'rejected');
                }

                if (e.target.classList.contains('btn-brand-name-reject')) {
                    const noteField = document.querySelector('.doc-note-textarea');
                    const note = noteField ? noteField.value.trim() : '';
                    console.log(distributor.id, "note:", note);
                    changeDistributorStatus(distributor.id, 'rejected', note);
                }

                if (e.target.classList.contains('btn-brand-name-approve')) {
                    const noteField = document.querySelector('.doc-note-textarea');
                    const note = noteField ? noteField.value.trim() : '';
                    console.log(distributor.id, "note:", note);
                    changeDistributorStatus(distributor.id, 'approved', note);
                }

                if (e.target.classList.contains('create-new-brand-btn')) {
                    console.log("Create new brand btn clicked");
                    console.log("Create new passed values: ",distributor.brand_name, distributor.distributor_logo, distributor.id, distributor.brand_url);
                    createNewBrand(distributor.brand_name, distributor.distributor_logo, distributor.id, distributor.brand_url);
                }

                if (e.target.classList.contains('confirm-avl-brand-btn')) {
                    const matchedBrand = brandsList?.find((b)=> b.name.toLowerCase() === distributor.brand_name.toLowerCase());
                    assignBrandToDistributor(matchedBrand.id, distributorDocumentId);
                }

                if (e.target.classList.contains('btn-visit-brand')) {
                    const brandUrl = document.getElementById('brandUrl').textContent.trim();

                    if (brandUrl) {
                        window.open(brandUrl, '_blank');
                    }
                }

                if (e.target.classList.contains('brand-doc-img')) {
                    const imgSrc = e.target.getAttribute('src'); // get the image URL

                    if (imgSrc) {
                        window.open(imgSrc, '_blank'); // open image in new tab
                    }
                }

                // PDF download click handler
                if (e.target.closest('.pdf-icon-wrapper')) {
                    const wrapper = e.target.closest('.pdf-icon-wrapper');
                    const pdfUrl = wrapper.getAttribute('data-pdf-url');
                    const pdfName = wrapper.getAttribute('data-pdf-name');
                    
                    if (pdfUrl) {
                        // Create temporary link for download
                        const link = document.createElement('a');
                        link.href = pdfUrl;
                        link.download = pdfName;
                        link.target = '_blank';
                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);
                    }
                }

                // Assign Brand button - Open modal and fetch brands
                if (e.target.classList.contains('attach-another-brand-btn')) {
                    console.log('Attach another brand button clicked!');
                    // Open the modal
                    $('#attachAnotheBrandModal').modal('show');
                }
            });

            document.addEventListener('click', function(e) {
                // brand select
                const brandCard = e.target.closest('.brand-name-drpdwn');
                if (brandCard) {
                    // remove existing selection
                    document.querySelectorAll('.brand-name-drpdwn').forEach(el => el.classList.remove('selected'));

                    // add new selection
                    brandCard.classList.add('selected');

                    // get brand id
                    selectedBrandId = brandCard.querySelector('[data-brand-id]').getAttribute('data-brand-id');
                    console.log("Selected brand ID:", selectedBrandId);

                    // enable save button
                    document.getElementById('saveBrandAssignment').disabled = false;
                }
            });

            // save button click
            document.getElementById('saveBrandAssignment').addEventListener('click', function() {
                console.log("Save clicked");
                console.log("Brand ID:", selectedBrandId, "Distributor ID:", distributorDocumentId);

                if (selectedBrandId && distributorDocumentId) {
                    assignBrandToDistributor(selectedBrandId, distributorDocumentId);
                } else {
                    alert("Please select a brand first.");
                }
            });

            // When modal is opened, fetch brands
            $('#attachAnotheBrandModal').on('show.bs.modal', function() {
                // Reset modal state
                document.getElementById('brandsLoadingSpinner').style.display = 'block';
                document.getElementById('brandsContainer').style.display = 'none';
                document.getElementById('brandsError').style.display = 'none';
                document.getElementById('saveBrandAssignment').disabled = true;
                selectedBrandId = null;
                
                fetchBrands();
            });
            
            $(document).on('click', '#brandUrl', function() {
                const brandUrl = $(this).text().trim();
                if (brandUrl) window.open(brandUrl, '_blank');
            });

            // Initialize on page load
            document.addEventListener('DOMContentLoaded', function() {
                renderDistributorData();
                 // Initialize Bootstrap tooltips
                $('[data-toggle="tooltip"]').tooltip();
                fetchBrands();
            });
        </script>
        
        <style>
            .bg-light-gray{
                background-color: #F8F9FA;
            }
            .bg-custom-blue{
                background-color: #001183
            }
            .visit-brand-link:hover{
                text-decoration: underline;
                color: blue;
            }
            .brand-logo-circle {
                width: 60px;
                height: 60px;
            }
            .brand-doc-img{
                height: 150px;
                object-fit: cover;
                width: 100%;
                overflow: hidden;
            }
            .btn-visit-brand:hover{
                background-color: #495057;
            }
            .attach-another-brand-btn{
                color: #001183;
                border: solid;
                border-width: 2px;
                border-color: #001183;
            }
            .confirm-avl-brand-btn{
                border: solid;
                border-width: 2px;
                border-color: #001183;
            }
            .accept-status{
                background-color: #28A745
            }
            .reject-status{
                background-color: #DC3545
            }
            .btn-brand-name-approve{
                background-color: #0AB824;
                border: solid;
                border-width: 2px;
                border-color: #1d9c3bff;
            }
            .btn-brand-name-reject{
                background-color: #F54848
            }
            .doc-accept-reject-btns button{
                transition: 0.2s ease;
            }
            .btn-doc-accept:hover{
                background-color: #28a745 !important;
                color: white !important;
            }
            .btn-doc-reject:hover{
                background-color: #dc3545 !important;
                color: white !important;
            }
            .brand-name-drpdwn{
                background-color: #f3f4f5ff;
                width: 100%;
                padding: 15px 20px;
                border-radius: 8px;
                margin-bottom: 15px;
            }
            .brand-name-drpdwn:hover{
                background-color: #d8d9dbff;
            }
            .brand-name-drpdwn.selected{
                border: 2px solid #686868ff;
                background-color: #d8d9dbff;
            }

            /* PDF Icon Styles */
            .pdf-icon-wrapper {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                height: 150px;
                transition: all 0.3s ease;
            }
            .pdf-icon-wrapper:hover {
                transform: scale(1.05);
            }
            .pdf-icon {
                width: 60px;
                height: 60px;
                color: #dc3545;
                stroke-width: 1.5;
            }
            .pdf-label {
                font-size: 14px;
                color: #666;
                font-weight: 500;
            }
            .pdf-icon-wrapper:hover .pdf-icon {
                color: #c82333;
            }
            .pdf-icon-wrapper:hover .pdf-label {
                color: #333;
            }
            /* Icon Overlay Styles */
            .icon-overlay {
                position: absolute;
                top: 0px;
                right: 0px;
                background-color: rgba(255, 255, 255, 0.9);
                border-radius: 50%;
                padding: 8px;
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 10;
            }
            .icon-overlay svg {
                color: #333;
            }

            .position-relative {
                position: relative;
            }
            .brand-available{
                line-height: 1;
            }
        </style>
    </section>
</main>
@endsection 