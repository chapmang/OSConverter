OSConverter
===========

OSConverter is PHP library aimed at converting OS GB national grid references to WGS84 latitude and longitude values and vice versa.

The library also contains a class for converting between ellipsoids that will work outside of this OSGB/WGS84 conversion allowing for the conversion between any two ellipsoids.

## How to use

### Standard OSGB - WGS84 Conversions

	$conversion = new OSConversions\OSConversions();
    $coordinates = $test->gridRefToWGS84('TG 51409 13177');

