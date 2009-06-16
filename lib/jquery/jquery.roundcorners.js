/**(You must include this commment)
 * Round Corners jQuery Plugin version 0.3.5-beta
 * written by Avinoam Henig
 * special thanks to Dan Blaisdell for help and mentoring (http://manifestwebdesign.com/)
 * 
 * for more information go to:
 * roundCorners.avinoam.info
 * or email me at: contact@avinoam.info
 * 
 * Your allowed to use and/or improve round corners, as long as you keep it free, open source, and include this comment.
 */
/**(You do not need to include this comment
 * Round corners is a jquery plugin to round the corners of the background of any HTML element
 * just by calling $(el).roundCorners(radius)
 * 
 * Needed files:
 * jQuery Core Javascirpt File
 * Explorer Canvas Javascript File (by google)
 * 
 * In future updates:
 * Dynamic javascript style changes will be implemented
 * Rounding images and background images
 * Implement seperate border colors for different sides
 * Gradients
 * Shadows
 * Attribute System
 * Custom Background Shapes
 * ToolTips
 */
(function($){

$.fn._offset = $.fn.offset;
$.fn.offset = function(newXY){
	return newXY ? this.setXY(newXY) : this._offset();
};

$.fn.setXY = function(newXY){
	$(this).each(function(){
		var el = this;
		var hide = false;
		if($(el).css('display')=='none'){
			hide = true;
			$(el).show();
		};
        var style_pos = $(el).css('position');
        if (style_pos == 'static') {
        	$(el).css('position','relative');
            style_pos = 'relative';
        };
		var pageXY = $(el).offset();
		if(pageXY){
			var delta = {
				left : parseInt($(el).css('left')),
				top : parseInt($(el).css('top'))
			};
			if (isNaN(delta.left)) delta.left = (style_pos == 'relative') ? 0 : el.offsetLeft;
			if (isNaN(delta.top)) delta.top = (style_pos == 'relative') ? 0 : el.offsetTop;
			if (newXY.left || newXY.left===0) $(el).css('left',newXY.left - pageXY.left + delta.left + 'px');
			if (newXY.top || newXY.top===0) $(el).css('top',newXY.top - pageXY.top + delta.top + 'px');
		};
		if(hide) $(el).show();
	});
	return this;
};

$.fn._css = $.fn.css;
$.fn.css = function(one, two){
	var el = this;
	if(arguments.length==1 && typeof one=='string'){
		var type = false;
		if(one.search('hover:')!=-1){
			type = 'hover';
		} else if(one.search('active:')!=-1){
			type= 'active';
		} else{
			return this._css(one, two);
		};
		if(type){
			var style = one.replace(' ', '').replace('hover:', '').replace('active:', '');
			var styleSplits = style.split('-');
			var styleNew = styleSplits[0];
			for(var s=1; s<styleSplits.length; s++){
				styleNew += styleSplits[s].substring(0, 1).toUpperCase();
				styleNew += styleSplits[s].substring(1, styleSplits[s].length);
			};
			type = ':'+type;
			var rules = $(el).getCSSRules(type);
			var result = $(el)._css(styleNew);
			if(type==':active'){
				var hoverRules = $(el).getCSSRules(':hover');
				for(var hr=0; hr<hoverRules.length; hr++){
					if(hoverRules[hr].style[styleNew]) result = hoverRules[hr].style[styleNew];
				};
			};
			for(var r=0; r<rules.length; r++){
				if(rules[r].style[styleNew]) result = rules[r].style[styleNew];
			};
			return result;
		} else{
			return $(el)._css(one, two);
		};
	} else{
		return this._css(one, two);
	};
};

$.fn.getCSSRules = function(type){
	var el = this;
	var elRules = new Array();
	if(type==null) type = '';
	for(var ss = 0; ss<document.styleSheets.length; ss++){
		if($.browser.msie) var rules = document.styleSheets[ss].rules;
		else var rules = document.styleSheets[ss].cssRules;
		for(var r=0; r<rules.length; r++){
			if(rules[r].selectorText.search(type)==-1) continue;
			var sel = rules[r].selectorText.replace(':hover', '').replace(':active', '');
			if ($(sel)) {
				$(sel).each(function(){
					if ($(this)[0] == $(el)[0]) {
						elRules[elRules.length] = rules[r];
					};
				});
			};
		};
	};
	return elRules;
};

$.fn.canvas = function(){
	$(this).each(function(){
		var el = this;
		if (el.canvas) return;
		var hide = false;
		if($(el).css('display')=='none'){
			hide = true;
			$(el).show();
		};
		$(el).wrapInner('<span style="position: relative; z-index: 1; background: none; border: none; margin: 0px; padding: 0px;" class="inner" />');
		var canvas = $('<canvas></canvas>')[$.browser.msie ? 'appendTo' : 'prependTo'](el).attr({
			'width': $(el).outerWidth(),
			'height': $(el).outerHeight()
		}).css({ 
			'position' : 'absolute', 
			'background' : 'none',
			'border' : 'none', 
			'padding' : '0px'
		}).offset($(el).offset());
		el.canvas = canvas[0];
		if ($.browser.msie) el.canvas = G_vmlCanvasManager.initElement(el.canvas);
		el.canvas.ctx = el.canvas.getContext('2d');
		el.canvas.roundCornersEvent = false;
		el.canvas.ctx.roundRect = function(width, height, x, y , tl, tr, bl, br){
			this.beginPath();
			if(tl>0) this.moveTo(tl + x, y);
			else this.moveTo(x, y);
			if(tl>0) this.quadraticCurveTo(x, y, x, tl + y);
			if(bl>0) this.lineTo(x, (height + y) - bl);
			else this.lineTo(x, height + y);
			if(bl>0) this.quadraticCurveTo(x, height + y, bl + x, height + y);
			if(br>0) this.lineTo((width + x) - br, height + y);
			else this.lineTo(width + x, height + y);
			if(br>0) this.quadraticCurveTo(width + x, height + y, width + x, (height + y) - br);
			if(tr>0) this.lineTo(width + x, tr + y);
			else this.lineTo(width + x, y);
			if(tr>0) this.quadraticCurveTo(width + x, y, width + x - tr, y);
			if(tl>0) this.lineTo(tl, y);
			else this.lineTo(x, y);
		};
		el.styles = {
			bgColor : $(el).css('background-color'),
			border : {
				top : {
					width : parseInt($(el).css('border-top-width')),
					color : $(el).css('border-top-color')
				},
				left : {
					width : parseInt($(el).css('border-left-width')),
					color : $(el).css('border-left-color')
				},
				bottom : {
					width : parseInt($(el).css('border-bottom-width')),
					color : $(el).css('border-bottom-color')
				},
				right : {
					width : parseInt($(el).css('border-right-width')),
					color : $(el).css('border-right-color')
				}
			},
			hover : {
				bgColor: $(el).css('hover:background-color'),
				border : {
					top : {
						width : parseInt($(el).css('hover:border-top-width')),
						color : $(el).css('hover:border-top-color')
					},
					left : {
						width : parseInt($(el).css('hover:border-left-width')),
						color : $(el).css('hover:border-left-color')
					},
					bottom : {
						width : parseInt($(el).css('hover:border-bottom-width')),
						color : $(el).css('hover:border-bottom-color')
					},
					right : {
						width : parseInt($(el).css('hover:border-right-width')),
						color : $(el).css('hover:border-right-color')
					}
				}
			},
			active : {
				bgColor: $(el).css('active:background-color'),
				border : {
					top : {
						width : parseInt($(el).css('active:border-top-width')),
						color : $(el).css('active:border-top-color')
					},
					left : {
						width : parseInt($(el).css('active:border-left-width')),
						color : $(el).css('active:border-left-color')
					},
					bottom : {
						width : parseInt($(el).css('active:border-bottom-width')),
						color : $(el).css('active:border-bottom-color')
					},
					right : {
						width : parseInt($(el).css('active:border-right-width')),
						color : $(el).css('active:border-right-color')
					}
				}
			}
		};
		if(hide) $(el).hide();
	});
	return this;
};

$.fn.getCanvas = function(){
	var el = this;
	$(el).canvas();
	var cvs = $(el)[0].canvas;
	if ($.browser.msie) {
		$(cvs).children().css({
			'position': 'relative',
			'background': 'none',
			'border': 'none',
			'padding': '0px',
			'margin': '0px',
			'left': '0px',
			'top': '0px'
		});
	};
	return cvs;
};

$.fn.roundCorners = function(tl, tr, br, bl, specialStyle){
	if(!arguments[0]){
		arguments[0] = 14;
	};
	for(var i=0; i<4; i++){
		if(arguments[i]==null){
			arguments[i] = arguments[0];
		};
		if(typeof arguments[i] == 'string'){
			if (arguments[i].search('em')!=-1) {
				arguments[i] = parseFloat(arguments[i]);
				arguments[i] = arguments[i] * 14;
			};
			arguments[i] = parseInt(arguments[i]);			
		};
	};
	tl = arguments[0];
	tr = arguments[1];
	br = arguments[2];
	bl = arguments[3];
	$(this).each(function(){
		var el = this;
		var hide = false;
		if($(el).css('display')=='none'){
			hide = true;
			$(el).show();
		};
		var canvas = $(el).getCanvas(tl, tr, br, bl, width, height);
		var border, bgColor;
		switch(specialStyle){
			case 'hover':
				border = el.styles.hover.border;
				bgColor = el.styles.hover.bgColor;
			break;
			case 'active':
				border = el.styles.active.border;
				bgColor = el.styles.active.bgColor;
			break;
			default:
				border = el.styles.border;
				bgColor = el.styles.bgColor;
		};
		var width = $(el).outerWidth();
		var height = $(el).outerHeight();
		$(canvas).attr({
			'width' : width,
			'height' : height
		}).offset($(el).offset());
		if (!canvas.roundCornersEvent) {
			canvas.roundCornersEvent = true;
			if (($.browser.msie && parseInt($.browser.version) != 6) || !$.browser.msie) {
				$(window).resize(function(){
					$(el).roundCorners(tl, tr, br, bl);
				});
				$(el).hover(function(){
					$(el).roundCorners(tl, tr, br, bl, 'hover');
				}, function(e){
					$(el).roundCorners(tl, tr, br, bl);
				});
				$(el).mousedown(function(){
					$(el).roundCorners(tl, tr, br, bl, 'active');
				});
				$(el).mouseup(function(){
					$(el).roundCorners(tl, tr, br, bl, 'hover');
				});
			};
		}
		var ctx = canvas.ctx;
			if(canvas.oldWidth && canvas.oldHeight) ctx.clearRect(0, 0, canvas.oldWidth, canvas.oldHeight);
			canvas.oldWidth = width;
			canvas.oldHeight = height;
			if (border.bottom.width > 0 || border.left.width > 0 || border.right.width > 0 || border.top.width > 0) {
				ctx.roundRect(width, height, 0, 0, tl, tr, bl, br);
				ctx.fillStyle = border.top.color;
				ctx.fill();
				if(border.top.width>tl){
					ctx.roundRect(width - (border.right.width+border.left.width), 
					height - (border.bottom.width+border.top.width), border.left.width, border.top.width, 
					0, 0, 0, 0);
				} else{
					ctx.roundRect(width - (border.right.width+border.left.width), 
						height - (border.bottom.width+border.top.width), border.left.width, border.top.width, 
						tl - border.left.width, tr - border.right.width, bl - border.left.width, br - border.right.width);
				};
				if(bgColor=='rgba(0, 0, 0, 0)' || bgColor=='transparent'){
					var parBgColor = '#ffffff';
					$(el).parents().each(function(){
						if($(this).css('background-color')!='rgba(0, 0, 0, 0)' && $(this).css('background-color')!='transparent'){
							parBgColor = $(this).css('background-color');
							return false;
						};
					});
					ctx.fillStyle = parBgColor;
				} else{
					ctx.fillStyle = bgColor;
				};
			} else{
				ctx.roundRect(width, 
					height, 0, 0, tl, tr, bl, br);
				ctx.fillStyle = bgColor;
			};
			ctx.fill();
		$(el).css({
			'background' : 'none',
			'border-color' : 'transparent'
		});
		if($.browser.msie &&  parseInt($.browser.version)==6){
			$(el).css({
				'padding-top' : (parseInt($(el).css('padding-top'))+border.top.width)+'px',
				'padding-left' : (parseInt($(el).css('padding-left'))+border.left.width)+'px',
				'border' : 'none'
			});
		};
		if(hide) $(el).hide();
	});
	return this;
};
})(jQuery);