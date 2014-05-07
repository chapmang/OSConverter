<?php
namespace OSConversions {

	class OSConversions {


		public function __construct() {

		}

		public function gridRefToWGS84($gridRef) {

			// Convert standard OS grid reference ('SU387148') to 
			// a fully numeric ref ([438700,114800])
			$gridRefConversion = new GridRefConversion();
			$coordinates = $gridRefConversion->gridRefToEN($gridRef);

			// Convert grid of eastings and northings to ellipsoidal coordinates
			// using a Transverse Mercator projection on the same datum.
			$convertFormat = new TransverseMercator();
			$latLon = $convertFormat->gridToLatLon($coordinates);


			// Convert from the OS Airy1830	ellipsoid to the WGS84 ellipsoid
			$convertEllipsoid = new EllipsoidConvert('OSGB36');
			$newCoordinates = $convertEllipsoid->convert($latLon, 'WGS84');

			return $newCoordinates;

		}

		public function eastNorthToWGS84(EastNorthValues $eastNorth) {

			// Convert grid of eastings and northings to ellipsoidal coordinates
			// using a Transverse Mercator projection on the same datum.
			$convertFormat = new TransverseMercator();
			$latLon = $convertFormat->gridToLatLon($eastNorth);

			// Convert from the OS Airy1830	ellipsoid to the WGS84 ellipsoid
			$convertEllipsoid = new EllipsoidConvert('OSGB36');
			$newCoordinates = $convertEllipsoid->convert($latLon, 'WGS84');

			return new LatLonValues($newCoordinates->lat, $newCoordinates->lon, $newCoordinates->height);

		}

		public function wgs84ToGridRef(LatLonValues $latLon, $digits = 6) {

			// Convert from the WGS84 ellipsoid to the OS Airy1830 ellipsoid
			$convertEllipsoid = new EllipsoidConvert('WGS84');
			$newCoordinates = $convertEllipsoid->convert($latLon, 'OSGB36');

			// Convert ellipsoidal coordinates to grid of eastings and northings
			// using a Transverse Mercator projection on the same datum.
			$convertFormat = new TransverseMercator();
			$eastNorth = $convertFormat->latLonToGrid($newCoordinates);

			$gridRefConversion = new GridRefConversion();
			$gridRef = $gridRefConversion->enToGridRef($eastNorth, $digits);

			return $gridRef;
		}

		public function wgs84ToEastNorth(LatLonValues $latLon) {
			
			// Convert from the WGS84 ellipsoid to the OS Airy1830 ellipsoid
			$convertEllipsoid = new EllipsoidConvert('WGS84');
			$newCoordinates = $convertEllipsoid->convert($latLon, 'OSGB36');
		
			// Convert ellipsoidal coordinates to grid of eastings and northings
			// using a Transverse Mercator projection on the same datum.
			$convertFormat = new TransverseMercator();
			$eastNorth = $convertFormat->latLonToGrid($newCoordinates);

			return $eastNorth;
		}
	}
}