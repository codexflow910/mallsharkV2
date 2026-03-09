<?php

namespace Packages\Admin\Http\Controllers\V3;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ItemNotFoundException;
use Packages\Admin\Contracts\V3\BaseApiResponse;
use Packages\Admin\Models\V3\CategoryTag;
use Packages\Admin\Services\V3\AdminService;

class CategoryTagController extends Controller
{
    use BaseApiResponse;

    protected $adminService;

    public function __construct(AdminService $adminService)
    {
        $this->adminService = $adminService;
    }
    public function getCategoryTags($categoryId)
    {
        try {
            $tags = $this->adminService->getCategoryTags($categoryId);
          
            if (!$tags) {
                throw new ItemNotFoundException('Tags not found');
            }

            return $this->successResponse($tags, 'Tags found successfully');
            
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function createTag(Request $request)
    {
        try {
            $validation = Validator::make(
                $request->all(),
                [
                    'name' => 'required|string|max:255',
                    'category_id' => 'required|exists:categories,id',
                ]
            );
    
            if ($validation->fails()) {
                return response()->json([
                    'status' => false,
                    'message' => 'Validation error',
                    'errors' => $validation->errors()
                ], 422);
            }

            $tags = $this->adminService->createTag($request->all());

            return $this->createdResponse($tags, 'Tag created successfully');
            
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    public function deleteTag($id)
    {
        try {
            $this->adminService->deleteTag($id);

            return $this->deletedResponse('Tag deleted successfully');
        } catch (ItemNotFoundException $e) {
            return $this->notFoundResponse($e->getMessage());
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
