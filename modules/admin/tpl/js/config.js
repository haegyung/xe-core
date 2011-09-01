function getFTPList(pwd)
{	
    var form = jQuery("#ftp_form").get(0);
    if(typeof(pwd) != 'undefined')
    {
        form.ftp_root_path.value = pwd;
    }
    else
    {
        if(!form.ftp_root_path.value && typeof(form.sftp) != 'undefined' && form.sftp.checked)
        {
            form.ftp_root_path.value = xe_root;
        }
		else
        {
          form.ftp_root_path.value = "/";
        }
    }
	
    var params= new Array();	
	//ftp_pasv not used	
	params['ftp_user'] = jQuery("#ftp_user").val();	
	params['ftp_password'] =jQuery("#ftp_password").val();
	params['ftp_host'] = jQuery("#ftp_host").val();
	params['ftp_port'] = jQuery("#ftp_port").val();
	params['ftp_root_path'] = jQuery("#ftp_root_path").val();
	
    exec_xml('admin', 'getAdminFTPList', params, completeGetFtpInfo, ['list', 'error', 'message'], params, form);	
}

function removeFTPInfo()
{
    var params = {};
    exec_xml('install', 'procInstallAdminRemoveFTPInfo', params, filterAlertMessage, ['error', 'message'], params);
}

function completeGetFtpInfo(ret_obj)
{
    if(ret_obj['error'] != 0)
    {
        alert(ret_obj['error']);
        alert(ret_obj['message']);
        return;
    }
    var e = jQuery("#ftpSuggestion").empty();
	
    var list = "";
    if(!jQuery.isArray(ret_obj['list']['item']))
    {
        ret_obj['list']['item'] = [ret_obj['list']['item']];
    }

    pwd = jQuery("#ftp_form").get(0).ftp_root_path.value;
    if(pwd != "/")
    {
        arr = pwd.split("/");
        arr.pop();
        arr.pop();
        arr.push("");
        target = arr.join("/");
        list = list + "<li><button type='button' onclick=\"getFTPList('"+target+"')\">../</button></li>";
    }
    
    for(var i=0;i<ret_obj['list']['item'].length;i++)
    {   
        var v = ret_obj['list']['item'][i];
        if(v == "../")
        {
            continue;
        } 
        else if( v == "./")
        {
            continue;
        }
        else
        {
            list = list + "<li><button type='button' onclick=\"getFTPList('"+pwd+v+"')\">"+v+"</button></li>";
        }
    }
    list = "<ul>"+list+"</ul>";
    e.append(jQuery(list));
}

function deleteIcon(iconname){	
	var params = new Array();
	params['iconname'] = iconname;
	exec_xml('admin', 'procAdminRemoveIcons', params, iconDeleteMessage, ['error', 'message'], params);
	
}
function iconDeleteMessage(ret_obj){
 alert(ret_obj['message']);
}
function doRecompileCacheFile() {	
	var params = new Array();
	exec_xml("admin","procAdminRecompileCacheFile", params, completeCacheMessage);	
}
function completeCacheMessage(ret_obj) {
    alert(ret_obj['message']);
}

