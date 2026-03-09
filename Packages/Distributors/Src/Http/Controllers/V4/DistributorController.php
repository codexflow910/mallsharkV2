<?php

namespace Packages\Distributors\Http\Controllers\V4;

use Exception;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ItemNotFoundException;
use Illuminate\Validation\ValidationException;
use Packages\Distributors\Contracts\V4\BaseApiResponse;
use Packages\Distributors\Http\Requests\V4\StoreRequest;
use Packages\Distributors\Http\Requests\V4\UpdateRequest;
use Packages\Distributors\Services\V4\DistributorService;

class DistributorController extends Controller
{
    use BaseApiResponse;
    protected $distributorService;

    public function __construct(DistributorService $distributorService)
    {
        $this->distributorService = $distributorService;
    }

    public function list(Request $request)
    {
        try {
            $requestData = $request->all();

            $distributors = $this->distributorService->list($requestData['id'] ?? null);

            return $this->successResponse($distributors, 'Distributor list retrieved successfully');
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function create(StoreRequest $request)
    {
        try {
            $data = $request->validated();

            $distributor = $this->distributorService->create($data);

            return $this->createdResponse($distributor, 'Distributor data has been added successfully');
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function update(UpdateRequest $request, $id)
    {
        try {
            $data = $request->all();

            $distributor = $this->distributorService->update($data, $id);

            return $this->successResponse($distributor, 'Distributor data has been updated successfully');
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (ValidationException $e) {
            return $this->validationErrorResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function delete(Request $request)
    {
        try {
            $validation = Validator::make($request->all(), [
                'distributor_document_id' => 'required|integer|exists:distributor_documents,id'
            ]);
            
            if ($validation->fails()) {
                return $this->validationErrorResponse($validation->errors());
            }
            
            $distributorDocumentId = $request->input('distributor_document_id');
            
            $response = $this->distributorService->delete($distributorDocumentId);
            return $this->deletedResponse('Distributor deleted successfully');
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function changeDistributorStatus(Request $request)
    {
        try {
            $validation = Validator::make($request->all(), [
                'distributor_document_id' => 'required|integer|exists:distributor_documents,id',
                'status'      => 'required|string|in:approved,rejected',
                'note'        => 'nullable|string|max:500',
            ]);

            if ($validation->fails()) {
                return $this->validationErrorResponse($validation->errors());
            }

            $validated = $validation->validated();

            $status = $validated['status'];
            $note = $validated['note'];
            $distributorDocumentId = $validated['distributor_document_id'];
            $response = $this->distributorService->changeDistributorStatus($distributorDocumentId, $status, $note);

            return $this->successResponse($response, "Distributor document status been updated successfully: " . ucfirst($status));
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function changeDocumentStatus(Request $request)
    {
        try {
            $validation = Validator::make($request->all(), [
                'document_id' => 'required|integer|exists:distributor_documents_statuses,id',
                'status'      => 'required|string|in:approved,rejected',
            ]);

            if ($validation->fails()) {
                return $this->validationErrorResponse($validation->errors());
            }

            $validated = $validation->validated();

            $status = $validated['status'];
            $documentId = $validated['document_id'];;
            $response = $this->distributorService->changeDocumentStatus($documentId, $status);

            return $this->successResponse($response, "Distributor document status been updated successfully: " . ucfirst($status));
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function getAllDistributors(Request $request)
    {
        try {
            $requestData = $request->all();
            $status = $requestData['status'] ?? null;
            
            $distributors = $this->distributorService->getAllDistributors($status);
            return view('admin/distributor-onboarding', compact('distributors'));
        } catch (ItemNotFoundException $e) {
            $distributors = [];
            return view('admin/distributor-onboarding', compact('distributors'));
        } catch (Exception $e) {
            $distributors = [];
            return view('admin/distributor-onboarding', compact('distributors'));
        }
    }

    public function getDistributorDetail(Request $request, $id)
    {
        try {
            
            $distributors = $this->distributorService->getDistributorDetail($id);
            $brands = $this->distributorService->getBrands();
            return view('admin/distributor-detail', compact('distributors', 'brands'));
        } catch (ItemNotFoundException $e) {
            $distributors = [];
            $brands = [];
            return view('admin/distributor-detail', compact('distributors', 'brands'));
        } catch (Exception $e) {
            $distributors = [];
            $brands = [];
            return view('admin/distributor-detail', compact('distributors', 'brands'));
        }
    }

    public function getDistributorBrandsList(Request $request)
    {
        try {
            $requestData = $request->all();
            $brandId = $requestData['brand_id'] ?? null;
            
            $brands = $this->distributorService->getBrandDistributors($brandId);
            
            return view('admin/distributor-brands', compact('brands'));
        } catch (ItemNotFoundException $e) {
            $brands = [];
            return view('admin/distributor-brands', compact('brands'));
        } catch (Exception $e) {
            $brands = [];
            return view('admin/distributor-brands', compact('brands'));
        }
    }

    public function assignBrandToDistributor(Request $request)
    {
        try {
            $validation = Validator::make($request->all(), [
                'distributor_document_id' => 'required|integer|exists:distributor_documents,id',
                'brand_id'      => 'integer|exists:brands,id'
            ]);

            if ($validation->fails()) {
                return $this->validationErrorResponse($validation->errors());
            }

            $validated = $validation->validated();

            $distributorDocumentId = $validated['distributor_document_id'];
            $brandId = $validated['brand_id'];
            $response = $this->distributorService->assignBrandToDistributor($distributorDocumentId, $brandId);

            return $this->successResponse($response, "Brand has been assigned to distributor successfully.");
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function registerBrand(Request $request)
    {
        try {
            $validation = Validator::make($request->all(), [
                'name' => 'required|string',
                'logo' => [
                    'required',
                    function ($attribute, $value, $fail) {
                        // Case 1: If it's numeric, treat as an existing file ID
                        if (is_numeric($value)) {
                        }

                        // Case 2: If it's an uploaded file, validate file type/size
                        elseif ($value instanceof \Illuminate\Http\UploadedFile) {
                            $mime = $value->getMimeType();
                            $size = $value->getSize();

                            $allowedMimes = ['image/png', 'image/jpeg', 'image/webp', 'image/jpg'];
                            $maxSize = 5 * 1024 * 1024; // 5 MB

                            if (!in_array($mime, $allowedMimes)) {
                                $fail('The logo must be a file of type: png, jpg, jpeg, or webp.');
                            }

                            if ($size > $maxSize) {
                                $fail('The logo file may not be greater than 5MB.');
                            }
                        }

                        // Case 3: Invalid value (neither file nor numeric ID)
                        else {
                            $fail('The logo must be either an uploaded image or an existing file ID.');
                        }
                    },
                ],
                'url'  => 'required|string|unique:brands,url',
                'distributor_document_id' => 'nullable|integer|exists:distributor_documents,id',
            ]);

            if ($validation->fails()) {
                return $this->validationErrorResponse($validation->errors());
            }

            $validated = $validation->validated();

            $name = $validated['name'];
            $logo = $validated['logo'];
            $url = $validated['url'];
            $distributorDocumentId = $validated['distributor_document_id'] ?? null;
            $response = $this->distributorService->registerBrand($name, $logo, $url);

            if ($distributorDocumentId) {
                $this->distributorService->assignBrandToDistributor($distributorDocumentId, $response->id);
            }

            return $this->successResponse($response, "Brand has been registered successfully.");
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function getBrands(Request $request)
    {
        try {
            $distributorId = $request->input('distributor_id') ?? null;
            $response = $this->distributorService->getBrands($distributorId);
            return $this->successResponse($response, 'Brand list retrieved successfully');
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function getBrandDistributors(Request $request)
    {
        try {
            $requestData = $request->all();
            $brandId = $requestData['brand_id'] ?? null;
            
            $distributors = $this->distributorService->getBrandDistributors($brandId);
            return $this->successResponse($distributors, 'Brand distributors retrieved successfully');
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
