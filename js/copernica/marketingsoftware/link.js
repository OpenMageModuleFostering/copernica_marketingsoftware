/**
 *  Javascript code for link.phtml template. 
 *  links.js
 *  Copernica Marketing Software
 */

// ensure namespace
var Copernica = Copernica || {};

/**
 *  This class will take care of linking page view.
 *  @param  object  The root element of the view
 */
Copernica.MainView = function (rootElement) {

    /**
     *  Current view object.
     *  @var object
     */
    var currentView;

    /**
     *  Currently viewed collection.
     *  @var string
     */
    var current;

    // install on lick handler on collection list
    rootElement.select('#collection_list')[0].observe('click', function (event) {

        // change active tab
        var active = rootElement.select('#collection_list .active');
        for (var i = 0; i < active.length; i++) active[i].removeClassName('active');
        (event.target.match('a') ?  event.target : event.target.up('a')).addClassName('active');

        // get the name of the collection that we want to show
        var name = event.target.up('li').readAttribute('data-target');

        // if picked name is the same as current collection we don't do anything special
        if (name == current) return;

        // assign picked collection as current one
        current = name;

        // clean collection view element
        $('collection_view').innerHTML = '';

        // should we show the default structure view?
        if (name == 'default') currentView = new Copernica.DefaultView($('collection_view'));

        // change to selected collection
        else currentView = new Copernica.CollectionView($('collection_view'), name);

    });

    // chanage collection to main database collection (the database)
    rootElement.select('#collection_list [data-target="main"] a')[0].click();

    // install on click handler on save button
    $('save').observe('click', function (event) {

        // store currently viewed collection
        currentView.store();
    });

    // install on click handler on validate button
    $('validate').observe('click', function (event) {

        // store currently viewed collection
        currentView.validate();
    });
};

/**
 *  This class will handle a default structure view.
 *  @param  object  The root element that will show the view
 */
Copernica.DefaultView = function (rootElement) {
    // create new info elements
    rootElement.insert(new Element('p').update("If it's not needed to customize what kind of data is send to Copernica, it's possible to create whole database structure by clicking below button."));
    rootElement.insert(new Element('p').update("Be aware that this process can take long time (event 10 minutes). Do not close you browser in the middle of the process."));

    // create button
    var button = new Element('button').update('Create default structure').observe('click', function () {
        new Ajax.Request(Copernica.ajaxUrls.collection.default, {
            method: 'post',
            requestHeaders: { Accept: 'application/json' },
            parameters: { name: name },
            onComplete: function (response) {
                window.location.reload();
            }
        });
    });
    
    // insert button
    rootElement.insert(button);

    /**
     *  Store default view
     */
    this.store = function () {
        // it's ok
    };

    /**
     *  Validate default view
     */
    this.validate = function () {
        // it's ok
    };
};

/**
 *  This class will handle collection view.
 *  @param  object  The root element that will show the collection
 *  @param  string  Name of the collection
 */
