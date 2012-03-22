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
   /* initialize helpballoon */
   init_helpballoon();

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

   var retobj = HTML_AJAX.post('rpc.php', objTemp);

   var retval = retobj.split(';');

   if(retval[0] != "ok") {
      raise_error('ajax_notify_bucket()', retval[0]);
      return;
   }

   show_box(retval[1]);
   WindowCloseKey.init();

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

   if(retval != "ok") {
      raise_error('js_submit_form()', retval);
      return false;
   }

   menutabs.expandit(target);
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

   if(retr != "ok") {
      raise_error('js_delete_obj()', retr);
      return;
   }

   ajax_show_content(target);

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

   if(retr != "ok") {
      raise_error('js_toggle_status()', retr);
      return;
   }

   ajax_show_content(target);

} // js_toggle_status()

function ajax_save_form(obj)
{
   return formSubmit(obj, null, {isAsync: false});

} // ajax_saveForm()

function ajax_show_content(req_content, options)
{
   // clear the current view
   clear_screen();

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
         if(el.item(0).select) {
            el.item(0).select();
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

   if(retr != "ok") {
      raise_error('js_login()', retr);
      return;
   }

   refreshMenu();
   ajax_show_content('main');
   init_ajaxtabs();

} // js_login()

function js_logout()
{
   var retr = HTML_AJAX.grab(encodeURI('rpc.php?action=logout'));

   if(retr != "ok") {
      raise_error('js_logout()', retr);
      return;
   }

   // cleanup
   clear_screen();
   // redraw the menu and switch to 'Start Page'
   refreshMenu();
   menutabs.expandit('main');
   // reinitialize ajaxtabs
   init_ajaxtabs();

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

/**
 * initialize helpballoon
 *
 * this function initialize the helpballoon, setting
 * the correct directories where to find the balloon
 * stuff.
 */
function init_helpballoon()
{
   // Override the default settings to point to the helpballoon directory
   HelpBalloon.Options.prototype = Object.extend(HelpBalloon.Options.prototype, {
      icon: 'helpballoon/images/icon.gif',
      button: 'helpballoon/images/button.png',
      balloonPrefix: 'helpballoon/images/balloon-',
      hideEffectOptions: {duration: 0.1},
      showEffectOptions: {duration: 0.1}
   });

   /* initialize it once, so the necessary images will get preloaded */
   balloon = new HelpBalloon({
      returnElement: true
   });

} // init_helpballoon()

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

function show_box(text, height)
{
   if(height == undefined)
      height = 75;

   if(dialog_open == true)
      return;

   dialog_open = true;

   Dialog.alert(text,
      {
         width:300,
         height: height,
         okLabel: "close",
         ok:function(win) {
            ajax_show_content('main');
            dialog_open = false;
            return true;
         },
         windowParameters: {
            className: "alphacube",
            resizeable: false,
            minimizeable: false,
            maximizeable: false,
            effectOptions: {duration:0.2},
            opactiy: 0.50,
            destroyOnClose: true
         }
      }
   );

} // show_box()

/**
 * replace image src
 *
 * this function is used to replace images
 * within a <img>-tag.
 * @param object obj
 * @param string src
 */
function swap_image(obj, src)
{
   if(obj == undefined || src == undefined)
      return;

   obj.src = src;

} // swap_image()

function update_sort_order(module, column, order, reload)
{
   // Create object with values of the form
   var objTemp = new Object();
   objTemp['module'] = module;
   objTemp['column'] = column;
   objTemp['order']  = order;

   var retr = HTML_AJAX.post('rpc.php?action=sortorder', objTemp);

   if(retr != "ok") {
      raise_error('update_sort_order()', retr);
      return;
   }

   if(reload == undefined)
      reload = 'main';

   ajax_show_content(reload);

} // update_sort_order()

function ajax_get_bucket_info(id)
{
   var objTemp = new Object();
   var text;

   objTemp['action'] = 'get_bucket_info';
   objTemp['id'] = id;

   var retobj = HTML_AJAX.post('rpc.php', objTemp);

   show_balloon(id, 'Bucket Details', retobj);

} // ajax_get_bucket_info()

/**
 * show some flying note
 *
 * @param int id
 * @param string text
 */
function show_balloon(id, title, text)
{
   /* if there is any balloon shown currently... */
   hide_balloon();

   /* initalize */
   balloon = new HelpBalloon({
      icon: $('bucketinfo'+id),
      title: title,
      content: text,
      autoHideTimeout: 2000
   });

   /* show */
   balloon.show()

} // show_balloon()

/**
 * if a balloon is currently shown, hide it
 * and destroy the object.
 */
function hide_balloon()
{
   if(balloon != undefined) {
      balloon.hide();
      balloon = null; /* destroy */
   }

} // hide_balloon()

/**
 * just a helper function to generally clear up the
 * screen from any left over stuff...
 */
function clear_screen()
{
   // when the user is going to quickly switch between
   // some views we should take care, that the balloon
   // does not get left...
   hide_balloon();

} // clear_screen()

function raise_error(calling_function, text)
{
   var error_str;

   error_str = calling_function + ": ";
   error_str+= text;

   window.alert(error_str);

} // raise_error()

function filemgr_open(bucket_id)
{
   /* if dialog is already open, return... */
   if(dialog_open == true)
      return;

   cur_path    = "/";
   cur_bucket  = bucket_id;
   dialog_open = true;

   // Create object with values of the form
   var objTemp = new Object();
   objTemp['action']  = 'filemgr';
   objTemp['command'] = 'show';
   objTemp['id']      = bucket_id;

   // open file-manager dialog window
   Dialog.alert(
      {
         url: 'rpc.php',
         options: {
            method: 'post',
            parameters: objTemp
         }
      },
      {
         className: "alphacube",
         width:600,
         height:400,
         okLabel: "Close",
         ok:function(win) {
            ajax_show_content('main');
            dialog_open = false;
            return true;
         },
         windowParameters: {
            className: "alphacube",
            resizeable: false,
            minimizeable: false,
            maximizeable: false,
            effectOptions: {duration:0.2},
            opactiy: 0.50,
            destroyOnClose: true,
            onFocus: function () {
               // load ajax_upload function
               initalize_ajax_upload();
            }
         }
      }
   );

   WindowCloseKey.init();

   return false;

} // filemgr_open()

function filemgr_chdir(path)
{
   if(path != undefined)
      cur_path = path;

   // Create object with values of the form
   var objTemp = new Object();
   objTemp['action']  = 'filemgr';
   objTemp['command'] = 'chdir';
   objTemp['path']    = cur_path;
   var retr = HTML_AJAX.post('rpc.php', objTemp);

   if(retr != "ok") {
      raise_error('filemgr_chdir()', retr);
      return;
   }

   filemgr_update();

} // filemgr_chdir()

function filemgr_update()
{
   var filemgr = document.getElementById("floatingwindow");
   filemgr.innerHTML = HTML_AJAX.grab(encodeURI('rpc.php?action=filemgr'));
   initalize_ajax_upload()

} // filemgr_update()

function filemgr_delete(item, path)
{
   // Create object with values of the form
   var objTemp = new Object();
   objTemp['action']  = 'filemgr';
   objTemp['command'] = 'delete';
   objTemp['item']    = item;
   objTemp['path']    = path;
   var retr = HTML_AJAX.post('rpc.php', objTemp);

   if(retr != "ok") {
      raise_error('filemgr_delete()', retr);
      return;
   }

   filemgr_update();

} // filemgr_delete()

function filemgr_mkdir(item, path)
{
   inputfield = document.getElementById("mkdir_name");
   dirname    = inputfield.value;

   if(dirname == "") {
      window.alert("Please enter a directory name first!");
      return;
   }

   // Create object with values of the form
   var objTemp = new Object();
   objTemp['action']  = 'filemgr';
   objTemp['command'] = 'mkdir';
   objTemp['path']    = dirname;
   var retr = HTML_AJAX.post('rpc.php', objTemp);

   if(retr != "ok") {
      raise_error('filemgr_mkdir()', retr);
      return;
   }

   filemgr_update();

} // filemgr_mkdir()

function initalize_ajax_upload()
{
   // wait for element to appear
   if(document.getElementById("bucket_upload") == undefined) {
      window.setTimeout("initalize_ajax_upload()", 100);
      return;
   }

   jQuery('#bucket_upload').uploadify({
      'uploader':  'jquery/jquery.uploadify-v2.1.4/uploadify.swf',
      'cancelImg': 'jquery/jquery.uploadify-v2.1.4/cancel.png',
      'script':    'rpc.php',
      'multi':     true,
      'auto':      false,
      'folder':    'test',
      'fileDataName': 'filemgr_upload',
      'Format:someInteger': 2,
      'scriptData':{
         action      : 'filemgr',
         command     : 'upload',
         upload_path : cur_path,
         sessionid   : getCookie('PHPSESSID')
      },
      /* errors generated by uploadify flash object */
      onError: function (upevent, queueID, fileObj, errorObj) {
         if (errorObj.info == 404)
            window.alert('Could not find upload script. Use a path relative to: '+'<?= getcwd() ?>');
         else
            window.alert('error ' + errorObj.type + ': ' + errorObj.info + ', ' + fileObj.name);
      },
      /* check return messages from server */
      onComplete: function (upevent, queueID, fileObj, response, data) {
         if(response != "" && response != "success") {
            window.alert(fileObj.name + ': ' + response);
         }
      },
      onAllComplete: function (filescnt, errorcnt, allBytesLoaded, speed) {
         // refresh filemgr window
         filemgr_update();
      }
   });

} // initialize_ajax_upload()

function do_upload()
{
   jQuery('#bucket_upload').uploadifyUpload();

} // do_upload()

function getCookie(searchfor)
{
    var ckList = document.cookie.split("; ");
    for (var i=0; i < ckList.length; i++)
    {
        var ck = ckList[i].split("=");
        if(searchfor == ck[0]) {
           return unescape(ck[1]);
        }
    }
    return 0;

} // getCookies()

var as = undefined;
var menutabs = undefined;
var balloon = undefined;
var dialog_open = undefined;
var uploadbtn = undefined;
var cur_path = undefined;
var cur_bucket = undefined;
