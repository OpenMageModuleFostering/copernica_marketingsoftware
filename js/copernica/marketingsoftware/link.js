/**
 *  Javascript code for link.phtml template. 
 *  Copernica Marketing Software v 2.4.5
 *  September 2014
 *  http://www.copernica.com/  
 */

var Copernica = Copernica || {};

/**
 *  Helper function to check if given name is valid
 *  @parma  string
 *  @return boolean
 */
function isValidName(name) {
    return /^[a-z0-9\_]+$/i.test(name);
}

/**
 *  Helper function to create database with current data
 */
function createDatabase() {

    // database name input value (trimmed)
    var databaseNameValue = $('db_input').value.trim();

    // check if database has proper name
    if (!isValidName(databaseNameValue)) {
        // if database name is something that should not happen we want to inform used about it
        $('db_check_result').textContent = 'Database name should be composed from alphanumeric characters without spaces. "_" characters are also allowed.';
        $('db_check_result').style.display = 'inline';

        // we are don with this function
        return;
    }

    new Ajax.Request(
        Copernica.ajaxUrls.database.create,
        {
            method: 'post',
            parameters: { name: databaseNameValue },
            requestHeaders: { Accept: 'application/json' },
            onLoading: function () {
                // we don't want that loader thingy
                $('loading-mask', 'loading_mask_loader').invoke('hide');

                // show proper status
                $('db_check_result').textContent = 'Creating database...';
                $('db_check_result').style.display = 'inline';

                // hide repair button
                $('db_check_repair').textContent = '';
                $('db_check_repair').onclick = null;
                $('db_check_repair').style.display = 'none';
            },
            onComplete: function (response) {
                // get the answer
                var answer = response.responseText.evalJSON();

                // reset result
                $('db_check_result').textContent = answer.message;
                $('db_check_result').style.display = 'inline';

                // reset repair
                $('db_check_repair').textContent = '';
                $('db_check_repair').onclick = null;
                $('db_check_repair').style.display = 'none';

                // check if we have an error
                if (answer.error) {
                    // do we have a fix for error?
                    if (answer.fix != '') {
                        $('db_check_repair').textContent = answer.fix;
                        $('db_check_repair').style.display = 'inline';  

                        // if the fix is to fix account then we have to go to 
                        // another page for that
                        if (answer.fix == 'Fix account') $('db_check_repair').onclick = function (event) {
                            goToAccountSettings();
                        };
                    } 
                }
                else {
                    // we want to validate database after creation
                    validateDatabase();
                }
            }
        }
    );
}

/**
 *  Helper function to valdiate database
 */
function validateDatabase() {
    // database name field value
    var databaseNameValue = $('db_input').value.trim();

    // check if database has proper name
    if (!isValidName(databaseNameValue)) {
        // if database name is something that should not happen we want to inform used about it
        $('db_check_result').textContent = 'Database name should be composed from alphanumeric characters without spaces. "_" characters are also allowed.';
        $('db_check_result').style.display = 'inline';

        // we are don with this function
        return;
    }

    new Ajax.Request(
        Copernica.ajaxUrls.database.validate,
        {
            method: 'post',
            parameters: { name: databaseNameValue },
            requestHeaders: { Accept: 'application/json' },
            onLoading: function () {
                // we don't want that loader thingy
                $('loading-mask', 'loading_mask_loader').invoke('hide');

                // show proper status
                $('db_check_result').textContent = 'Checking database...';
                $('db_check_result').style.display = 'inline';

                // hide repair button 
                $('db_check_repair').textContent = '';
                $('db_check_repair').onclick = null;
                $('db_check_repair').style.display = 'none';
            },
            onComplete: function (response) {
                // get the answer
                var answer = response.responseText.evalJSON();

                // we are just fine
                $('db_check_result').textContent = answer.message;
                $('db_check_result').style.display = 'inline';
                $('db_check_repair').textContent = '';
                $('db_check_repair').onclick = null; 
                $('db_check_repair').style.display = 'inline';

                // check if we have an answer
                if (answer.error && answer.fix != '') {
                    // set the repair element and show it
                    $('db_check_repair').textContent = answer.fix;
                    $('db_check_repair').style.display = 'inline';

                    // if we can create database we can show the repair button
                    if (answer.fix == 'Create') $('db_check_repair').onclick = createDatabase;  

                    // if the fix is to fix account then we have to go to 
                    // another page for that
                    if (answer.fix == 'Fix account') $('db_check_repair').onclick = function (event) {
                        goToAccountSettings();
                    };
                }
            }
        }
    );
}

