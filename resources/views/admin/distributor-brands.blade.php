@extends('layouts.dashboard-main')
@section('content')
@section('title', 'Distributor Brands')
<main>
    <section class="admin-dashboard dshbrdin buyer mt-0 increased-fnt-cards">
        @include('admin.adminsidebar')
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <meta name="csrf-token" content="{{ csrf_token() }}">
            <title>Distributor Brands</title>
            <!-- Bootstrap 4 CSS -->
            <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
            <link rel="stylesheet" href="styles.css">
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
        </head>

        <body class="bg-white p-4">
            <div class="container-fluid">
                <div class="mt-3 card">
                    <div class="container py-5">
                        <div class="bg-white rounded-sm shadow-sm p-4">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <p class="font-30 mb-0" id="totalBrandsCount">Brands</p>
                                <div class="search-bar-wrapper position-relative">
                                    <div class="search-bar d-flex align-items-center">
                                        <input 
                                            type="text" 
                                            id="brandSearchInput" 
                                            class="form-control" 
                                            placeholder="Search brands..."
                                            autocomplete="off"
                                        >
                                        <button id="searchButton" class="btn btn-primary search-btn">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-search" viewBox="0 0 16 16">
                                                <path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001q.044.06.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1 1 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0"/>
                                            </svg>
                                        </button>
                                    </div>
                                    <!-- Suggestions dropdown -->
                                    <div id="searchSuggestions" class="search-suggestions"></div>
                                </div>
                            </div>
                            <div class="brands-container rounded px-3 py-4">
                                <div id="brandsListContainer">
                                    
                                </div>
                            </div>                           
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
            const brands = @json($brands);
            console.log("Brands data: ", brands);

            let allBrands = [...brands]; // Keep original data
            let filteredBrands = [...brands]; // For display
            let selectedSuggestion = null;

            // Search functionality
            const searchInput = document.getElementById('brandSearchInput');
            const searchButton = document.getElementById('searchButton');
            const suggestionsBox = document.getElementById('searchSuggestions');

            // Show suggestions as user types
            searchInput.addEventListener('input', function(e) {
                const query = e.target.value.trim().toLowerCase();
                
                if (query.length === 0) {
                    hideSuggestions();
                    selectedSuggestion = null;
                    return;
                }

                // Filter brands that start with the query
                const matches = allBrands.filter(brand => 
                    brand.name.toLowerCase().startsWith(query)
                );

                if (matches.length > 0) {
                    showSuggestions(matches, query);
                } else {
                    hideSuggestions();
                }
            });

            // Handle Enter key press
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    performSearch();
                }
            });

            // Handle search button click
            searchButton.addEventListener('click', function() {
                performSearch();
            });

            // Perform search
            function performSearch() {
                const query = searchInput.value.trim().toLowerCase();
                
                if (query.length === 0) {
                    // If empty, show all brands
                    filteredBrands = [...allBrands];
                    renderBrands(filteredBrands);
                    hideSuggestions();
                    return;
                }

                // If a suggestion was selected, use it
                if (selectedSuggestion) {
                    filteredBrands = [selectedSuggestion];
                } else {
                    // Otherwise, filter brands that start with query
                    filteredBrands = allBrands.filter(brand => 
                        brand.name.toLowerCase().startsWith(query)
                    );
                }

                renderBrands(filteredBrands);
                hideSuggestions();
            }

            // Show suggestions dropdown
            function showSuggestions(matches, query) {
                suggestionsBox.innerHTML = '';
                
                matches.forEach(brand => {
                    const suggestionItem = document.createElement('div');
                    suggestionItem.className = 'suggestion-item';
                    
                    // Highlight matching part
                    const brandName = brand.name;
                    const matchingPart = brandName.substring(0, query.length);
                    const remainingPart = brandName.substring(query.length);
                    
                    suggestionItem.innerHTML = `
                        <strong>${matchingPart}</strong>${remainingPart}
                    `;
                    
                    suggestionItem.addEventListener('click', function() {
                        searchInput.value = brand.name;
                        selectedSuggestion = brand;
                        hideSuggestions();
                        performSearch();
                    });
                    
                    suggestionsBox.appendChild(suggestionItem);
                });
                
                suggestionsBox.style.display = 'block';
            }

            // Hide suggestions
            function hideSuggestions() {
                suggestionsBox.style.display = 'none';
                suggestionsBox.innerHTML = '';
            }

            // Close suggestions when clicking outside
            document.addEventListener('click', function(e) {
                if (!e.target.closest('.search-bar-wrapper')) {
                    hideSuggestions();
                }
            });

            // Function to render all brands
            function renderBrands(brandsData) {
                const container = document.getElementById('brandsListContainer');
                const totalBrandsCount = document.getElementById('totalBrandsCount');
                const totalBrandslength = brandsData.length;
                console.log("total brands count: ", totalBrandslength);
                
                // Check if elements exist
                if (!container) {
                    console.error('brandsListContainer element not found');
                    return;
                }
                
                if (!brandsData || brandsData.length === 0) {
                    container.innerHTML = '<p class="text-center py-4">No brands available</p>';
                    totalBrandsCount.textContent = '0 Brands';
                    return;
                }

                // Update total count
                totalBrandsCount.textContent = `Brand${brandsData.length !== 1 ? 's' : ''} (${totalBrandslength})`;

                // Clear container
                container.innerHTML = '';

                // Create brand elements
                brandsData.forEach((brand, index) => {
                    const brandElement = createBrandElement(brand, index);
                    container.appendChild(brandElement);
                });
            }

            // Function to create brand element with dropdown
            function createBrandElement(brand, index) {
                const brandWrapper = document.createElement('div');
                brandWrapper.className = 'brand-wrapper';
                brandWrapper.setAttribute('data-brand-id', brand.id);

                const hasDistributors = brand.distributors && brand.distributors.length > 0;
                const distributorsCount = hasDistributors ? brand.distributors.length : 0;
                const logoUrl = brand.logo_url || 'https://via.placeholder.com/50?text=No+Logo';

                // Create the main brand dropdown header
                const brandHeader = document.createElement('div');
                brandHeader.className = `brand-name-drpdwn ${hasDistributors ? 'cursor-pointer' : ''}`;
                brandHeader.setAttribute('data-has-distributors', hasDistributors);
                brandHeader.setAttribute('data-expanded', 'false');

                brandHeader.innerHTML = `
                    <div class="d-flex align-items-center">
                        ${hasDistributors ? '<div class="dropdown-arrow mr-3">▼</div>' : '<div class="dropdown-arrow-placeholder mr-3"></div>'}
                        <div class="brand-logo rounded-circle mr-3">
                            <img src="${logoUrl}" alt="${brand.name}" class="h-100 w-100 object-fit-cover rounded-circle">
                        </div>
                        <div>
                            <p class="font-20 mb-0">${brand.name}</p>
                        </div>
                    </div>
                    <div class="d-flex align-items-center">
                        <p class="mb-0">${distributorsCount} Distributor${distributorsCount !== 1 ? 's' : ''}</p>
                    </div>
                `;

                brandWrapper.appendChild(brandHeader);

                // Create distributors list (initially hidden)
                if (hasDistributors) {
                    const distributorsList = document.createElement('div');
                    distributorsList.className = 'distributors-list';
                    distributorsList.style.display = 'none';

                    brand.distributors.forEach(distributor => {
                        const distributorElement = createDistributorElement(distributor);
                        distributorsList.appendChild(distributorElement);
                    });

                    brandWrapper.appendChild(distributorsList);
                }

                return brandWrapper;
            }

            // Function to create distributor element
            function createDistributorElement(distributor) {
                const distributorDocumentId = distributor.distributor_document_id;
                const distributorDiv = document.createElement('div');
                distributorDiv.className = 'brand-name-drpdwn-list d-flex justify-content-between align-items-center px-4 py-3 mb-2 cursor-pointer';
                
                const storeLogo = distributor.store_logo_url || 'https://via.placeholder.com/60?text=No+Logo';
                const productsCount = distributor.brand_products_count || 0;

                distributorDiv.addEventListener('click', () => {
                        window.location.href = `/admin/distributor/detail/${distributorDocumentId}`;
                    });

                distributorDiv.innerHTML = `
                    <div class="d-flex align-items-center">
                        <div class="store-logo">
                            <img src="${storeLogo}" alt="${distributor.store_name}" class="h-100 w-100 object-fit-cover rounded">
                        </div>
                        <div>
                            <p class="mb-0 font-weight-bold">${distributor.store_name}</p>
                            ${distributor.email ? `<p class="mb-0 small text-muted">${distributor.email}</p>` : ''}
                        </div>
                    </div>
                    <div>
                        <span class="badge badge-secondary">${productsCount} Product${productsCount !== 1 ? 's' : ''}</span>
                    </div>
                `;

                return distributorDiv;
            }


            // Toggle dropdown functionality
            function toggleDropdown(brandHeader) {
                const hasDistributors = brandHeader.getAttribute('data-has-distributors') === 'true';
                
                if (!hasDistributors) {
                    return; // Don't toggle if no distributors
                }

                const isExpanded = brandHeader.getAttribute('data-expanded') === 'true';
                const brandWrapper = brandHeader.closest('.brand-wrapper');
                const distributorsList = brandWrapper.querySelector('.distributors-list');
                const dropdownArrow = brandHeader.querySelector('.dropdown-arrow');

                if (isExpanded) {
                    // Collapse
                    distributorsList.style.display = 'none';
                    brandHeader.setAttribute('data-expanded', 'false');
                    dropdownArrow.style.transform = 'rotate(0deg)';
                } else {
                    // Expand
                    distributorsList.style.display = 'block';
                    brandHeader.setAttribute('data-expanded', 'true');
                    dropdownArrow.style.transform = 'rotate(180deg)';
                }
            }

            // Event delegation for brand dropdown clicks
            document.addEventListener('click', function(e) {
                const brandHeader = e.target.closest('.brand-name-drpdwn');
                
                if (brandHeader) {
                    const hasDistributors = brandHeader.getAttribute('data-has-distributors') === 'true';
                    
                    if (hasDistributors) {
                        toggleDropdown(brandHeader);
                    }
                }
            });

            // Initialize on page load
            document.addEventListener('DOMContentLoaded', function() {
                renderBrands(brands);
            });
        </script>
            
        
        <style>
            /* Search Bar Styles */
            .search-bar-wrapper {
                width: 350px;
            }
            .search-bar {
                position: relative;
                background: white;
                border: 1px solid #ddd;
                border-radius: 8px;
                overflow: hidden;
            }
            .search-bar input {
                border: none;
                padding: 10px 15px;
                flex: 1;
                outline: none;
                font-size: 15px;
            }
            .search-bar input:focus {
                outline: none;
                box-shadow: none;
            }
            .search-btn {
                border: none;
                background-color: #007bff;
                color: white;
                padding: 10px 18px;
                cursor: pointer;
                transition: background-color 0.3s;
                border-radius: 0;
            }
            .search-btn:hover {
                background-color: #0056b3;
            }
            .search-btn:focus {
                outline: none;
                box-shadow: none;
            }
            
            /* Search Suggestions */
            .search-suggestions {
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background: white;
                border: 1px solid #ddd;
                border-top: none;
                border-radius: 0 0 8px 8px;
                max-height: 250px;
                overflow-y: auto;
                display: none;
                z-index: 1000;
                box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            }
            .suggestion-item {
                padding: 12px 15px;
                cursor: pointer;
                transition: background-color 0.2s;
                border-bottom: 1px solid #f0f0f0;
            }
            .suggestion-item:last-child {
                border-bottom: none;
            }
            .suggestion-item:hover {
                background-color: #f8f9fa;
            }
            .suggestion-item strong {
                color: #007bff;
            }

            .brands-container {
                background-color: #F5F5F5;
            }
            .brand-wrapper {
                margin-bottom: 10px;
            }
            .brand-name-drpdwn {
                display: flex;
                justify-content: space-between;
                align-items: center;
                background-color: white;
                padding: 15px 20px;
                border-radius: 8px;
                transition: all 0.3s ease;
                user-select: none;
            }
            .brand-name-drpdwn.cursor-pointer:hover {
                background-color: #f8f9fa;
                box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            }
            .cursor-pointer {
                cursor: pointer;
            }
            .dropdown-arrow {
                font-size: 14px;
                transition: transform 0.3s ease;
                width: 20px;
                text-align: center;
            }
            .dropdown-arrow-placeholder {
                width: 20px;
            }
            .brand-logo {
                width: 50px;
                height: 50px;
                flex-shrink: 0;
            }
            .distributors-list {
                padding-left: 20px;
                padding-top: 10px;
            }
            .brand-name-drpdwn-list {
                background-color: #EDEDED;
                border-radius: 6px;
                transition: all 0.2s ease;
            }
            .brand-name-drpdwn-list:hover {
                background-color: #e0e0e0;
            }
            .store-logo {
                width: 60px;
                height: 60px;
                margin-right: 15px;
                flex-shrink: 0;
            }
            .object-fit-cover {
                object-fit: cover;
            }

            /* Scrollbar for suggestions */
            .search-suggestions::-webkit-scrollbar {
                width: 6px;
            }
            .search-suggestions::-webkit-scrollbar-track {
                background: #f1f1f1;
            }
            .search-suggestions::-webkit-scrollbar-thumb {
                background: #888;
                border-radius: 3px;
            }    
            .search-suggestions::-webkit-scrollbar-thumb:hover {
                background: #555;
            }
        </style>
    </section>
</main>
@endsection 