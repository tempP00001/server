/**
    rechargeOrderClean();
});

function rechargeCardDelete(id)
{
    if (!confirm('确认删除吗？')) return;
    recardOping(id);
    $.get('?mod=recharge&code=card&op=delete&id='+id+$.rnd.stamp(), function(data){
        if (data == 'ok')
        {
            recardOping(id, 'close');
        }
        else
        {
            $.notify.failed('删除失败！');
            recardOping(id, 'end');
        }
    });
}
function recardOping(id, op)
{
    if (op == undefined)
    {
        $('#rc_on_'+id).removeClass().addClass('tips');
        return;
    }
    if (op == 'end')
    {
        $('#rc_on_'+id).removeClass();
        return;
    }
    if (op == 'close')
    {
        $('#rc_on_'+id).fadeOut();
        return;
    }
}
function rechargeOrderClean()
{
    $.get('admin.php?mod=recharge&code=order&op=clean'+$.rnd.stamp(), function(data){
        if (data != 'no')
        {
            $('#recharge_order_clean').html(data).css('display', 'none').fadeIn();
            setTimeout(function(){$('#recharge_order_clean').css('position', 'absolute').animate({top:'-300px'})}, 3000);
        }
    });
}