Copernica.CollectionView = function (rootElement, name) {

    /**
     *  Element that will hold validation results.
     */
    var validateElem;

    /**
     *  Validate collection
     */
    var validateAll = function () {

        // initialize validate data
        var validateData = {
            name: rootElement.select('form input[name="collection_name"]')[0].value,
            type: name,
            fields: []
        }

        // get inputs
        var inputs = rootElement.select('form #fields_list input');

        // placeholder for fields
        var fields = [];

        // prepare fields data
        for (var i = 0; i < inputs.length; i++) if (inputs[i].value) fields.push(inputs[i].readAttribute('name')+','+inputs[i].value);

        /**
         *  This is just silly. Since all data that is send by prototype and received
         *  by magento is encoded in form-multipart type, we can not create more 
         *  complex data structures. The '[]' is a PHP only hack that will allow 
         *  to create an array server side. Well, it seems that it's the best that
         *  we can do here.
         */
        validateData['fields[]'] = fields;

        new Ajax.Request(Copernica.ajaxUrls.collection.validate, {
            method: 'post',
            requestHeaders: { Accept: 'application/json' },
            parameters: validateData,
            onComplete: function ( response ) {
                
                validateElem.show();
                validateElem.innerHTML = '';

                // get object
                var answer = response.responseText.evalJSON();

                if (answer.length == 0) validateElem.insert((new Element('p')).update('Collection is perfectly valid'));

                // iterate over problems
                for(var i = 0; i < answer.length; i++)
                {
                    var problem = new Element('p');
                    validateElem.insert(problem);

                    var parts = answer[i].split(',');

                    if (parts.length > 1) problem.update('Field '+parts[0]+': '+parts[1]);
                    else problem.update('Collection '+parts[0]);
                }
            }
        });
    };

    /**
     *  Store collection name
     */
    var storeAll = function () {

        // set collection info
        var data = {
            name: rootElement.select('form input[name="collection_name"]')[0].value,
            type: name
        };

        // get inputs
        var inputs = rootElement.select('form #fields_list input');

        // placeholder for fields
        var fields = [];

        // prepare fields data
        for (var i = 0; i < inputs.length; i++) fields.push(inputs[i].readAttribute('name')+','+inputs[i].value);

        /**
         *  This is just silly. Since all data that is send by prototype and received
         *  by magento is encoded in form-multipart type, we can not create more 
         *  complex data structures. The '[]' is a PHP only hack that will allow 
         *  to create an array server side. Well, it seems that it's the best that
         *  we can do here.
         */
        data['fields[]'] = fields;

        // store collection data
        new Ajax.Request(Copernica.ajaxUrls.collection.store, {
            method: 'post',
            requestHeaders: { Accept: 'application/json' },
            parameters: data,
            onComplete: function () {
                // huh?
            }
        });
    };
    
    // we want to make all initializations in private namespace.
    (function () {

        /**
         *  Show main collection
         */
        var showMainCollection = function () {
            $$('#collection_list [data-collection="main"] a')[0].click();
        };

        // show that we are fetching data
        rootElement.insert("<p>Loading data...</p>");

        // make AJAX request to fetch collection data
        new Ajax.Request(Copernica.ajaxUrls.collection.fetch, {
            method: 'post',
            requestHeaders: { Accept: 'application/json' },
            parameters: { name: name },
            onComplete: function (response) {

                // eval JSON response
                var answer = response.responseText.evalJSON();

                // check if we have an error
                if (answer.error == 'no database') return showMainCollection();

                // clear root element
                rootElement.innerHTML = '';

                // create form element
                var form = new Element('form');
                rootElement.insert(form);

                // create entry edit element
                var entryEdit = new Element('div', { class: 'entry-edit'} );
                form.insert(entryEdit);

                // create field set with name
                validateElem = new Element('div', { class: 'fieldset fieldset-wide' }).hide();
                entryEdit.insert(validateElem);                                

                // create field set with name
                var nameFieldSet = new Element('div', { class: 'fieldset fieldset-wide' });
                entryEdit.insert(nameFieldSet);

                // create name form list
                var nameFormList = new Element('table', { class: 'form-list' });
                nameFieldSet.insert(nameFormList);

                // create tbody
                var nameTbody = new Element('tbody');
                nameFormList.insert(nameTbody);

                // create form row
                var tr = new Element('tr');
                nameTbody.insert(tr);

                // create label cell
                var label = (new Element('td', { class: 'label' })).update( answer.label );
                tr.insert(label);

                // create value cell
                var value = (new Element('td', { class: 'value' }));
                tr.insert(value);

                // create input
                var input = new Element('input', { class: 'input-text', name: 'collection_name' }).setValue(answer.linkedName);
                value.insert(input);

                // create fieldset
                var fieldSet = new Element('div', { class: 'fieldset fieldset-wide' });
                entryEdit.insert(fieldSet);

                // create form list
                var formList = new Element('table', { class: 'form-list', id: 'fields_list' });
                fieldSet.insert(formList);

                // create tbody
                var tbody = new Element('tbody');
                formList.insert(tbody);

                // iterate over all received fields
                for (var f = 0; f < answer.fields.length; f++) {
                    // brinf field to local scope
                    var field = answer.fields[f];

                    // create tr
                    var tr = new Element('tr');
                    tbody.insert(tr);

                    // create label
                    var label = new Element('td', { class: 'label' });
                    tr.insert(label);
                    label.update(field.label);

                    // create value
                    var value = new Element('td', { class: 'value' });
                    tr.insert(value);

                    // create input field
                    var input = new Element('input', { class: 'input-text', name: field.magento });
                    value.insert(input);
                    input.setValue(field.copernica);
                }
            }
        });
    })();

    /**
     *  Store whole collection
     */
    this.store = function () {
        storeAll();          
    };

    /**
     *  Validate whole collection
     */
    this.validate = function () {
        validateAll();
    };
};
