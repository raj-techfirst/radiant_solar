@extends('layouts.app')
@section('title', 'News & Updates')
@section('css')
<link rel="stylesheet" type="text/css" href="{{asset('app-assets/css/typography.css')}}">
<link rel="stylesheet" type="text/css" href="{{asset('app-assets/css/katex.css')}}">
<link rel="stylesheet" type="text/css" href="{{asset('app-assets/css/editor.css')}}">
@endsection

@section('content')
<div class="row">
     <div class="col-12 my-50">
          @if((isset($newsupdate) && isset($newsupdate->id)))
          <h4 class="content-header-title float-start">Edit News & Updates</h4>
          @else
          <h4 class="content-header-title float-start">Add News & Updates</h4>
          @endif
     </div>
     <div class="col-12">
          <div class="card">
               <div class="card-body">
                    <form id="newsupdate_form" class="form" action="javascript:void(0);" method="POST" enctype="multipart/form-data">
                         <div class="row">
                              @if((isset($newsupdate) && isset($newsupdate->id)))
                              <input type="hidden" name="newsupdate_id" id="newsupdate_id" value="{{ $newsupdate->id }}">
                              @endif
                              <div class="col-md-4 col-12 mb-1 custom-input-group">
                                   <label class="form-label" for="title">Title<span class="text-danger">*</span></label>
                                   <input type="text" class="form-control" name="title" id="title" placeholder="Title" value="{{ ((isset($newsupdate) && isset($newsupdate->title)) ? $newsupdate->title : '')  }}">
                              </div>
                              <div class="col-md-4 col-12 mb-1 custom-input-group">
                                   <label class="form-label" for="type">Status</label>
                                   <select class="form-control select2 custom-select2" name="status" id="status">
                                        <option selected disabled>-- Select --</option>
                                        <option value="0" {{ (isset($newsupdate) && $newsupdate->status == '0') ? 'selected' : '' }}>Front Side</option>
                                        <option value="1" {{ (isset($newsupdate) && $newsupdate->status == '1') ? 'selected' : '' }}>Back Side</option>
                                   </select>
                              </div>
                              <div class="col-md-4 col-12 mb-1 custom-input-group">
                                   <label class="form-label" for="type">Type</label>
                                   <select class="form-control select2 custom-select2" name="type" id="type">
                                        <option selected disabled>-- Select --</option>
                                        <option value="0" {{ (isset($newsupdate) && $newsupdate->type == '0') ? 'selected' : '' }}>Top</option>
                                        <option value="1" {{ (isset($newsupdate) && $newsupdate->type == '1') ? 'selected' : '' }}>Middle</option>
                                        <option value="2" {{ (isset($newsupdate) && $newsupdate->type == '2') ? 'selected' : '' }}>Bottom </option>
                                        <option value="3" {{ (isset($newsupdate) && $newsupdate->type == '3') ? 'selected' : '' }}>Natural Fruit </option>
                                        <option value="4" {{ (isset($newsupdate) && $newsupdate->type == '4') ? 'selected' : '' }}>Other</option>
                                   </select>
                              </div>
                              <div class="col-md-6 col-12 mb-1 custom-input-group">
                                   <label class="form-label" for="sequence">Sequence<span class="text-danger">*</span></label>
                                   <input type="text" class="form-control" name="sequence" id="sequence" placeholder="Sequence" value="{{ ((isset($newsupdate) && isset($newsupdate->sequence)) ? $newsupdate->sequence : '')  }}">
                              </div>

                              <div class="col-lg-6 col-md-12 mb-1 mb-sm-0 custom-input-group">
                                   <label for="image" class="form-label"> Image @if((!isset($newsupdate) && !isset($newsupdate->id)))<span class="text-danger">*</span>@endif<small> (1400 X 800)</small></label>
                                   <input class="form-control" type="file" id="image" name="image" accept="image/*">
                                   @if((isset($newsupdate) && isset($newsupdate->id)))
                                   <img class="mt-1 rounded" src="{{asset('upload/newsupdate/'.$newsupdate->image)}}" height="100" width="100">
                                   @endif
                              </div>
                              <div class="col-lg-12 light-style form-group custom-input-group mt-1">
                                   <label class="form-label">Description</label>
                                   <div id="full-editor1">{!! ((isset($newsupdate) && isset($newsupdate->description)) ? $newsupdate->description : '') !!}</div>
                                   <input type="hidden" name="description" id="editor1">
                              </div>

                              <div class="col-lg-12 light-style form-group custom-input-group mt-1">
                                   <label class="form-label">Short Description</label>
                                   <div id="full-editor2">{!! ((isset($newsupdate) && isset($newsupdate->short_description)) ? $newsupdate->short_description : '') !!}</div>
                                   <input type="hidden" name="short_description" id="editor2">
                              </div>

                              <div class="col-12 mt-2">
                                   <button type="submit" class="btn btn-sm btn-primary float-end save-newsupdate">Submit</button>
                              </div>
                         </div>
                    </form>
               </div>
          </div>
     </div>
