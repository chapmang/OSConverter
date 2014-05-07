<?php
namespace OSConversions {

	use Framework\Configuration as Configuration;

	class EllipsoidReference {

		protected $_ellipsoidName;
		protected $_semiMajorAxis;
		protected $_semiMinorAxis;
		protected $_flattening;
		protected $_inverseFlattening;
		protected $_firstEccentricity;
		protected $_firstEccentricitySquared;
		protected $_secondEccentricity;
		protected $_secondEccentricitySquared;

		public function __construct($ellipsoid = "WGS84") {

			if (!is_null($ellipsoid)) {
				$this->setEllipsoid($ellipsoid);
			}
		}

		public function setEllipsoid($ellipsoid) {

			if (Configuration::get("constants/ellipsoids.{$ellipsoid}")) {
				$ellipsoidConfig = Configuration::get("constants/ellipsoids.{$ellipsoid}");
			} else {
				throw new \Exception("{$ellipsoid} is not a valid ellipsoid", 500);
			}

			$this->_ellipsoidName = $ellipsoid;
			$this->_semiMajorAxis = $ellipsoidConfig['semiMajorAxis'];
			$this->_semiMinorAxis = $ellipsoidConfig['semiMinorAxis'];
			$this->_flattening = $ellipsoidConfig['flattening'];
			$this->_inverseFlattening = $ellipsoidConfig['inverseFlattening'];
			$this->_firstEccentricity = $ellipsoidConfig['firstEccentricity'];
			$this->_firstEccentricitySquared = $ellipsoidConfig['firstEccentricitySquared'];
			$this->_secondEccentricity = $ellipsoidConfig['secondEccentricity'];
			$this->_secondEccentricitySquared = $ellipsoidConfig['secondEccentricitySquared'];
			return $this;

		}

		public function getEllipsiodName() {

			return $this->_ellipsoidName;
		}

		public function getSemiMajorAxis() {

			return $this->_semiMajorAxis;
		}

		public function getSemiMinorAxis() {

			return $this->_semiMinorAxis;
		}

		public function getFlattening() {

			return $this->_flattening;
		}

		public function getInverseFlattening() {

			return $this->_inverseFlattening;
		}

		public function getFirstEccentricity() {

			return $this->firstEccentricity;
		}

		public function getFirstEccentricitySquared() {

			return $this->_firstEccentricitySquared;
		}

		public function getSecondEccentricity() {

			return $this->_secondEccentricity;
		}

		public function getSecondEccentricitySquared() {

			return $this->_secondEccentricitySquared;
		}


	}

}