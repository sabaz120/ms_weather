<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Routing\Exceptions\InvalidSignatureException;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Symfony\Component\HttpFoundation\Response;
class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    public function render($request, Throwable $exception)
    {
        if ($exception instanceof AuthenticationException) {
            return response()->json([
                'message' => 'No estas autenticado en el sistema.',
                'success' => false
            ], 403);
        } elseif ($exception instanceof AuthorizationException) {
            return response()->json([
                'message' => 'No estas autorizado para realizar esta peticion.',
                'success' => false
            ], 401);
        } elseif ($exception instanceof InvalidSignatureException) {
            return response()->json([
                'message' => 'La url no es valida',
                'success' => false
            ], 400);
        } elseif ($exception instanceof ValidationException) {
            return response()->json([
                'message' => 'Los datos no son validos',
                'success' => false,
                'data' => $exception->errors()
            ], 400);
        } elseif ($exception instanceof UnauthorizedException) {
            return response()->json([
                'message' => 'No tiene los permisos necesarios para acceder a esta ruta.',
                'success' => false,
            ], 403);
        }

        return parent::render($request, $exception);
    }

    protected function unauthenticated($request, AuthenticationException $exception)
    {

        // Here you can return your own response or work with request
        // return response()->json(['status' : false], 401);

        // This is the default
        return $request->expectsJson()
            ? response()->json(['message' => $exception->getMessage()], 401)
            : redirect()->guest($exception->redirectTo() ?? route('index'));
    }
}
