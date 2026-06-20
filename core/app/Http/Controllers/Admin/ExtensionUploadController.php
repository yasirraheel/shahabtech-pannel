<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ExtensionUploadController extends Controller
{
    public function index()
    {
        $pageTitle = 'Extension Distribution';
        $downloadUrl = url('extension/download');
        
        $extensionExists = file_exists(storage_path('app/public/extension/wemate-ext.zip'));
        $lastModified = $extensionExists ? date('F d Y, H:i:s', filemtime(storage_path('app/public/extension/wemate-ext.zip'))) : 'Never';

        return view('admin.extension.upload', compact('pageTitle', 'downloadUrl', 'extensionExists', 'lastModified'));
    }

    public function upload(Request $request)
    {
        $request->validate([
            'extension_zip' => 'required|file|mimes:zip',
        ]);

        if ($request->hasFile('extension_zip')) {
            $file = $request->file('extension_zip');
            
            // Define the storage directory
            $directory = storage_path('app/public/extension');
            
            // Create the directory if it does not exist
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }
            
            // We always save it as wemate-ext.zip to keep the download link constant
            $file->move($directory, 'wemate-ext.zip');
            
            $notify[] = ['success', 'Extension uploaded successfully!'];
            return back()->withNotify($notify);
        }

        $notify[] = ['error', 'File upload failed.'];
        return back()->withNotify($notify);
    }
}
