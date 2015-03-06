jQuery( document ).ready( function ( e ) {
	if ( jQuery.find( '#superfero-groups' ) ) {
		superfero_groups();
	}
});

function superfero_groups() {
	var lang = 'EN' , load_text = 'Loading...';

	if (superfero_language)
		lang = superfero_language; 

	if ( lang == 'DA' ) {
		load_text = 'Henter...';
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
			var template = []  ;
			if ( response ) {
				if ( response.courses ) {
					template = response.courses ;
					if ( template.length ) {
						jQuery.each( template, function( key, val ) {
							jQuery( '<div/>' , {
								'id' : 'superfero-template-' + key ,
							    'class' :'superfero-group' ,
							} ).appendTo( '#superfero-groups' );
						    superfero_group( val , '#superfero-template-' + key , lang );
						});
					}	
				}
			} 
		} 
	});
}

function superfero_group( data , parent , lang) {
	var html = '' ,
		path = '' ,
		url = '' ,
		by_text = 'by' ;

	if ( data.availability ) {
		if ( data.availability == 'programme' ) {
			path = 'programme' ;
		} else {
			path = 'course' ;
		}
	} else {
		path = 'course' ;
	}

	if ( superfero_host ) {
		url = superfero_host + path + '/' + data.slug ;
	} else {
		url = 'http://www.superfero.com/' + path + '/' + data.slug ;
	}

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

	if ( data.group_lead ) {
		html = buildHTML( "span" , by_text + ' ' + data.group_lead , {
			class : 'author'  
		}, parent );
	}

	if ( data.excerpt ) {
		html = buildHTML( "span" , data.excerpt , {
			class : 'excerpt'  
		}, parent );
	}

	if ( data.price ) {
		html = buildHTML( "div" , data.currency, {
			class: 'label'  
		});
		html = data.price + html;

		html = buildHTML( "div" , html , {
			class: 'price'  
		}, parent );
	} 
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