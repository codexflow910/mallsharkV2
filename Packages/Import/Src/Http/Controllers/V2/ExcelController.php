<?php

namespace Packages\Import\Http\Controllers\V2;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Response;
use InvalidArgumentException;
use Packages\Import\Contracts\V2\BaseApiResponse;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Packages\Product\Models\V2\Product;
use Packages\Import\Services\V2\ExcelService;
use Illuminate\Support\Facades\Log;
use Packages\Import\Contracts\V2\ExcelHelpers;
class ExcelController extends Controller
{
    use BaseApiResponse;
    use ExcelHelpers;

    protected $excelService;

    public function __construct(ExcelService $excelService)
    {
        $this->excelService = $excelService;
    }

    public function download(Request $request)
    {
        $data = $request->all([]);
        return $this->excelService->downloadSampleExcel($data);
    }

    public function bulkUpload(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls'
        ]);
        try {
            $result = $this->excelService->bulkUpdateProducts($request->file('file'));

            if (!empty($result['errors'])) {
                return $this->errorResponse($result, Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            return $this->successResponse(
                "Products updated successfully. Updated: {$result['updated_count']}",
                Response::HTTP_OK
            );
        } catch (InvalidArgumentException $e) {
            return $this->errorResponse(json_decode($e->getMessage(), true), Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}