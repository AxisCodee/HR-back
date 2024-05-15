<?php

namespace App\Services;

use Illuminate\Support\Facades\File;

class FileService
{
    public function upload($request, $type)
    {
        $path = null;
        if ($type == 'image') {
            if ($request->hasFile('image')) {
                $file = $request->file('image');
                $filename = time() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/images'), $filename);
                $path = 'uploads/images/' . $filename;
            }
        }
        if ($type == 'file') {
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $filename = time() . '.' . $file->getClientOriginalExtension();
                $file->move(public_path('uploads/files'), $filename);
                $path = 'uploads/files/' . $filename;
            }
            return $path;
        }
    }

    public function delete($filename): void
    {
        File::delete($filename);
    }

    public function update($oldFilename, $newFile, $type): string
    {
        $this->delete($oldFilename);
        return $this->upload($newFile, $type);
    }
}
