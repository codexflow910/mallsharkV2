<?php

namespace App\Http\Controllers;

use File;
use Redirect;
use App\Models\Post;
use App\Models\BlogCategory;
use App\Models\PostCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Repositories\Post\Postsaccess;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PostController extends Controller
{
    private $_request;
    private $_postsobj;
    private $_category;
    private $_post;
    private $_postCategory;

    public function __construct(Request $request)
    {
        $this->_postsobj = new Postsaccess();
        $this->_category = new BlogCategory();
        $this->_post = new Post();
        $this->_postCategory = new PostCategory();
        $this->_request = $request;
    }

    /**
     * Display a listing of the All Post resource.
     *
     */
    public function index()
    {
        try {
            $posts = Post::with('categories')->orderByDesc('id')->paginate(10); // Retrieve 10 posts per page
            return view('admin.articles.allpost', ['posts' => $posts]);
        } catch (\Throwable $th) {
            echo $th->getMessage();
        }
    }


    /**
     * Show the Post form for creating a new Post resource.
     *
     */
    public function createPost()
    {
        return view('admin.articles.post');
    }

    /**
     * Post a newly created resource in storage.
     *
     */
    public function storePost()
    {
        $postedData = $this->_request->input();
        try {
            $response = $this->_postsobj->savePost($postedData);
            if ($response == 'save') {
                $this->_request->session()->flash('message_success', 'Post is Created Successully');
                return redirect('/admin/articles');
            } else {
                return Redirect::back()->with('message_error', 'Please check the post, the post are not inserted');
            }
        } catch (\Throwable $th) {
            echo $th->getMessage();
        }
    }

    /**
     * Show the form for editing Post the specified resource.
     *
     * @param  int  $id
     */
    public function editPost($id)
    {
        try {
            $postdata = Post::with('categories')->findOrFail($id);
            return view('admin.articles.post', ["actionUrl" => '/admin/updatepost', 'title' => "Edit Post", 'postData' => $postdata,]);
        } catch (ModelNotFoundException $exception) {
            echo  $exception->getMessage();
        }
    }

    /**
     * Update the specified Post resource in storage.
     *
     */
    public function updatePost()
    {
        if ($this->_request->isMethod('post')) {
            $validateUser = Validator::make(
                $this->_request->all(),
                [
                    'title' => 'required',
                    'postCategory' => 'required',
                ]
            );

            if ($validateUser->fails()) {
                $errors = $validateUser->errors();
                $response = [
                    'status' => 'error',
                    'message' => 'Validation failed',
                    'errors' => $errors->all(),
                ];

                return response()->json($response, 422);
            }
            $postedData = $this->_request->input();

            try {
                $response = $this->_postsobj->updatePostData($postedData);
                if ($response) {
                    $this->_request->session()->flash('message_success', 'Post has been Updated successfully.');
                    return redirect('/admin/articles');
                } else {
                    $this->_request->session()->flash('message_error', 'Post has been not Updated.');
                    return redirect()->back();
                }
            } catch (\Throwable $th) {
                echo $th->getMessage();
            }
        }
    }

    /**
     * Upload the Image Post the specified resource.
     *
     */
    public function uploadPostAvatar()
    {
        $data = array();
        $img = str_replace('data:image/png;base64,', '', $this->_request->input('ImageData'));
        $img = str_replace(' ', '+', $img);
        $data = base64_decode($img);

        $fileName = $this->_postsobj->generateRandomString(15) . '.png';
        $fileName = 'cropImage--_--' . $this->_postsobj->generateRandomString(15) . '.png';
        $destinationPath = public_path() . '/uploads/temp';
        if (!File::exists($destinationPath)) {
            File::makeDirectory($destinationPath, 0775, true);
        }

        $file = $destinationPath . '/' . $fileName;
        file_put_contents($file, $data);

        $attachmentId = $this->_postsobj->getAttachmentId($fileName, 'image', true, true);

        $arr['id'] = $attachmentId;
        $arr['image'] = getAttachmentUrlById($attachmentId, 'media', '');
        echo json_encode($arr);
    }


    /**
     * Remove the specified Post resource from storage.
     *
     */
    public function deleteBlogPost()
    {

        $data = $this->_request->input();
        try {

            $response = $this->_postsobj->deletePost($data);
            if ($response) {
                return [
                    'status' => true,
                    'message' => "Post deleted Successfully",
                ];
            } else {
                return [
                    'status' => false,
                    'message' => "There are no Post available"
                ];
            }
        } catch (\Throwable $th) {
            echo $th->getMessage();
        }
    }

    /**
     * Display Post Category of the resource.
     *
     */
    public function createPostCategories()
    {
        try {
            $categories = $this->_category->get();
            return view('admin/articles.categories', ['categories' => $categories]);
        } catch (\Throwable $th) {
            echo $th->getMessage();
        }
    }


    /**
     * Category a newly created resource in storage.
     *
     */
    public function storePostCategories()
    {
        if ($this->_request->isMethod('post')) {
            $alldata = $this->_request->all();
            try {
                $response = $this->_postsobj->saveBlogCategory($alldata);
                if ($response) {
                    return Redirect::back()->with('message_success', 'Post Category is added successully');
                } else {
                    return Redirect::back()->with('message_error', 'Please Add Post Category Name');
                }
            } catch (\Throwable $th) {
                echo $th->getMessage();
            }
        }
    }
    /**
     * Show the form for editing Category the specified resource.
     *
     * @param  int  $id
     */
    public function editPostCategory($id)
    {
        try {
            $categoryData = BlogCategory::findOrFail($id);
            // echo 'Data: <pre>' .print_r($categoryData,true). '</pre>'; die();
        } catch (ModelNotFoundException $exception) {
            echo  $exception->getMessage();
        }

        return view('admin.articles.categories', ["actionUrl" => '/admin/updatecategory', 'title' => "Edit Category", 'categoryData' => $categoryData,]);
    }

    /**
     * Update the specified Category resource in storage.
     *
     */
    public function updateCategory()
    {
        if ($this->_request->isMethod('post')) {

            $postedData = $this->_request->input();

            try {
                $response = $this->_postsobj->updateCategoryData($postedData);
                if ($response) {
                    $this->_request->session()->flash('message_success', 'Category has been Updated successfully.');
                    return redirect('/admin/postcategories');
                } else {
                    $this->_request->session()->flash('message_error', 'Category has been not Updated.');
                    return redirect()->back();
                }
            } catch (\Throwable $th) {
                echo $th->getMessage();
            }
        }
    }

    /**
     * Check the Catgory Name Already Exist then Return False.
     *
     */
    public function checkPostCategoryAvailablity()
    {
        $postedData = $this->_request->input();
        $result = $this->_postsobj->getPostCategoryByname($postedData);
        if ($result == true) {
            echo 'true';
        } else {
            echo 'false';
        }
    }

    /**
     * Remove the specified Post Catgory resource from storage.
     */
    public function deleteCategory()
    {
        try {
            $category_id = $this->_request->input('category_id');

            if ($category_id != '') {
                $related_posts = DB::table('post_category')
                    ->where('category_id', '=', $category_id)
                    ->select('post_id')
                    ->get()
                    ->toArray();

                $category = DB::table('blog_categories')
                    ->where('id', '=', $category_id)
                    ->first();

                if ($category) {
                    if ($category->parent_id) {
                        // Get the parent category
                        $parent_category = DB::table('blog_categories')
                            ->where('id', '=', $category->parent_id)
                            ->first();

                        if ($parent_category) {
                            $category->parentcategoryName = $parent_category->category_name;
                            $category->parentcategoryId = $parent_category->id;
                        }

                        if (count($related_posts) > 0) {

                            foreach ($related_posts as $pr) {
                                $this->_postCategory->where('post_id', '=', $pr->post_id)->update(['category_id' => $category->parentcategoryId]);
                            }
                            DB::table('blog_categories')->where('id', $category_id)->delete();
                            return [
                                'status' => true,
                                'message' => "Category deleted and Associated Posts Assigned to Parent Category",
                            ];
                        } else {
                            DB::table('blog_categories')->where('id', $category_id)->delete();
                            return [
                                'status' => true,
                                'message' => "Category deleted there are no any associated Posts",
                            ];
                        }
                    } else {
                        // Assign "Uncategorized" category
                        $category->new_category_name = "Uncategorized";

                        $unCategorizedCat = getBlogCategoryUncategorized();
                        if (count($related_posts) > 0) {
                            foreach ($related_posts as $pr) {
                                $this->_postCategory->where('post_id', '=', $pr->post_id)->update(['category_id' => $unCategorizedCat->id]);
                            }
                        }
                        DB::table('blog_categories')->where('id', $category_id)->delete();
                        return [
                            'status' => true,
                            'message' => "Category deleted there are no any associated Post",
                        ];
                    }
                }
            } else {
                return [
                    'status' => false,
                    'message' => "There are no category available"
                ];
            }
        } catch (\Throwable $th) {
            return [
                'status' => false,
                'message' => $th->getMessage()
            ];
        }
    }
}
