<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote.min.js"></script>

<script type="text/javascript" src="{{ asset('backend/js/summernote-cleaner.js') }}"></script> 
<style>
.note-icon-caret::before {
	content: "";
}
.note-editable {
	background: #fff;
}
</style>
<script>   
    $(document).ready(function() {
        $('.summernote').summernote({                  
          tabsize: 2,
          height: 250,
          toolbar: [            
            ['style', ['style']],            
            ['fontsize', ['fontsize']],
            ['style', ['bold', 'italic', 'underline', 'clear']],
            ['color', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['height', ['height']],
            ['table', ['table']],
           // ['insert', ['link', 'picture', 'video']],
            ['insert', ['link']],
            // ['view', ['codeview']]
          ],
          cleaner:{
            action: 'both', 
            newline: '<br>', 
            icon: '<i class="fa fa-eraser"></i>',
            keepHtml: false,
            keepOnlyTags: ['<p>', '<br>', '<ul>', '<li>', '<b>', '<strong>','<i>', '<a>'], 
            keepClasses: false,
            badTags: ['style', 'script', 'applet', 'embed', 'noframes', 'noscript', 'html'],
            badAttributes: ['style', 'start'],
            limitChars: false, 
            limitDisplay: 'both',
            limitStop: false
         }
        });
    });
</script>