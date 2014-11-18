/**
* Helpers
*/
if (!String.format){
	String.format = function(format) {
		var args = Array.prototype.slice.call(arguments, 1);
		return format.replace(/{(\d+)}/g, function(match, number) { 
			return typeof args[number] != 'undefined'? args[number] : match;
		});
	};
}

/**
* Controller for the twitter API
*/
var twitterAPI = {
	count	: 5,
	max_id	: 0,
	
	is_loading : false,

	init : function(){
		twitterAPI.load();
	},

	load : function(){
		twitterAPI.lock();

		var is_first_page = ( twitterAPI.max_id==0 );
		var count_fixed = ( is_first_page? twitterAPI.count : twitterAPI.count+1 );//twitter duplicates the first tweet since second page 
		$.getJSON("app/controller/TwitterAPI.php", { max_id: twitterAPI.max_id, count: count_fixed }, function( data ){
			var html = '<li><img src="{0}" alt="{1}"><span>{2}</span></li>';
			var text = new Array();
			var start = ( is_first_page? 0 : 1 );//reference 1
			for(i=start; i<data.length; i++){
				tweet = data[i];
				text.push( String.format( html, tweet.profile_image_url, tweet.screen_name, tweet.text ) );
			}
			if(text.length>0){
				//needed for scroll a bit to show the new tweet
				var new_elements_size = text.length;
				
				//append the new content
				$('.tweets-list ul').append( text.join("") );
				twitterAPI.max_id = data[ data.length - 1 ].id;
				
				//now I scroll
				if( is_first_page==false ){
					var scroll_bottom = $('.tweets-list ul').outerHeight(true) - $('.tweets-list').outerHeight(true);
					$('.tweets-list').animate({ 'scrollTop' : ( scroll_bottom ) + 'px' }, 400);
				}
			}

			twitterAPI.unlock();
		});
	},

	lock: function(){
		twitterAPI.is_loading = true;
		$('.btn-default').addClass('loading');
	},

	unlock: function(){
		twitterAPI.is_loading = false;
		$('.btn-default').removeClass('loading');	
	}
};