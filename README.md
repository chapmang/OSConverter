OSConverter
===========

OSConverter is PHP library aimed at converting OS GB national grid references to WGS84 latitude and longitude values and vice versa.

The library also contains a class for converting between datums that will work outside of this OSGB/WGS84 conversion allowing for the conversion between any two datums.

## How to use

#### Standard OSGB Grid Reference - WGS84 Lat/Long Conversions

	$conversion = new OSConversions\OSConversions();
    $coordinates = $conversion->gridRefToWGS84('TG 51409 13177');


#### Standard WGS84 Lat/Long - OSGB Grid Reference Conversions

	// Need to create a valid LatLonValue object first
	$coordinates = new OSConversions\LatLonValues($latitude, $longitude, $height);
	$conversion = new OSConversions\OSConversions();
	// wgs84ToGridRef(LatLonValues, number of digits in gridref)
    $gridReference = $conversion->wgs84ToGridRef($coordinates, 10);

#### OSGB Easting/Northing - WGS84 Lat/Long Conversions
	
	// Need to create a valid EastNorthValue object first
	$coordinates = new OSConversions\EastNorthValues($easting, $northing, $height);
	$conversion = new OSConversions\OSConversions();
	$coordinates = $conversion->eastNorthToWGS84($eastNorth);

#### WGS84 Lat/Long - OSGB Easting/Northing Conversions

	// Need to create a valid LatLonValue object first
	$coordinates = new OSConversions\LatLonValues($latitude, $longitude, $height);
	$conversion = new OSConversions\OSConversions();
	// wgs84ToGridRef(LatLonValues, number of digits in gridref)
    $eastNorth = $conversion->wgs84ToEastNorth($coordinates);

### Non-OSGB Ellipsoid conversion
The OSGB national grid uses the OSGB36 datum based upon the AIRY_1830 ellipsoid, the parameters for which the above methods use to convert a grid reference to lat/lon values. However, using the built-in EllipsoidConvert class it is possible to simply convert coordinates between any given ellipsoid provided the following values have been added to the appropriate configuration files.

NB: the following are the minimum require values
#### datums.php

	"WGS84" => array(
            'name'           => 'WGS 1984',
            'defaultRegion'       => 'Global Definition',
            'referenceEllipsoid'  => 'WGS_84',
            'regions'             => array(
                'Global Definition' => array(
                	// Translation values to WGS84
                    'translationVectors' => array(
                        'x' => 0.0,
                        'y' => 0.0,
                        'z' => 0.0,
                    ),
                    'rotationMatrix' => array(
                        'x' => 0.0,
                        'y' => 0.0,
                        'z' => 0.0,
                    ),
                    'scaleFactor' => 0.0    //  ppm
                ),
            ),
        ),

#### ellipsoids.php

	"WGS_84" => array(
		'semiMajorAxis' => 6378137.0,
		'semiMinorAxis' => 6356752.3142,
		'flattening' => 0.0033528106718309896,
		'inverseFlattening' => 298.2572229328697,
		'firstEccentricity' => 0.08181919092890624,
		'firstEccentricitySquared' => 0.006694380004260827,
		'secondEccentricity' => 0.08209443803685366,
		'secondEccentricitySquared' => 0.006739496756586903
	)
