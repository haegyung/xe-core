function doCheckAll(bToggle) {
    var fo_obj = xGetElementById('fo_list');
	if(typeof(bToggle) == "undefined") bToggle = false;
    for(var i=0;i<fo_obj.length;i++) {
        if(fo_obj[i].name == 'cart'){
			if( !fo_obj[i].checked || !bToggle) fo_obj[i].checked = true; else fo_obj[i].checked = false;
		}
    }
}

function insertSelectedModule(id, module_srl, mid, browser_title) {
    location.href = current_url.setQuery('module_srl',module_srl);
}
