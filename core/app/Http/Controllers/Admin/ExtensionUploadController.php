<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ExtensionUploadController extends Controller
{
    public function index()
    {
        $pageTitle = 'Extension Distribution & Versioning';
        
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
        $minVersion = gs('min_extension_version') ?: '1.9.6';
        $forceUpdate = gs('force_extension_update') ? 1 : 0;

        return view('admin.extension.upload', compact('pageTitle', 'downloadUrl', 'extensionExists', 'lastModified', 'minVersion', 'forceUpdate'));
    }

    public function upload(Request $request)
    {
        $request->validate([
            'extension_zip'         => 'nullable|file|mimes:zip',
            'min_extension_version' => 'required|string',
        ]);

        $general = gs();
        $general->min_extension_version = $request->min_extension_version;
        $general->force_extension_update = $request->force_extension_update ? 1 : 0;
        $general->save();

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
            
            // Use the original filename provided by the admin (e.g. wemate-ext-v1.6.zip)
            $filename = $file->getClientOriginalName();
            $targetPath = $directory . '/' . $filename;
            $file->move($directory, $filename);

            // Auto-flatten ZIP so manifest.json and extension files are in a single root folder (no nested folder-in-folder)
            $this->flattenExtensionZip($targetPath);
        }

        $notify[] = ['success', 'Extension distribution settings updated successfully!'];
        return back()->withNotify($notify);
    }

    private function flattenExtensionZip($zipPath)
    {
        if (!file_exists($zipPath) || !class_exists('ZipArchive')) {
            return false;
        }

        $zip = new \ZipArchive();
        if ($zip->open($zipPath) !== true) {
            return false;
        }

        $manifestPath = null;
        $prefix = '';
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);
            if ($name === 'manifest.json') {
                $manifestPath = $name;
                $prefix = '';
                break;
            } elseif (preg_match('#(^|/)(manifest\\.json)$#i', $name)) {
                $manifestPath = $name;
                $prefix = substr($name, 0, strlen($name) - strlen('manifest.json'));
                break;
            }
        }

        if (!$manifestPath || $prefix === '') {
            $zip->close();
            return true;
        }

        $tempZipPath = $zipPath . '.clean.tmp.zip';
        $newZip = new \ZipArchive();
        if ($newZip->open($tempZipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            $zip->close();
            return false;
        }

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = $zip->getNameIndex($i);

            if (strpos($name, '__MACOSX/') === 0 || basename($name) === '.DS_Store' || basename($name) === 'Thumbs.db') {
                continue;
            }

            if (strpos($name, $prefix) === 0) {
                $relName = substr($name, strlen($prefix));
                if ($relName === '' || $relName === false) {
                    continue;
                }

                if (substr($relName, -1) === '/') {
                    $newZip->addEmptyDir($relName);
                } else {
                    $content = $zip->getFromIndex($i);
                    if ($content !== false) {
                        $newZip->addFromString($relName, $content);
                    }
                }
            }
        }

        $zip->close();
        $newZip->close();

        if (file_exists($tempZipPath)) {
            @unlink($zipPath);
            rename($tempZipPath, $zipPath);
        }

        return true;
    }
}
