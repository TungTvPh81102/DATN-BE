<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SpinTypes\StoreSpinTypeRequest;
use App\Http\Requests\Admin\SpinTypes\UpdateSpinTypeRequest;
use App\Models\SpinType;
use Illuminate\Http\Request;

class SpinTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $spinTypes = SpinType::all();
        return view('spin-types.index', compact('spinTypes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSpinTypeRequest $request)
    {
        $request->validated();

        SpinType::create([
            'name' => $request->name,
            'display_name' => $request->display_name,
        ]);

        return redirect()->route('admin.spin-types.index')->with('success', 'Thêm loại phần thưởng thành công');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSpinTypeRequest $request, string $id)
    {
        $spinType = SpinType::findOrFail($id);

        $request->validated();

        $spinType->update([
            'name' => $request->name,
            'display_name' => $request->display_name,
        ]);

        return redirect()->route('admin.spin-types.index')->with('success', 'Cập nhật loại phần thưởng thành công');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SpinType $spinType)
    {
        $spinType->delete();
        return redirect()->route('admin.spin-types.index')->with('success', 'Xóa loại phần thưởng thành công');
    }
}
