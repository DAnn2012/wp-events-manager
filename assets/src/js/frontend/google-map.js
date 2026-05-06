( function ( window, document ) {
	'use strict';

	function dataValue( element, key ) {
		return element.getAttribute( 'data-' + key );
	}

	function dataJson( element, key ) {
		var value = dataValue( element, key );
		if ( ! value ) {
			return null;
		}

		try {
			return JSON.parse( value );
		} catch {
			return null;
		}
	}

	window.initialize = function () {
		document.querySelectorAll( '.event-google-map-canvas' ).forEach( function ( canvas ) {
			var geocoder = new window.google.maps.Geocoder();

			geocoder.geocode( { address: dataValue( canvas, 'address' ) }, function ( results, status ) {
				if ( status === window.google.maps.GeocoderStatus.ZERO_RESULTS ) {
					canvas.insertAdjacentHTML( 'beforeend', '<div><p><strong>There were no results for the place you entered. Please try another.</strong></p></div>' );
					return;
				}

				if ( status !== window.google.maps.GeocoderStatus.OK ) {
					return;
				}

				var userMapTypeId = 'user_map_style';
				var zoom = Number( dataValue( canvas, 'zoom' ) ) || 14;
				var map = new window.google.maps.Map( canvas, {
					zoom: zoom,
					scrollwheel: 'true' === dataValue( canvas, 'scroll-zoom' ),
					center: results[0].geometry.location,
					mapTypeControlOptions: {
						mapTypeIds: [ window.google.maps.MapTypeId.ROADMAP, userMapTypeId ]
					}
				} );

				var userMapStyles = dataJson( canvas, 'map-styles' );
				if ( userMapStyles ) {
					var userMapType = new window.google.maps.StyledMapType( userMapStyles, {
						name: dataValue( canvas, 'map-name' )
					} );

					map.mapTypes.set( userMapTypeId, userMapType );
					map.setMapTypeId( userMapTypeId );
				}

				if ( 'true' === dataValue( canvas, 'marker-at-center' ) ) {
					new window.google.maps.Marker( {
						position: results[0].geometry.location,
						map: map,
						icon: dataValue( canvas, 'marker-icon' ),
						title: ''
					} );
				}

				var markerPositions = dataJson( canvas, 'marker-positions' );
				if ( markerPositions && markerPositions.length ) {
					markerPositions.forEach( function ( marker ) {
						geocoder.geocode( { address: marker.place }, function ( markerResults, markerStatus ) {
							if ( markerStatus === window.google.maps.GeocoderStatus.OK ) {
								new window.google.maps.Marker( {
									position: markerResults[0].geometry.location,
									map: map,
									icon: dataValue( canvas, 'marker-icon' ),
									title: ''
								} );
							}
						} );
					} );
				}

				var directions = dataJson( canvas, 'directions' );
				if ( directions ) {
					if ( directions.waypoints && directions.waypoints.length ) {
						directions.waypoints.forEach( function ( waypoint ) {
							waypoint.stopover = Boolean( waypoint.stopover );
						} );
					}

					var directionsRenderer = new window.google.maps.DirectionsRenderer();
					var directionsService = new window.google.maps.DirectionsService();

					directionsRenderer.setMap( map );
					directionsService.route( {
						origin: directions.origin,
						destination: directions.destination,
						travelMode: directions.travelMode.toUpperCase(),
						avoidHighways: Boolean( directions.avoidHighways ),
						avoidTolls: Boolean( directions.avoidTolls ),
						waypoints: directions.waypoints,
						optimizeWaypoints: Boolean( directions.optimizeWaypoints )
					}, function ( result, directionStatus ) {
						if ( directionStatus === window.google.maps.DirectionsStatus.OK ) {
							directionsRenderer.setDirections( result );
						}
					} );
				}
			} );
		} );
	};

	document.addEventListener( 'DOMContentLoaded', function () {
		var canvas = document.querySelector( '.event-google-map-canvas' );
		var apiKey = canvas ? dataValue( canvas, 'api-key' ) : '';
		if ( ! apiKey ) {
			return;
		}

		var script = document.createElement( 'script' );
		script.type = 'text/javascript';
		script.src = 'https://maps.googleapis.com/maps/api/js?v=3.exp&callback=initialize&key=' + encodeURIComponent( apiKey );
		document.body.appendChild( script );
	} );
}( window, document ) );
