jQuery('.accordion-toggle').click(function(){
    var has = jQuery(this);
    if(has.hasClass('collapsed')){
        jQuery(this).find('#sec_i').css("transform","rotate(90deg)");
        jQuery(this).find('#sec_i').css("transition","all linear 0.25s");
    }
    else
        jQuery(this).find('#sec_i').css("transform","rotate(0deg)");
});

function delete_order(id) {
    if (window.XMLHttpRequest) xmlhttp = new XMLHttpRequest();
    xmlhttp.onreadystatechange = function() 
    {
        if (this.readyState == 4 && this.status == 200) 
        {
            if(this.responseText == 1)
            {
                bootbox.alert({ message: 'Usunięto pomyślnie!', backdrop: true, buttons: { ok: { label: 'OK', className: 'btn-outline-success' } }, callback: function () {
                        location.reload(); 
                    }  
                });
            }
            else
                bootbox.alert({ message: 'Wystąpił błąd!', backdrop: true, buttons: { ok: { label: 'OK', className: 'btn-outline-success' } } });
        }
    };
        xmlhttp.open("GET","include/actions.php?delete_order="+id,true);
        xmlhttp.send();
}