var nat_locaties = {
    map: false,
    window: new google.maps.InfoWindow(),
    init: function () {
        var self = this;

        // noinspection JSCheckFunctionSignatures
        jQuery(window).on('resize', function () {
            self.center_map();
        });

        self.create_map();
    },

    create_map: function () {
        var self = this;

        var map_el = jQuery('.locations-map');

        if (map_el.length === 0) {
            return;
        }

        self.markers = map_el.find('.marker');
        self.map = new google.maps.Map(map_el[0], {
            zoom: 16,
            center: new google.maps.LatLng(0, 0),
            mapTypeId: google.maps.MapTypeId.ROADMAP,
            disableDefaultUI: false,
            navigationControl: true,
            mapTypeControl: false,
            clickableIcons: false,
            styles: [
                {
                    "elementType": "geometry",
                    "stylers": [
                        {
                            "color": "#f5f5f5"
                        }
                    ]
                },
                {
                    "elementType": "labels.icon",
                    "stylers": [
                        {
                            "visibility": "off"
                        }
                    ]
                },
                {
                    "elementType": "labels.text.fill",
                    "stylers": [
                        {
                            "color": "#616161"
                        }
                    ]
                },
                {
                    "elementType": "labels.text.stroke",
                    "stylers": [
                        {
                            "color": "#f5f5f5"
                        }
                    ]
                },
                {
                    "featureType": "administrative",
                    "elementType": "geometry",
                    "stylers": [
                        {
                            "visibility": "off"
                        }
                    ]
                },
                {
                    "featureType": "administrative.land_parcel",
                    "elementType": "labels.text.fill",
                    "stylers": [
                        {
                            "color": "#bdbdbd"
                        }
                    ]
                },
                {
                    "featureType": "administrative.neighborhood",
                    "stylers": [
                        {
                            "visibility": "off"
                        }
                    ]
                },
                {
                    "featureType": "poi",
                    "stylers": [
                        {
                            "visibility": "off"
                        }
                    ]
                },
                {
                    "featureType": "poi",
                    "elementType": "geometry",
                    "stylers": [
                        {
                            "color": "#eeeeee"
                        }
                    ]
                },
                {
                    "featureType": "poi",
                    "elementType": "labels.text",
                    "stylers": [
                        {
                            "visibility": "off"
                        }
                    ]
                },
                {
                    "featureType": "poi",
                    "elementType": "labels.text.fill",
                    "stylers": [
                        {
                            "color": "#757575"
                        }
                    ]
                },
                {
                    "featureType": "poi.park",
                    "elementType": "geometry",
                    "stylers": [
                        {
                            "color": "#e5e5e5"
                        }
                    ]
                },
                {
                    "featureType": "poi.park",
                    "elementType": "labels.text.fill",
                    "stylers": [
                        {
                            "color": "#9e9e9e"
                        }
                    ]
                },
                {
                    "featureType": "road",
                    "stylers": [
                        {
                            "visibility": "off"
                        }
                    ]
                },
                {
                    "featureType": "road",
                    "elementType": "geometry",
                    "stylers": [
                        {
                            "color": "#ffffff"
                        }
                    ]
                },
                {
                    "featureType": "road",
                    "elementType": "labels",
                    "stylers": [
                        {
                            "visibility": "off"
                        }
                    ]
                },
                {
                    "featureType": "road",
                    "elementType": "labels.icon",
                    "stylers": [
                        {
                            "visibility": "off"
                        }
                    ]
                },
                {
                    "featureType": "road.arterial",
                    "elementType": "labels.text.fill",
                    "stylers": [
                        {
                            "color": "#757575"
                        }
                    ]
                },
                {
                    "featureType": "road.highway",
                    "elementType": "geometry",
                    "stylers": [
                        {
                            "color": "#dadada"
                        }
                    ]
                },
                {
                    "featureType": "road.highway",
                    "elementType": "labels.text.fill",
                    "stylers": [
                        {
                            "color": "#616161"
                        }
                    ]
                },
                {
                    "featureType": "road.local",
                    "elementType": "labels.text.fill",
                    "stylers": [
                        {
                            "color": "#9e9e9e"
                        }
                    ]
                },
                {
                    "featureType": "transit",
                    "stylers": [
                        {
                            "visibility": "off"
                        }
                    ]
                },
                {
                    "featureType": "transit.line",
                    "elementType": "geometry",
                    "stylers": [
                        {
                            "color": "#e5e5e5"
                        }
                    ]
                },
                {
                    "featureType": "transit.station",
                    "elementType": "geometry",
                    "stylers": [
                        {
                            "color": "#eeeeee"
                        }
                    ]
                },
                {
                    "featureType": "water",
                    "elementType": "geometry",
                    "stylers": [
                        {
                            "color": "#c9c9c9"
                        }
                    ]
                },
                {
                    "featureType": "water",
                    "elementType": "labels.text",
                    "stylers": [
                        {
                            "visibility": "off"
                        }
                    ]
                },
                {
                    "featureType": "water",
                    "elementType": "labels.text.fill",
                    "stylers": [
                        {
                            "color": "#9e9e9e"
                        }
                    ]
                }
            ]
        });

        self.map.markers = [];

        self.markers.each(function () {
            self.add_marker(jQuery(this));
        });

        var cluster_icon = 'cluster_nat.png';

        var style_object = [{
            url: '/wp-content/themes/nat64check/graphics/' + cluster_icon,
            height: 52,
            width: 50,
            textColor: '#FFFFFF',
            textSize: 10
        }];

        new MarkerClusterer(
            self.map,
            self.map.markers,
            {
                styles: style_object
            }
        );

        self.center_map();
    },

    add_marker: function (marker) {
        var self = this;
        var icon = '';
        if (marker.data('bg') === 'bg-primary') {
            icon = 'check color-white';
        } else if (marker.data('bg') === 'bg-secondary') {
            icon = 'times color-white';
        } else if (marker.data('bg') === 'world') {
            icon = 'globe color-white bg-dark';
        } else {
            icon = 'minus color-white';
        }
//		console.log( marker.data( 'server' ) );
        var rich_marker = new RichMarker({
            position: new google.maps.LatLng(marker.data('lat'), marker.data('lng')),
            shadow: false,
            content: '<div class="location-marker"><span class="nat-marker ' + marker.data('bg') + ' "><i class="fa fa-' + icon + '" aria-hidden="true"></i></span><div>',
            html: marker.html(),
            redirect: marker.data('redirect'),
            anchor: RichMarkerPosition['TOP']
        });
        google.maps.event.addDomListener(rich_marker, 'click', function () {
            if (!jQuery('body').hasClass('page-template-page-map')) {
                window.location.href = rich_marker.redirect;
            }

        });
        self.map.markers.push(rich_marker);

        if (rich_marker.html) {
            google.maps.event.addListener(rich_marker, 'click', function () {

                self.window.setContent(this.html);
                self.window.open(self.map, this);
            });
        }
    },
    center_map: function () {
        var self = this;

        if (self.map.markers.length) {
            var bounds = new google.maps.LatLngBounds();

            jQuery.each(self.map.markers, function () {
                bounds.extend(
                    new google.maps.LatLng(this.position.lat(), this.position.lng())
                );
            });

            if (self.map.markers.length === 1) {
                self.map.setCenter(bounds.getCenter());
                self.map.setZoom(5);
            } else {
                self.map.fitBounds(bounds);
            }
        }
    }

};

jQuery(function () {
    nat_locaties.init();
});
