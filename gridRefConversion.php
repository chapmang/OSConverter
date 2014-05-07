<?php
namespace OSConversions {

	class GridRefConversion {

		/** 
		 * gridrefLetToNum
		 * 	
		 * Convert standard OS grid reference ('SU387148') to fully numeric ref ([438700,114800])
		 * returned co-ordinates are in metres, centred on grid square for conversion to lat/long
		 *
		 * NB: northern-most grid squares will give 7-digit northings
		 * no error-checking is done on gridref (bad input will give bad results or NaN)
		 * @param	string	An OS grid reference
		 * @return	object 	EastNorthValue object
		 * 
		 */
		public function gridRefToEN($gridRef) {

			// Get numeric values of letter references, mapping A->0, B->1, C->2, etc:
	  		$l1 = $this->charCodeAt(strtoupper($gridRef), 0) - $this->charCodeAt("A", 0);	  	
	  		$l2 = $this->charCodeAt(strtoupper($gridRef), 1) - $this->charCodeAt("A", 0);	  	
		  	
		  	// Shuffle down letters after 'I' since 'I' is not used in grid:
		  	if ($l1 > 7):
		  		$l1--;
		  	endif;
		  	if ($l2 > 7):
		  		$l2--;
		  	endif;
		
		  	// Convert grid letters into 100km-square indexes from false origin (grid square SV):
		 	$e = (($l1-2)%5)*5 + ($l2%5);
		  	$n = (19-floor($l1/5)*5) - floor($l2/5);
		  	
		  	// Skip grid letters to get numeric part of ref, stripping any spaces:
		  	$gridRef = str_replace(" ","", substr($gridRef,2));
		
		  	// Append numeric part of references to grid index:
		  	$e .= substr($gridRef, 0, strlen($gridRef)/2);
		  	$n .= substr($gridRef, strlen($gridRef)/2);
		  	
		  	// Normalise to 1m grid, rounding up to centre of grid square:
		  	switch (strlen($gridRef))
		  		{
		    	case 6:
		    		$e .= '50';
		    		$n .= '50';
		    		break;
		    	case 8:
		    		$e .= '5';
		    		$n .= '5';
		    		break;
		    	// 10-digit refs are already 1m
		  	}
	
	  		return new EastNorthValues($e, $n);
		}

		/**
		 * gridrefNumToLet
		 * 
		 * Convert fully numeric grid reference([438700,114800]) to standard grid ref ('SU387148')
		 * supplied parameters should be in metres.
		 * @param	interger	Easting value
		 * @param	interger	Northing value
		 * @param	interger	Digits defining length of final grid reference
		 * @return	string		Standard OS grid reference
		 * 
		 */
		public function enToGridRef(EastNorthValues $coordinates, $digits = 6) {

			$e = $coordinates->getX();
			$n = $coordinates->getY();

			// Get the 100km-grid indices
			$e100k = floor($e/100000);
			$n100k = floor($n/100000);
		  
		  	if ($e100k<0 || $e100k>6 || $n100k<0 || $n100k>12):
		  		return '';
		  	endif;
		
		  	// Translate those into numeric equivalents of the grid letters
		  	$l1 = (19-$n100k) - (19-$n100k)%5 + floor(($e100k+10)/5);
		  	$l2 = (19-$n100k)*5%25 + $e100k%5;
		
		  	// Compensate for skipped 'I' and build grid letter-pairs
		  	if ($l1 > 7):
		  		$l1++;
		  	endif;
		  	if ($l2 > 7):
		  		$l2++;
		  	endif;  	
		  	$letPair = $this->fromCharCode($l1+$this->charCodeAt("A",0), $l2+$this->charCodeAt("A",0));
		
		  	// Strip 100km-grid indices from easting & northing, and reduce precision
		  	// Note use of floor, as ref is bottom-left of relevant square!
		  	$e = floor(($e%100000)/pow(10,5-$digits/2));
		  	$n = floor(($n%100000)/pow(10,5-$digits/2));
		  	
		  	$gridRef = $letPair . ' ' . $this->padLZ($e,$digits/2) . ' ' . $this->padLZ($n, $digits/2);
		
		  	return $gridRef;
		}

		/**
		 * charCodeAt
		 *  
		 * Convert Alpha/Numerical Character to Ascii
		 * (does not work with utf-8, but not needed in this instance)
		 * php version of JavaScript charCodeAt() Method.
		 * @param	string		String to be converted
		 * @param	interger	Start point of substring
		 * @return	interger	The ascii value of substring
		 *  
		 */
		protected function charCodeAt($str, $i) {
		  	return ord(substr($str, $i, 1));
		}

		/**
		 * fromCharCode
		 * 
		 * Convert Unicode values into characters
		 * (does not work with utf-8, but not need in this instance)
		 * php version of JavaScript fromCharCode() Method 
		 * @return	string	Character represented by submitted unicode value	
		 */
		protected function fromCharCode() {
		  	$output = '';
		  	$chars = func_get_args();
		  	foreach($chars as $char)
		  		{
		    	$output .= chr((int) $char);
		  		}
		  	return $output;
		}

		/**
		 * padLZ
		 * 
		 * Pad a number with sufficient leading zeros to make it w chars wide
		 * @param	interger	The Number to be padded out
		 * @param	interger	The final total number of digits
		 * @return	interger	The padded number
		 * 
		 */
		protected function padLZ($num, $width) {	
		  	$len = strlen($num);
		  	for ($i=0; $i<$width-$len; $i++):
		  		$num = '0' + $num;
		  	endfor;
		  	return $num;
		}
	}
}