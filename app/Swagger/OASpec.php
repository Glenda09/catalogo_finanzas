<?php

namespace App\Swagger;

/**
 * @OA\Info(
 *     title="API de Cursos de Idiomas",
 *     version="1.0.0",
 *     description="Documentación de la API"
 * )
 *
 * @OA\Components(
 *     @OA\SecurityScheme(
 *         securityScheme="bearerAuth",
 *         type="http",
 *         scheme="bearer",
 *         bearerFormat="JWT",
 *         description="Introduce el token JWT aquí. Ejemplo: Bearer {token}"
 *     )
 * )
 *
 * @OA\SecurityRequirement(
 *     {
 *         "bearerAuth": {}
 *     }
 * )
 */
class OASpec {}
