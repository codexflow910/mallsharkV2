<?php

namespace Packages\Core\Repositories\Eloquent\V4;

use Packages\Core\Contracts\V4\CoreRepositoryInterface;
use Packages\Core\Models\V4\category;

class CategoryRepository implements CoreRepositoryInterface
{

    protected $model;

    public function __construct(Category $model)
    {
        $this->model = $model;
    }

    
}
