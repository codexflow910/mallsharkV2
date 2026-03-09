<?php

namespace Packages\Klavio\Http\Controllers;


use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Controllers\Controller;
use Packages\Klavio\Services\KlavioService;
use Illuminate\Support\ItemNotFoundException;
use Illuminate\Validation\ValidationException;
use Packages\Klavio\Contracts\BaseApiResponse;
use Packages\Klavio\Http\Requests\KlavioRequest;

class KlavioController extends Controller
{
    use BaseApiResponse;


    protected $klavioService;

    public function __construct(KlavioService $klavioService)
    {
        $this->klavioService = $klavioService;
    }


    public function subscribe(KlavioRequest $request)
    {
        try {

            $data = $request->validated();

            $response = $this->klavioService->subscribe($data);

            return $this->successResponse($response, 'Subscribed successfully', Response::HTTP_CREATED);
        } catch (Exception $e) {

            return $this->errorResponse($e->getMessage());
        }
    }

    public function addToList()
    {
        try {
            $this->klavioService->addToList();
        } catch (Exception $e) {

            //check exception type and return reponse based on that using errorResponse method

            if ($e instanceof ItemNotFoundException) {
                return $this->notFoundResponse($e->getMessage());
            }
            return $this->errorResponse($e->getMessage());
        }
    }
    public function addToMetrice(KlavioRequest $request)
    {
        try {
            $this->klavioService->addToMetrice();
        } catch (Exception $e) {

            //check exception type and return reponse based on that using errorResponse method

            if ($e instanceof ItemNotFoundException) {
                return $this->notFoundResponse($e->getMessage());
            }
            return $this->errorResponse($e->getMessage());
        }
    }
}
