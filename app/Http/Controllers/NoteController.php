<?php

namespace App\Http\Controllers;

use App\Helper\ResponseHelper;
use App\Http\Requests\NoteRequest\StoreNoteRequest;
use App\Http\Requests\NoteRequest\UpdateNoteRequest;
use App\Models\Note;
use App\Services\NoteService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class NoteController extends Controller
{
    protected $noteService;

    public function __construct(NoteService $noteService)
    {
        $this->noteService = $noteService;
    }

    public function index()
    {
        return $this->noteService->index();
    }

    public function store(StoreNoteRequest $request)
    {
        $validatedData = $request->validated();
        return $this->noteService->store($validatedData);
    }


    public function update(UpdateNoteRequest $request, $id)
    {
        $validatedData = $request->validated();
        return $this->noteService->update($validatedData, $id);
    }
    public function destroy($id)
    {
      return $this->noteService->destroy($id);
    }

    public function user_notes($id)
    {
       return $this->noteService->getUserNotes($id);
    }
}
