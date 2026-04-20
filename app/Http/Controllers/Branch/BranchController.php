<?php

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Models\Branch;
use Inertia\Inertia;

class BranchController extends Controller
{
    public function index()
    {
        try {
            return Inertia::render("Branch/Index");
        } catch (err) {

        }
    }

    public function read()
    {
        try {
            $branchs = Branch::with(['dtconfig', 'dtprovince', 'dtcity', 'dtdistrict', 'dtvillage'])
                ->get();

            $response = [
                'branchs' => $branchs
            ];

            return successHandler($response);
        } catch (err) {

        }
    }

}