/** 
 *  Fetch database fields
 */ 
function fetchDatabaseFields() {
    // we want to fetch data for all database fields
    $('customer_fields').select('.customer_field').map(function(row){
        // get magento field name
        var magentoField = row.select('.magento-field-system')[0].value;

        // get status field
        var statusField = row.select('.field-status')[0];

        // get copernica field
        var copernicaField = row.select('.copernica-field')[0];

        // make an ajax call so we can get info about field
        new Ajax.Request(Copernica.ajaxUrls.database.fetch, {
            method: 'post',
            parameters: { name: magentoField },
            requestHeaders: { Accept: 'application/json' }, 
            onLoading: function() {
                // we don't want that loader thingy
                $('loading-mask', 'loading_mask_loader').invoke('hide');

                // we want to set the status as 'in progress'
                statusField.textContent = 'Fetching field data...';
            },
            onComplete: function (response) {
                // get the answer
                var answer = response.responseText.evalJSON();

                // set the message
                statusField.textContent = answer.message;

                // create copernica field name input
                var copernicaName = new Element('input', { class: 'input-text', type: 'text' });    

                // insert copernica field name input
                copernicaField.insert(copernicaName);

                // if we have data about field we can set it right now
                if (answer.data) {
                    // set copernica name 
                    copernicaName.value = answer.data.copernicaName;  

                    // when we have data stored in magento we want to check if 
                    // that info is valid
                    validateDatabaseField(magentoField);
                } 

                // whenever user is done with field edition we want to validate 
                // that field and present current field status
                copernicaName.onblur = function (event) {
                    validateDatabaseField(magentoField);
                }
            }
        });
    });
}

/**
 *  Validate database field.
 *  @paaram string  name of them magento field
 */
function validateDatabaseField(magentoField) {
    // get the row where field is
    var row = $('customer_fields').select('.magento-field-system[value='+magentoField+']')[0].up('.customer_field');

    // fetch status field
    var statusField = row.select('.field-status')[0];

    // get copernica field value
    var copernicaFieldValue = row.select('.copernica-field input')[0].value.trim();

    // check if database field name is valid
    if (!isValidName(copernicaFieldValue)){
        // inform user about wrong database field name
        statusField.textContent = 'Field name should be composed from alphanumeric characters without spaces. "_" characters are also allowed.';
        statusField.style.display = 'inline';

        // we are done here
        return;
    }

    // make the ajax call to validate database field
    new Ajax.Request( Copernica.ajaxUrls.databaseField.validate, {
        method: 'post',
        parameters: { 
            databaseName: $('db_input').value.trim(),
            fieldName: copernicaFieldValue,
            magentoField: magentoField
        },
        requestHeaders: { Accept: 'application/json' },
        onLoading: function () {
            // we don't want that loader thingy
            $('loading-mask', 'loading_mask_loader').invoke('hide');

            // we want to set the status as 'in progreass'
            statusField.textContent = 'Checking field...';
        },
        onComplete: function (response) {
            // get the answer
            var answer = response.responseText.evalJSON();

            // get the status cell
            statusField.innerHTML = '';

            // create new status message element
            var message = new Element('span');
            message.textContent = answer.message;
            statusField.insert(message);

            // do we have an error and an fix for it?
            if (answer.fix) {
                // create new fix button
                var fix = new Element('b');
                fix.textContent = answer.fix;

                // if fix is to create a field we can attach a onclick event
                if (answer.fix == 'Create') {
                    fix.onclick = function (event) {
                        createDatabaseField(magentoField);
                    }
                }

                // we don't have a database... 
                if (answer.fix == 'Create database') {
                    fix.onclick = function (event) {
                        scrollToDatabase();
                    }
                }

                // insert fix button
                statusField.insert(fix);
            }
        }
    });
}

