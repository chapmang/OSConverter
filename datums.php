<?php
// Transform values to WGS84 (invert then to reverse the conversion)
return array(

	"WGS84" => array(    //    Global GPS
            'name'           => 'WGS 1984',
            'epsg_id'             => '4326',
            'esri_name'           => 'D_WGS_1984',
            'defaultRegion'       => 'Global Definition',
            'referenceEllipsoid'  => 'WGS_84',
            'regions'             => array(
                'Global Definition' => array(
                    'translationVectors' => array(
                        'x' => 0.0,
                        'y' => 0.0,
                        'z' => 0.0,
                    ),
                    'translationVectorsUOM' => 'meters',
                    'rotationMatrix' => array(
                        'x' => 0.0,
                        'y' => 0.0,
                        'z' => 0.0,
                    ),
                    'rotationMatrixUOM' => 'ARCSECONDS',
                    'scaleFactor' => 0.0    //  ppm
                ),
            ),
        ),

    "OSGB36" => array(
            'name' => 'Ordnance Survey - Great Britain (1936)',
            'synonyms'            => 'OSGB',
            'epsg_id'             => '4277',
            'esri_name'           => 'D_OSGB_1936',
            'defaultRegion'       => 'GB - Great Britain',
            'referenceEllipsoid'  => 'AIRY_1830',
            'regions'             => array(
                'GB - Great Britain' => array(
                    'translationVectors' => array(
                        'x' => 446.448,
                        'y' => -125.157,
                        'z' => 542.06,
                    ),
                    'translationVectorsUOM' => 'meters',
                    'rotationMatrix' => array(
                        'x' => 0.1502,
                        'y' => 0.247,
                        'z' => 0.8421,
                   ),
                    'rotationMatrixUOM' => 'ARCSECONDS',
                    'scaleFactor' => -20.4894    //  ppm
                ),
            ),
        ),

);