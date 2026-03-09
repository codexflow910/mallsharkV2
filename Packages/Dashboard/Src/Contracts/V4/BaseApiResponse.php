<?php

namespace Packages\Dashboard\Contracts\V4;

use Illuminate\Http\Response;

trait BaseApiResponse
{
    protected function successResponse($data, $message = null, $code = Response::HTTP_OK)
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'data' => $data
        ], $code);
    }

    protected function createdResponse($data, $message = 'Record created successfully')
    {
        return $this->successResponse($data, $message, Response::HTTP_CREATED);
    }

    protected function updatedResponse($data, $message = 'Record updated successfully')
    {
        return $this->successResponse($data, $message);
    }

    protected function deletedResponse($message = 'Record deleted successfully')
    {
        return response()->json([
            'status' => true,
            'message' => $message,
        ], Response::HTTP_OK);
    }

    protected function errorResponse($message, $code = Response::HTTP_INTERNAL_SERVER_ERROR)
    {
        return response()->json([
            'status' => false,
            'message' => $message,
            'data' => null
        ], $code);
    }

    protected function validationErrorResponse($errors, $message = 'Validation errors')
    {
        return response()->json([
            'status' => false,
            'message' => $message,
            'errors' => $errors
        ], Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    protected function notFoundResponse($message = 'Record not found')
    {
        return response()->json([
            'status' => false,
            'message' => $message,
        ], Response::HTTP_NOT_FOUND);
    }
}