/**
 *  Create database field.
 */
function createDatabaseField(magentoField) {
    // get the row where field is
    var row = $('customer_fields').select('.magento-field-system[value='+magentoField+']')[0].up('.customer_field');

    // fetch status field
    var statusField = row.select('.field-status')[0];

    // get copernica field value (trimmed)
    var copernicaFieldValue = row.select('.copernica-field input')[0].value.trim();

    // check if database field name is valid
    if (!isValidName(copernicaFieldValue)){
        // inform user about wrong database field name
        statusField.textContent = 'Field name should be composed from alphanumeric characters without spaces. "_" characters are also allowed.';
        statusField.style.display = 'inline';

        // we are done here
        return;
    }

    // make new AJAX request
    new Ajax.Request ( Copernica.ajaxUrls.databaseField.create, {
        method: 'post',
        parameters: {
            databaseName: $('db_input').value.trim(),
            fieldName: copernicaFieldValue,
            magentoField: row.select('.magento-field-system')[0].value
        }, 
        requestHeaders: { Accept: 'application/json' },
        onLoading: function () {
            // we don't want that loader thingy
            $('loading-mask', 'loading_mask_loader').invoke('hide');

            // change the status field
            row.select('.field-status')[0].textContent = 'Creating database field...';
        },
        onComplete: function (response) {
            // get the answer
            var answer = response.responseText.evalJSON();

            // get the status cell
            var statusCell = row.select('.field-status')[0];

            // create new status message element
            var statusMessage = new Element('span');
            statusMessage.textContent = answer.message;

            // clear the status cell and insert new message
            statusCell.innerHTML = '';
            statusCell.insert(statusMessage);

            // after creation we want to validate field once more
            validateDatabaseField(magentoField);
        }
    });
}

/**
 *  Validate collection
 */
function validateCollection(collectionName, collectionType) {
    // get the status field
    var statusField = $$('.collection-'+collectionType+' .field-status')[0];

    // check if collection name is valid
    if (!isValidName(collectionName)) {
        // infrom user about wrong collection name
        statusField.textContent = 'Collection name should be composed from alphanumeric characters without spaces. "_" characters are also allowed.';
        statusField.style.display = 'inline';
        
        // we are done in this function
        return;
    }

    // make new Ajax request
    new Ajax.Request( Copernica.ajaxUrls.collection.validate, {
        method: 'post',
        parameters: {
            databaseName: $('db_input').value.trim(),
            collectionName: collectionName,
            collectionType: collectionType
        },
        requestHeaders: { Accept: 'application/json' },
        onLoading: function () {
            // we don't want that loader thingy
            $('loading-mask', 'loading_mask_loader').invoke('hide');

            // set status field to 'in progress'
            statusField.textContent = 'Checking collection...';
        },
        onComplete: function (response) {
            // we want answer as object
            var answer = response.responseText.evalJSON();

            // create new message element
            var message = new Element('span');
            message.textContent = answer.message;

            // reset status field inner HTML
            statusField.innerHTML = '';

            // insert message element into status field
            statusField.insert(message);

            // check if we have a fix for error
            if (answer.fix)
            {
                // create fix button and insert it into status field
                var fix = new Element('b');
                fix.textContent = answer.fix;
                statusField.insert(fix);

                // can we create collection to solve the problem?
                if (answer.fix == 'Create') {
                    fix.onclick = function (event) {
                        createCollection(collectionName, collectionType);
                    }
                }

                // can we create database to solve the problem? 
                if (answer.fix == ' Create database') {
                    fix.onclick = function (event) {
                        scrollToDatabase();
                    }
                }
            }
        }
    });
}

/**
 *  Create collection
 *  @param  string  name of the collection
 *  @param  string  type of the collection
 */
