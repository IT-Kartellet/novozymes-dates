(function(){
	$("div.meta_year > h3").click(function(){
		$("div.meta_year > div").stop().slideUp(200);
		$(this).siblings().stop().slideToggle(200);
	});
})();