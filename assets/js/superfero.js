jQuery( document ).ready( function ( e ) {
	if ( jQuery.find( '#superfero-groups' ) ) {
		superfero_groups();
	}
});

function superfero_groups() {
	var lang = 'EN' , load_text = 'Loading...', other_text = 'In other languages:';

	if (superfero_language)
		lang = superfero_language; 

	if ( lang == 'DA' ) {
		load_text = 'Henter...';
		other_text = 'På andre sprog:' ;
	} 
	
	jQuery.ajax( {
		dataType : "json",
		url : myAjax.ajaxurl,
		data : { action: "superfero_campaign" },
		beforeSend: function(){
			jQuery( '<div/>' , {
			    'class':'superfero-loading',
			    'html': load_text
			} ).appendTo( '#superfero-groups' );
		},
		complete: function(){
			jQuery( '.superfero-loading' ).remove();
		},
		success: function( response ) {
			var template = [] , other = [];
			template = response.template ;
			//other = response.other ;
			
			if ( template.length ) {
				jQuery.each( template, function( key, val ) {
					jQuery( '<div/>' , {
						'id' : 'superfero-template-' + key ,
					    'class' :'superfero-group' ,
					} ).appendTo( '#superfero-groups' );
				    superfero_group( val , '#superfero-template-' + key , lang );
				});
			}
			/*
			if ( other.length ) {
				jQuery( '<div/>' , {
				    'class' : 'header',
				    'html' : other_text
				} ).appendTo( '#superfero-groups' );

				jQuery.each( other, function( key, val ) {
					jQuery( '<div/>' , {
						'id' : 'superfero-other-' + key ,
					    'class' : 'superfero-group' ,
					} ).appendTo( '#superfero-groups' );
				    superfero_group( val , '#superfero-other-' + key , lang );
				});
			}
			*/
		} 
	});
}

function superfero_group( data , parent , lang) {
	var html = '' ,
		url = '' ,
		by_text = 'by' ;
	if (superfero_host)
		url = superfero_host + 'course/' + data.slug ;
	else 
		url = 'http://www.superfero.com/course/' + data.slug ;

	if ( lang  == "DA" ) by_text = 'af' ;

	html = buildHTML( "img" , {
	  src : data.thumb_url
	} );

	html = buildHTML( "a" , html , {
		href: url,
		class : 'thumb' ,
		target : '_blank' 
	}, parent );

	html = buildHTML( "a" , data.name , {
		href: url,
		target: '_blank' 
	});

	html = buildHTML( "h2" , html , {
		class : 'name'  
	}, parent );

	html = buildHTML( "span" , by_text + ' ' + data.group_lead , {
		class : 'author'  
	}, parent );

	html = buildHTML( "span" , data.excerpt , {
		class : 'excerpt'  
	}, parent );

	html = buildHTML( "div" , data.currency, {
		class: 'label'  
	});

	html = data.price + html;

	html = buildHTML( "div" , html , {
		class: 'price'  
	}, parent );
}
// extract out the parameters
function gup(n,s){
	n = n.replace(/[\[]/,"\\[").replace(/[\]]/,"\\]");
	var p = (new RegExp("[\\?&]"+n+"=([^&#]*)")).exec(s);
	return (p===null) ? "" : p[1];
}

buildHTML = function( tag , html , attrs , parent ) {
  // you can skip html param
  if ( typeof( html ) != 'string' ) {
    attrs = html;
    html = null;
  }
  var h = '<' + tag;
  for ( attr in attrs ) {
    if( attrs[attr] === false) continue;
    h += ' ' + attr + '="' + attrs[attr] + '"';
  }
  h += html ? ">" + html + "</" + tag + ">" : "/>";

  if ( parent ) {
  	jQuery( parent ).append( h );
  	return '';
  } 
  return h;
}