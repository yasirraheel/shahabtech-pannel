<?php

namespace App\Http\Controllers\Admin;

use App\Lib\FormProcessor;
use App\Models\SocialMedia;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class SocialMediaController extends Controller
{
    public function index()
    {
        $pageTitle    = 'Manage Platforms';
        $socialsMedia = SocialMedia::searchable(['name'])->withCount('accountListing')->orderBy('name')->paginate(getPaginate());
        return view('admin.social_media.index', compact('pageTitle', 'socialsMedia'));
    }

    public function store(Request $request, $id = null)
    {
        $request->validate([
            'name'  => 'required',
            'domain'=> 'required',
            'url'   => 'required|url',
        ]);

        if ($id) {
            $socialMedia   = SocialMedia::findOrFail($id);
            $notifyMessage = 'Social media update successfully';
        } else {
            $socialMedia   = new SocialMedia();
            $notifyMessage = 'Social media added successfully';
        }

        $socialMedia->name    = $request->name;
        $socialMedia->domain  = $request->domain;
        $socialMedia->url     = $request->url;
        $socialMedia->save();

        $notify[] = ['success', $notifyMessage];
        return back()->withNotify($notify);
    }

    public function info($id)
    {
        $socialMedia = SocialMedia::findOrFail($id);
        $form        = $socialMedia->form;
        $pageTitle   = 'Add Social Media Information';
        return view('admin.social_media.add_info', compact('pageTitle', 'socialMedia', 'form'));
    }

    public function infoStore(Request $request, $id)
    {
        $formProcessor = new FormProcessor();

        $generatorValidation = $formProcessor->generatorValidation();
        $validation          =  $generatorValidation['rules'];
        $request->validate($validation, $generatorValidation['messages']);

        $socialMedia   = SocialMedia::findOrFail($id);
        $generate      = $formProcessor->generate('social_media', true, 'id', $socialMedia->form_id);

        $socialMedia->form_id = $generate->id;
        $socialMedia->save();

        $notify[] = ['success', 'Social media requirements updated successfully'];
        return back()->withNotify($notify);
    }

    public function status($id)
    {
        return SocialMedia::changeStatus($id);
    }
}
