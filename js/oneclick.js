String.prototype.capitalize = function(){ 
    return this.replace(/\w+/g, function(a){
        return a.charAt(0).toUpperCase() + a.substr(1).toLowerCase();
    });
};
function getArgs() {
    var args = new Object();
    var query = location.search.substring(1);  // Get query string.
    var pairs = query.split(",");              // Break at comma.
    for(var i = 0; i < pairs.length; i++) {
	var pos = pairs[i].indexOf('=');       // Look for "name=value".
	if (pos == -1) continue;               // If not found, skip.
	var argname = pairs[i].substring(0,pos);  // Extract the name.
	var value = pairs[i].substring(pos+1); // Extract the value.
	args[argname] = unescape(value);          // Store as a property.
    }
    return args;                               // Return the object.
}
function changeDisplayById(id,val){
  if (document.getElementById){
    var nodeObj = document.getElementById(id);
    var display='none';
    if (val == 'on'){
      display = '';
      //display = 'table-row';
    }  
    nodeObj.style.display = display;
  }
}
function EntryCheck(){
 if (!document.forms.OneClick.Project.value){
  alert('Project must be specified');
  return false;
 }
 var opt = document.forms.OneClick.type.selectedIndex;
 var type = document.forms.OneClick.type.options[opt];
 switch (type.value){
  case 'IGEM':
    if (!document.forms.OneClick.Institution.value){
      alert('Institution must be specified');
      return false;
    }
    break;
  case 'USER':
    break;
  default:
  case 'LAB':
    if (!document.forms.OneClick.Lab.value){
      alert('Lab name must be specified');
      return false;
    }
    break;
 }
 return true;
}
function MakePageName(){
  var igem = 'IGEM';
  var user = 'User';
  var notebook = 'Notebook';
  var igemYear = '2009';
  var project = document.forms.OneClick.Project.value;
  var inst = document.forms.OneClick.Institution.value;
  var lab = document.forms.OneClick.Lab.value;
  if (project){
    project = project.capitalize();
  }
    document.forms.OneClick.Username.value = mw.config.get('wgUserName');
  var opt = document.forms.OneClick.type.selectedIndex;
  var type = document.forms.OneClick.type.options[opt];
  var url = '';
  switch (type.value){
    case 'IGEM':
      changeDisplayById('LabRow','off');
      changeDisplayById('ProjectRow','on');
      changeDisplayById('InstitutionRow','on');
      if (inst.length == 0 || project.length == 0)
        url= '';
      else
        url = igem+':'+inst+'/'+igemYear+'/'+notebook+'/'+project;
      break;
    case 'USER':
      changeDisplayById('LabRow','off');
      changeDisplayById('ProjectRow','on');
      changeDisplayById('InstitutionRow','off');
      if (project.length == 0)
        url= '';
      else
          url = user+':'+mw.config.get('wgUserName')+'/'+ notebook+'/'+project;
      break;
    case 'LAB':
      changeDisplayById('LabRow','on');
      changeDisplayById('ProjectRow','on');
      changeDisplayById('InstitutionRow','off');
      if (lab.length == 0 || project.length == 0)
        url= '';
      else
        url = lab +':'+notebook+'/'+project;
      break;
  }
  return url;
}
function ShowURL(){
    if (!mw.config.exists('wgUserName')){
      alert ('You must be logged in to create a new Notebook.');
      window.location = "/wiki/Special:Userlogin&returnto=Help:Notebook/One_Click_Setup";
    }
    var url = document.forms.OneClick.CurrentURL;
    url.value = MakePageName();
    document.getElementById('DisplayURL').innerHTML = url.value;
    return true;
}
function loadMessage(){
  var args = getArgs();
  if (args.Message){
    changeDisplayById('form_body','off');
    var nodeObj = document.getElementById('message_body');
    nodeObj.style.verticalAlign='middle';
    nodeObj.style.width='600px';
    nodeObj.style.height='120px';
    if (args.Error == null){
      nodeObj.style.backgroundColor='#e5edc8';
      nodeObj.innerHTML=
        "<img src='/images/f/f8/Owwnotebook_icon.png' style='float: left;' " +
          "  alt='Owwnotebook_icon.png' /><br />"+
        "<span style=\"font-size: 20px; font-weight: bold;\">Success!</span><br />"+args.Message;
    }else{
      nodeObj.style.backgroundColor='#e0bcc1';
      nodeObj.innerHTML=
        "<img src='/images/f/f8/Owwnotebook_icon.png' style='float: left;' " +
        "  alt='Owwnotebook_icon.png' /><br />"+
        "<span style=\"font-size: 20px; font-weight: bold;\">Error</span><br />"+
            args.Message+"<br />"+
            "Click <a href='/wiki/Special:NewNotebook'>here</a> to continue";
    }
    return true;
  }
  return false; 
}

jQuery(document).ready(function($) {
    if (!loadMessage()){
        ShowURL();
    }
});
