<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Url;
use Illuminate\Support\Str;
use Illuminate\Database\QueryException;

/**
 * @OA\Info(title="URL Shortener API", version="1.0.0")
 */
class UrlController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/create",
     *     summary="Create a new shortened URL",
     *     tags={"URL"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="original_url", type="string", format="url", example="https://www.example.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Successfully created shortened URL",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object", ref="#/components/schemas/Url")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Invalid input."),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="An unexpected error occurred."),
     *             @OA\Property(property="errors", type="string")
     *         )
     *     )
     * )
     */
    public function create(Request $request)
    {
        try {
            // Validar la URL original
            $request->validate([
                'original_url' => 'required|url'
            ]);

            // Crear una nueva instancia de la URL
            $url = new Url();
            $url->original_url = $request->input('original_url');

            // Generar una URL corta única de 8 caracteres
            do {
                $url->shortened_url = $this->generateShortUrl(8);
            } while (Url::where('shortened_url', $url->shortened_url)->exists());

            $url->save();

            return response()->json([
                'success' => true,
                'data' => $url
            ], 201);  // HTTP status code 201 Created

        } catch (\Illuminate\Validation\ValidationException $e) {
            // Manejar errores de validación
            return response()->json([
                'success' => false,
                'message' => 'Invalid input.',
                'errors' => $e->errors()
            ], 422);  // HTTP status code 422 Unprocessable Entity

        } catch (QueryException $e) {
            // Manejar errores de base de datos
            return response()->json([
                'success' => false,
                'message' => 'Database error.',
                'errors' => $e->getMessage()
            ], 500);  // HTTP status code 500 Internal Server Error

        } catch (\Exception $e) {
            // Manejar errores generales
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred.',
                'errors' => $e->getMessage()
            ], 500);  // HTTP status code 500 Internal Server Error
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/delete/{id}",
     *     summary="Delete a shortened URL by ID",
     *     tags={"URL"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the URL to delete",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successfully deleted URL",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="URL eliminada")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="URL not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Not Found")
     *         )
     *     )
     * )
     */
    public function delete($id)
    {
        try {
            $url = Url::findOrFail($id);
            $url->delete();

            return response()->json(['message' => 'URL eliminada']);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Not Found'], 404);
        } catch (QueryException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Database error.',
                'errors' => $e->getMessage()
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred.',
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/history",
     *     summary="Get the history of all shortened URLs",
     *     tags={"URL"},
     *     @OA\Response(
     *         response=200,
     *         description="Successfully retrieved URL history",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/Url")
     *         )
     *     )
     * )
     */
    public function history()
    {
        $urls = Url::all();
        return response()->json($urls);
    }

    /**
     * @OA\Get(
     *     path="/api/{shortened_url}",
     *     summary="Redirect to the original URL using the shortened URL",
     *     tags={"URL"},
     *     @OA\Parameter(
     *         name="shortened_url",
     *         in="path",
     *         required=true,
     *         description="Shortened URL to redirect",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=302,
     *         description="Redirect to original URL"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Shortened URL not found",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Not Found")
     *         )
     *     )
     * )
     */
    public function redirect($shortened_url)
    {
        try {
            // Buscar la URL corta en la base de datos
            $url = Url::where('shortened_url', $shortened_url)->firstOrFail();

            // Redirigir a la URL original
            return redirect($url->original_url);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['message' => 'Not Found'], 404);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred.',
                'errors' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Schema(
     *     schema="Url",
     *     type="object",
     *     required={"original_url", "shortened_url"},
     *     @OA\Property(property="id", type="integer", example=1),
     *     @OA\Property(property="original_url", type="string", format="url", example="https://www.example.com"),
     *     @OA\Property(property="shortened_url", type="string", example="abcd1234"),
     *     @OA\Property(property="created_at", type="string", format="date-time", example="2024-01-01T00:00:00Z"),
     *     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-01-01T00:00:00Z")
     * )
     */
    private function generateShortUrl($length)
    {
        // Define un conjunto de caracteres fáciles de leer
        $characters = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789';
        $charactersLength = strlen($characters);
        $shortUrl = '';

        for ($i = 0; $i < $length; $i++) {
            $shortUrl .= $characters[rand(0, $charactersLength - 1)];
        }

        return $shortUrl;
    }
}
