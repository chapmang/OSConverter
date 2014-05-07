<?php
namespace OSConversions {

	class TransverseMercator {

		const OSNG =  "OS National Grid";

		private static $_projectionConstants = array(
			self::OSNG => array(
				"F0"	=> 0.9996012717,			// NatGrid scale factor on central meridian
				"lat0"	=> 0.8552113334772215,		// NatGrid true origin - Latitude (Radians)
				"lon0"	=> -0.0349065850398866,		// NatGrid true origin - Longitude (Radians)
				"N0"	=> -100000,					// Northing of true origin (metres)		
				"E0"	=> 400000,					// Easting of true origin (meters)
				"a"		=> 6377563.396,				// Semi-major axis (metres)
				"b"		=> 6356256.909,				// Semi-minor axis (metres)
				"e2"	=> 0.006670540074149084,	// Eccentricity squared
				"n"		=> 0.0016732203289875151100618582194
			)
		);


		/**
		 * latLonToENGrid
		 * 
		 * Convert ellipsoidal coordinates (latitude and longitude) to
		 * a grid of eastings and northings using a Transverse Mercator
		 * projection (for example, the OSGB National Grid or UTM) on the same datum.
		 * see www.gps.gov.uk/guidecontents.asp Annex C
		 *
		 * @access	private
		 * @param	string	The Latitude value
		 * @param	string	The Longitude value
		 * @return	object	Standard Grid Ref
		 * @link 	www.gps.gov.uk/guidecontents.asp
		 */
		public function latLonToGrid(LatLonValues $coordinates, $projection = "OS National Grid") {

			extract(self::$_projectionConstants[$projection]);

		  	$lat = deg2Rad($coordinates->getX());
		  	$lon = deg2Rad($coordinates->getY());

		  	$cosLat = cos($lat);
		  	$sinLat = sin($lat);
		  	$tanLat = tan($lat);
		  	$nu = $a * $F0 * pow(1 - $e2 * pow($sinLat, 2), -0.5);				// transverse radius of curvature
		  	$rho = $a * $F0 * (1-$e2) * pow(1 - $e2 * pow($sinLat, 2), -1.5);	// meridional radius of curvature
		  	$eta2 = ($nu / $rho) - 1;

		  	$Ma = (1 + $n + (5 / 4) * pow($n, 2) + (5 / 4) * pow($n, 3)) * ($lat - $lat0);
		  	$Mb = (3 * $n + 3 * pow($n, 2) + (21 / 8) * pow($n, 3)) * sin($lat - $lat0) * cos($lat + $lat0);
		  	$Mc = ((15 / 8) * pow($n, 2) + (15 / 8) * pow($n, 3)) * sin(2 * ($lat - $lat0)) * cos(2 * ($lat + $lat0));
		 	$Md = (35 / 24) * pow($n, 3) * sin(3 * ($lat - $lat0)) * cos(3 * ($lat + $lat0));
		  	$M = $b * $F0 * ($Ma - $Mb + $Mc - $Md);              	// meridional arc

		  	$I = $M + $N0;
		  	$II = ($nu / 2) * $sinLat * $cosLat;
		  	$III = ($nu / 24) * $sinLat * pow($cosLat, 3) * (5 - pow($tanLat, 2) + 9 * $eta2);
		  	$IIIA = ($nu / 720) * $sinLat * pow($cosLat, 5) * (61 - 58 * pow($tanLat, 2) + pow($tanLat, 4));
		  	$IV = $nu * $cosLat;
		  	$V = ($nu / 6) * pow($cosLat, 3) * ($nu / $rho - pow($tanLat, 2));
		  	$VI = ($nu / 120) * pow($cosLat, 5) * (5 - 18 * pow($tanLat, 2) + pow($tanLat, 4) + 14 * $eta2 - 58 * pow($tanLat, 2) * $eta2);
		
		  	$dLon = $lon-$lon0;

		  	$N = round($I + $II*pow($dLon, 2) + $III*pow($dLon, 4) + $IIIA*pow($dLon, 6));
		  	$E = round($E0 + $IV*$dLon + $V*pow($dLon, 3) + $VI*pow($dLon, 5));
		  	
		  	return new EastNorthValues($E, $N);
		}
		
