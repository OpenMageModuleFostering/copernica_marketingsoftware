/**
 *  Javascript holds the implementation of the CopernicaDatabase class
 *  A database object handles a database
 *  Copernica Marketing Software v 1.2.0
 *  April 2011
 *  http://www.copernica.com/  
 */
 
/**
 *  Constructor
 */
function CopernicaDatabase()
{
    // set type
    this.type = 'database';
    this.invalidFields = [];
    
    // the database has two collections
    this.cartItemsCollection = new CopernicaCollection(this, 'cartproducts');
    this.ordersCollection = new CopernicaCollection(this, 'orders');
    this.orderItemsCollection = new CopernicaCollection(this, 'orderproducts');
    this.addressCollection = new CopernicaCollection(this, 'addresses');
    this.viewedProductCollection = new CopernicaCollection(this, 'viewedproduct');
    
    // construct an array of all fields in this collection
    this.fields = [];

    // we always want to have an object, PHP can do this
    // for us but this is only supported in PHP 5.3.0 and up
    if (customerFields.length === 0) customerFields = {};
    
    // fields are stored in a global variable,
    // loop through those fields and convert them to objects
    for (var x in customerFields)
    {
        // append field to the 
        this.fields.push(new CopernicaField(this, x));
    }
    
    // access to the input html element
    var input = this.htmlElement('input');

    // the initial value for the input element
    this.initialValue = input.value;
    
    // timer that is set when key event is caught
    var timer = 0;
    
    // get reference to ourselves
    var self = this;

    // install handler when a key is pressed in the input box
    input.onkeydown = function()
    {
        // a key was pressed, mark the field status as 'checking'
        self.setStatus('checking');

        // is already a timer running for the key events?
        if (timer) clearTimeout(timer);
        
        // we do not want to send the ajax call right away, because that might
        // result in a lot of ajax call for every character typed. Instead, we
        // set a timer and if a new key event occurs within that period, we cancel
        // the ajax call and schedule a new one
        timer = setTimeout(function() {

            // start checking
            self.check();

        }, 1000);
    }
}

/**
 *  Has the database - or one of its collection or fields - been modified
 *  @return boolean
 */
CopernicaDatabase.prototype.modified = function()
{
    // check if the input box has been modified
    var input = this.htmlElement('input');
    if (input.value != this.initialValue) return true;
    
    // check if one of the fields was modified
    for (var i=0; i<this.fields.length; i++)
    {
        // check the field
        if (this.fields[i].modified()) return true;
    }
    
    // check if the collections were modified
    return this.cartItemsCollection.modified() || this.orderItemsCollection.modified() || this.ordersCollection.modified() || this.addressCollection.modified() || this.viewedProductCollection.modified();
}

/**
 *  Enable or disable the warning that fields still need to be saved
 *  Also, enable the Save button and hide the "Not valid account settings" notice.
 */
CopernicaDatabase.prototype.updateSaveWarning = function(fieldName)
{
    // get the right warning element
    var el = document.getElementById('cp_save_notice');
    if (!el) return;
    
    // if a field name is given and appears in the list of invalid
    // fields, than it should be removed there.
    if (fieldName != undefined)
    {
        var newInvalidFields = [];
    
        // iterate over the already registered invalid fields
        for (var i = 0; i < this.invalidFields.length; i++)
        {   
            // remove the matching element
            if (this.invalidFields[i] != fieldName) newInvalidFields[newInvalidFields.length] = this.invalidFields[i];
        }
        
        // store the data in the old var
        this.invalidFields = newInvalidFields;
    }
    
    // there are still some fields with errors, return
    if (this.invalidFields.length > 0) return;
    
    // show or hide the warning
    el.style.display = this.modified() ? 'inline' : 'none';
    
    // hide the 'couldn't establish a connection' notice
    document.getElementById('cp_warning_notice').style.display = 'none'; 
    
    // enable the save button
    document.getElementById('cp_pc_save').disabled = false;
    document.getElementById('cp_pc_save').setAttribute("class","");
}

