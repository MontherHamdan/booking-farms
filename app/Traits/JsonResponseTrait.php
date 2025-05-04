<?php

namespace App\Traits;

trait JsonResponseTrait
{

    public function successResponse($success , $results, $error ,$code = 200)
    {
        return response()->json([
             'success' => $success,
             'results' => $results ?? "",
             'error' => $error ?? "",
        ], $code);
    }

    public function errorResponse($error ,$code = 422)
    {
        return response()->json([
            'success' => false,
            'results' => "",
            'error' => $error ,
        ], $code);
    }
}
