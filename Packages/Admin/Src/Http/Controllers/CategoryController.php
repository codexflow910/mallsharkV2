<?php

namespace Packages\Admin\Http\Controllers;


use App\Http\Controllers\Controller;
use Exception;
use Facade\FlareClient\Http\Exceptions\NotFound;
use Packages\Admin\Requests\Auth\LoginRequest;
use Packages\Admin\Contracts\ProductRepositoryInterface;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\ItemNotFoundException;
use InvalidArgumentException;
use Nette\Schema\Elements\Base;
use Packages\Admin\Http\Requests\StoreRequest;
use Packages\Admin\Contracts\BaseApiResponse;
use Packages\Admin\Services\ProductTypes\SimpleProductService;
use Packages\Admin\Services\ProductTypes\VariableProductService;

class CategoryController extends Controller
{
    use BaseApiResponse;

    public function __construct()
    {
        
    }

    
 
}
