<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\ProjectAddon;
use Illuminate\Http\Request;

class ProjectAddonController extends Controller
{
    public function store(Request $request, Project $project)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:50',
            'description' => 'nullable|string|max:1000',
            'amount' => 'required|numeric|min:0|max:99999999.99',
            'status' => 'required|in:pending,approved,in_progress,completed,cancelled',
            'requested_date' => 'nullable|date',
            'approved_date' => 'nullable|date',
            'completed_date' => 'nullable|date',
            'notes' => 'nullable|string|max:500',
        ]);

        $project->addons()->create($validated);

        return redirect()->route('admin.projects.show', ['project' => $project, 'tab' => 'addons'])
            ->with('success', 'Add-on created successfully.');
    }

    public function update(Request $request, Project $project, ProjectAddon $addon)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:50',
            'description' => 'nullable|string|max:1000',
            'amount' => 'required|numeric|min:0|max:99999999.99',
            'status' => 'required|in:pending,approved,in_progress,completed,cancelled',
            'requested_date' => 'nullable|date',
            'approved_date' => 'nullable|date',
            'completed_date' => 'nullable|date',
            'notes' => 'nullable|string|max:500',
        ]);

        $addon->update($validated);

        return redirect()->route('admin.projects.show', ['project' => $project, 'tab' => 'addons'])
            ->with('success', 'Add-on updated successfully.');
    }

    public function destroy(Project $project, ProjectAddon $addon)
    {
        $addon->delete();

        return redirect()->route('admin.projects.show', ['project' => $project, 'tab' => 'addons'])
            ->with('success', 'Add-on deleted successfully.');
    }
}
