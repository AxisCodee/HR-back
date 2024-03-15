<?php

namespace App\Services;

use App\Models\Note;
use App\Helper\ResponseHelper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NoteService
{
    public function index()
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
            return ResponseHelper::error('An error occurred while retrieving the notes.', null);
        }
    }



    public function store($validatedData)
    {
        try {
            return DB::transaction(function () use ($validatedData) {
                $note = Note::query()->create($validatedData);
                return ResponseHelper::success($note, null);
            });
        } catch (\Exception $e) {
            return ResponseHelper::error('An error occurred while storing the note.', null);
        }
    }



    public function update($validatedData, $id)
    {
        try {
            return DB::transaction(function () use ($validatedData, $id) {
                Note::query()
                    ->findOrFail($id)
                    ->update($validatedData);
                return ResponseHelper::success('Note has been updated', null);
            });
        } catch (\Exception $e) {
            return ResponseHelper::error('An error occurred while editing the note.', null);
        }
    }


    public function destroy($id)
    {
        try {
            return DB::transaction(function () use ($id) {
                $note = Note::query()->findOrFail($id);
                $note->delete();
                return ResponseHelper::success('Note has been deleted', null);
            });
        } catch (\Exception $e) {
            return ResponseHelper::error('An error occurred while deleting the note.', null);
        }
    }


    public function getUserNotes($id)
    {
        try {
            $notes = Note::where('user_id', $id)->get()->toArray();
            return ResponseHelper::success($notes, 'Notes returned successfully', null);
        } catch (\Exception $e) {
            return ResponseHelper::error('An error occurred while retrieving the notes.', null);
        }
    }
}
