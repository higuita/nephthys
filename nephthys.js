function showCredits()
{
   var credits = document.getElementById("content");
   credits.innerHTML = HTML_AJAX.grab(encodeURI('rpc.php?action=showcredits'));
}

function click(object)
{
   if(object.blur)
      object.blur();

} // click()

function AskServerWhatToDo()
{
   return HTML_AJAX.grab(encodeURI('rpc.php?action=what_to_do'));
} // AskServerWhatToDo()

function init_nephthys(mode)
{
   /* ask the server what we are currently displaying */
   whattodo = AskServerWhatToDo();

} // init_nephthys()

function WSR_getElementsByClassName(oElm, strTagName, oClassNames){
   var arrElements = (strTagName == "*" && oElm.all)? oElm.all : oElm.getElementsByTagName(strTagName);
   var arrReturnElements = new Array();
   var arrRegExpClassNames = new Array();
   if(typeof oClassNames == "object"){
      for(var i=0; i<oClassNames.length; i++){
         arrRegExpClassNames.push(new RegExp("(^|\s)" + oClassNames[i].replace(/-/g, "\-") + "(\s|$)"));
      }
   }
   else{
      arrRegExpClassNames.push(new RegExp("(^|\s)" + oClassNames.replace(/-/g, "\-") + "(\s|$)"));
   }
   var oElement;
   var bMatchesAll;
   for(var j=0; j<arrElements.length; j++){
      oElement = arrElements[j];
      bMatchesAll = true;
      for(var k=0; k<arrRegExpClassNames.length; k++){
         if(!arrRegExpClassNames[k].test(oElement.className)){
            bMatchesAll = false;
            break;
         }
      }
      if(bMatchesAll){
         arrReturnElements.push(oElement);
      }
   }
   return (arrReturnElements)
}

function ajax_notify_slot(id)
{
   var objTemp = new Object();

   objTemp['action'] = 'notifyslot';
   objTemp['id'] = id;

   var retr = HTML_AJAX.post('rpc.php', objTemp);
   if(retr != "ok") {
      window.alert("Server message: "+ retr);
   }
}

function ajax_delete_slot(id)
{
   var objTemp = new Object();

   objTemp['action'] = 'deleteslot';
   objTemp['id'] = id;

   var retr = HTML_AJAX.post('rpc.php', objTemp);
   if(retr == "ok") {
      ajax_show_content('main');
   }
   else {
      window.alert("Server message: "+ retr);
   }
} // ajax_delete_slot()

function js_create_slot(obj, target)
{
   if(obj.slot_name.value == "") {
      window.alert("Please enter a name for this slot!");
      return false;
   }
   if(ajax_validate_email(obj.slot_sender.value) != "ok") {
      window.alert("Please enter a valid sender email address!");
      return false;
   }
   if(obj.slotmode.value == "send" && ajax_validate_email(obj.slot_receiver.value) != "ok") {
      window.alert("Please enter a valid receiver email address!");
      return false;
   }

   var retval = ajax_save_form(obj, target);

   if(retval == "ok") {
      ajax_show_content('main');
   }
   else {
      var errortext = document.getElementById('generalerror');
      errortext.style.visibility = 'visible';
      errortext.innerHTML = retval;
   }

} // js_create_slot()

function ajax_save_form(obj, target)
{
   var content = document.getElementById("content");
   return formSubmit(obj, null, {isAsync: false});

} // ajax_saveForm()

function ajax_show_content(mode)
{
   var content = document.getElementById("content");
   var retval = HTML_AJAX.grab('rpc.php?action=' + mode);
   content.innerHTML = retval;

} // ajax_show_content()

function ajax_validate_email(address)
{
   var objTemp = new Object();

   objTemp['action'] = 'validateemail';
   objTemp['address'] = address;

   return HTML_AJAX.post('rpc.php', objTemp);

} // ajax_validate_email()

function js_validate_email(email, errorobj)
{
   var errortext = document.getElementById(errorobj);

   if(email.value != "")
      var retr = ajax_validate_email(email.value);

   if(email.value == "" || retr == "ok") {
      errortext.style.visibility = 'hidden';
      errortext.innerHTML = '';
   }
   else {
      errortext.style.visibility = 'visible';
      errortext.innerHTML = 'Enter a valid email address!';
      email.focus();
   }

} // js_validate_email()

var NetScape4 = (navigator.appName == "Netscape" && parseInt(navigator.appVersion) < 5);
var autoload = undefined;

/**
 * stolen from HTM_AJAX, since it seems to have a bug as 
 * it always returns true.
 * see http://pear.php.net/bugs/bug.php?id=12415
 */
function formSubmit(form, target, options)
{
   form = HTML_AJAX_Util.getElement(form);
   if (!form) {
      // let the submit be processed normally
      return false;
   }

   var out = HTML_AJAX.formEncode(form);
   target = HTML_AJAX_Util.getElement(target);
   if (!target) {
      target = form;
   }
   try
   {
      var action = form.attributes['action'].value;
   }
   catch(e){}
   if(action == undefined)
   {
      action = form.getAttribute('action');
   }
   var callback = false;
   if (HTML_AJAX_Util.getType(target) == 'function') {
      callback = target;
   }
   else {
      callback = function(result) {
         // result will be undefined if HA_Action is returned, so skip the replace
         if (typeof result != 'undefined') {
            HTML_AJAX_Util.setInnerHTML(target,result);
         }
      }
   }
   var serializer = HTML_AJAX.serializerForEncoding('Null');
   var request = new HTML_AJAX_Request(serializer);
   request.isAsync = true;
   request.callback = callback;

   switch (form.getAttribute('method').toLowerCase()) {
      case 'post':
         var headers = {};
         headers['Content-Type'] = 'application/x-www-form-urlencoded';
         request.customHeaders = headers;
         request.requestType = 'POST';
         request.requestUrl = action;
         request.args = out;
         break;
      default:
         if (action.indexOf('?') == -1) {
            out = '?' + out.substr(0, out.length - 1);
         }
         request.requestUrl = action+out;
         request.requestType = 'GET';
   }

   if(options) {
      for(var i in options) {
         request[i] = options[i];
      }
   }

   if(request.isAsync == true) {
      HTML_AJAX.makeRequest(request);
      return true;
   }
   else {
      return HTML_AJAX.makeRequest(request);
   }
} // formSubmit()
