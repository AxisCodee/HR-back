<?php
namespace App\Http\Traits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

trait Files
{
    public static function saveFile(Request $request)
    {

        $file = $request->file('path');
        $theFilePath = null;

        if ($request->hasFile('path')) {
            $theFilePath = time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('Filepath'), $theFilePath);
            $theFilePath = 'Filepath/' . $theFilePath;
        }

        return $theFilePath;

    }
    public static function deleteFile($file)
    {
        Storage::delete($file);

    }
}
