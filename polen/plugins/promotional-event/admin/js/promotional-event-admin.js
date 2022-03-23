(function( $ ) {
	'use strict';

const settings={
		fill: '#1abc9c',
		background: '#d7dcdf'
	}

const sliders = document.querySelectorAll('.range-slider');

Array.prototype.forEach.call(sliders,(slider)=>{
	slider.querySelector('input').addEventListener('input', (event)=>{
		slider.querySelector('span').innerHTML = event.target.value;
		applyFill(event.target);
	});
	applyFill(slider.querySelector('input'));
});

function applyFill(slider) {
	const percentage = 100*(slider.value-slider.min)/(slider.max-slider.min);
	const bg = `linear-gradient(90deg, ${settings.fill} ${percentage}%, ${settings.background} ${percentage+0.1}%)`;
	slider.style.background = bg;
}

})( jQuery );
