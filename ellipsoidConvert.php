<?php
namespace OSConversions {

	/**
	 * 
	 * EllipsoidConvert
	 * 
	 * A class for converting between ellipsoids.
	 * 
	 * @author Geoff Chapman <geoff.chapman@mac.com>
	 * @version 2.0
	 * @package OSConversions
	 */
			
	class EllipsoidConvert {

		// Source Datum
		protected $_fromDatum;

		// Destination Datum
		protected $_toDatum;

		// Source Ellipse parameters
		protected $_fromEllipsoid = array();

		// Destination Ellipse parameters
		protected $_toEllipsoid = array();

		// Source Helmert transform parameters
		protected $_fromHelmert = array();
		
		// Destination Helmert transform parameters
		protected $_toHelmert = array();


		public function __construct($fromDatum = null) {

			if (is_null($fromDatum)) {
				throw new Exception("A source datum must be set", 1);
			}
			// Set the source datum of the coordinates to be transformed
			$this->_fromDatum = new DatumReference($fromDatum);
			$this->_fromEllipsoid = $this->_fromDatum->getEllipsoid();
			$this->_fromHelmert = $this->_fromDatum->getHelmertParameters();
			$this->_fromDatumReference = $this->_fromDatum->getDatumReference();
		}


		/**
		 * 3D conversion of a set of coordinates from one ellipsoid to another
		 * @param	object		A LatLon pair in decimal degrees
		 * @param	array		Source ellipsoid parameters
		 * @param	array		Helmert transformation parameters
		 * @param	array		Destination ellipsoid parameters
		 * @return	object		LatLon as decimal degrees
		 *  
		 */
		public function convert(LatLonValues $coordinates, $toDatum) {

			$this->_toDatum = $toDatum;

			// Get the details of the destination datum
			$this->_toDatum = new DatumReference($toDatum);
			$this->_toEllipsoid = $this->_toDatum->getEllipsoid();
			$this->_toHelmert = $this->_toDatum ->getHelmertParameters();
			$this->_toDatumReference = $this->_toDatum->getDatumReference();


			// Convert to ECEF coordinates
			list($x, $y, $z) = $this->_toCartesian($coordinates);


			if ($this->_toDatumReference == "WGS84") {
				// Use standard transformation to WGS84 
				list($x1, $y1, $z1) = $this->_toWGS84($x, $y, $z);
			} else if ($this->_fromDatumReference == "WGS84") {
				// Use inverted transformation from WGS84
				list($x1, $y1, $z1) = $this->_fromWGS84($x, $y, $z);
			} else {
				// Pass via WGS84 for other ellipoids
				list($x1, $y1, $z1) = $this->_toWGS84($x, $y, $z);
				list($x1, $y1, $z1) = $this->_fromWGS84($x, $y, $z);
			}

			// Convert to Decimal Degrees
			list($lat, $lon, $height) = $this->_toDecimalDegrees($x1, $y1, $z1);
			
			return new LatLonValues($lat, $lon, $height);

		}

		/**
		 * Convert to the WGS84 ellipsoidal datum
		 * @param float $x X axis value to be converted
		 * @param float $y Y axis value to be converted
		 * @param float $z Z axis value to be converted
		 * @return array   Converted axis values
		 */
		protected function _toWGS84($x, $y, $z) {

			$t = $this->_fromHelmert;
			return	$this->_helmertTransform($x, $y, $z, $t);
		}

		/**
		 * Convert from the WGS84 ellipsoidal datum
		 * @param float $x X axis value to be converted
		 * @param float $y Y axis value to be converted
		 * @param float $z Z axis value to be converted
		 * @return array   Converted axis values
		 */
		protected function _fromWGS84($x, $y, $z) {

			foreach ($this->_toHelmert as $key => $value) {
				if (is_array($value)) {
					foreach ($value as $k => $v) {
						$t[$key][$k] = $this->_invertSign($v);
					}
				} else {
					$t[$key] = $this->_invertSign($value);
				}
			}

			return $this->_helmertTransform($x, $y, $z, $t);
		}

		/**
		 * Change the sign of a given number
		 * @param mixed $number Number for sign to be inverted
		 * @return mixed Converted number
		 */
		protected function _invertSign($number) {
			if (is_array($number)) {
				foreach ($number as $value) {
					return $this->_invertSign($value);
				}
			}
			$invertNumber = $number * -1;
			return $invertNumber;
		}


