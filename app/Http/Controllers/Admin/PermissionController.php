<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $permissions = Permission::orderBy('permission_id', 'DESC')->paginate(10);
        return view('permissions.index', compact('permissions'))
            ->with('i', ($request->input('page', 1) - 1) * 10);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // 
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'display_name' => 'required|string|max:255|unique:permissions,display_name',
                'group_name'   => 'nullable|string|max:255',
            ]);
        } catch (ValidationException $e) {
            return back()->with('error', 'Validation failed: ' . implode(', ', $e->validator->errors()->all()))
                        ->withInput();
        }


        Permission::create([
            'name'         => \Str::slug($request->display_name), // e.g. Edit User -> edit-user
            'display_name' => $request->display_name,
            'group_name'   => $request->group_name,
            // 'gaurd_name'   => 'web',
        ]);

        return back()->with('success', 'Permission created successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $permission = Permission::findOrFail($id);

        // Check if assigned
        if ($permission->roles()->exists()) {
            return back()->with('error', 'Permission is assigned to a role, cannot update name.');
        }

        try {
            $request->validate([
                'display_name' => 'required|string|max:255|unique:permissions,display_name,' . $permission->permission_id . ',permission_id',
                'group_name'   => 'nullable|string|max:255',
            ]);
        } catch (ValidationException $e) {
            return back()->with('error', 'Validation failed: ' . implode(', ', $e->validator->errors()->all()))
                        ->withInput();
        }

        $permission->update([
            'name'         => \Str::slug($request->display_name),
            'display_name' => $request->display_name,
            'group_name'   => $request->group_name,
        ]);

        return back()->with('success', 'Permission updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $permission = Permission::findOrFail($id);

        if ($permission->roles()->exists()) {
            return back()->with('error', 'Permission is assigned to a role, cannot delete.');
        }

        $permission->delete();
        return back()->with('success', 'Permission deleted successfully.');
    }
}
