/**
    var dData2Check = new Array('username', 'email', 'phone');
    $.each(dData2Check, function(i, id){
        $('#'+id).trigger('blur');
    });
});
function accconfig()
{
	$('#accintro').fadeOut();
	$('#accdetail').slideDown();
}