function createCollection(collectionName, collectionType) {
    // get the status field
    var statusField = $$('.collection-'+collectionType+' .field-status')[0];

    // check if collection name is valid
    if (!isValidName(collectionName)) {
        // infrom user about wrong collection name
        statusField.textContent = 'Collection name should be composed from alphanumeric characters without spaces. "_" characters are also allowed.';
        statusField.style.display = 'inline';
        
        // we are done in this function
        return;
    }

    // make ajax request
    new Ajax.Request(Copernica.ajaxUrls.collection.create, {
        method: 'post',
        parameters: {
            databaseName: $('db_input').value.trim(),
            collectionName: collectionName,
            collectionType: collectionType
        },
        requsetHeaders: { Accept: 'application/json' },
        onLoading: function () {
            // we don't want that loader thingy
            $('loading-mask', 'loading_mask_loader').invoke('hide');

            // change to 'in progress'
            statusField.textContent = 'Creating collection...';
        },
        onComplete: function (response) {
            // we want answer as object
            var answer = response.responseText.evalJSON();

            // reset status field
            statusField.innerHTML = '';

            // create message
            var message = new Element('span');
            message.textContent = answer.message;

            // insert message
            statusField.insert(message);

            // after creation we want to revalidate collection
            validateCollection(collectionName, collectionType);
        }
    });
}


/**
 *  Fetch all collections fields.
 */
function fetchCollectionsFields() {
    // iterate over all collections
    $$('.collection input[name="collection"]').map(function (collectionTypeElem){
        // fetch fields for given collection
        fetchCollectionFields(collectionTypeElem.value);
    });
}

/** 
 *  Fetch collection fields
 *  @param string
 */
function fetchCollectionFields(collectionType) {
    // get collection fields container
    var collectionFieldsContainer = $$('.collection .collection-fields.collection-'+collectionType)[0];

    // iterate over all collection rows
    collectionFieldsContainer.select('.field').map(function (fieldRow){
        // get magento field name
        var magentoField = fieldRow.select('.field-magento input[type="hidden"]')[0].value;

        // get status field
        var statusField = fieldRow.select('.field-status')[0];

        // get the current collection name
        var collectionName = $$('.collection .collection-container.collection-'+collectionType+' input')[0].value;

        // make ajax request
        new Ajax.Request( Copernica.ajaxUrls.collection.fetch, {
            method: 'post',
            requestHeaders: { Accept: 'application/json' },
            parameters: {
                databaseName: $('db_input').value.trim(),
                collectionName: collectionName,
                collectionType: collectionType,
                magentoField: magentoField
            },
            onLoading: function () {
                // we don't want that loader thingy
                $('loading-mask', 'loading_mask_loader').invoke('hide');

                // change to 'in progress' text
                statusField.textContent = 'Fetching field...';
            },
            onComplete: function (response) {
                // get answer as object
                var answer = response.responseText.evalJSON();

                // reset status field
                statusField.innerHTML = '';

                // create new element that will hold answer message
                var message = new Element('span');
                message.textContent = answer.message;

                // insert message to status field
                statusField.appendChild(message);

                // create input field
                var inputField = new Element('input', { type: 'text', class: 'input-text' });

                // install handler on blur event
                inputField.onblur = function (event) {
                    validateCollectionField(collectionType, magentoField);
                }

                // get copernica field cell
                var copernicaField = fieldRow.select('.field-copernica')[0];

                // insert input field into copernica cell
                copernicaField.insert(inputField);

                // we have an error
                if (answer.error) {
                    // @todo error handling ?
                }
                else 
                {
                    inputField.value = answer.fieldData;

                    validateCollectionField(collectionType, magentoField);
                }
            }
        });
    });
}

/**
 *  Validate collection field
 */
