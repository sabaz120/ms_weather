# 📚 API de Gestión de Usuarios y Visualización del clima por ciudad

Este microservicio gestiona la información de los usuarios, incluyendo su creación, actualización, eliminación y asignación de roles. Además, permite a los usuarios visualizar el clima mediante la API de WeatherAPI, almacenar ciudades favoritas y visualizar sus últimas 5 búsquedas recientes.

## Características Adicionales:
* **Multilenguaje**: El microservicio soporta los idiomas inglés y español. Puedes especificar el idioma deseado enviando el parámetro lang en la solicitud API, con los valores en para inglés y es para español. Las respuestas de la API, incluyendo la descripción del clima, se traducirán al idioma seleccionado.
* **Caché de Clima**: Para optimizar el rendimiento y evitar solicitudes excesivas a la API de WeatherAPI, el microservicio implementa un caché de 30 minutos por ciudad consultada.
* **Procesamiento en Segundo Plano**: Las búsquedas de clima se almacenan en una cola para su procesamiento en segundo plano, lo que garantiza una respuesta rápida al usuario.
* **Documentación Swagger**: La API está documentada utilizando Swagger, y puedes acceder a la documentación interactiva en /api/documentation. Aquí, puedes explorar los endpoints, realizar solicitudes de prueba y obtener información detallada sobre cada API.
* **Autenticación JWT**: Todas las APIs, excepto las de autenticación (/api/v1/auth/login, /api/v1/auth/register), están protegidas por autenticación JWT. Debes registrarte o iniciar sesión para obtener un token JWT y utilizar las demás APIs.
* **Roles y Permisos (Spatie)**: El microservicio utiliza el paquete Spatie Laravel Permission para la gestión de roles y permisos. Por defecto, se crea un usuario administrador con el rol "admin" (admin@pulpoline.com, clave: 123789a1A1). El rol "admin" tiene permisos para gestionar usuarios (listar, editar, eliminar) a diferencia de los usuarios normales.

## Endpoints

La API expone los siguientes endpoints:

## Gestión de Usuarios

* **`GET /api/v1/users`**:
    * Devuelve un listado paginado de todos los usuarios registrados.
    * Permite filtrar y ordenar los resultados mediante parámetros de consulta.
    * Esto solo lo pueden realizar los usuarios con rol 'admin'.
    * Ejemplo de respuesta:
        ```json
            {
                "data": [
                    {
                        "id": 1,
                        "name": "Sabas Admin",
                        "email": "sabas@pulpoline.com",
                        "created_at": "...",
                        "updated_at": "..."
                    },
                    // ... otros usuarios
                ],
                "pagination": {
                    "current_page": 1,
                    "from": 1,
                    "last_page": 1,
                    "next_page_url": null,
                    "per_page": 10,
                    "prev_page_url": null,
                    "to": 1,
                    "total": 1
                },
                "success": true,
                "message": "Petición exitosa, todo salio bien!"
            }
        ```
* **`POST /api/v1/users`**:
    * Crea un nuevo usuario y le asigna un rol.
    * Esto solo lo pueden realizar los usuarios con rol 'admin'.
    * Ejemplo de respuesta (éxito):
        ```json
        {
            "data": {
                "id": 1,
                "name": "Sabas Admin",
                "email": "sabas@pulpoline.com",
                "created_at": "...",
                "updated_at": "..."
            },
            "message": "Usuario creado correctamente",
            "success": true
        }
        ```
* **`PUT /api/v1/users/{id}`**:
    * Actualiza la información de un usuario existente.
    * Permite actualizar el nombre, correo electrónico, contraseña y rol del usuario.
    * Esto solo lo pueden realizar los usuarios con rol 'admin'.
    * Ejemplo de respuesta (éxito):
        ```json
        {
            "data": {
                "id": 1,
                "name": "Updated Name",
                "email": "updated.email@example.com",
                "created_at": "...",
                "updated_at": "..."
            },
            "message": "Usuario actualizado correctamente",
            "success": true
        }
        ```
