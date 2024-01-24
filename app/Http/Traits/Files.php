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
    public static function saveFileF($file)
    {
        //$file = $request->file('path');
        $theFilePath = null;
        if ($file) {
            $theFilePath = time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('Filepath'), $theFilePath);
            $theFilePath = 'Filepath/' . $theFilePath;
        }
        return $theFilePath;
    }

    public static function saveImage(Request $request)
    {
        $file = $request->file('image');
        $theFilePath = null;
        if ($request->hasFile('image')) {
            $theFilePath = time() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('Imagepath'), $theFilePath);
            $theFilePath = 'Imagepath/' . $theFilePath;
        }
        return $theFilePath;
    }
    public static function deleteFile($file)
    {
        Storage::delete($file);
    }
}
