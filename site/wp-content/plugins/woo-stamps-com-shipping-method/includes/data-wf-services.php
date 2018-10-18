<?php

/**
 * USPS Services and subservices
 */
return array(
	// Domestic
	'US-FC' => array(
		// Name of the service shown to the user
		'name'  => 'First-Class Mail&#0174;',

		// Services which costs are merged if returned (cheapest is used). This gives us the best possible rate.
		'services' => array(
			"US-FC"  => "First-Class Mail",
		)
	),
	'US-XM' => array(
		// Name of the service shown to the user
		'name'  => 'Priority Mail Express&#8482;',

		// Services which costs are merged if returned (cheapest is used). This gives us the best possible rate.
		'services' => array(
			'US-XM'  => "Priority Mail Express&#8482;",
		)
	),
	'US-MM' => array(
		// Name of the service shown to the user
		'name'  => 'Media Mail Parcel',

		// Services which costs are merged if returned (cheapest is used). This gives us the best possible rate.
		'services' => array(
			'US-MM'  => "USPS Media Mail"
		)
	),
	'US-LM' => array(
		// Name of the service shown to the user
		'name'  => "Library Mail Parcel",

		// Services which costs are merged if returned (cheapest is used). This gives us the best possible rate.
		'services' => array(
			'US-LM'  => "USPS Library Mail"
		)
	),
	'US-PP' => array(
		// Name of the service shown to the user
		'name'  => "USPS Parcel Post",

		// Services which costs are merged if returned (cheapest is used). This gives us the best possible rate.
		'services' => array(
			'US-PP'  => "USPS Parcel Post"
		)
	),
	'US-PS' => array(
		// Name of the service shown to the user
		'name'  => "USPS Parcel Select",

		// Services which costs are merged if returned (cheapest is used). This gives us the best possible rate.
		'services' => array(
			'US-PS'  => "USPS Parcel Select"
		)
	),
	'US-CM' => array(
		// Name of the service shown to the user
		'name'  => "USPS Critical Mail",

		// Services which costs are merged if returned (cheapest is used). This gives us the best possible rate.
		'services' => array(
			'US-CM'  => "USPS Critical Mail"
		)
	),
	'US-PM' => array(
		// Name of the service shown to the user
		'name'  => "Priority Mail&#0174;",

		// Services which costs are merged if returned (cheapest is used). This gives us the best possible rate.
		'services' => array(
			"US-PM"  => "USPS Priority Mail&#0174;",
		)
	),

	// International
	'US-EMI' => array(
		// Name of the service shown to the user
		'name'  => "Priority Mail Express International&#8482;",

		// Services which costs are merged if returned (cheapest is used). This gives us the best possible rate.
		'services' => array(
			"US-EMI"  => "Priority Mail Express International&#8482;",
		)
	),
	'US-PMI' => array(
		// Name of the service shown to the user
		'name'  => "Priority Mail International&#0174;",

		// Services which costs are merged if returned (cheapest is used). This gives us the best possible rate.
		'services' => array(
			"US-PMI"  => "Priority Mail International&#0174;",
		)
	),
	'US-FCI' => array(
		// Name of the service shown to the user
		'name'  => "First Class Package Service&#8482; International",

		// Services which costs are merged if returned (cheapest is used). This gives us the best possible rate.
		'services' => array(
			"US-FCI"  => "USPS First Class Mail International&#8482;",
		)
	)
);