@extends('layouts.frontend-main')

@section('content')
@section('title', 'Seller Guide')
@section('description', 'Seller Guide')
@section('google-analytics')
@include('layouts.google-analytics')
@stop
<main>
    <style>
       
        @media only screen and (min-width : 767px) and (max-width : 10000px) {}
    </style>
    <section class=" terms-condition-page">
        <div class="container">
            <div class="row">
                <div class="col-sm-12">
                    <div class="terms-and-condition-heading">
                        <h1>Mallshark Seller Guide</h1>
                        <p>Welcome to Mallshark! As a valued seller, we want to ensure your products stand out and attract buyers. Follow this guide to create the best listings and improve your sales on our platform.
                        </p>
                    </div>
                    <div class=" terms-wrapper  mt-40 pt-30">

                        <h2 class="mb-10">1. Store Description</h2>
                        <ul>
                            <li>
                                <p class="padding-right-term"> Refine and present store information professionally to effectively attract customers.</p>
                            </li>
                            <li>
                                <ul class="list-style-type-icon">
                                    <li><p class="padding-right-term">Add your store logo JPG or PNG picture only of size 350x350.</p></li>
                                    <li><p class="padding-right-term">Add an appropriate banner for your store JPG or PNG picture only of size 1180x350.</p></li>
                                    <li><p class="padding-right-term">Add complete contact details including your address and phone number.</p></li>
                                    <li><p class="padding-right-term">Add a clear description of your store introducing the type of products you have and your main goal to acquaint the buyers.</p></li>
                                </ul>
                            </li>
                        </ul>
                        <h2 class="mb-10">2. Provide Accurate Size Information</h2>

                        <div class="nav flex-column flex-nowrap">
                            <p>
                                Use American metrics (inches, pounds, etc.) for all size charts to align with U.S. standards.
                            </p>
                            <p>
                                Include detailed size instructions for every category:
                                <ul class="list-style-type-icon">
                                    <li><strong>Apparel:</strong> Provide chest, waist, hip measurements, and length for clothing items.</li>
                                    <li> <strong>Shoes:</strong> Include U.S. shoe sizes with clear conversion charts if necessary.</li>
                                    <li> <strong>Houseland Items:</strong>Specify dimensions (length x width x height) and weight.</li>
                                </ul>
                            </p>
                            <p>
                                Double-check measurements for accuracy.
                            </p>
                        </div>

                        <div class="nav flex-column flex-nowrap">
                            <h2 class="mb-10">3. Use High-Quality Product Photography</h2>
                            <p>
                                Ensure photos are clear, appealing, and professional.
                            </p>
                            <p>
                                Use a white or light grey background for all images to keep them clean and consistent.
                            </p>
                            <p>
                                Capture multiple angles of the product, including:
                                 <ul class="list-style-type-icon">
                                    <li>Front, back, and side views.</li>
                                    <li>Close-ups of key features or textures.</li>
                                </ul>
                            </p>
                            <p>Avoid cluttered or distracting backgrounds.</p>

                        </div>

                        <div class="nav flex-column flex-nowrap">
                            <h2 class="mb-10">4. Write Clear and Proper Descriptions</h2>

                            <p>Your descriptions must be free of grammatical errors and spelling mistakes.</p>
                            <p>Use professional language to describe the product, focusing on:
                                <ul class="list-style-type-icon">
                                    <li>Key features.</li>
                                    <li>Material and quality.</li>
                                    <li>Usage instructions (if applicable)</li>
                                </ul>
                            </p>

                        </div>

                        <h2 class="mb-10 mt-10" >5. Add a Short, Concise Description</h2>

                        <div class="nav flex-column flex-nowrap">

                            <p>
                                Provide a quick overview of the product (1-2 sentences) to grab attention
                            </p>
                            <p>
                                Highlight the most important features in this section.
                            </p>
                        </div>


                        <h2 class="mb-10">6. Avoid HTML or Special Characters</h2>

                        <div class="nav flex-column flex-nowrap">

                            <p>
                                Do not use HTML, symbols, or special characters in your descriptions. This ensures consistency and prevents display issues.
                            </p>
                        </div>

                        <h2 class="mb-10">7. Approximate Delivery Date (Optional)</h2>

                        <div class="nav flex-column flex-nowrap">
                            <p>Specify the expected delivery date within the U.S., if possible.</p>
                            <p>
                                For example:
                                    <ul class="list-style-type-icon">
                                        <li> "Estimated delivery: 5-7 business days."</li>
                                    </ul>
                            </p>
                            <p>
                                While optional, including this can help build buyer confidence.
                            </p>
                
                        </div>

                        <h2 class="mb-10">8. Create Clear and Meaningful Titles & Tags</h2>

                        <div class="nav flex-column flex-nowrap">

                            <p>
                                Titles should be specific and easy to understand:
                                    <ul class="list-style-type-icon">
                                        <li>&#9989; Example: "Men's Cotton T-Shirt - Navy Blue, Size L"</li>
                                        <li>&#10060; Avoid: "Cool Tee in Blue"</li>
                                    </ul>
                            </p>
                            <p>
                                Tags should describe the product accurately and improve discoverability:
                                    <ul class="list-style-type-icon">
                                        <li>&#9989; Example Tags: "Cotton T-Shirt, Men's Fashion, Navy Blue Shirt"</li>
                                        <li>Avoid overly long or generic tags</li>
                                    </ul>
                            </p>
        
                        </div>
                        <h2 class="mb-10">9. Add Images for Every Variation</h2>

                        <div class="nav flex-column flex-nowrap">

                            <p>
                                For products with variations (e.g., different colors, sizes):
                                    <ul class="list-style-type-icon">
                                        <li>Upload all product images first.</li>
                                        <li>Associate each image with the correct variation during the listing process.</li>
                                        <li> Example:
                                            <ul class="list-style-type-icon">
                                                <li>A blue shirt should have the blue image, a red shirt should have the red image, and so on.</li>
                                            </ul>
                                        </li>
                                    </ul>
                            </p>
        
                        </div>

                        <h2 class="mb-10">Checklist for a Perfect Product Listing</h2>

                        <div class="nav flex-column flex-nowrap">

                            <ul class="list-style-type-icon">
                                <li>Store details ,logo, banner.</li>
                                <li>Accurate American size charts and dimensions.</li>
                                <li>Clear, professional photos with a clean background.</li>
                                <li>Detailed, error-free product descriptions.</li>
                                <li>A short and compelling summary of the product.</li>
                                <li>No HTML or special characters in any text.</li>
                                <li>(Optional) Include an approximate delivery date.</li>
                                <li> Well-structured titles and relevant tags.</li>
                                <li>Variations with corresponding images.</li>
                            </ol>
                        </div>

                        <div class="nav flex-column flex-nowrap">
                        
                            <p>By following these guidelines, you’ll create listings that attract more buyers and build trust with your customers. If you have any questions or need assistance, feel free to reach out to our support team at <strong>care@mallshark.com</strong>.</p>  
                            <ul>
                                <li>
                                <p>Happy Selling!</p>
                                </li>
                                <li>
                                <p>The Mallshark Team &#128044;</p>
                                </li>
                            </ul>
                        </div>
                        
                      
                    </div>
                </div>
            </div>
            <div class="backgroung-change-log"></div>
        </div>
    </section>





    <style>
        .show-more-results {
            display: none;
        }
    </style>

</main>
@endsection
<!-- Content Ends-->