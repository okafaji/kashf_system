<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Signature;

class SignatureController extends Controller
{
    // عرض قائمة التواقيع
    public function index()
    {
        $signatures = Signature::all();
        return view('signatures.index', compact('signatures'));
    }

    // عرض صفحة الإضافة
    public function create()
    {
        return view('signatures.create');
    }

    // حفظ توقيع جديد
    public function store(Request $request)
    {
        $request->validate([
            'responsibility_code' => ['required', 'integer', 'between:1,4'],
            'name' => 'required|string|max:255',
        ]);

        $titles = [
            1 => 'مسؤول وحدة',
            2 => 'مسؤول الشعبة',
            3 => 'قسم التدقيق',
            4 => 'رئيس قسم الشؤون المالية'
        ];

        Signature::where('responsibility_code', $request->responsibility_code)
            ->update(['is_active' => false]);

        Signature::create([
            'responsibility_code' => $request->responsibility_code,
            'title' => $titles[$request->responsibility_code],
            'name' => $request->name,
            'is_active' => true,
        ]);

        return redirect()->route('signatures.index')->with('success', 'تم إضافة التوقيع بنجاح');
    }

    // عرض صفحة التعديل
    public function edit($id)
    {
        $signature = Signature::findOrFail($id);
        return view('signatures.edit', compact('signature'));
    }

    // حفظ التعديلات
    public function update(Request $request, $id)
    {
        $signature = Signature::findOrFail($id);

        $request->validate([
            'responsibility_code' => ['required', 'integer', 'between:1,4'],
            'name' => 'required|string|max:255',
        ]);

        $titles = [
            1 => 'مسؤول وحدة',
            2 => 'مسؤول الشعبة',
            3 => 'قسم التدقيق',
            4 => 'رئيس قسم الشؤون المالية'
        ];

        $isActive = $request->boolean('is_active');

        if ($isActive) {
            Signature::where('responsibility_code', $request->responsibility_code)
                ->where('id', '!=', $signature->id)
                ->update(['is_active' => false]);
        }

        $signature->update([
            'responsibility_code' => $request->responsibility_code,
            'title' => $titles[$request->responsibility_code],
            'name' => $request->name,
            'is_active' => $isActive,
        ]);

        return redirect()->route('signatures.index')->with('success', 'تم تحديث التوقيع بنجاح');
    }

    // حذف توقيع
    public function destroy($id)
    {
        $signature = Signature::findOrFail($id);
        $signature->delete();
        return redirect()->route('signatures.index')->with('success', 'تم حذف التوقيع بنجاح');
    }

    // تفعيل/إلغاء التفعيل
    public function toggleActive($id)
    {
        $signature = Signature::findOrFail($id);
        $nextState = !$signature->is_active;

        if ($nextState) {
            Signature::where('responsibility_code', $signature->responsibility_code)
                ->where('id', '!=', $signature->id)
                ->update(['is_active' => false]);
        }

        $signature->update(['is_active' => $nextState]);

        return redirect()->route('signatures.index')->with('success', 'تم تحديث حالة التوقيع');
    }
}
