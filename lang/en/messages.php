<?php

return [
    'api' => [
        "success" => "Successful request, everything went well!",
        "error" => "Error processing the request",
        'invalid_data' => 'Invalid data',
        'auth' => [
            'register' => [
                'success' => 'User registered successfully',
                'error' => 'Validation error',
                'email_exists' => 'The email has already been taken.',
            ],
            'login' => [
                'success' => 'Login successful',
                'error' => 'Invalid credentials',
            ],
            'logout' => [
                'success' => 'Logged out successfully',
            ],
        ],
        'lang' => [
            'change' => [
                'success' => 'Language changed successfully',
                'error' => 'Error changing language',
            ],
        ],
    ],
    'weather_module' => [
        'weather' => [
            'success' => 'Weather data retrieved successfully',
            'error' => 'Error retrieving weather data',
            'invalid_city' => 'Invalid city name',
        ],
        'favorite_cities' => [
            'add' => [
                'success' => 'City added to favorites',
                'error' => 'Error adding city to favorites',
                'invalid_data' => 'Invalid data',
                'city_exists' => 'City already exists in favorites',
            ],
            'remove' => [
                'success' => 'City removed from favorites',
                'error' => 'Error removing city from favorites',
                'not_found' => 'City not found in favorites',
            ],
            'get' => [
                'success' => 'Favorite cities retrieved successfully',
                'error' => 'Error retrieving favorite cities',
            ],
        ],
        'search_history' => [
            'get' => [
                'success' => 'Search history retrieved successfully',
                'error' => 'Error retrieving search history',
            ],
        ],
    ],
    'user' => [
        'list' => [
            'success' => 'User list retrieved successfully',
            'error' => 'Error retrieving user list',
        ],
        'create' => [
            'success' => 'User created successfully',
            'error' => 'Error creating user',
            'invalid_data' => 'Invalid data',
            'email_exists' => 'The email has already been taken.',
        ],
        'update' => [
            'success' => 'User updated successfully',
            'error' => 'Error updating user',
            'not_found' => 'User not found',
        ],
        'deleted' => 'User deleted successfully',
    ],
    'roles' => [
        'not_found' => 'Role does not exist',
        'success' => 'Role retrieved successfully',
        'error' => 'Error retrieving role',
    ],
    'permissions' => [
        'not_found' => 'Permission does not exist',
        'success' => 'Permission retrieved successfully',
        'error' => 'Error retrieving permission',
    ],
];