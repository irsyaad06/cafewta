<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CafeTable;
use Inertia\Inertia;

class TableSimulationController extends Controller
{
    public function index()
    {
        $tables = CafeTable::where('status', 'available')->get();
        return Inertia::render('SimulasiMeja', [
            'tables' => $tables
        ]);
    }
}
