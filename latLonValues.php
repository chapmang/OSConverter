<?php
namespace OSConversions {


	class LatLonValues extends XYZCoordinatesAbstract {


		public function __construct($latitude = 0, $longitude = 0, $height = 0) {

			$this->setCoordinates($latitude, $longitude, $height);
		}
	}
}