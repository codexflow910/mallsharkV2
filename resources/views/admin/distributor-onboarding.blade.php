@extends('layouts.dashboard-main')
@section('content')
@section('title', 'Distributor Onboarding')

<main>
    <section class="admin-dashboard dshbrdin buyer mt-0 increased-fnt-cards">
        @include('admin.adminsidebar')
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <meta name="csrf-token" content="{{ csrf_token() }}">
            <title>Distributor Onboarding</title>
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
                            <h3 class="mb-4 fw-semibold">Distributors</h3>

                            <!-- Tabs -->
                            <ul class="nav nav-tabs mb-4">
                                <li class="nav-item">
                                    <a 
                                        class="nav-link {{ request('status') === 'active' ? 'active' : '' }}" 
                                        href="{{ url('admin/distributors/list?status=active') }}">
                                        Active
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a 
                                        class="nav-link {{ request('status') !== 'active' ? 'active' : '' }}"
                                        href="{{ url('admin/distributors/list') }}">
                                        Pending
                                    </a>
                                </li>
                            </ul>

                            @php
                            $distributorsCount = 0;
                            if (request('status') === 'active') {
                                $distributorsCount = count($distributors);
                            } else {
                                foreach ($distributors as $store) {
                                    $distributorsCount = count($distributors);
                                }
                            }
                            @endphp
                                <!-- Table -->
                            <div class="table-responsive">
                                <table class="table align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            @if (request('status') === 'active')
                                                <th scope="col">{{ $distributorsCount }} Active Distributors</th>
                                                <th scope="col" class="align-baseline">Active till</th>
                                                <th scope="col" class="">Brand Logos</th>
                                            @else
                                                <th scope="col">{{ $distributorsCount }} Pending Requests</th>
                                                <th scope="col" class="">Request Received on</th>
                                                <th scope="col" class="text-right">Brand Logos</th>
                                            @endif
                                        </tr>
                                    </thead>

                                    <tbody id="distributorsTableBody">

                                    </tbody>
                                </table>
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
            const distributors = @json($distributors);
            const isPending = "{{ request('status') }}" === 'pending' || "{{ request('status') }}" === '';
            const isActive = "{{ request('status') }}" === 'active';
            console.log(distributors);
            console.log('Is Pending:', isPending);

            const tableBody = document.getElementById('distributorsTableBody');

            if(!isActive) {
                distributors.forEach(distributor => {
                    const { store_name, store_logo_url, brand } = distributor;
    
                    // Create table row
                    const tr = document.createElement('tr');
                    tr.style.cursor = 'pointer'; // show clickable pointer

                    // Row click → redirect to brand detail page
                    tr.addEventListener('click', () => {
                        window.location.href = `/admin/distributor/detail/${brand.id}`;
                    });

                    // Column 1: Store info
                    const storeTd = document.createElement('td');
                    storeTd.classList.add('align-middle');
                    
                    storeTd.innerHTML = `
                        <div class="d-flex align-items-center">
                            <div class="store-logo rounded" style="width:55px; height:55px;">
                                <img src="${store_logo_url}" alt="${store_name}" class="h-100 w-100 object-fit-cover rounded">
                            </div>
                            <div class="ml-2">${store_name}</div>
                        </div>
                    `;
                    tr.appendChild(storeTd);
                    
                    // Column 2: Expiry Dates
                    const dateTd = document.createElement('td');
                    dateTd.classList.add('align-middle', 'd-flex', 'align-items-center');
    
                    // Pending tab - show Requested Date
                    const requestedDate = brand.updated_at 
                        ? new Date(brand.updated_at).toLocaleDateString('en-GB', { 
                            day: 'numeric', 
                            month: 'short', 
                            year: 'numeric' 
                        })
                        : 'No date';
                    
                    dateTd.innerHTML = `
                        <div class="text-center py-3">
                            <div class="font-weight-bold mr-2"></div>
                            <div>${requestedDate}</div>
                        </div>
                    `;
    
                    // Column 3: Brand logos
                    const brandTd = document.createElement('td');
                    brandTd.classList.add('align-middle', 'text-start');
                    
                    brandTd.innerHTML = `
                        <div class="d-flex justify-content-end align-items-center">
                            <div class="mr-2">${brand.brand_name}</div>
                            <div class="brand-circle">
                                <img src="${brand.logo_url || 'https://via.placeholder.com/50'}" 
                                    alt="${brand.brand_name}" 
                                    class="h-100 w-100 object-fit-cover rounded-circle">
                            </div>
                        </div>
                    `;
                    
                    // Append all to row
                    tr.appendChild(storeTd);
                    tr.appendChild(dateTd);
                    tr.appendChild(brandTd);
    
                    // Add to table body
                    tableBody.appendChild(tr);
                });
            } else {
                distributors.forEach(distributor => {
                    const { store_name, store_logo_url, brands } = distributor;
    
                    // Create table row
                    const tr = document.createElement('tr');
    
                    // Column 1: Store info
                    const storeTd = document.createElement('td');
                    storeTd.classList.add('align-middle');
                    
                    storeTd.innerHTML = `
                        <div class="d-flex align-items-center">
                            <div class="store-logo rounded" style="width:55px; height:55px;">
                                <img src="${store_logo_url}" alt="${store_name}" class="h-100 w-100 object-fit-cover rounded">
                            </div>
                            <div class="ml-2">${store_name}</div>
                        </div>
                    `;
                    tr.appendChild(storeTd);
                   
                    // Column 2: Expiry Dates
                    const dateTd = document.createElement('td');
                    dateTd.classList.add();
    
                    // Active tab - show Active till (expiry date)
                    const expiryList = brands.map(b => `<li>
                            <div class="d-flex align-items-center">
                                <div class="mr-1">${b.brand_name}:</div>
                                <div>${new Date(b.expiry_date).toLocaleDateString('en-GB', { 
                                        day: 'numeric', 
                                        month: 'short', 
                                        year: 'numeric' 
                                        })
                                    }
                                </div>
                            </div>
                        </li>`).join('');
                    
                    dateTd.innerHTML = `
                        <div>
                            <ul class="mb-0">${expiryList}</ul>
                        </div>
                    `;
                   
                    // Column 3: Brand logos
                    const brandTd = document.createElement('td');
                    brandTd.classList.add('align-middle', 'text-start');
    
                    const brandImgs = brands
                        .map(b => `
                            <div class="brand-circle">
                                <img src="${b.logo_url}" alt="${b.brand_name}" class="h-100 w-100 object-fit-cover rounded-circle">
                            </div>
                        `)
                        .join('');
                    brandTd.innerHTML = `<div class="brand-logos d-flex">${brandImgs}</div>`;
    
                    // Append all to row
                    tr.appendChild(storeTd);
                    tr.appendChild(dateTd);
                    tr.appendChild(brandTd);

                    // Add to table body
                    tableBody.appendChild(tr);
                });
            }
        </script>

        <style>
            .brand-circle {
            width: 50px;
            height: 50px;
            margin-right: 8px;
            }
        </style>
    </section>
</main>
@endsection