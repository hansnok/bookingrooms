<?php
$capabilities = array(

    'local/bookingrooms:viewuser' => array(
    	// Capability type (write, read, etc.)
        'captype' => 'read',
        // Context in which the capability can be set (course, category, etc.)
        'contextlevel' => CONTEXT_SYSTEM,
        // Default values for different roles (only teachers and managers can modify)
        'legacy' => array(
            'teacher' => CAP_ALLOW,
            'editingteacher' => CAP_ALLOW,
            'manager' => CAP_ALLOW,
			'student'=>CAP_ALLOW,
            )),

	'local/bookingrooms:blocking'=> array(
		'captype' => 'read',
		'contextlevel' =>CONTEXT_SYSTEM,
		'legacy' => array(
            'manager' => CAP_ALLOW,
			'student'=>CAP_PROHIBIT,
            'teacher' => CAP_PROHIBIT,
            'editingteacher' => CAP_ALLOW
		)),
		
		'local/bookingrooms:administration'=> array(
				'captype' => 'read',
				'contextlevel' =>CONTEXT_SYSTEM,
				'legacy' => array(
						'manager' => CAP_ALLOW,
						'student'=>CAP_PROHIBIT,
						'teacher' => CAP_PROHIBIT,
						'editingteacher' => CAP_ALLOW
				)),
		
		'local/bookingrooms:libreryrules'=> array(
				'captype' => 'read',
				'contextlevel' =>CONTEXT_SYSTEM,
				'legacy' => array(
						'manager' => CAP_ALLOW,
						'student'=>CAP_PROHIBIT,
						'teacher' => CAP_PROHIBIT,
						'editingteacher' => CAP_ALLOW
				)),
		'local/bookingrooms:popup'=> array(
				'captype' => 'read',
				'contextlevel' =>CONTEXT_SYSTEM,
				'legacy' => array(
						'manager' => CAP_ALLOW,
						'student'=>CAP_PROHIBIT,
						'teacher' => CAP_PROHIBIT,
						'editingteacher' => CAP_ALLOW
				)),
		'local/bookingrooms:overwrite'=> array(
				'captype' => 'read',
				'contextlevel' =>CONTEXT_SYSTEM,
				'legacy' => array(
						'manager' => CAP_ALLOW,
						'student'=>CAP_PROHIBIT,
						'teacher' => CAP_PROHIBIT,
						'editingteacher' => CAP_ALLOW
				)),
		'local/bookingrooms:delete'=> array(
				'captype' => 'read',
				'contextlevel' =>CONTEXT_SYSTEM,
				'legacy' => array(
						'manager' => CAP_ALLOW,
						'student'=>CAP_PROHIBIT,
						'teacher' => CAP_PROHIBIT,
						'editingteacher' => CAP_ALLOW
				)),
		'local/bookingrooms:changewith'=> array(
				'captype' => 'read',
				'contextlevel' =>CONTEXT_SYSTEM,
				'legacy' => array(
						'manager' => CAP_ALLOW,
						'student'=>CAP_PROHIBIT,
						'teacher' => CAP_PROHIBIT,
						'editingteacher' => CAP_ALLOW
				)),
		'local/bookingrooms:typeroom'=> array(
				'captype' => 'read',
				'contextlevel' =>CONTEXT_SYSTEM,
				'legacy' => array(
						'manager' => CAP_ALLOW,
						'student'=>CAP_PROHIBIT,
						'teacher' => CAP_PROHIBIT,
						'editingteacher' => CAP_ALLOW
				)),
		'local/bookingrooms:bockinginfo'=> array(
				'captype' => 'read',
				'contextlevel' =>CONTEXT_SYSTEM,
				'legacy' => array(
						'manager' => CAP_ALLOW,
						'student'=>CAP_PROHIBIT,
						'teacher' => CAP_PROHIBIT,
						'editingteacher' => CAP_ALLOW
				)),
		'local/bookingrooms:advancesearch'=> array(
				'captype' => 'read',
				'contextlevel' =>CONTEXT_SYSTEM,
				'legacy' => array(
						'manager' => CAP_ALLOW,
						'student'=>CAP_PROHIBIT,
						'teacher' => CAP_PROHIBIT,
						'editingteacher' => CAP_ALLOW
				)),
	'local/bookingrooms:upload'=> array(
				'captype' => 'read',
				'contextlevel' =>CONTEXT_SYSTEM,
				'legacy' => array(
						'manager' => CAP_ALLOW,
						'student'=>CAP_PROHIBIT,
						'teacher' => CAP_PROHIBIT,
						'editingteacher' => CAP_ALLOW
				)),
		

		
);
?>	
