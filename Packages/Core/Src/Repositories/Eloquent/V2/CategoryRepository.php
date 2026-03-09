<?php

namespace Packages\Core\Repositories\Eloquent\V2;

use Packages\Core\Contracts\V2\CoreRepositoryInterface;
use Packages\Core\Models\V2\Category;

class CategoryRepository implements CoreRepositoryInterface
{

    protected $model;

    public function __construct(Category $model)
    {
        $this->model = $model;
    }

    
}
