<?php

namespace Packages\Core\Repositories\Eloquent;

use Packages\Core\Contracts\CoreRepositoryInterface;
use Packages\Core\Models\Category;

class CategoryRepository implements CoreRepositoryInterface
{

    protected $model;

    public function __construct(Category $model)
    {
        $this->model = $model;
    }

    
}
