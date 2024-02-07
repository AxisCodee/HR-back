<?php

namespace App\Http\Controllers;

use App\Helper\ResponseHelper;
use App\Http\Requests\NoteRequest;
use App\Http\Requests\UpdateNoteRequest;
use App\Models\Note;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NoteController extends Controller
{
    public function index() //all user notes
    {
        $results = Note::query()
            ->where('user_id', Auth::id())
            ->get()
            ->toArray();
        if (empty($results)) {
            return ResponseHelper::success('empty');
        }
        return ResponseHelper::success($results, null);
    }

    public function store(NoteRequest $request)
    {
        $validate = $request->validated();
        return DB::transaction(function () use ($validate) {
            $note = Note::query()->create($validate);
            return ResponseHelper::success($note, null);
        });
        return ResponseHelper::error('error', null);
    }
    public function update(UpdateNoteRequest $request, $id)
    {
        $validate = $request->validated();
        return DB::transaction(function () use ($validate, $id) {
            Note::query()
                ->findOrFail($id) //????
                ->update($validate);
            return ResponseHelper::success('Note has been updated', null);
        });
        return ResponseHelper::error('error', null);
    }

    public function destroy($id)
    {
        return DB::transaction(function () use ($id) {
            $note = Note::query()->findOrFail($id);
            if (Auth::id() !== $note->user_id) {
                return ResponseHelper::error('You do not have permission to delete this note.', null);
            }
            $note->delete();
            return ResponseHelper::success('Note has been deleted', null);
        });
        return ResponseHelper::error('not deleted', null);
    }

    public function specific_note($id)
    {
        $note = Note::findOrFail($id);
        return ResponseHelper::success($note,'Note returned successfully', null);
    }
}
