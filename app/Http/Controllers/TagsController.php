<?php

namespace App\Http\Controllers;

use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class TagsController extends Controller
{

    public function index(): Response
    {
        return Inertia::render('Tags', [
            'tags' => Tag::all(),
        ]);
    }

    public function create(Request $request): Response
    {
        return Inertia::render('Tags/Create', [
            'name' => $request->query('name'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique(Tag::class)
            ]
        ]);
        Tag::create($validated);

        return redirect()->route('tags.index')->with('message', 'Tag created successfully.');
    }


    public function edit(Tag $tag): Response
    {
        return Inertia::render('Tags/Edit', [
            'tag' => $tag
        ]);
    }

    public function update(Request $request, Tag $tag): RedirectResponse
    {

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique(Tag::class)->ignore($tag->id)
            ]
        ]);

        $tag->update($validated);

        return redirect()->route('tags.index')->with('message', 'Tag updated successfully.');
    }

    public function destroy(Tag $tag): RedirectResponse
    {
        $tag->delete();
        return redirect()->route('tags.index')->with('message', 'Tag deleted successfully.');
    }

    /**
     * Display the specified resource.
     *
     * @param Tag $tag
     * @return Response
     */
    public function show(Tag $tag): Response
    {
        return Inertia::render('Tags/Show', [
            'tag' => $tag,
        ]);
    }

}