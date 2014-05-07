<?php
namespace OSConversions {

	class EastNorthValues extends XYZCoordinatesAbstract {


		public function __construct($easting = 0, $northing = 0, $height = 0) {

			$this->setCoordinates($easting, $northing, $height);
		}
	}
}