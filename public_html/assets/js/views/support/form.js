var editorQuill;
$(function(){
    editorQuill = new Quill('#descriptionDiv', {
        modules: {
            toolbar: [
                [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                [{ 'font': [] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'color': [] }, { 'background': [] }],
                [{ 'align': [] }],
                [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                ['link', 'image', 'video']
            ]
        },
        theme: 'snow' // or 'bubble'
    });


    /**
     * Step1. select local image
     *
     */
    function selectLocalImage() {
        const input = document.createElement('input');
        input.setAttribute('type', 'file');
        input.click();

        // Listen upload local image and save to server
        input.onchange = () => {
            const file = input.files[0];

            // file type is only image.
            if (/^image\//.test(file.type)) {
                saveToServer(file);
            } else {
                console.warn('You could only upload images.');
            }
        };
    }

    /**
     * Step2. save to server
     *
     * @param {File} file
     */
    function saveToServer(file) {
        const fd = new FormData();
        fd.append('image', file);

        let sub_url = $('#modalViewSupport [name="path_files"]').val() ?? '';
        if (sub_url !== '') {
            sub_url = `/${sub_url}`;
        }

        const xhr = new XMLHttpRequest();
        xhr.open('POST', $('[name="route_to_save_image_support"]').val() + sub_url, true);
        xhr.setRequestHeader('X-CSRF-TOKEN', $('meta[name="csrf-token"]').attr('content'));
        xhr.onload = () => {
            if (xhr.status === 200) {
                // this is callback data: url
                const url = JSON.parse(xhr.responseText).data;
                insertToEditor(url);
            } else {
                Swal.fire({
                    icon: 'warning',
                    title: 'Atenção',
                    html: '<ol><li>' + JSON.parse(xhr.responseText).join('</li><li>') + '</li></ol>'
                });
            }
        };
        xhr.send(fd);
    }

    /**
     * Step3. insert image url to rich editor.
     *
     * @param {string} url
     */
    function insertToEditor(url) {
        // push image url to rich editor.
        const range = editorQuill.getSelection();
        editorQuill.insertEmbed(range.index, 'image', $('[name="base_url"]').val() + `/${url}`);
    }

    // quill editor add image handler
    editorQuill.getModule('toolbar').addHandler('image', () => {
        selectLocalImage();
    });
});