		/**
		 * OSGridtoLatLon
		 * 
		 * Convert grid of eastings and northings to ellipsoidal coordinates 
		 * (latitude and longitude) using a Transverse Mercator projection 
		 * (for example, the OSGB National Grid or UTM) on the same datum.
		 * see www.gps.gov.uk/guidecontents.asp Annex C
		 *
		 * @access	private
		 * @param	string	An OS grid reference
		 * @return	object	LatLon
		 * @link 	www.gps.gov.uk/guidecontents.asp
		 * 
		 */
		public function gridToLatLon(EastNorthValues $coords, $projection = "OS National Grid") {

			$E = $coords->getX();
			$N = $coords->getY();

		  	extract(self::$_projectionConstants[$projection]);
		
		  	$lat = $lat0;
		  	$M = 0;
		  	// ie until < 0.01mm
		  	while ($N - $N0 - $M >= 0.00001) { 
		    	$lat = ($N - $N0 - $M) / ($a * $F0) + $lat;
			    $Ma = (1 + $n + (5 / 4) * pow($n,2) + ( 5 / 4 ) * pow($n, 3)) * ($lat - $lat0);
		    	$Mb = (3 * $n + 3 * pow($n, 2) + (21 / 8) * pow($n,3)) * sin($lat - $lat0) * cos($lat + $lat0);
		    	$Mc = ((15 / 8) * pow($n, 2) + ( 15 / 8 ) * pow($n, 3)) * sin( 2 * ($lat - $lat0)) * cos(2 * ($lat + $lat0));
		    	$Md = (35 / 24) * pow($n, 3) * sin(3 * ($lat - $lat0)) * cos(3 * ($lat + $lat0));
		    	$M = $b * $F0 * ($Ma - $Mb + $Mc - $Md);	// meridional arc
			}
		
		  	$cosLat = cos($lat);
		  	$sinLat = sin($lat);
		  	$nu = $a * $F0 * pow(1 - $e2 * pow($sinLat, 2), -0.5);				// transverse radius of curvature
		  	$rho = $a * $F0 * (1-$e2) * pow(1 - $e2 * pow($sinLat, 2), -1.5);	// meridional radius of curvature
		  	$eta2 = ($nu / $rho) - 1;

		  	$tanLat = tan($lat);
			$secLat = 1/$cosLat;

		 	$VII = $tanLat / (2 * $rho * $nu);
		  	$VIII = $tanLat / (24 * $rho * pow($nu,3)) * (5 + 3 * pow($tanLat, 2) + $eta2 - 9 * pow($tanLat, 2) * $eta2);
		  	$IX = $tanLat / (720 * $rho * pow($nu, 5)) * (61 + 90 * pow($tanLat, 2) + 45 * pow($tanLat, 4));
		  	$X = $secLat / $nu;
		  	$XI = $secLat / (6 * pow($nu, 3)) * ($nu / $rho + 2 * pow($tanLat, 2));
		  	$XII = $secLat / (120 * pow($nu, 5)) * (5 + 28 * pow($tanLat, 2) + 24 * pow($tanLat, 4));
		  	$XIIA = $secLat / (5040 * pow($nu,7)) * (61 + 662 * pow($tanLat,2) + 1320 * pow($tanLat, 4) + 720 * pow($tanLat, 6));
		
		  	$dE = ($E - $E0);

		  	$lat = $lat - $VII * pow($dE, 2) + $VIII * pow($dE, 4) - $IX * pow($dE, 6);
		  	$lon = $lon0 + $X * $dE - $XI * pow($dE, 3) + $XII * pow($dE, 5) - $XIIA * pow($dE, 7);
			
		  	return new LatLonValues(rad2deg($lat), rad2deg($lon));
		}

	}

}