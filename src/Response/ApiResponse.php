<?php

namespace App\Response;

use Symfony\Component\HttpFoundation\JsonResponse;

class ApiResponse
{
    public const SUCCESS_RESPONSE_CODE = 200;

    public const VALIDATE_ERROR_RESPONSE_CODE = 400;
    public const INTERNAL_ERROR_RESPONSE_CODE = 500;
    public const SUCCESS_STATUS = 'success';
    public const ERROR_STATUS = 'error';
    private int $code;
    private string $status;
    private array $data = [];
    private array $errors = [];

    /**
     * @param int $code
     * @param string $status
     */
    public function __construct(int $code = self::SUCCESS_RESPONSE_CODE, string $status = self::SUCCESS_STATUS)
    {
        $this->code = $code;
        $this->status = $status;
    }
    public function setCode(int $code): ApiResponse
    {
        $this->code = $code;
        return $this;
    }
    public function setStatus(string $status): ApiResponse
    {
        $this->status = $status;
        return $this;
    }
    public function setData(?array $data): ApiResponse
    {
        $this->data = $data;
        return $this;
    }
    public function setErrors(?array $errors): ApiResponse
    {
        $this->errors = $errors;
        return $this;
    }


    public function getResponse(): JsonResponse
    {
        return new JsonResponse([
            'status' => $this->status,
            'data' => $this->data,
            'errors' => $this->errors
        ],$this->code);
    }
}