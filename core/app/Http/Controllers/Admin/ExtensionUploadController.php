<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ExtensionUploadController extends Controller
{
    public function index()
    {
        $pageTitle = 'Extension Distribution';
        
        $directory = storage_path('app/public/extension');
        $extensionExists = false;
        $lastModified = 'Never';
        if (is_dir($directory)) {
            $files = scandir($directory);
            foreach ($files as $file) {
                if (pathinfo($file, PATHINFO_EXTENSION) === 'zip') {
                    $extensionExists = true;
                    $lastModified = date('F d Y, H:i:s', filemtime($directory . '/' . $file));
                    break;
                }
            }
        }
        
        $downloadUrl = getExtensionDownloadUrl();

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
            
            // Delete any existing extension files to keep the directory clean
            if (is_dir($directory)) {
                $files = scandir($directory);
                foreach ($files as $item) {
                    if (pathinfo($item, PATHINFO_EXTENSION) === 'zip') {
                        @unlink($directory . '/' . $item);
                    }
                }
            }
            
            // Use the original filename provided by the admin (e.g. wemate-ext-v1.4.zip)
            $filename = $file->getClientOriginalName();
            $file->move($directory, $filename);
            
            $notify[] = ['success', 'Extension uploaded successfully!'];
            return back()->withNotify($notify);
        }

        $notify[] = ['error', 'File upload failed.'];
        return back()->withNotify($notify);
    }
}