		/**
		 * Converting latitude, longitude and ellipsoid height to
		 * 3-D (ECEF) Cartesian coordinates.
		 * @param string $datum Datum to be used in converting to Cartesian coordinates
		 * @return array
		 */
		protected function _toCartesian($coordinates) {

			if (is_null($this->_fromDatum)) {
				throw new \Exception("A datum is required for this conversion", 500);
			}

			// Convert from degrees to radians
		  	$radLat = deg2rad($coordinates->getX());
		  	$radLon = deg2rad($coordinates->getY());
		  	$height = $coordinates->getZ(); 
			
		  	$semiMajor = $this->_fromEllipsoid->getSemiMajorAxis(); // semi-major axis length of ellipsoid
		  	$semiMinor = $this->_fromEllipsoid->getSemiMinorAxis(); // semi-minor axis length of ellipsoid
			
		  	$sinLat = sin($radLat);
		  	$cosLat = cos($radLat);
		  	$sinLon = sin($radLon);
		  	$cosLon = cos($radLon);
		
			$eSq = (pow($semiMajor,2) - pow($semiMinor,2)) / pow($semiMajor,2); // eccentricity squared of ellipsoid
		  	$v = $semiMajor / sqrt(1 - $eSq * pow($sinLat,2)); // prime vertical radius of curvature @ $this->lat
		
		  	$xAxis1 = ($v+$height) * $cosLat * $cosLon;
		  	$yAxis1 = ($v+$height) * $cosLat * $sinLon;
		  	$zAxis1 = ((1-$eSq)*$v + $height) * $sinLat;

		  	return array($xAxis1, $yAxis1, $zAxis1);
		}


		/**
		 * Transform 3-D Cartesian coordinates from one ellipsoidal datum to
		 * a second using the Helmert Transformation
		 * @param float $x X axis Coordinates
		 * @param float $y Y axis coordinates
		 * @param float $z Z axis coordinates
		 * @param array $t Helmert transformation vectors
		 * @return array
		 */
		protected function _helmertTransform($x, $y, $z, $t) {
			
	  		// Retrieve the translation vectors between the
	  		// fromDatum and the toDatum
			$tx = $t['translationVectors']["x"]; // X-axis translation (metres)
			$ty = $t['translationVectors']["y"]; // Y-axis translation (metres)
			$tz = $t['translationVectors']["z"]; // Z-axis translation (metres)

			// Retrieve the rotations to be applied to the points vector
			// and normalise seconds to radians
			$rx = deg2rad($t['rotationMatrix']["x"] / 3600); // X-axis rotation (radians)
			$ry = deg2rad($t['rotationMatrix']["y"] / 3600); // Y-axis rotation (radians)
			$rz = deg2rad($t['rotationMatrix']["z"] / 3600); // Z-axis rotation (radians)

			// Retrieve the scale correction while normalising from ppm
			$s = $t['scaleFactor'] / 1e6; // Scale factor (unit less)
			
			// Apply Helmert 7-parameter transform
			$xAxis2 = $tx + $x * (1 + $s) - $y * $rz + $z * $ry;
			$yAxis2 = $ty + $x * $rz + $y * (1 + $s) - $z * $rx;
			$zAxis2 = $tz - $x * $ry + $y * $rx + $z * (1 + $s);

			return array($xAxis2, $yAxis2, $zAxis2);

		}


		/**
		 * Converting 3-D (ECEF) Cartesian coordinates to latitude, longitude
		 * and ellipsoid height (decimal degrees)
		 * @param float $x1 
		 * @param float $y1
		 * @param float $z1 
		 * @return array
		 */
		protected function _toDecimalDegrees($x, $y, $z) {

			if (is_null($this->_toDatum)) {
				throw new \Exception("A datum is required for this conversion", 500);
			}

		  	$semiMajor = $this->_toEllipsoid->getSemiMajorAxis(); 	// semi-major axis length of fromDatum
		  	$semiMinor = $this->_toEllipsoid->getSemiMinorAxis(); 	// semi-minor axis length of ellipsoid
		  	$precision = 4 / $semiMajor;  							// results accurate to around 4 metres
			
			// Initial value of latitude
			$eSq = (pow($semiMajor,2) - pow($semiMinor,2)) / pow($semiMajor,2); // eccentricity squared of ellipsoid
		 	$p = sqrt(pow($x,2) + pow($y,2));
			$phi = atan2($z, $p * (1-$eSq)); // Initial value of latitude (before precision improvement)
			$phiP = 2 * pi();

			// Iteratively improve latitude by computing v until change between
			// successive values of $phi is smaller than $precision
		  	while (abs($phi-$phiP) > $precision) {
		    	$v = $semiMajor / sqrt(1 - $eSq * pow(sin($phi),2)); // prime vertical radius of curvature @ $phi
		    	$phiP = $phi;
		    	$phi = atan2($z + $eSq * $v * sin($phi), $p);
		  	}

		  	// Longitude
		  	$lambda = atan2($y, $x);

		  	// Convert from radians to degrees
		  	$xAxis3 = rad2deg($phi);
		  	$yAxis3 = rad2deg($lambda);
		  	$zAxis3 = $p / cos($phi) - $v;

		  	return array($xAxis3, $yAxis3, $zAxis3);
		}
		
	}

}