* **`DELETE /api/v1/users/{id}`**:
    * Elimina un usuario existente.
    * Esto solo lo pueden realizar los usuarios con rol 'admin'.
    * Ejemplo de respuesta (éxito):
        ```json
        {
            "success": true,
            "message": "Ciudad removida de favoritos"
        }
        ```
## Gestión del Clima
* **`GET /api/v1/weather-module/weather/by-city?city={city}`**:
    * Obtiene el clima de una ciudad específica utilizando la API de WeatherAPI.
    * Esto retorna la temperatura, condición atmosférica, viento y humedad.
    * Ejemplo de respuesta (éxito):
        ```json
        {
        "data": {
            "location": {
                "name": "Punto Fijo",
                "region": "Falcon",
                "country": "Venezuela",
                "localtime": "2025-04-06 02:40"
            },
            "current": {
                "temp_c": 25.1,
                "temp_f": 77.2,
                "condition": {
                    "text": "Despejado",
                    "icon": "//cdn.weatherapi.com/weather/64x64/night/113.png",
                    "code": 1000
                },
                "wind_kph": 40,
                "humidity": 84
            }
        },
        "message": "Datos del clima recuperados exitosamente",
        "success": true
        }
        ```
## Gestión de Ciudades Favoritas
* **`POST /api/v1/weather-module/favorite-cities`**:
    * Agrega una ciudad a la lista de ciudades favoritas del usuario autenticado.
    * Ejemplo de respuesta (éxito):
        ```json
        {
            "success": true,
            "message": "Ciudad agregada a favoritos",
            "data": {
                "id": 1,
                "user_id": 1,
                "city": "London",
                "created_at": "...",
                "updated_at": "...."
            }
        }
        ```
* **`GET /api/v1/weather-module/favorite-cities`**:
    * Obtiene la lista de ciudades favoritas del usuario autenticado.
    * Ejemplo de respuesta (éxito):
        ```json
        {
            "success": "Success",
            "message": "Successful request, everything went well!",
            "data": [
                {
                "id": 1,
                "user_id": 1,
                "city": "London",
                "created_at": "2025-04-06T06:52:56.928Z",
                "updated_at": "2025-04-06T06:52:56.928Z"
                }
            ],
            "pagination": {
                "current_page": 1,
                "from": 1,
                "last_page": 1,
                "next_page_url": null,
                "per_page": 10,
                "prev_page_url": null,
                "to": 1,
                "total": 1
            }
        }
        ```
* **`DELETE /api/v1/weather-module/favorite-cities/{id}`**:
    * Elimina una ciudad de la lista de ciudades favoritas del usuario autenticado.
    * Ejemplo de respuesta (éxito):
        ```json
        {
            "success": true,
            "message": "Ciudad eliminada de favoritos"
        }
        ```
## Gestión de Ciudades Favoritas
* **`GET /api/v1/weather-module/search-history`**:
    * Obtiene las últimas 5 búsquedas recientes del usuario autenticado.
    * Ejemplo de respuesta (éxito):
        ```json
        [
            {
                "id": 1,
                "city": "London",
                "created_at": "...",
                "updated_at": "..."
            },
            // ... otras búsquedas recientes
        ]
        ```
## Autenticación
* **`POST /api/v1/auth/login`**:
    * Autentica a un usuario y devuelve un token JWT.
    * Ejemplo de respuesta (éxito):
        ```json
        {
            "data": {
                "access_token": "X|XXXXXXSo9e5FlexVn5nYZSGqCPpPqZRFqW8JkkQH789420d4",
                "user_data": {
                    "id": 34,
                    "name": "Admin pulpoline",
                    "email": "admin@pulpoline.com",
                    "created_at": "....",
                    "updated_at": "...."
                },
                "role": {
                    "id": 25,
                    "name": "admin",
                    "guard_name": "web"
                },
                "token_type": "Bearer"
            },
            "message": "Successful request, everything went well!",
            "success": true
        }
        ```
