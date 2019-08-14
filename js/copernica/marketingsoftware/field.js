/**
 *  Javascript holds the implementation of the CopernicaField class
 *  A field object represents one Magento field in a database
 *  Copernica Marketing Software v 1.2.0
 *  April 2011
 *  http://www.copernica.com/  
 */

/**
 *  Constructor
 *  @param  Database|Collection     Database or Collection object that holds the field
 *  @param  string                  Name of the field
 */
function CopernicaField(parent, name)
{
    // store reference to the database and the name
    this.parent = parent;
    this.name = name;

    // get reference to ourselved
    var self = this;
    
    // get access to a number of HTML elements that we need in the handlers
    var input = this.htmlElement('input');
    var select = this.htmlElement('select');
    
    // store the initial value
    this.initialSelectValue = select ? select.selectedIndex : -1;
    this.initialInputValue = input.value;
    
    // timer that is set for handling keypress events
    var timer = 0;
    
    if (select)
    {
        // install handler when select box changes its value
        select.onchange = function()
        {
            // is the field now linked?
            if (select.selectedIndex == 0)
            {
                // the selectbox is set to 'not linked'
                self.setStatus('notlinked');
                input.value = '';
                input.style.display = 'none';
            }
            else if (select.selectedIndex == 2)
            {
                input.value = self.fieldName();
                input.style.display = 'block';
                
                // the field should be checked
                self.ajaxCall('check_field', false);
            }
            else if (input.value == '')
            {
                // the selectbox is set to 'linked', but no value is entered in the input box
                self.setStatus('impossible');
                input.style.display = 'block';
            }
            else
            {
                // check if the field exists
                self.check();
                input.style.display = 'block';
            }
        }
    }
    
    // install handler when a key is pressed in the input box
    input.onchange = input.onkeydown = function()
    {
        // a key was pressed, mark the field status as 'checking'
        self.setStatus('checking');
        
        // is already a timer running for the key events?
        if (timer) clearTimeout(timer);
        
        // we do not want to send the ajax call right away, because that might
        // result in a lot of ajax call for every character typed. In stead, we
        // set a timer and if a new key event occurs within that period, we cancel
        // the ajax call and schedule a new one
        timer = setTimeout(function() {
        
            // start checking
            self.check();
        
        }, 1000);
    }
}

/**
 *  Check if the field setting has been modified since it was loaded on the page
 *  @return boolean
 */
CopernicaField.prototype.modified = function()
{
    // get access to a number of HTML elements that we need in the handlers
    var input = this.htmlElement('input');
    var select = this.htmlElement('select');
    
    // check if the selectbox or input box has been changed
    if (select && select.selectedIndex != this.initialSelectValue) return true;
    return input.value != this.initialInputValue;
}

/**
 *  Function to get access to the HTML element that represents the field.
 *  Inside the form, the field is linked to a number of HTML elements, like
 *  the textbox, the selectbox and a number of span elements that represent
 *  the field status. The type parameter shows the type of HTML element to
 *  return. The following types are recognized:
 *      input       Returns the <input type='text'> element
 *      select      Returns the <select> box
 *      checking    Returns the 'span' element that says that the field is busy checking
 *      impossible  Returns the 'span' element that says that the check can not be performed, because API is unreachable or database does not exist
 *      notexists   Returns the 'span' element that says that the field does not exist
 *      ok          Returns the 'span' element that says that the collection is valid
 *  @param  string      Type of element
 *  @return object      Element object
 */
CopernicaField.prototype.htmlElement = function(type)
{
    return document.getElementById(type + '_' + this.name);
}

/**
 *  Get the current field value
 *  @return string
 */
CopernicaField.prototype.value = function()
{
    return this.htmlElement('input').value;
}

/**
 *  Get the name of the field
 *  @return string
 */
CopernicaField.prototype.fieldName = function()
{
    // what do we need to replace
    var search = this.parent.type == 'database' ? 'customer' : this.parent.type;

    // remove the parent type from the name
    return this.name.replace(search + '_', '');
}

/**
 *  Get the current field status.
 *  @return string  'notlinked', 'checking', 'impossible', 'notexists', 'ok'
 */
CopernicaField.prototype.status = function()
{
    // check if the selectbox is set to 'not linked'
    if (this.htmlElement('select') && this.htmlElement('select').selectedIndex == 0) return 'notlinked';

    // the supported status values
    var supported = ['checking','impossible','notexists','ok'];
    
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
 *  Change the current field status
 *  @param  string  New status, supported are: 'notlinked', 'checking', 'impossible', 'notexists' and 'ok'
 */
CopernicaField.prototype.setStatus = function(status)
{
    // skip if status does not change
    //if (this.status() == status) return;

    // the supported status values
    var supported = ['checking','impossible','notexists','ok','notlinked','notvalid'];
    
    // skip if the field is not linked
    if (this.status() == 'notlinked') status = 'notlinked_empty';
    
    // loop through the span elements that describe this status
    for (var i=0; i<supported.length; i++)
    {
        // update the visibility of this span element
        this.htmlElement(supported[i]).style.display = (status === supported[i]) ? 'inline' : 'none';
    }
}

/**
 *  Do a certain ajax call
 *  @param  string      Type of call
 *  @param  function    Callback function
 */
CopernicaField.prototype.ajaxCall = function(type, callback)
{
    // self reference
    var self = this;
    
    // checking status
    if (type == 'create_field' || type == 'check_field' || type == 'repair_field') self.setStatus('checking');
    
    // do the ajax call
    doAjaxCall({
        type:               type,
        database:           this.parent.type == 'database' ? this.parent.value() : this.parent.parent.value(),
        collection:         this.parent.type,
        collectionName:     this.parent.type == 'database' ? '' : this.parent.value(),
        field:              this.value(),
        field_system_name:  this.fieldName()
    }, function(answer) {
        // set the status
        answer.responseText = (answer.responseText === '') ? 'ok' : answer.responseText;
        self.setStatus(answer.responseText);

        // depending on the status, display the relevant notice
        if (self.parent.type == 'database') var database = self.parent;
        else                                var database = self.parent.parent;
        
        // we have the database object, we can now change the value
        (answer.responseText !== 'ok') ? database.invalidSettingsWarning(self.name) : database.updateSaveWarning(self.name);
        
        // call the callback, if it was supplied
        if (callback) callback(answer.responseText == 'ok');
    });
}

/**
 *  Perform the check.
 *  This method sends an ajax request to the server to check if the field exists
 *  @param  function    Optional callback function that will be called when the ajax call completes
 */
CopernicaField.prototype.check = function(callback)
{
    // do the ajax call to check the field
    if (this.htmlElement('select') && this.htmlElement('select').selectedIndex == 0) 
    {
        // field is not linked, we need to call the callback right away
        if (callback) setTimeout(function() { callback(false); }, 0);
    }
    else 
    {
        // do the ajax call to check
        this.ajaxCall('check_field', callback);
    }
}

/**
 *  Create the field
 *  This method sends an ajax request to create the field in Copernica
 *  @param  function    Optional callback function that will be called when ajax call completes
 */
CopernicaField.prototype.create = function(callback)
{
    // do the ajax call to create the field
    this.ajaxCall('create_field', callback);
}

/**
 *  Create a special field
 *  This methods sends an ajax request to create a special field (telephone, fax) in Copernica  
 *  @param  function   Optional callback function that will be called when ajax call completes
 */
CopernicaField.prototype.repair = function(callback)
{
    // do the ajax call to create the special field
    this.ajaxCall('repair_field', callback);
}