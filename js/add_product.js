function change_unit(unit)
{
    unit_value = document.getElementById('unit_value');
    amount = document.getElementById('amount');
    switch(unit)
    {
        case 'op':
            unit_value.innerHTML = 'Waga opakowania:';
            $('#unit_div').show();
            break;
        case 'szt':
            unit_value.innerHTML = 'Ilość sztuk w opakowaniu:';
            $('#unit_div').show();
            break;
        case 'kg':
            $('#unit_div').hide();
            amount.value = 1;
            break;
    }
}