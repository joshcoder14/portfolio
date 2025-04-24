jQuery(function ($) {
	var filterList = {
		init: function () {
			$('.stories-grid').mixItUp({
				selectors: {
  			  target: '.stories',
  			  filter: '.filter'	
            },
  		    load: {
    		  filter: 'all'
    		}     
			});								
		}
	};

	filterList.init();
});	