function validateCollectionField(collectionType, magentoFieldName) {
    // get magento field
    var magentoField = $$('.collection-fields.collection-'+collectionType+' .field-magento input[value="'+magentoFieldName+'"]')[0];

    // get field row
    var fieldRow = magentoField.up('.field', 0);

    // get copernica field
    var copernicaField = fieldRow.select('.field-copernica input')[0];

    // get status cell
    var statusField = fieldRow.select('.field-status')[0];

    // get copernica field value (trimmed)
    var copernicaFieldValue = copernicaField.value.trim();

    // check if collection field name is valid
    if (copernicaFieldValue.length && !isValidName(copernicaFieldValue)) {
        // tell used about mistake
        statusField.textContent = 'Collection field should be composed from alphanumeric characters without spaces. "_" characters are also allowed.';
        statusField.style.display = 'inline';

        // we are done in this function
        return;
    }

    // get collection name
    var collectionName = fieldRow.up('.collection', 0).select('.collection-container .field-name input')[0].value;

    // make ajax request
    new Ajax.Request( Copernica.ajaxUrls.collectionField.validate, {
        method: 'post',
        requestHeaders: { Accept: 'application/json' },
        parameters: {
            databaseName: $('db_input').value.trim(),
            collectionType: collectionType,
            collectionName: collectionName, 
            magentoName: magentoFieldName,
            copernicaName: copernicaFieldValue
        },
        onLoading: function () {
            // we don't want that loader thingy
            $('loading-mask', 'loading_mask_loader').invoke('hide');

            // change status to 'in progress'
            statusField.textContent = 'Checking field...';
        },
        onComplete: function (response) {
            var answer = response.responseText.evalJSON();

            // create message element
            var message = new Element('span');
            message.textContent = answer.message;

            // clear status field
            statusField.innerHTML = '';

            // insert message to status field
            statusField.insert(message);

            // check if we have a fix for error
            if (answer.fix) {
                // create fix element
                var fix = new Element('b');
                fix.textContent = answer.fix;

                // insert fix element into status field
                statusField.insert(fix);

                if (answer.fix == 'Create') {
                    fix.onclick = function (event) {
                        createCollectionField(collectionType, magentoFieldName);
                    }
                }
            }
        }
    });
}

/**
 *  Create collection field
 */
function createCollectionField(collectionType, magentoFieldName) {
    // get magento field
    var magentoField = $$('.collection-fields.collection-'+collectionType+' .field-magento input[value="'+magentoFieldName+'"]')[0];

    // get field row
    var fieldRow = magentoField.up('.field', 0);

    // get copernica field
    var copernicaField = fieldRow.select('.field-copernica input')[0];

    // get status cell
    var statusField = fieldRow.select('.field-status')[0];

    // get copernica field value
    var copernicaFieldValue = copernicaField.value.trim();

    // check if collection field name is valid
    if (!isValidName(copernicaFieldValue)) {
        // tell used about mistake
        statusField.textContent = 'Collection field should be composed from alphanumeric characters without spaces. "_" characters are also allowed.';
        statusField.style.display = 'inline';

        // we are done in this function
        return;
    }

    // get collection name
    var collectionName = fieldRow.up('.collection', 0).select('.collection-container .field-name input')[0].value;

    new Ajax.Request( Copernica.ajaxUrls.collectionField.create, {
        method: 'post',
        requestHeaders: { Accept: 'application/json' },
        parameters: {
            databaseName: $('db_input').value.trim(),
            collectionType: collectionType,
            collectionName: collectionName, 
            magentoName: magentoFieldName,
            copernicaName: copernicaFieldValue
        },
        onLoading: function () {
            // we don't want that loader thingy
            $('loading-mask', 'loading_mask_loader').invoke('hide');

            // change status to 'in progress'
            statusField.textContent = 'Creating field...';

        },
        onComplete: function (response) {
            var answer = response.responseText.evalJSON();

            // check if we have an error
            if (answer.error == 0) {
                statusField.innerHTML = '';

                // change status field text
                statusField.textContent = answer.message;

                // we want to validata created field
                validateCollectionField(collectionType, magentoFieldName);
            }
            else
            {
                // create message element
                var message = new Element('span');
                message.textContent = answer.message;

                // clear up the status field
                statusField.innerHTML = '';                

                // insert message
                statusField.insert(message);
            }
        }
    });
}

/**
 *  Scroll to database input
 */
function scrollToDatabase() {
    $('db_input').up('form', 0).scrollTo();
}

/**
 *  Go to account settings
 */
