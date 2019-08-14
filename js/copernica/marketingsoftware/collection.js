/**
 *  Javascript holds the implementation of the CopernicaCollection class
 *  A collection object handles a collection
 *  Copernica Marketing Software v 1.2.0
 *  April 2011
 *  http://www.copernica.com/  
 */
 
/**
 *  Constructor
 *  @param  Database        The containing database
 *  @param  string          Type of collection, 'cartproducts', 'orders', 'orderproducts' or 'addresses'
 */

function CopernicaCollection(database, type)
{
    // store arguments to the constructor
    this.parent = database;
    this.type = type;
    
    // construct an array of all fields in this collection
    this.fields = [];
    
    // fields are stored in a global variable
    if (type == 'cartproducts')         var theFields = cartItemFields;
    else if (type == 'orders')          var theFields = orderFields;
    else if (type == 'orderproducts')   var theFields = orderItemFields;
    else if (type == 'viewedproduct')   var theFields = viewedProductFields;
    else                                var theFields = addressFields;

    // we always want to have an object, PHP can do this
    // for us but this is only supported in PHP 5.3.0 and up
    if (theFields.length === 0) theFields = {};
    
    // loop through the fields and convert them to objects
    for (var x in theFields)
    {
        // append field to the 
        this.fields.push(new CopernicaField(this, x));
    }
    
    // access to the input html element
    var input = this.htmlElement('input');
    
    // the initial value for the input element
    this.initialValue = input.value;
    
    // get reference to ourselves
    var self = this;
    
    // timer that is set when key event is caught
    var timer = 0;
    
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
 *  Has the collection - or one of it fields - been modified
 *  @return boolean
 */
CopernicaCollection.prototype.modified = function()
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
    
    // not modified
    return false;
}

/**
 *  Function to get access to the HTML element that represents the field.
 *  Inside the form, the field is linked to a number of HTML elements, like
 *  the textbox, the selectbox and a number of span elements that represent
 *  the field status. The type parameter shows the type of HTML element to
 *  return. The following types are recognized:
 *      input       Returns the <input type='text'> element
 *      checking    Returns the 'span' element that says that the collection is busy checking
 *      impossible  Returns the 'span' element that says that the check can not be performed, because API is unreachable or database does not exist
 *      notexists   Returns the 'span' element that says that the collection does not exist
 *      notvalid    Returns the 'span' element that says that the collection exists, but misses some required fields
 *      ok          Returns the 'span' element that says that the collection is valid
 *  @param  string      Type of element
 *  @return Object      Element object
 */
CopernicaCollection.prototype.htmlElement = function(type)
{
    return document.getElementById(this.type + '_' + type);
}

/**
 *  Get the current field value
 *  @return string
 */
CopernicaCollection.prototype.value = function()
{
    return this.htmlElement('input').value;
}

/**
 *  Get the current field status.
 *  @return string  'checking', 'impossible', 'notexists', 'notvalid' or 'ok'
 */
CopernicaCollection.prototype.status = function()
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
 *  Does the collection exist?
 *  @return boolean
 */
CopernicaCollection.prototype.exists = function()
{
    var status = this.status();
    return status == 'ok' || status == 'notvalid';
}

/**
 *  Function to start checking all fields if they exist
 */
CopernicaCollection.prototype.checkFields = function()
{
    // self reference
    var self = this;
    
    // function to check if a field exist
    var checkField = function(counter)
    {
        // stop if this field does not exist
        if (self.fields.length <= counter) return;
        
        // if the current collection no longer exists, it makes no sense to continue checking fields
        if (!self.exists()) return;
    
        // check if the field exist, with a callback for checking the next field
        self.fields[counter].check(function() {
            checkField(counter + 1);
        });
    }
    
    // start checking the first field
    checkField(0);
}

/**
 *  Retrieve one specific Field Object from all Fields Objects available in the CopernicaCollections Structure
 *  @param string  Name of the field
 *  @return Object|null  We return the Fields object or null
 */
CopernicaCollection.prototype.field = function(name)
{
    for (var i=0;i<this.fields.length;i++)
    {
        if (this.fields[i].name == name) return this.fields[i];
    }
    return null;
}
    
/**
 *  Change the current field status
 *  @param  string  New status, supported are: 'checking', 'impossible', 'notexists', 'notvalid' and 'ok'
 */
CopernicaCollection.prototype.setStatus = function(status)
{
    // skip if status does not change
    var oldstatus = this.status();
    if ((oldstatus == status) && (status != 'impossible')) return;
    
    // check if the collection did exist before
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
    }
}

/**
 *  Do a certain ajax call
 *  @param  string      Type of call
 *  @param  function    Callback function
 */
CopernicaCollection.prototype.ajaxCall = function(type, callback)
{
    // while the ajax call is in progress, the status is set to 'checking'
    this.setStatus('checking');

    // self reference
    var self = this;

    // do the ajax call
    doAjaxCall({
        type:               type,
        collection_type:    this.type,
        database:           this.parent.value(),
        collection:         this.value()
    }, function(answer) {
        // set the status
        answer.responseText = (answer.responseText === '') ? 'ok' : answer.responseText;
        self.setStatus(answer.responseText);
        
        // depending on the status, display the relevant notice
        var database = self.parent;
        
        // we have the database object, we can now change the value
        (answer.responseText !== 'ok') ? database.invalidSettingsWarning('collection_'+self.type) : database.updateSaveWarning('collection_'+self.type);
                
        // call the callback, if it was supplied
        if (callback) callback(answer.responseText == 'ok');
    });
}
 
/**
 *  Perform the check.
 *  This method sends an ajax request to the server to check if the collection exists and is valid
 *  @param  function    Optional callback function that will be called when the ajax call completes
 */
CopernicaCollection.prototype.check = function(callback)
{
    // do the ajax call
    this.ajaxCall('check_collection', callback);
    
}

/**
 *  Create the collection.
 *  This method sends an ajax request to create the collection in Copernica
 *  @param  function    Optional callback function that will be called when ajax call completes
 */
CopernicaCollection.prototype.create = function(callback)
{
    // do the ajax call
    this.ajaxCall('create_collection', callback);
}

/**
 *  Repair the collection.
 *  This method sends an ajax request to create missing fields in the collection
 *  @param  function    Optional callback function that will be called when ajax call completes
 */
CopernicaCollection.prototype.repair = function(callback)
{
    // do the ajax call
    this.ajaxCall('repair_collection', callback);
}