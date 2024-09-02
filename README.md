# API de Acortamiento de URLs

Esta es una API para acortar URLs y recuperar el historial de URLs acortadas. La API está construida con Laravel y documentada con Swagger.

## Endpoints

### Acortar URL

- **URL**: `/api/create`
- **Método**: `POST`
- **Descripción**: Acorta una URL proporcionada.
- **Parámetros**:
  - **Cuerpo de la solicitud (JSON)**:
    - `original_url` (string, requerido): La URL original que deseas acortar.
- **Respuesta**:
  - **Código 201 Created**:
    ```json
    {
      "success": true,
      "data": {
        "id": 1,
        "original_url": "https://www.example.com",
        "shortened_url": "abcd1234",
        "created_at": "2024-01-01T00:00:00Z",
        "updated_at": "2024-01-01T00:00:00Z"
      }
    }
    ```
  - **Código 422 Unprocessable Entity**:
    ```json
    {
      "success": false,
      "message": "Invalid input.",
      "errors": {
        "original_url": ["The original url field is required."]
      }
    }
    ```
  - **Código 500 Internal Server Error**:
    ```json
    {
      "success": false,
      "message": "An unexpected error occurred.",
      "errors": "Error details here"
    }
    ```

### Obtener Historial

- **URL**: `/api/history`
- **Método**: `GET`
- **Descripción**: Recupera el historial de URLs acortadas.
- **Respuesta**:
  - **Código 200 OK**:
    ```json
    [
      {
        "id": 1,
        "original_url": "https://www.example.com",
        "shortened_url": "abcd1234",
        "created_at": "2024-01-01T00:00:00Z",
        "updated_at": "2024-01-01T00:00:00Z"
      },
      {
        "id": 2,
        "original_url": "https://www.another-example.com",
        "shortened_url": "efgh5678",
        "created_at": "2024-01-02T00:00:00Z",
        "updated_at": "2024-01-02T00:00:00Z"
      }
    ]
    ```

### Redireccionar URL

- **URL**: `/api/{shortened_url}`
- **Método**: `GET`
- **Descripción**: Redirige a la URL original usando la URL acortada.
- **Parámetros**:
  - **Ruta**:
    - `shortened_url` (string, requerido): La URL acortada que deseas redirigir.
- **Respuesta**:
  - **Código 302 Found**: Redirige a la URL original.
  - **Código 404 Not Found**:
    ```json
    {
      "message": "Not Found"
    }
    ```

### Eliminar URL

- **URL**: `/api/delete/{id}`
- **Método**: `DELETE`
- **Descripción**: Elimina una URL acortada por su ID.
- **Parámetros**:
  - **Ruta**:
    - `id` (integer, requerido): ID de la URL a eliminar.
- **Respuesta**:
  - **Código 200 OK**:
    ```json
    {
      "message": "URL eliminada"
    }
    ```
  - **Código 404 Not Found**:
    ```json
    {
      "message": "Not Found"
    }
    ```

## Documentación Swagger

La documentación de la API está disponible en [http://your-app-url/api/documentation](http://your-app-url/api/documentation).

## Instalación

1. Clona el repositorio:
   ```bash
   git clone https://github.com/tu-usuario/tu-repositorio.git