* **`POST /api/v1/auth/register`**:
    * Registra un nuevo usuario y devuelve un token JWT.
    * Ejemplo de respuesta (éxito):
        ```json
        {
            "data": {
                "access_token": "your_access_token",
                "token_type": "Bearer"
            },
            "message": "Successful request, everything went well!",
            "success": true
        }
        ```
* **`POST /api/v1/auth/logout`**:
    * Invalida el token JWT del usuario autenticado.
    * Requiere un token JWT válido en el encabezado Authorization.
    * Ejemplo de respuesta (éxito):
        ```json
        {
            "data": {
                "message": "Logged out successfully"
            },
            "message": "Successful request, everything went well!",
            "success": true
        }
        ```
## Configuración y Ejecución con Docker

Para ejecutar este microservicio utilizando Docker, sigue estos pasos:

1.  **Copia el archivo `.env.example` a `.env`:**
    * `cp .env.example .env`
    * Asegurarse de configurar las variables de entorno en el archivo `.env` (Esto por si lo levantan sin el docker).
2.  **Obtener las credenciales de WeatherAPI**
    * Ve al sitio web de WeatherAPI (https://www.weatherapi.com/).
    * Haz clic en "Sign up" o "Regístrate" y crea una cuenta gratuita.
    * Una vez que hayas iniciado sesión, ve a tu panel de control o a la sección de "API keys".
    * Allí encontrarás tu clave API única, copia la clave API y pégala en el archivo `.env` en la variable `WEATHER_API_KEY`.

3.  **Levanta los contenedores con Docker Compose:**
    * `docker-compose up --build -d`
    * Este comando construirá las imágenes de Docker y levantará los contenedores en modo "detached" (en segundo plano).

4.  **Instalar las dependencias de Composer:**
    * `docker-compose exec api composer install`

5.  **Ejecuta las migraciones y semillas en el contenedor `api`:**
    * `docker-compose exec api php artisan migrate --seed`
    * Este comando ejecutará las migraciones de la base de datos y los seeders para poblar la base de datos con datos iniciales.

6.  **Genera la clave de la aplicación Laravel:**
    * `docker-compose exec api php artisan key:generate`
    * Este comando generará una clave de aplicación única para tu instalación de Laravel.

7.  **Accede a la API:**
    * La documentación de la API está disponible en `http://localhost:8004/api/documentation`.

## Tecnologías Utilizadas

* PHP 8.2+
* Laravel 10
* MySQL
* PHPUnit (para los tests)
* Guzzle HTTP Client (Para las peticiones a otros microservicios)
* Spatie Laravel Permission (para la gestión de roles y permisos)
* Laravel modules (Para dividir el código del servicio de clima)

## Docker Compose

El archivo `docker-compose.yml` se utiliza para la configuración de los contenedores Docker.

## Tests

Este microservicio incluye tests unitarios para verificar el correcto funcionamiento de los endpoints. Los tests cubren los siguientes escenarios:

* test_user_can_login
* test_login_invalid_credentials
* test_login_validation_fails
* test_user_can_register
* test_register_validation_fails
* test_register_unique_email
* test_user_can_logout
* test_logout_unauthenticated
* test_index_success
* test_index_validation_error
* test_store_success
* test_store_validation_error
* test_store_role_not_found
* test_update_success
* test_update_validation_error
* test_update_user_not_found
* test_update_role_not_found
* test_delete_success
* test_delete_user_not_found
* test_get_weather_by_city_success
* test_get_weather_by_city_api_error
* test_get_search_history_success
* test_get_search_history_validation_error
* test_add_favorite_city_success
* test_add_favorite_city_validation_error
* test_remove_favorite_city_success
* test_remove_favorite_city_not_found
* test_get_favorite_cities_success
* test_get_favorite_cities_validation_error

Para ejecutar los tests, puedes utilizar el siguiente comando dentro del contenedor `api`:

```bash
docker-compose exec api php artisan test
```

## Nota Importante

Para que el almacenamiento de búsquedas recientes funcione correctamente, es crucial mantener la ejecución de colas activa. Puedes hacerlo ejecutando el siguiente comando dentro del contenedor api:

```bash
docker-compose exec api php artisan queue:work
```