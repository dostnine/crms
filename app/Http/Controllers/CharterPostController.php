<?php

namespace App\Http\Controllers;

use App\Models\CharterPost;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

/**
 * What is posted on the Bulletin, Events and Others sheets of the Citizen's
 * Charter book (public/charter): kept by an admin under Libraries, and read
 * by the book as JSON. A post on the Others sheet may have a picture.
 */
class CharterPostController extends Controller
{
    // The pictures are kept on the local disk, out of the public folder, and
    // served by image() below: nothing needs to be linked into public/ for
    // them to show.
    const PICTURE_DISK = 'local';
    const PICTURE_FOLDER = 'charter-posts';

    public function index(Request $request)
    {
        $search = $request->search;
        $sheet = in_array($request->sheet, CharterPost::SHEETS) ? $request->sheet : null;

        $posts = CharterPost::when($search, function ($query, $search) {
            $query->where(function ($query) use ($search) {
                $query->where('title', 'like', '%' . $search . '%')
                      ->orWhere('details', 'like', '%' . $search . '%');
            });
        })
        ->when($sheet, function ($query, $sheet) {
            $query->where('sheet', $sheet);
        })
        ->orderByDesc('id')
        ->paginate(10);

        return Inertia::render('Libraries/CharterPosts/Index')
                    ->with('posts', $posts)
                    ->with('sheet', $sheet);
    }

    public function store(Request $request)
    {
        $post = $this->checked($request);
        $post['image_path'] = $this->picture($request, $post['sheet'], null);
        CharterPost::create($post);

        return Redirect::back();
    }

    public function update(Request $request)
    {
        $post = CharterPost::findOrFail($request->id);
        $changed = $this->checked($request);
        $changed['image_path'] = $this->picture($request, $changed['sheet'], $post->image_path);
        $post->update($changed);

        return Redirect::back();
    }

    public function destroy(Request $request)
    {
        $post = CharterPost::findOrFail($request->id);
        if ($post->image_path) {
            Storage::disk(self::PICTURE_DISK)->delete($post->image_path);
        }
        $post->delete();

        return Redirect::back();
    }

    // The words of a post as the form sends them, checked. The lengths are
    // what a sheet of the book has room for. (The picture is checked here
    // too, and kept by picture() below.)
    private function checked(Request $request)
    {
        $post = $request->validate([
            'sheet' => ['required', Rule::in(CharterPost::SHEETS)],
            'title' => ['required', 'string', 'max:150'],
            'date_text' => ['nullable', 'string', 'max:80'],
            'details' => ['nullable', 'string', 'max:600'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:8192'],
        ], [
            'image.uploaded' => 'The picture is larger than this server takes in one upload.',
            'image.image' => 'The picture must be a JPG, PNG, WebP or GIF image.',
            'image.mimes' => 'The picture must be a JPG, PNG, WebP or GIF image.',
            'image.max' => 'The picture may not be larger than 8 MB.',
        ], [
            'date_text' => 'date',
        ]);

        return Arr::only($post, ['sheet', 'title', 'date_text', 'details']);
    }

    // Where the picture of a post is kept once the post is saved. Only a
    // post on the Others sheet has one: a new picture takes the place of
    // the one it had, and `remove_image`, or a move to another sheet, takes
    // the picture away.
    private function picture(Request $request, $sheet, $had)
    {
        $keeps = $sheet === CharterPost::PICTURE_SHEET && !$request->boolean('remove_image');
        $new = $keeps && $request->hasFile('image');

        if ($keeps && !$new) {
            return $had;
        }
        if ($had) {
            Storage::disk(self::PICTURE_DISK)->delete($had);
        }

        return $new ? $request->file('image')->store(self::PICTURE_FOLDER, self::PICTURE_DISK) : null;
    }

    /**
     * Everything that is posted, for the book: the posts of each sheet, the
     * newest first, as the book shows them ({ title, when, text, image }).
     * Not cached, so a post shows on a display that is left running as soon
     * as the book asks again.
     */
    public function posts()
    {
        $posted = CharterPost::orderByDesc('id')->get()->groupBy('sheet');

        $sheets = [];
        foreach (CharterPost::SHEETS as $sheet) {
            $sheets[$sheet] = $posted->get($sheet, collect())->map(fn ($post) => [
                'title' => $post->title,
                'when' => $post->date_text,
                'text' => $post->details,
                'image' => $post->image_url,
            ])->values();
        }

        return response()->json($sheets)->header('Cache-Control', 'no-store');
    }

    /**
     * The picture of a post, for the book and for the list under Libraries.
     * Its address changes when the post does (CharterPost::image_url), so a
     * browser may keep it.
     */
    public function image(CharterPost $post)
    {
        $disk = Storage::disk(self::PICTURE_DISK);
        abort_unless($post->image_path && $disk->exists($post->image_path), 404);

        return $disk->response($post->image_path, null, [
            'Cache-Control' => 'public, max-age=604800',
        ]);
    }
}