</div>
@endsection

@section('script')
<script src="{{asset('app-assets/js/katex.js')}}"></script>
<script src="{{asset('app-assets/js/quill.js')}}"></script>
@endsection

@section('pagescript')

<script type="text/javascript">
     "use strict";
     $.ajaxSetup({
          headers: {
               'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
          }
     });

     $(document).ready(function() {
          var quill1 = new Quill("#full-editor1", {
               bounds: "#full-editor1",
               placeholder: "Type Something...",
               modules: {
                    formula: !0,
                    toolbar: [
                         [{
                              font: []
                         }, {
                              size: []
                         }],
                         ["bold", "italic", "underline", "strike"],
                         [{
                              color: []
                         }, {
                              background: []
                         }],
                         [{
                              script: "super"
                         }, {
                              script: "sub"
                         }],
                         [{
                              header: "1"
                         }, {
                              header: "2"
                         }, "blockquote", "code-block"],
                         [{
                              list: "ordered"
                         }, {
                              list: "bullet"
                         }, {
                              indent: "-1"
                         }, {
                              indent: "+1"
                         }],
                         [{
                              direction: "rtl"
                         }],
                         // ["link", "image", "video", "formula"],
                         // ["clean"]
                    ]
               },
               theme: "snow"
          });

          var quill2 = new Quill("#full-editor2", {
               bounds: "#full-editor2",
               placeholder: "Type Something...",
               modules: {
                    formula: !0,
                    toolbar: [
                         [{
                              font: []
                         }, {
                              size: []
                         }],
                         ["bold", "italic", "underline", "strike"],
                         [{
                              color: []
                         }, {
                              background: []
                         }],
                         [{
                              script: "super"
                         }, {
                              script: "sub"
                         }],
                         [{
                              header: "1"
                         }, {
                              header: "2"
                         }, "blockquote", "code-block"],
                         [{
                              list: "ordered"
                         }, {
                              list: "bullet"
                         }, {
                              indent: "-1"
                         }, {
                              indent: "+1"
                         }],
                         [{
                              direction: "rtl"
                         }],
                         // ["link", "image", "video", "formula"],
                         // ["clean"]
                    ]
               },
               theme: "snow"
          });

          $('.save-newsupdate').on('click', function() {
               var content1 = quill1.root.innerHTML;
               var content2 = quill2.root.innerHTML;
               $("#editor1").val(content1);
               $("#editor2").val(content2);
               var formData = new FormData($("#newsupdate_form")[0]);
               if ($("#newsupdate_id").val() != "" && $("#newsupdate_id").val() != undefined) {
                    var temp = $("#title").val() != "";
                    var img = false;
               } else {
                    var temp = $("#title").val() != "" && $("#image").val() != "";
                    var img = true;
               }
               if (temp) {
                    $.ajax({
                         type: "POST",
                         url: "{{route('newsupdate.store')}}",
                         data: formData,
                         dataType: 'json',
                         cache: false,
                         contentType: false,
                         processData: false,
                         beforeSend: function() {
                              $(".save-newsupdate").html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Wait');
                              $(".save-newsupdate").attr('disabled', true);
                         },
                         success: function(response) {
                              $(".save-newsupdate").html('Submit');
                              $(".save-newsupdate").attr('disabled', false);
                              if (response.status == false) {
                                   $.each(response.errors, function(key, value) {
                                        $('#error_' + key).html('<p class="text-danger mb-0">' + value + '</p>');
                                   });
                                   toastr.warning('Please Input Propper Data.', 'Warning');
                              } else if (response.server_error && response.status == true) {
                                   toastr.error(response.server_error, 'Error');
                              } else {
                                   $('#newsupdate_form')[0].reset();
                                   toastr.success(response.message, 'Success');
                                   setTimeout(function() {
                                        location.href = response.data;
                                   }, 2000);
                              }
                         }
                    });
               } else {
                    $("#newsupdate_form").validate({
                         rules: {
                              title: {
                                   required: true,
                              },
                              image: {
                                   required: img,
                              }
                         },
                         messages: {
                              title: {
                                   required: "Enter Title"
                              },
                              image: {
                                   required: "Upload Image"
                              },
                         },
                         errorElement: "p",
                         errorClass: "text-danger mb-0",

                         highlight: function(element) {
                              $(element).addClass('has-error');
                         },
                         unhighlight: function(element) {
                              $(element).removeClass('has-error');
                         },
                         errorPlacement: function(error, element) {
                              $(element).closest('.custom-input-group').append(error);
                         }
                    });
               }
          });
     });
</script>
@endsection