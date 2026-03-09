<?php

namespace Packages\Import\Http\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Response;
use InvalidArgumentException;
use Packages\Import\Contracts\BaseApiResponse;
use Packages\Import\Services\CsvService;

use Packages\Import\Http\Requests\CsvUploadRequest;

class CsvController extends Controller
{
    use BaseApiResponse;

    protected $csvService;

    public function __construct(CsvService $csvService)
    {
        $this->csvService = $csvService;
    }

    public function bulkUpdate(CsvUploadRequest $request)
    {
        ini_set('max_execution_time', 0);
    
        try {
            $csvFile = $request->file('csv_file');
            $rows = $this->csvService->validateCsvFile($csvFile); 
    
            list($updates, $errors) = $this->csvService->updateProducts($rows);
            
            if (!empty($errors)) {
                return $this->errorResponse(['errors' => $errors], Response::HTTP_BAD_REQUEST);
            }
    
            return $this->successResponse('All products updated successfully', Response::HTTP_OK);
            
        } catch (InvalidArgumentException $e) {

            return $this->errorResponse(json_decode($e->getMessage(), true), Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (Exception $e) {

            return $this->errorResponse($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
    
}
