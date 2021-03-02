const $ = require('jquery');

$(document).ready(function() {
    // Get the ul that holds the collection of options
    var $optionsCollectionHolder = $('ul.options');

    // add a delete link to all of the existing option form li elements
    $optionsCollectionHolder.find('li').each(function() {
        addOptionFormDeleteLink($(this));
    });

    // count the current form inputs we have (e.g. 2), use that as the new
    // index when inserting a new item (e.g. 2)
    $optionsCollectionHolder.data('index', $optionsCollectionHolder.find('input').length);

    $('body').on('click', '.add_option_link', function(e) {
        var $collectionHolderClass = $(e.currentTarget).data('collectionHolderClass');
        // add a new tag form (see next code block)
        addFormToCollection($collectionHolderClass);
    })
});

function addFormToCollection($collectionHolderClass) {
    // Get the ul that holds the collection of options
    var $collectionHolder = $('.' + $collectionHolderClass);

    // Get the data-prototype explained earlier
    var prototype = $collectionHolder.data('prototype');

    // get the new index
    var index = $collectionHolder.data('index');

    var newForm = prototype;
    // Replace '__name__' in the prototype's HTML to
    // instead be a number based on how many items we have
    newForm = newForm.replace(/__name__/g, index);

    // increase the index with one for the next item
    $collectionHolder.data('index', index + 1);

    // Display the form in the page in an li, before the "Add a tag" link li
    var $newFormLi = $('<li></li>').append(newForm);

    addOptionFormDeleteLink($newFormLi);

    // Add the new form at the end of the list
    $collectionHolder.append($newFormLi)
}

function addOptionFormDeleteLink($optionFormLi) {
    var $removeFormButton = $('<button type="button" class="btn btn-danger">Diese Option löschen</button>');
    $optionFormLi.append($removeFormButton);

    $removeFormButton.on('click', function(e) {
        // remove the li for the option form
        $optionFormLi.remove();
    });
}