CopernicaDatabase.prototype.invalidSettingsWarning = function(fieldName)
{
    // if a field name is given add it to the list of fields with 
    // errors
    if (fieldName != undefined)
    {
        // By default the item is not found
        var found = false;
        
        for (var i = 0; i < this.invalidFields.length; i++)
        {   
            // is the element already in there
            if (this.invalidFields[i] == fieldName) found = true;
        }
        
        // the element is not already in there add it
        if (!found) this.invalidFields[this.invalidFields.length] = fieldName;
    }

    // hide the 'save your settings' notice, as is irelevant
    document.getElementById('cp_save_notice').style.display = 'none';
    
    // pop-up a warning notice with a connection couldn't be established message
    document.getElementById('cp_warning_notice').style.display = 'inline';
    
    // disable the save button
    document.getElementById('cp_pc_save').disabled = true;
    document.getElementById('cp_pc_save').setAttribute("class","disabled");
}
/**
 *  Function to get access to the HTML element that represents the database.
 *  Inside the form, the database is linked to a number of HTML elements, like
 *  the textbox and a number of span elements that represent
 *  the status. The type parameter shows the type of HTML element to
 *  return. The following types are recognized:
 *      input       Returns the <input type='text'> element
 *      checking    Returns the 'span' element that says that the database is busy checking
 *      impossible  Returns the 'span' element that says that the check can not be performed, because API is unreachable or some other reason
 *      notexists   Returns the 'span' element that says that the database does not exist
 *      notvalid    Returns the 'span' element that says that the database exists, but misses some required fields
 *      ok          Returns the 'span' element that says that the database is valid
 *  @param  string      Type of element
 *  @return object      Element object
 */
CopernicaDatabase.prototype.htmlElement = function(type)
{
    return document.getElementById('db_' + type);
}

/**
 *  Get the current database value
 *  @return string
 */
CopernicaDatabase.prototype.value = function()
{
    return this.htmlElement('input').value;
}

/**
 *  Get the current database status.
 *  @return string  'checking', 'impossible', 'notexists', 'notvalid' or 'ok'
 */
CopernicaDatabase.prototype.status = function()
{
    // the supported status values
    var supported = ['checking','impossible','notexists','notvalid','ok'];
    
    // loop through the span elements that describe this status
    for (var i=0; i<supported.length; i++)
    {
        // check if this HTML element is now visible
        var status = supported[i];
        if (this.htmlElement(status).style.display != 'none') return status;
    }
    
    // not a single HTML element was visible, assume status is 'impossible'
    return 'impossible';
}

/**
 *  Does the database exist?
 *  @return boolean
 */
CopernicaDatabase.prototype.exists = function()
{
    var status = this.status();
    return status == 'ok' || status == 'notvalid';
}

/**
 *  Function to start checking all fields if they exist
 */
CopernicaDatabase.prototype.checkFields = function()
{
    // self reference
    var self = this;
    
    // function to check if a field exist
    var checkField = function(counter)
    {
        // if the current collection no longer exists, it makes no sense to continue checking fields
        if (!self.exists()) return;
        
        // special treatment for checking the collections
        if (counter == self.fields.length) 
        {
            // check the products collection   
            self.cartItemsCollection.check(function() { checkField(counter + 1); })
        }
        else if (counter == self.fields.length + 1) 
        {
            // check the orders collection   
            self.ordersCollection.check(function() { checkField(counter + 1); })
        }
        else if (counter == self.fields.length + 2) 
        {
            // check the orders collection   
            self.orderItemsCollection.check(function() { checkField(counter + 1); })
        }
        else if (counter == self.fields.length + 3) 
        {
            // check the orders collection   
            self.addressCollection.check(function() { checkField(counter + 1); })
        }
        else if (counter == self.fields.length + 4) 
        {
            // check the orders collection   
            self.viewedProductCollection.check(function() { checkField(counter + 1); })
        }
        else if (counter < self.fields.length)
        {
            // check if the field exist, with a callback for checking the next field
            self.fields[counter].check(function() {
                checkField(counter + 1);
            });
        }
    }

    // start checking
    checkField(0);
}

