<?php

namespace App\Http\Controllers;

use App\Config;
use Illuminate\Http\Request;
use App\Http\Requests\ConfigRequest;

class ConfigController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }
    
    public function edit()
    {
        $this->authorize('logado');
        $config = Config::orderByDesc('created_at')->first();
        if(!$config) $config =  new Config;
        return view('configs.edit', compact('config'));
    }

    public function store(ConfigRequest $request)
    {
        $this->authorize('logado');
        $validated = $request->validated();
        Config::create($validated);
        return redirect('/configs');
    }
}
