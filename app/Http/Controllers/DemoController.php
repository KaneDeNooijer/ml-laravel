<?php

namespace App\Http\Controllers;

use App\Repositories\DemoRepository;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DemoController extends Controller
{
    public function __invoke(Request $request, DemoRepository $repository): View
    {
        $meal = $repository->currentMeal($request->string('meal')->toString() ?: null);

        return view('demo', ['demo' => $repository->state($meal)]);
    }
}