function goToAccountSettings() {
    window.location.href = Copernica.pageUrls.accountSettings;
}

/**
 *  Helper function to save whole form in correct format
 */
function saveForm() {
    // prepare data for AJAX
    var formData = {};
    formData.database = { name: '', fields: {} };
    formData.collections = {};

    // check if we are setting database
    if ($('db_input').value.trim().length) {
        formData.database.name = $('db_input').value.trim();
    }

    // iterater over all customer fields rows
    $('customer_fields').select('.customer_field').map(function(row){
        // get magento and copernica name
        var magentoName = row.select('.magento-field-system')[0].value.trim();
        var copernicaName = row.select('.copernica-field input')[0].value.trim();

        // check if magento and copernica name are set
        formData.database.fields[magentoName] = copernicaName;
    });

    // iterater over all collections
    $$('.collection').map(function (collectionContainer) {
        // get the collection type
        var collectionType = collectionContainer.select('input[name="collection"]')[0].value.trim();

        // get collection name
        var collectionName = collectionContainer.select('.collection-container input')[0].value.trim();

        // we don't have a collection name
        if (collectionName.length == 0) return;

        // store collection name
        formData.collections[collectionType] = { name: collectionName };

        // make an empty object for collection fields
        formData.collections[collectionType].fields = { };

        // iterate over all collection fields
        collectionContainer.select('.collection-fields .field').map(function(fieldRow){
            // fetch copernica and magento field values
            var copernicaField = fieldRow.select('.field-copernica input')[0].value.trim();
            var magentoField = fieldRow.select('.field-magento input[type="hidden"]')[0].value.trim();

            // assign proper values to collection fields property
            formData.collections[collectionType].fields[magentoField] = copernicaField;
        });
    });

    // we can make proper Ajax request
    new Ajax.Request( Copernica.ajaxUrls.save, {
        method: 'post',
        parameters: { data: Object.toJSON(formData) },
        requestHeaders: { Accept: 'application/json' },
        onLoading: function () {
            // now! we want that loader thingy
            $('loading-mask', 'loading_mask_loader').invoke('show');
        },
        onComplete: function (response) {
            // we don't want that loader thingy
            $('loading-mask', 'loading_mask_loader').invoke('hide');

            // get the answer from server
            var answer = response.responseText.evalJSON();
        }
    });
}

/*
 *  Initialize logic after full DOM is loaded.
 */
document.observe('dom:loaded', function(){
    // when user is finish with typing database name we want to validate it
    $('db_input').onblur = function (event) {
        validateDatabase();
    }

    // when save button is clicked we want to save all info
    $('save').onclick = function (event) {
        saveForm();
    }

    // we want to initialize all collections
    $$('.collection').map(function(container) {
        var collectionNameField = container.select('.collection-container input')[0];

        // get collection type element
        var collectionTypeElem = collectionNameField.up('.collection',0).select('input[name="collection"]')[0];

        // we we do loose focus on input field we want to validate collection
        collectionNameField.onblur = function (event) {
            // validate collection
            validateCollection(collectionNameField.value, collectionTypeElem.value);
        };

        // get status field
        var collectionStatusField = container.select('.collection-container .field-status')[0];

        // do ajax call to get info about collection
        new Ajax.Request(Copernica.ajaxUrls.collection.info, {
            method: 'post',
            requestHeaders: { Accept: 'application/json' },
            parameters: { collectionType: collectionTypeElem.value },
            onLoading: function () {
                // now! we want that loader thingy
                $('loading-mask', 'loading_mask_loader').invoke('hide');

                // change to 'in progress' status
                collectionStatusField.textContent = 'Fetching collection info...';
            },
            onComplete: function (response) {
                var answer = response.responseText.evalJSON();

                // clear status field text
                collectionStatusField.textContent = '';

                // basically we will ignore error
                if (answer.error == 1) return; 
               
                // assign name
                collectionNameField.value = answer.collectionName;
            }
        });
    });

    // fetch database fields data
    fetchDatabaseFields();

    // fetch all collections fields data
    fetchCollectionsFields();
});