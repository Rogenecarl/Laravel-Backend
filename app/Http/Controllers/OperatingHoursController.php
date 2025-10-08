<?php

namespace App\Http\Controllers;

use App\Services\OperatingHoursService;
use Illuminate\Http\Request;

class OperatingHoursController extends Controller
{
    protected OperatingHoursService $operatingHoursService;

    public function __construct(OperatingHoursService $operatingHoursService)
    {
        $this->operatingHoursService = $operatingHoursService;
    }

    public function index(Request $request)
    {
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
