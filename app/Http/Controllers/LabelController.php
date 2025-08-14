<?php

namespace App\Http\Controllers;

use App\Models\Label;
use Illuminate\Http\Request;

class LabelController extends Controller
{
    public function index()
    {
        $labels = Label::withCount('donors')->paginate(15);
        return view('labels.index', compact('labels'));
    }

    public function create()
    {
        return view('labels.create');
    }

    public function show(Label $label)
    {
        $label->load('donors');
        return view('labels.show', compact('label'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'label_name' => 'required|string|max:255|unique:labels',
            'label_code' => 'nullable|string|max:255|unique:labels',
        ]);

        $data = $request->all();
        if (empty($data['label_code'])) {
            $data['label_code'] = strtoupper(substr($data['label_name'], 0, 3)) . rand(100, 999);
        }

        Label::create($data);

        return redirect()->route('labels.index')->with('success', 'Label created successfully.');
    }

    public function edit(Label $label)
    {
        return view('labels.edit', compact('label'));
    }

    public function update(Request $request, Label $label)
    {
        $request->validate([
            'label_name' => 'required|string|max:255|unique:labels,label_name,' . $label->id,
            'label_code' => 'nullable|string|max:255|unique:labels,label_code,' . $label->id,
        ]);

        $data = $request->all();
        if (empty($data['label_code'])) {
            $data['label_code'] = strtoupper(substr($data['label_name'], 0, 3)) . rand(100, 999);
        }

        $label->update($data);

        return redirect()->route('labels.index')->with('success', 'Label updated successfully.');
    }

    public function destroy(Label $label)
    {
        $label->delete();
        return redirect()->route('labels.index')->with('success', 'Label deleted successfully.');
    }
} 