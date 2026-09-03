<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\WarzonePurchasedLink;
use Illuminate\Http\Request;

class WarzoneLinkController extends Controller
{
    public function index(Request $request)
    {
        $pageTitle = 'Warzone Purchased Links';

        $query = WarzonePurchasedLink::query();

        // Filter by Status
        if ($request->has('status') && $request->status !== null && $request->status !== '') {
            $query->where('status', $request->status);
        }

        // Filter by Source
        if ($request->filled('source')) {
            $query->where('source', $request->source);
        }

        // Search Query
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('link', 'like', "%{$search}%")
                  ->orWhere('product_name', 'like', "%{$search}%")
                  ->orWhere('order_id', 'like', "%{$search}%")
                  ->orWhere('assigned_to', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%");
            });
        }

        $links = $query->orderBy('id', 'desc')->paginate(getPaginate());

        // Quick Summary Counts
        $totalCount     = WarzonePurchasedLink::count();
        $availableCount = WarzonePurchasedLink::where('status', WarzonePurchasedLink::STATUS_AVAILABLE)->count();
        $activeCount    = WarzonePurchasedLink::where('status', WarzonePurchasedLink::STATUS_ACTIVE)->count();
        $usedCount      = WarzonePurchasedLink::where('status', WarzonePurchasedLink::STATUS_USED)->count();
        $expiredCount   = WarzonePurchasedLink::where('status', WarzonePurchasedLink::STATUS_EXPIRED)->count();

        return view('admin.warzone_links.index', compact(
            'pageTitle', 'links', 'totalCount', 'availableCount', 'activeCount', 'usedCount', 'expiredCount'
        ));
    }

    public function store(Request $request, $id = null)
    {
        $request->validate([
            'product_name' => 'required|string|max:255',
            'status'       => 'required|in:0,1,2,3',
            'link'         => $id ? 'required|string' : 'nullable|string',
            'links_batch'  => $id ? 'nullable|string' : 'nullable|string',
            'assigned_to'  => 'nullable|string|max:255',
            'notes'        => 'nullable|string',
        ]);

        if ($id) {
            $link = WarzonePurchasedLink::findOrFail($id);
            $link->product_name = $request->product_name;
            $link->link         = trim($request->link);
            $link->status       = (int) $request->status;
            $link->assigned_to  = $request->assigned_to;
            $link->notes        = $request->notes;
            $link->save();

            $notify[] = ['success', 'Link updated successfully!'];
            return back()->withNotify($notify);
        }

        // New Link(s) addition
        $rawText = $request->filled('links_batch') ? $request->links_batch : $request->link;
        if (empty($rawText)) {
            $notify[] = ['error', 'Please provide at least one link or credential.'];
            return back()->withNotify($notify);
        }

        // Split by lines if multiple links are pasted
        $lines = preg_split('/\r\n|\r|\n/', $rawText);
        $count = 0;

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if (!empty($trimmed)) {
                WarzonePurchasedLink::create([
                    'product_name' => $request->product_name,
                    'service_id'   => $request->service_id ?: 'S_01',
                    'order_id'     => $request->order_id ?: null,
                    'link'         => $trimmed,
                    'source'       => 'manual',
                    'status'       => (int) $request->status,
                    'assigned_to'  => $request->assigned_to,
                    'notes'        => $request->notes,
                    'purchased_at' => now(),
                ]);
                $count++;
            }
        }

        $notify[] = ['success', "{$count} link(s) added successfully!"];
        return back()->withNotify($notify);
    }

    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:0,1,2,3',
        ]);

        $link = WarzonePurchasedLink::findOrFail($id);
        $link->status = (int) $request->status;
        $link->save();

        $notify[] = ['success', 'Status updated successfully!'];
        return back()->withNotify($notify);
    }

    public function delete($id)
    {
        $link = WarzonePurchasedLink::findOrFail($id);
        $link->delete();

        $notify[] = ['success', 'Link deleted successfully!'];
        return back()->withNotify($notify);
    }
}
