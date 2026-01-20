<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Insight;
use Illuminate\Http\Request;

class InsightController extends Controller
{
    public function index()
    {
        return Insight::paginate();
    }

    public function show($id)
    {
        return Insight::findOrFail($id);
    }
}
