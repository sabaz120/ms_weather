<?php

return [
    'api' => [
        "success" => "Solicitud exitosa, ¡todo salió bien!",
        "error" => "Error al procesar la solicitud",
        'invalid_data' => 'Datos inválidos',
        'auth' => [
            'register' => [
                'success' => 'Usuario registrado exitosamente',
                'error' => 'Error de validación',
                'email_exists' => 'El correo electrónico ya está en uso.',
            ],
            'login' => [
                'success' => 'Inicio de sesión exitoso',
                'error' => 'Credenciales inválidas',
            ],
            'logout' => [
                'success' => 'Cierre de sesión exitoso',
            ],
        ],
        'lang' => [
            'change' => [
                'success' => 'Idioma cambiado exitosamente',
                'error' => 'Error al cambiar el idioma',
            ],
        ],
    ],
    'weather_module' => [
        'weather' => [
            'success' => 'Datos del clima recuperados exitosamente',
            'error' => 'Error al recuperar los datos del clima',
            'invalid_city' => 'Nombre de ciudad inválido',
        ],
        'favorite_cities' => [
            'add' => [
                'success' => 'Ciudad añadida a favoritos',
                'error' => 'Error al añadir la ciudad a favoritos',
                'invalid_data' => 'Datos inválidos',
                'city_exists' => 'La ciudad ya existe en favoritos',
            ],
            'remove' => [
                'success' => 'Ciudad eliminada de favoritos',
                'error' => 'Error al eliminar la ciudad de favoritos',
                'not_found' => 'Ciudad no encontrada en favoritos',
            ],
            'get' => [
                'success' => 'Ciudades favoritas recuperadas exitosamente',
                'error' => 'Error al recuperar las ciudades favoritas',
            ],
        ],
        'search_history' => [
            'get' => [
                'success' => 'Historial de búsqueda recuperado exitosamente',
                'error' => 'Error al recuperar el historial de búsqueda',
            ],
        ],
    ],
    'user' => [
        'list' => [
            'success' => 'Lista de usuarios recuperada exitosamente',
            'error' => 'Error al recuperar la lista de usuarios',
        ],
        'create' => [
            'success' => 'Usuario creado exitosamente',
            'error' => 'Error al crear el usuario',
            'invalid_data' => 'Datos inválidos',
            'email_exists' => 'El correo electrónico ya está en uso.',
        ],
        'update' => [
            'success' => 'Usuario actualizado exitosamente',
            'error' => 'Error al actualizar el usuario',
            'not_found' => 'Usuario no encontrado',
        ],
        'deleted' => 'Usuario eliminado exitosamente',
    ],
    'roles' => [
        'not_found' => 'El rol no existe',
        'success' => 'Rol recuperado exitosamente',
        'error' => 'Error al recuperar el rol',
    ],
    'permissions' => [
        'not_found' => 'El permiso no existe',
        'success' => 'Permiso recuperado exitosamente',
        'error' => 'Error al recuperar el permiso',
    ],
];
