jQuery(document).ready(function() {
    //Delete grid system if the screen is small
    const size780 = window.matchMedia('(max-width: 780px)');
    size780.addListener(layout780);
	layout780(size780);

    const size780and1080 = window.matchMedia('(min-width: 780px) and (max-width: 1024px)');
	size780and1080.addListener(layout780and1080px);
	layout780and1080px(size780and1080);

    const size1024 = window.matchMedia('(min-width: 1024px)');
    size1024.addListener(layout1024px);
	layout1024px(size1024);
});

function layout780(e) {
	if(e.matches) {
		jQuery('.idcf-grid-projects-block').css('gridTemplateColumns', '');
	}
}

function layout780and1080px(e) {
	if(e.matches && gridBlockAttributes.columnsInGrid > 1) {
        jQuery('.idcf-grid-projects-block').css('gridTemplateColumns', 'repeat(2, minmax(0px, 1fr))');
	}
}

function layout1024px(e) {
	if(e.matches) {
		jQuery('.idcf-grid-projects-block').css('gridTemplateColumns', 'repeat(' + gridBlockAttributes.columnsInGrid + ', minmax(0px, 1fr))');
	}
}