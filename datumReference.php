<?php
namespace OSConversions {

	use Framework\Configuration as Configuration;
	use OSConversions\EllipsiodReference as EllipsiodReference;

	class DatumReference {

		// Datum reference
		protected $_datumReference;

		// Datum name
		protected $_datumName;

		// Ellipsoid name
		protected $_ellipsoidName;

		// Reference ellipsoid
		protected $_ellipsoid;

		// Helmert Parameters
		protected $_helmertPatameters;
		
		
		public function __construct($datum = "WGS84", $region = null) {

			if (!is_null($datum)) {
				$this->setDatum($datum, $region);
			}
		}

		/**
		 * Set a new datum and its parameters from a config file
		 * @param string $datum Name of datum parameters to be set
		 * @param string $region Name of datum region
		 * @return object
		 */ 
		public function setDatum($datum, $region = null) {

			if (Configuration::get("constants/datums.{$datum}")) {
				$datumConfig = Configuration::get("constants/datums.{$datum}");
			} else {
				throw new \Exception("{$datum} is not a valid datum", 500);
			}

			if (is_null($region)) {
				$region = $datumConfig['defaultRegion'];
			}
			$this->_datumReference = $datum;
			$this->_datumName = $datumConfig['name'];
			$this->_ellipsoidName = $datumConfig['referenceEllipsoid'];
			$this->_ellipsoid = new EllipsoidReference($this->_ellipsoidName);

			$this->_setHelmertParameters($region);

			return $this;
		}

		/**
		 * Set the Helmert Parameters for a given datum region
		 * @param string $region Name of the datum region to be used
		 * @return object
		 */
		protected function _setHelmertParameters($region = null) {

			if(is_null($region)) {
				throw new \Exception("No region name set");
			}

			$datum = $this->_datumReference;

			if (Configuration::get("constants/datums.{$datum}.regions.$region")) {
				$regionParameters = Configuration::get("constants/datums.{$datum}.regions.$region");
			} else {
				throw new Exception("{$region} is not valid region for this datum", 1);	
			}
			
			$this->_helmertPatameters = array(
				'translationVectors' => $regionParameters['translationVectors'], 
				'rotationMatrix' => $regionParameters['rotationMatrix'],
				'scaleFactor' => $regionParameters['scaleFactor']
			);

			return $this;
		}

		/**
		 * Getter for datum reference
		 * @return string Datum Reference
		 */
		public function getDatumReference() {

			return $this->_datumReference; 
		}

		/**
		 * Getter for ellipsoid parameters
		 */
		public function getEllipsoid() {

			return $this->_ellipsoid;
		}

		/**
		 * Getter for Helmert Parmeters
		 */
		public function getHelmertParameters(){

			return $this->_helmertPatameters;
		}
	}
}