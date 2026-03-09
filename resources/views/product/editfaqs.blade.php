@extends('layouts.new-main')

@section('content')
@section('title', 'Edit Faqs')
<main>
  

    <style>
        @media only screen and (min-width : 767px) and (max-width : 10000px) {
            .search-icon-header {
                display: none;
            }
        }
    </style>
    <style>
        .tmpbx {
            box-shadow: 0 0 5px 0 #64646459;
            display: inline-block
        }

        .clpgbx {
            background-color: #fff;
            box-shadow: 0 0 5px 0 #64646459;
            width: 100%;
            padding: 30px;
            border-radius: 3px;
            margin-bottom: 10px
        }

        .ovrly {
            background-color: #00a08e;
            position: absolute;
            width: 100%;
        }
    </style>
    <script src="https://cdn.ckeditor.com/4.22.1/standard/ckeditor.js"></script>
    <script>
    CKEDITOR.config.versionCheck = false;
    </script>
    <section>
        <div class="card-body faqs-body-add no-padd-left ">
            <div class="container">
                <div class="row">
                    @if (\Session::has('success'))
                    <div class="alert alert-success">
                        <ul>
                            <li>{!! \Session::get('success') !!}</li>
                        </ul>
                    </div>
                    @endif
                    <?php
                    //  echo 'Data: <pre>' .print_r($faqs,true). '</pre>'; die;
                    ?>
                    <div class="col-md-4 ">
                        <h6>Setup Product FAQs</h6>
                        <p class="mb-0">Answer the possible questions</p>
                    </div>
                    <div class="col-md-8  ">
                        <form action="{{URL::to('/updateproductfaq/'. $productFaq['faq_id'])}}" method="POST" id="editFaqs_section">
                            @csrf
                            @method('PUT')
                            <div class="form-group mb-15">
                                <label>
                                    Question
                                </label>
                                <input type="text" class="form-control" name="question" value="{{$productFaq['question']}}">
                            </div>
                            <div class="form-group mb-15">
                                <label>
                                    Answer
                                </label>
                                <textarea id='page_content-edit' class="form-control" name="answer">{{$productFaq['answer']}}</textarea>
                            </div>

                     
                            <div class="add-button-option">
                                <input class="btn btn-primary btn-simple" type="submit" value="Update FAQs">
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </section>
    <script src="{{ URL::asset('assets/js/jquery.validate.js',env('HTTPS_STATUS')) }}"></script>
    <script>
        try {
            CKEDITOR.instances['page_content-edit'].destroy(true);
        } catch (e) {}
        CKEDITOR.replace('page_content-edit', {
            wordcount: {
                showParagraphs: false,
                showWordCount: true,
                showCharCount: true,
                countSpacesAsChars: false,
                countHTML: false,
                maxWordCount: 1000,
                maxCharCount: 10000
            }
        });
        var editAbstract = CKEDITOR.instances.page_content-edit;

        editAbstract.on("key", function(e) {
            if (CKEDITOR.instances.page_content-edit.document.getBody().getChild(0).getText().length > 0) {
                $('body').find('#page_content-edit').css('border-color', '#ced4da');
            }
        }, editAbstract.element.$);


        function KeyUp(editorID, maxLimit, infoID) {

            //If you want it to count all html code then just remove everything from and after '.replace...'
            var text = editorID.getData().replace(/<("[^"]*"|'[^']*'|[^'">])*>/gi, '').replace(/^\s+|\s+$/g, '');
            $("#" + infoID).text(text.length);
            if (text.length > maxLimit) {
                alert("You cannot have more than " + maxLimit + " characters");
                editorID.setData(text.substr(0, maxLimit));
                editor.cancel();
            } else if (text.length == maxLimit - 1) {
                alert("WARNING:\nYou are one character away from your limit.\nIf you continue you could lose any formatting");
                editor.cancel();
            }
        }
        CKEDITOR.replace('page_content-edit');

        $("#editFaqs_section").validate({
            errorElement: "span",
            rules: {
                question: {
                    required: true
                },
                answer: {
                    required: true
                },


            },
            messages: {
                question: {
                    required: "Please Enter Question."
                },
                answer: {
                    required: "Please Enter Answer."
                },

            },
            errorPlacement: function(error, element) {
                error.appendTo(element.closest('.form-group'));
            },
            submitHandler: function(form) {
                form.submit();
            }
        });
    </script>
</main>
@endsection
<!-- Content Ends-->