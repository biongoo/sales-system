	function change(str) 
	{
		if (window.XMLHttpRequest) xmlhttp = new XMLHttpRequest();
		xmlhttp.onreadystatechange = function() 
		{
			if (this.readyState == 4 && this.status == 200) 
				document.getElementById('list2').innerHTML = this.responseText;
        };
        xmlhttp.open("GET","include/actions.php?symbols="+str,true);
        xmlhttp.send();
	}

	function transfer(str) 
	{
		if(document.getElementById('transfer'+str).checked == true)
			set_tr = 'check';
		else 
			set_tr = 'uncheck';

		if (window.XMLHttpRequest) xmlhttp = new XMLHttpRequest();
		xmlhttp.onreadystatechange = function() 
		{
			if (this.readyState == 4 && this.status == 200) 
				bootbox.alert(this.responseText);
		};
			xmlhttp.open("GET","include/actions.php?set_tr="+set_tr+"&id="+str,true);
			xmlhttp.send();
	}
	