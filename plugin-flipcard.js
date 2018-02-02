jQuery(document).ready(function ($) {
    // Set all variables to be used in scope
    var frame, 
        metaBox = $('#flipcard.postbox'), // Your meta box id here
        addFront = $('#upload_front'),
        delFront = $('#delete_front'),
        imgFront = $('#preview_front'),
        idFront = $('#id_front'),
        addBack = $('#upload_back'),
        delBack = $('#delete_back'),
        imgBack = $('#preview_back'),
        idBack = $('#id_back');

    // ONCLICKS
    addFront.on('click', function (event) {
        event.preventDefault();
        side = "front";
        frame = openFrame(side, frame);
    });
    addBack.on('click', function (event) {
        event.preventDefault();
        side = "back";
        frame = openFrame(side, frame);
    });
    delFront.on('click', function (event) {
        event.preventDefault();
        deleteImage("front");
    });
    delBack.on('click', function (event) {
        event.preventDefault();
        deleteImage("back");
    });

    // COLORPICKER
    var myOptions = {
        defaultColor: "#000000",
        palettes: true
    };

    $('.color-picker').wpColorPicker(myOptions);
    $('.text_row').find('.wp-color-result-text').text("Text Color");
    $('.image_row').find('.wp-color-result-text').text("Background Color");

    if ($('#id_back').val()){
        $('#back_bkg_color').closest('.wp-picker-container').hide();
    }
    if ($('#id_front').val()) {
        $('#front_bkg_color').closest('.wp-picker-container').hide();
    }
    
});

function openFrame(side, frame) {
    // If the media frame already exists, reopen it.
    if (frame) {
        addImage(side, frame);
        frame.open();
        return frame;
    }
    // Create a new media frame
    frame = wp.media({
        title: 'Select or Upload Media Of Your Chosen Persuasion',
        button: { text: 'Use this media' },
        multiple: false
    });
    // When an image is selected in the media frame...
    addImage(side,frame);
    // Finally, open the modal on click
    frame.open();

    return frame;
}

function addImage(side, frame){
    frame.off('select');
    frame.on('select', function () {
        // Get media attachment details from the frame state
        var attachment = frame.state().get('selection').first().toJSON();
        // Send the attachment URL to our custom image input field.
        $('#preview_' + side).append('<img src="' + attachment.url + '" />');
        // Send the attachment id to our hidden input
        $('#id_' + side).val(attachment.id);
        // Hide the add image link and bkgcolorpicker
        $('#upload_' + side).hide();
        // Unhide the remove image link
        $('#delete_' + side).show();

        $('#' + side + '_bkg_color').closest('.wp-picker-container').hide();

    });
}

function deleteImage(side) {
    // Clear out the preview image
    $('#preview_' + side).html('');
    // Un-hide the add image link
    $('#upload_' + side).show();
    // Hide the delete image link
    $('#delete_' + side).hide();
    // Delete the image id from the hidden input
    $('#id_' + side).val('');

    $('#' + side + '_bkg_color').closest('.wp-picker-container').show();
}