/**
 *  Retrieve one specific Field Object from all Fields Objects available in the CopernicaDatabase Profile Structure
 *  @param string  Name of the field
 *  @return Object|null  We return the Fields object or null
 */
CopernicaDatabase.prototype.field = function(name)
{
    for (var i=0;i<this.fields.length;i++)
    {
        if (this.fields[i].name == name) return this.fields[i];
    }
    return null;
}
 
 
/**
 *  Change the current database status
 *  @param  string  New status, supported are: 'checking', 'impossible', 'notexists', 'notvalid' and 'ok'
 */
CopernicaDatabase.prototype.setStatus = function(status)
{
    // skip if status does not change
    var oldstatus = this.status();
    if (oldstatus == status) return;
    
    // check if the database did exist before
    var didexist = this.exists();

    // the supported status values
    var supported = ['checking','impossible','notexists','notvalid','ok'];
    
    // loop through the span elements that describe this status
    for (var i=0; i<supported.length; i++)
    {
        // update the visibility of this span element
        this.htmlElement(supported[i]).style.display = (status === supported[i]) ? 'inline' : 'none';
    }
    
    // should we re-check all fields?
    var doesexist = this.exists();
    if ((didexist == doesexist) && (status != 'impossible')) return;
    
    // does the collection came into existance?
    if (doesexist)
    {
        // check all fields
        this.checkFields();
    }
    else
    {   
        // mark all fields as impossible to check
        for (var i=0; i<this.fields.length; i++) this.fields[i].setStatus('impossible');

        // mark the collections as impossible to check too
        this.ordersCollection.setStatus('impossible');
        this.cartItemsCollection.setStatus('impossible');
        this.orderItemsCollection.setStatus('impossible');
        this.addressCollection.setStatus('impossible');
        this.viewedProductCollection.setStatus('impossible');
    }
}

/**
 *  Do a certain ajax call
 *  @param  string      Type of call
 *  @param  function    Callback function
 */
CopernicaDatabase.prototype.ajaxCall = function(type, callback)
{
    // while the ajax call is in progress, the status is set to 'checking'
    this.setStatus('checking'); 
    
    // self reference
    var self = this;

    // do the ajax call
    doAjaxCall({
        type:       type,
        database:   this.value()
    }, function(answer) {
        
        // set the status
        answer.responseText = (answer.responseText === '') ? 'ok' : answer.responseText;
        self.setStatus(answer.responseText);
        
        // depending on the status, display the relevant notice
        (answer.responseText !== 'ok') ? self.invalidSettingsWarning('database') : self.updateSaveWarning('database');
        
        // call the callback, if it was supplied
        if (callback) callback(answer.responseText == 'ok');
    });
}
 
/**
 *  Perform the check.
 *  This method sends an ajax request to the server to check if the database exists and is valid
 *  @param  function    Optional callback function that will be called when the ajax call completes
 */
CopernicaDatabase.prototype.check = function(callback)
{
    // do the ajax call
    this.ajaxCall('check_database', callback);
}

/**
 *  Create the database.
 *  This method sends an ajax request to create the database in Copernica
 *  @param  function    Optional callback function that will be called when ajax call completes
 */
CopernicaDatabase.prototype.create = function(callback)
{
    // do the ajax call
    this.ajaxCall('create_database', callback);
}

/**
 *  Repair the database.
 *  This method sends an ajax request to create missing fields in the database
 *  @param  function    Optional callback function that will be called when ajax call completes
 */
CopernicaDatabase.prototype.repair = function(callback)
{
    // do the ajax call
    this.ajaxCall('repair_database', callback);
}