<style>
    .documentation-editor-page .note-editor {
        overflow: hidden;
        border: 1px solid #d8e1ed;
        border-radius: 12px;
        box-shadow: 0 5px 18px rgba(30, 41, 59, .06);
    }

    .documentation-editor-page .note-editor .note-toolbar {
        display: flex;
        flex-wrap: wrap;
        gap: 4px;
        padding: 9px;
        color: #334155 !important;
        background: #edf2f8 !important;
        border: 0 !important;
        border-bottom: 1px solid #d8e1ed !important;
        border-radius: 0 !important;
        box-shadow: none !important;
    }

    .documentation-editor-page .note-toolbar .note-btn-group {
        margin: 0 3px 0 0;
    }

    .documentation-editor-page .note-toolbar .note-btn {
        min-width: 36px;
        min-height: 34px;
        padding: 6px 9px;
        color: #fff !important;
        background: #334a6a !important;
        border: 1px solid #425d82 !important;
        border-radius: 7px;
        box-shadow: none;
    }

    .documentation-editor-page .note-toolbar .note-btn:hover,
    .documentation-editor-page .note-toolbar .note-btn.active {
        color: #fff !important;
        background: #1f6fb2 !important;
        border-color: #63a4d8 !important;
    }

    /* The admin theme forces icon glyphs to white. Summernote renders most
       toolbar symbols through ::before, so target both the element and glyph. */
    .documentation-editor-page .note-toolbar .note-btn,
    .documentation-editor-page .note-toolbar .note-btn span,
    .documentation-editor-page .note-toolbar .note-btn i,
    .documentation-editor-page .note-toolbar .note-btn [class^="note-icon"],
    .documentation-editor-page .note-toolbar .note-btn [class*=" note-icon"],
    .documentation-editor-page .note-toolbar .note-btn [class^="note-icon"]::before,
    .documentation-editor-page .note-toolbar .note-btn [class*=" note-icon"]::before,
    .documentation-editor-page .note-toolbar .note-icon-caret,
    .documentation-editor-page .note-toolbar .note-icon-caret::before {
        color: #fff !important;
        -webkit-text-fill-color: #fff !important;
        opacity: 1 !important;
        visibility: visible !important;
    }

    .documentation-editor-page .note-toolbar .note-btn:hover,
    .documentation-editor-page .note-toolbar .note-btn.active,
    .documentation-editor-page .note-toolbar .note-btn:hover span,
    .documentation-editor-page .note-toolbar .note-btn.active span,
    .documentation-editor-page .note-toolbar .note-btn:hover i,
    .documentation-editor-page .note-toolbar .note-btn.active i,
    .documentation-editor-page .note-toolbar .note-btn:hover [class^="note-icon"]::before,
    .documentation-editor-page .note-toolbar .note-btn:hover [class*=" note-icon"]::before,
    .documentation-editor-page .note-toolbar .note-btn.active [class^="note-icon"]::before,
    .documentation-editor-page .note-toolbar .note-btn.active [class*=" note-icon"]::before {
        color: #fff !important;
        -webkit-text-fill-color: #fff !important;
    }

    .documentation-editor-page .note-toolbar .note-btn:focus {
        color: #fff !important;
        background: #1f6fb2 !important;
        border-color: #8bc4ef !important;
        box-shadow: 0 0 0 2px rgba(99, 164, 216, .35) !important;
    }

    .documentation-editor-page .note-editable {
        min-height: 460px;
        padding: 24px;
        color: #273449;
        background: #fff;
        font-family: Arial, sans-serif;
        font-size: 15px;
        line-height: 1.65;
    }

    .documentation-editor-page .note-editable p { margin-bottom: .8rem; }
    .documentation-editor-page .note-editable img { max-width: 100%; height: auto; }
    .documentation-editor-page .note-statusbar { background: #f8fafc; }

    .documentation-editor-page .editor-hint {
        margin: 7px 0 0;
        color: #718096;
        font-size: .8rem;
    }

    @media (max-width: 767px) {
        .documentation-editor-page .note-editable {
            min-height: 360px;
            padding: 16px;
        }
    }
</style>

<script src="{{ asset('plugins/summernote/summernote-bs4.min.js') }}"></script>
<script>window.applyUiLocale && window.applyUiLocale();</script>
<script>
    $(function () {
        $('#documentation').summernote({
            height: 480,
            minHeight: 320,
            placeholder: @js(__('ui.write_the_documentation_here')),
            dialogsInBody: true,
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'italic', 'underline', 'clear']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['insert', ['table', 'link', 'picture', 'hr']],
                ['view', ['fullscreen', 'codeview', 'help']]
            ],
            styleTags: ['p', 'h2', 'h3', 'h4', 'blockquote', 'pre']
        });
    });
</script>
