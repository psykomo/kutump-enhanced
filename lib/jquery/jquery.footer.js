(function($){  

$.fn.positionFooter = function(center){  
  
var element = this; 

var originalPosition = $(element).offset();
var originalTopPosition = originalPosition.top;  

positionTheFooter();  
  
$(window).bind("resize", function(){  
    positionTheFooter();  
});
 
function positionTheFooter(){ 
  
  var elementPosition = $(element).offset();
  var elementPaddingTop = $(element).css("padding-top");
  var elementPaddingBottom = $(element).css("padding-bottom");
  var elementPaddingleft = $(element).css("padding-left");
  var elementPaddingRight = $(element).css("padding-right");
  var elementHeight = $(element).height();
  var elementWidth = $(element).width();
  
  var windowHeight = $(window).height();
  var windowWidth = $(window).width();
  
  elementPaddingTop = elementPaddingTop.replace(/px/,"");
  elementPaddingBottom = elementPaddingBottom.replace(/px/,"");
  elementPaddingleft = elementPaddingleft.replace(/px/,"");
  elementPaddingRight = elementPaddingRight.replace(/px/,"");
  
  var newPosition = (parseInt(windowHeight) - (parseInt(elementHeight) + (parseInt(elementPaddingTop) + parseInt(elementPaddingBottom))));
  var currentPosition = elementPosition.top - (parseInt(elementHeight) + (parseInt(elementPaddingTop) + parseInt(elementPaddingBottom)));
    
  if(originalTopPosition < windowHeight){
    
    $(element).css({  
      "position" : "absolute",
      "top" : newPosition
    });
    
    if(center == true){
      $(element).css({  
        "left" : windowWidth / 2 - (((elementWidth + parseInt(elementPaddingleft) + parseInt(elementPaddingRight)) / 2))
      });
    }
  
  }
  
  if(newPosition <= originalTopPosition){

    $(element).css({  
      "position" : "absolute",
      "top" : originalTopPosition
    });
    
    if(center == true){
      $(element).css({  
        "left" : windowWidth / 2 - (((elementWidth + parseInt(elementPaddingleft) + parseInt(elementPaddingRight)) / 2))
      });
    }
  
  }
  
};
  
};  
  
})(jQuery);