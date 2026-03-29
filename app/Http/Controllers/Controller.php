<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

abstract class Controller
{
    protected function respond(array $data = [], int $status = Response::HTTP_OK): JsonResponse
    {
        return response()->json($data, $status);
    }
}
