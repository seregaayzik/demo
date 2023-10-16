<?php

namespace App\Controller;

use App\Entity\User;
use App\Formatter\ValidationResultFormatter;
use App\Model\DbCriteria;
use App\Response\ApiResponse;
use App\Response\ApiResponseBuilder;
use App\Serializer\SerializerFactory;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Exception\NotEncodableValueException;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use OpenApi\Attributes as OA;
use Nelmio\ApiDocBundle\Annotation\Model;

#[Route('/api')]
class UserController extends AbstractController
{
    private Request $_request;
    private UserService $_userService;
    private SerializerInterface $_serializer;
    private ValidationResultFormatter $_validationResultFormatter;
    private ValidatorInterface $_validator;


    public function __construct(
        SerializerFactory $serializer,
        UserService $userService,
        ValidationResultFormatter $validationResultFormatter,
        ValidatorInterface $validator
    ){
          $this->_serializer = $serializer->create() ;
          $this->_userService = $userService;
          $this->_validationResultFormatter = $validationResultFormatter;
          $this->_validator = $validator;
    }
    #[Route('/user', name: 'list', methods: ['GET'])]
    #[
        OA\Parameter(
            name: 'offset',
            description: 'Starting from the record',
            in: 'query',
            schema: new OA\Schema(type: 'integer')
        ),
        OA\Parameter(
            name: 'limit',
            description: 'Limit of records',
            in: 'query',
            schema: new OA\Schema(type: 'integer')
        ),
        OA\Parameter(
            name: 'query',
            description: 'Search Query by email/first name/last name fields',
            in: 'query',
            schema: new OA\Schema(type: 'string')
        ),
        OA\Response(
            response: 200,
            description: 'Successful response',
            content: new OA\JsonContent(
                type: 'array',
                items: new OA\Items(
                    properties: [
                        new OA\Property(
                            property: "status",
                            type: "string",
                            example: "Success"
                        ),
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items(ref: new Model(type: User::class))
                        ),
                        new OA\Property(
                            property: "errors",
                            type: "array",
                            items: new OA\Items()
                        )
                    ],
                    type: 'object',
                )
            )
        )
    ]
    public function list(
        #[MapQueryParameter] ?int $offset,
        #[MapQueryParameter] ?int $limit,
        #[MapQueryParameter] ?string $query
    ): JsonResponse
    {
        $apiResponse = new ApiResponse();
        $dbCriteria = new DbCriteria($offset,$limit,$query);
        $users = $this->_userService->getUsers($dbCriteria);
        $apiResponse->setData($users);
        return $apiResponse->getResponse();
    }
    #[Route('/user', name: 'create',methods: ['POST'])]
    #[
        OA\RequestBody(
            required: true,
            content: new Model(type: User::class, groups: ['api'])
        ),
        OA\Response(
            response: 200,
            description: 'Successful response',
            content: new OA\JsonContent(
                type: 'array',
                items: new OA\Items(
                    properties: [
                        new OA\Property(
                            property: "status",
                            type: "string",
                            example: "success"
                        ),
                        new OA\Property(
                            property: "data",
                            ref: new Model(type: User::class),
                            type: "object"
                        ),
                        new OA\Property(
                            property: "errors",
                            type: "array",
                            items: new OA\Items()
                        )
                    ]
                )
            )
        ),
        OA\Response(
            response: 400,
            description: 'Validation Errors ',
            content: new OA\JsonContent(
                type: 'array',
                items: new OA\Items(
                    properties: [
                        new OA\Property(
                            property: "status",
                            type: "string",
                            example: "error"
                        ),
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items()
                        ),
                        new OA\Property(
                            property: "errors",
                            type: "array",
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(
                                        property: "email",
                                        type: "array",
                                        items: new OA\Items(
                                            type: "string",
                                            example: "This value is already used."
                                        )
                                    ),
                                    new OA\Property(
                                        property: "firstName",
                                        type: "array",
                                        items: new OA\Items(
                                            type: "string",
                                            example: "This value is too short. It should have 3 characters or more."
                                        )
                                    )
                                ],
                                type: 'object'
                            )
                        )
                    ],
                    type: 'object',
                )
            )
        ),
    ]
    public function post(Request $request): JsonResponse
    {
        $user = new User();
        return $this->proceedUserSave($request, $user);
    }

    #[Route('/user/{id}', name: 'update', requirements: ['id' => '\d+'], methods: ['PATCH'])]
    #[
        OA\Parameter(
            name: 'id',
            description: 'ID of a user to edit',
            in: 'path',
            schema: new OA\Schema(type: 'integer')
        ),
        OA\RequestBody(
            required: true,
            content: new Model(type: User::class, groups: ['api'])
        ),
        OA\Response(
            response: 200,
            description: 'Successful response',
            content: new OA\JsonContent(
                type: 'array',
                items: new OA\Items(
                    properties: [
                        new OA\Property(
                            property: "status",
                            type: "string",
                            example: "success"
                        ),
                        new OA\Property(
                            property: "data",
                            ref: new Model(type: User::class),
                            type: "object"
                        ),
                        new OA\Property(
                            property: "errors",
                            type: "array",
                            items: new OA\Items()
                        )
                    ]
                )
            )
        ),
        OA\Response(
            response: 400,
            description: 'Validation Errors ',
            content: new OA\JsonContent(
                type: 'array',
                items: new OA\Items(
                    properties: [
                        new OA\Property(
                            property: "status",
                            type: "string",
                            example: "error"
                        ),
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items()
                        ),
                        new OA\Property(
                            property: "errors",
                            type: "array",
                            items: new OA\Items(
                                properties: [
                                    new OA\Property(
                                        property: "email",
                                        type: "array",
                                        items: new OA\Items(
                                            type: "string",
                                            example: "This value is already used."
                                        )
                                    ),
                                    new OA\Property(
                                        property: "firstName",
                                        type: "array",
                                        items: new OA\Items(
                                            type: "string",
                                            example: "This value is too short. It should have 3 characters or more."
                                        )
                                    )
                                ],
                                type: 'object'
                            )
                        )
                    ],
                    type: 'object',
                )
            )
        ),
        OA\Response(
            response: 404,
            description: 'The user not exists.',
            content: new OA\JsonContent(
                type: 'array',
                items: new OA\Items(
                    properties: [
                        new OA\Property(
                            property: "status",
                            type: "string",
                            example: "error"
                        ),
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items()
                        ),
                        new OA\Property(
                            property: "errors",
                            type: "array",
                            items: new OA\Items(
                                type: "string",
                                example: "The user does not exist"
                            )
                        ),
                    ]
                )
            )
        ),
    ]
    public function update(Request $request,int $id): JsonResponse
    {
        $user = $this->_userService->findById($id);
        if(!$user){
            throw $this->createNotFoundException('The user does not exist');
        }
        return $this->proceedUserSave($request, $user);
    }
    #[Route('/user/{id}', name: 'delete', requirements: ['id' => '\d+'], methods: ['DELETE'])]
    #[
        OA\Response(
            response: 200,
            description: 'Successful response',
            content: new OA\JsonContent(
                type: 'array',
                items: new OA\Items(
                    properties: [
                        new OA\Property(
                            property: "status",
                            type: "string",
                            example: "success"
                        ),
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items()
                        ),
                        new OA\Property(
                            property: "errors",
                            type: "array",
                            items: new OA\Items()
                        )
                    ]
                )
            )
        ),
        OA\Response(
            response: 404,
            description: 'The user not exists.',
            content: new OA\JsonContent(
                type: 'array',
                items: new OA\Items(
                    properties: [
                        new OA\Property(
                            property: "status",
                            type: "string",
                            example: "error"
                        ),
                        new OA\Property(
                            property: "data",
                            type: "array",
                            items: new OA\Items()
                        ),
                        new OA\Property(
                            property: "errors",
                            type: "array",
                            items: new OA\Items(
                                type: "string",
                                example: "The user does not exist"
                            )
                        ),
                    ]
                )
            )
        )
    ]
    public function delete(Request $request,int $id): JsonResponse
    {
        $user = $this->_userService->findById($id);
        if(!$user){
            throw $this->createNotFoundException('The user does not exist');
        }
        $this->_userService->removeUser($user);
        $response = new ApiResponse(
            ApiResponse::SUCCESS_RESPONSE_CODE,
            ApiResponse::SUCCESS_STATUS
        );
        return $response->getResponse();
    }

    private function proceedUserSave(Request $request, User $user): JsonResponse
    {
        $apiResponse = new ApiResponse();
        try {
            $this->_serializer->deserialize(
                $request->getContent(),
                User::class, JsonEncoder::FORMAT,
                [
                    'object_to_populate' => $user,
                    AbstractNormalizer::IGNORED_ATTRIBUTES => ['id', 'timeOfUpdate'],
                ]
            );
            $errors = $this->_validator->validate($user);
            if ($errors->count()) {
                $formattedMessage = $this->_validationResultFormatter->formatResult($errors);
                $apiResponse->setErrors($formattedMessage)->setStatus(ApiResponse::ERROR_STATUS)->setCode(ApiResponse::VALIDATE_ERROR_RESPONSE_CODE);
            } else {
                $changedUser = $this->_userService->saveUser($user);
                $apiResponse->setData($this->_serializer->normalize($changedUser));
            }
        } catch (NotEncodableValueException $e) {
            throw new BadRequestHttpException ('Invalid Request Format');
        }
        return $apiResponse->getResponse();
    }
}
