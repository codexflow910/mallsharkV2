<?php

namespace Packages\Core\Repositories\Eloquent\V3;

use Packages\Core\Contracts\V3\CoreRepositoryInterface;
use Packages\Core\Models\V3\category;

class CategoryRepository implements CoreRepositoryInterface
{

    protected $model;

    public function __construct(Category $model)
    {
        $this->model = $model;
    }

    
}
