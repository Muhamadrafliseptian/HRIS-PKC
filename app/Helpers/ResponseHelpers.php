<?php

function successHandler($params = null, $message = 'Success')
{
      $response = [
            'status' => true,
            'validation_errors' => null,
            'message' => $message,
            'params' => $params
      ];

      return response()->json($response, 200);
}

function errorValidationHandler($ev = null)
{
      $error = [
            'status' => false,
            'message' => 'Validation Error',
            'validation_errors' => $ev->errors(),
            'params' => null
      ];
      return response()->json($error, 400);
}

function errorHandler($e)
{
      $code = 500;
      if ($e->getCode() == 422) {
            $code = 422;
      }

      if ($code == 500) {
            report($e);
      }

      $error = [
            'status' => false,
            'message' => $e->getMessage(),
            'validation_errors' => null,
            'params' => null
      ];
      return response()->json($error, $code);
}