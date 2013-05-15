function init_dealform()
{
	//绑定副标题20个字数的限制
	$("input[name='sub_name']").bind("keyup change",function(){
		if($(this).val().length>20)
		{
			$(this).val($(this).val().substr(0,20));
		}		
	});
	
	//绑定团购券时间行显示
	$("select[name='is_coupon']").bind("change",function(){
		load_coupon_time();
	});
	
	//绑定团购商品类型，显示属性
	$("select[name='deal_goods_type']").bind("change",function(){
		load_attr_html();
	});
	
	//绑定配送行的显示
	$("select[name='is_delivery']").bind("change",function(){
		load_weight();
	});
	
	 $("select[name='buy_type']").bind("change",function(){
	 	switch_buy_type();
	 });
	 
	 $(".buy_type_0").show();
	load_coupon_time();
	load_weight();
}


function load_coupon_time()
{
		if($("select[name='is_coupon']").val()==0)
		{
			$(".coupon_time").hide();
		}
		else
		{
			$(".coupon_time").show();
		}
}

function load_weight()
{
		if($("select[name='is_delivery']").val()==0)
		{
			$(".weight_row").hide();
		}
		else
		{
			$(".weight_row").show();
		}
}
