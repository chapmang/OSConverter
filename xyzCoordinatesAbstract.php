<?php
namespace OSConversions {


	abstract class XyzCoordinatesAbstract {

		protected $_xAxis;

		protected $_yAxis;

		protected $_zAxis;


		protected function setCoordinates($xAxis, $yAxis, $zAxis = 0) {

			if(is_numeric($xAxis)) {
				$this->_xAxis = $xAxis;
			} else {
				throw new \Exception("x-Axis must be numeric");
			}

			if (is_numeric($yAxis)) {
				$this->_yAxis = $yAxis;
			} else  {
				throw new \Exception("y-Axis must be numeric");
			}

			if (is_numeric($zAxis)) {
				$this->_zAxis = $zAxis;
			} else  {
				throw new \Exception("z-Axis must be numeric");
			}
		}

		public function getX() {

			return $this->_xAxis;
		}

		public function getY() {

			return $this->_yAxis;
		}

		public function getZ() {

			return $this->_zAxis;
		}

	}
}