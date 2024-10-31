(function( $ ) {
	'use strict';
	jQuery(document).ready(function() {
			if(jQuery('#qr')[0]){
				let options = {
					render: 'img',
					size: 300,
					text: jQuery('#qr')[0].dataset.qr,
					fill: '#000',
					fontcolor: '#000',
					mSize: 0.1,
				    mPosX: 0.5,
				    mPosY: 0.5
				};
				jQuery('#qr').qrcode(options);
			}

	/**/
});
	
})( jQuery );
