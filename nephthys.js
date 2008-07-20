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

function init_nephthys(mode)
{
   /* initialize menu-tabs */
   init_ajaxtabs();

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

function ajax_notify_bucket(id)
{
   var objTemp = new Object();

   objTemp['action'] = 'notifybucket';
   objTemp['id'] = id;

   var retr = HTML_AJAX.post('rpc.php', objTemp);
   if(retr != "ok") {
      window.alert("Server message: "+ retr);
      return;
   }

   ajax_show_content('main');

} // ajax_notify_bucket()

function js_create_bucket(obj, target)
{
   if(obj.bucket_name.value == "") {
      window.alert("Please enter a name for this bucket!");
      return false;
   }
   if(obj.bucket_sender != undefined &&
      ajax_validate_email(obj.bucket_sender.value) != "ok") {
      window.alert("Please enter a valid sender email address!");
      return false;
   }
   if(((obj.bucketmode != undefined && obj.bucketmode.value == "receive") ||
      (obj.bucket_receiver.value != undefined && obj.bucket_receiver.value != "")) &&
      ajax_validate_email(obj.bucket_receiver.value) != "ok") {
      window.alert("Please enter a valid receiver email address!");
      return false;
   }

   var retobj = ajax_save_form(obj, target);
   var retval = retobj.split(';');

   if(retval[0] == "ok") {
      if(retval[1] != undefined)
         ajax_show_content('savedbucket', '&idx=' + retval[1]);
      else
         ajax_show_content('main');
   }
   else {
      var errortext = document.getElementById('generalerror');
      errortext.style.visibility = 'visible';
      errortext.innerHTML = retval;
   }

   return false;

} // js_create_bucket()

/**
 * submit form via AJAX
 *
 * this common function is used when submit a form.
 * if RPC call was successful ('ok'), menutabs are used
 * to switch to the correct interface tab.
 *
 * @param mixed object
 * @param string target
 * @return boolean
 */
function js_submit_form(obj, target)
{
   var retval = ajax_save_form(obj)

   if(retval == "ok") {
      menutabs.expandit(target);
      //aun, 2008-07-10, use menutabs.epandit() instead
      //ajax_show_content(target);
   }
   else {
      window.alert(retval);
      /*var errortext = document.getElementById('generalerror');
      errortext.style.visibility = 'visible';
      errortext.innerHTML = retval;
      */
      return false;
   }

   return true;

} // js_submit_form()

function js_delete_obj(module, target, idx)
{
   // Create object with values of the form
   var objTemp = new Object();
   objTemp['module'] = module;
   objTemp['mode'] = 'delete';
   objTemp['idx'] = idx;
   var retr = HTML_AJAX.post('rpc.php?action=store', objTemp);
   if(retr == "ok") {
      ajax_show_content(target);
   }
   else {
      window.alert("Server returned: " + retr);
   }
} // js_delete_obj()

function js_toggle_status(module, target, idx, to)
{
   // Create object with values of the form
   var objTemp = new Object();
   objTemp['module'] = module;
   objTemp['mode'] = 'toggle';
   objTemp['idx'] = idx;
   objTemp['to'] = to;
   var retr = HTML_AJAX.post('rpc.php?action=store', objTemp);
   if(retr == "ok") {
      ajax_show_content(target);
   }
   else {
      window.alert(retr);
   }
} // js_toggle_status()

function ajax_save_form(obj)
{
   return formSubmit(obj, null, {isAsync: false});

} // ajax_saveForm()

function ajax_show_content(req_content, options)
{
   if(req_content == undefined)
      req_content = "";

   var content = document.getElementById("content");
   content.innerHTML = "Loading...";
   var url = 'rpc.php?action=get_content&id=' + req_content;
   if(options != undefined) {
      url = url+options;
   }
   content.innerHTML = HTML_AJAX.grab(encodeURI(url));

} // ajax_show_content()

function refreshMenu()
{
   var menu = document.getElementById("menu");
   menu.innerHTML = "Loading...";
   menu.innerHTML = HTML_AJAX.grab(encodeURI('rpc.php?action=get_menu'));

} // refreshMenu()

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

   if(email.value == "")
      return;

   if(retr == "ok") {
      errortext.style.visibility = 'hidden';
      errortext.innerHTML = '';
   }
   else {
      errortext.style.visibility = 'visible';
      errortext.innerHTML = '<img src="images/warning.png" />&nbsp;Enter a valid email address!';
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

/**
 * set focus to specified object
 *
 * this function will search for the first matching
 * object and if possible, set the focus to it.
 */
function setFocus(obj) {
   if(el = document.getElementsByName(obj)) {
      if(el.item(0)) {
         if(el.item(0).focus) {
            el.item(0).focus();
         }
      }
   }
} // setFocus()

function setBackGrdColor(item, color)
{
   if(color == 'mouseover')
      item.style.backgroundColor='#c6e9ff';
   if(color == 'mouseout')
      item.style.backgroundColor='transparent';
   if(color == 'mouseclick')
      item.style.backgroundColor='#93A8CA';
}

function js_login()
{
   if(document.forms['login'].login_name.value == "") {
      window.alert("Please enter a username");
      return;
   }
   if(document.forms['login'].login_pass.value == "") {
      window.alert("Please enter a password");
      return;
   }

   // Create object with values of the form
   var objTemp = new Object();
   objTemp['login_name'] = document.forms['login'].login_name.value;
   objTemp['login_pass'] = document.forms['login'].login_pass.value;

   var retr = HTML_AJAX.post('rpc.php?action=login', objTemp);

   if(retr == "ok") {
      refreshMenu();
      ajax_show_content('main');
      init_ajaxtabs();
   }
   else {
      window.alert(retr);
   }

} // js_login()

function js_logout()
{
   var retr = HTML_AJAX.grab(encodeURI('rpc.php?action=logout'));

   if(retr == "ok") {
      refreshMenu();
      menutabs.expandit('main');
      init_ajaxtabs();
   }
   else {
      window.alert(retr);
   }

} // js_logout()

function init_ajaxtabs()
{
   if(menutabs != undefined) {
      menutabs = undefined;
   }

   menutabs = new ddajaxtabs("menutabs", "content");
   menutabs.setpersist(false);
   menutabs.setselectedClassTarget("link");
   menutabs.init()

} // init_ajaxtabs()

function load_autosuggest(obj)
{
   if(as == undefined) {
      var options = {
         script: "rpc.php?action=getxmllist&",
         varname: "search",
         json: false,
         shownoresults: false,
         maxresults: 15
      };

      as = new bsn.AutoSuggest(obj, options);
   }
} // load_autosuggest()

var as = undefined;
var menutabs = undefined;

