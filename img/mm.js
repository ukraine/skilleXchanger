function GotoPageNumber() {

	var GETvariables;

	// alert("searchslice = " + location.search.slice(1))
	// alert("pathname = " + location.pathname)
	// alert("INIT GETVArs = " + GETvariables)
	GETvariables = location.search.slice(1).replace(/.{0,1}page=[0-9]+[&]{0,1}/,"")
	GETvariables = GETvariables.replace(/.{0,1}orderby=[a-z]+[&]{0,1}/,"")
	//	alert("AFTER 'page' removed = " + GETvariables)

	location = location.pathname + '?page='+document.PageNav.page.options[document.PageNav.page.selectedIndex].value + "&" + GETvariables

}

function openURL(id) {
	window.open("./?action=emailto&id="+id,"emailto"+id,"status=0,toolbar=0,menubar=0,location=0,directories=0,resizable=0,scrollbars=0,width=400,height=490")
}

function GenerateSomethingCool() {

	document.getElementById("antis").innerHTML='<input type="text" name="nickname1233">';

}