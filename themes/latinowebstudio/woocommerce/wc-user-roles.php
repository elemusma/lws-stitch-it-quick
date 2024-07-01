<?php

// Add a custom user role with default capabilities of the Customer role
function add_custom_roles() {
    // Get the capabilities of the Customer role
    $customer_role = get_role('customer');
    
    // If the Customer role exists, add your custom role with the same capabilities
    if ($customer_role) {
        add_role(
            'client_gates_enterprises', // Role slug
            'Gates Enterprises',      // Role display name
            $customer_role->capabilities // Use Customer role capabilities
        );
    } else {
        // If the Customer role doesn't exist, you can manually define capabilities or handle the case as needed
        // For example, you could create your own default capabilities array
        $custom_capabilities = array(
            // Define your custom capabilities here
        );
        
        add_role(
            'specific_client_role', // Role slug
            'Specific Client',      // Role display name
            $custom_capabilities   // Use custom capabilities
        );
    }
}
add_action('init', 'add_custom_roles');

function currentUser() {
	$user = get_userdata( get_current_user_id() );
	return $user;
}

function currentUserGates() {
	$role_slug = 'client_gates_enterprises';
	return $role_slug;
}

// Assigns admin with Gates User Role
$user_id = 1; // Replace 123 with the actual user ID
$user = get_user_by('ID', $user_id);

if ($user) {
    $user->add_role('client_gates_enterprises'); // Assign the role to the user
}

?>