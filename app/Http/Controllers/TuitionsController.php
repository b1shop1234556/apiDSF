<?php

namespace App\Http\Controllers;

use App\Models\tuitions;
use App\Http\Requests\StoretuitionsRequest;
use App\Http\Requests\UpdatetuitionsRequest;

class TuitionsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoretuitionsRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(tuitions $tuitions)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatetuitionsRequest $request, tuitions $tuitions)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(tuitions $tuitions)
    {
        //
    }
}
