orders = 0;

function delete_temp(id) {
    if (window.XMLHttpRequest) xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function() 
    {
        if (this.readyState == 4 && this.status == 200) 
        {
            if(this.responseText == 1)
            {
                $('#row'+id).remove();
                orders--;
                bootbox.alert({ message: 'Usunięto pomyślnie!', backdrop: true, callback: function () {
                        show_alert(name_src);
                    }  
                });
            }
            else
                bootbox.alert({ message: 'Wystąpił błąd!', backdrop: true });
        }
    };
        xmlhttp.open("GET","include/actions.php?delete_temp="+id,true);
        xmlhttp.send();
}

function show_alert(name_src) {
    if(!orders) {
        bootbox.confirm({ message: "Brak niezakończonych zamówień!", 
            buttons: { 
                confirm: { label: 'Odśwież', className: 'btn-success' }, 
                cancel: { label: 'Wróć', className: 'btn-danger' } 
            }, 
            callback: function (result) { 
                if(result) location.reload(); 
                else window.location.href = name_src; 
            }
        });
    }
}
