<?php

namespace App\Http\Controllers;

use App\Helper\ResponseHelper;
use App\Http\Requests\NoteRequest\StoreNoteRequest;
use App\Http\Requests\NoteRequest\UpdateNoteRequest;
use App\Models\Note;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NoteController extends Controller
{
    public function index() //all user notes
    {
        try {
            $results = Note::query()
                ->where('user_id', Auth::id())
                ->get()
                ->toArray();
            if (empty($results)) {
                return ResponseHelper::success('empty');
            }
            return ResponseHelper::success($results, null);
        } catch (\Exception $e) {
            return ResponseHelper::error('An error occurred while retriving the notes.', null);
        }
    }

    public function store(StoreNoteRequest $request)
    {
        try {
            $validate = $request->validated();
            return DB::transaction(function () use ($validate) {
                $note = Note::query()->create($validate);
                return ResponseHelper::success($note, null);
            });
        } catch (\Exception $e) {
            return ResponseHelper::error('An error occurred while storing the note.', null);
        }
    }

    public function update(UpdateNoteRequest $request, $id)
    {
        try {
            $validate = $request->validated();
            return DB::transaction(function () use ($validate, $id) {
                Note::query()
                    ->findOrFail($id)
                    ->update($validate);
                return ResponseHelper::success('Note has been updated', null);
            });
        } catch (\Exception $e) {
            return ResponseHelper::error('An error occurred while editing the note.', null);
        }
    }

    public function destroy($id)
    {
        try {
            $note = Note::query()->findOrFail($id);
            return DB::transaction(function () use ($note) {
                $note->delete();
                return ResponseHelper::success('Note has been deleted', null);
            });
        } catch (\Exception $e) {
            return ResponseHelper::error('An error occurred while deleting the note.', null);
        }
    }

    public function user_notes($id)
    {
        $note = Note::where('user_id', $id)->get()->toArray();
        return ResponseHelper::success($note, 'Note returned successfully', null);
    }
}
