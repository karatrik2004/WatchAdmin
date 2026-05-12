<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\NotificationEmail;

class NotificationEmailController extends Controller
{
    public function index()
    {
        $emails = NotificationEmail::orderBy('id', 'desc')->paginate(25);
        return view('admin.notification_emails.index', ['emails' => $emails]);
    }

    public function create()
    {
        return view('admin.notification_emails.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'type' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'status' => 'nullable|boolean',
        ]);
        NotificationEmail::create($data);
        return redirect()->route('admin.notification-emails.index')->with('success', 'Notification email added.');
    }

    public function edit($id)
    {
        $item = NotificationEmail::findOrFail($id);
        return view('admin.notification_emails.edit', ['notificationEmail' => $item]);
    }

    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'type' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'status' => 'nullable|boolean',
        ]);
        $item = NotificationEmail::findOrFail($id);
        $item->update($data);
        return redirect()->route('admin.notification-emails.index')->with('success', 'Notification email updated.');
    }

    public function destroy($id)
    {
        $item = NotificationEmail::findOrFail($id);
        $item->delete();
        return redirect()->route('admin.notification-emails.index')->with('success', 'Notification email deleted.');
    }

    public function toggleStatus(Request $request, $id)
    {
        $item = NotificationEmail::findOrFail($id);
        $item->status = !$item->status;
        $item->save();
        return response()->json(['success' => true, 'status' => (bool) $item->status]);
    }
}
