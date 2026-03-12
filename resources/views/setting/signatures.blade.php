@extends('layouts.app')

@section('content')
<div class="container mx-auto p-6">
    <div class="bg-white shadow-md rounded-lg p-6">
        <h2 class="text-2xl font-bold mb-6 border-b pb-2 text-blue-800">إعدادات أسماء الموقعين على الكشوفات</h2>

        <form action="{{ route('signatures.update_all') }}" method="POST">
            @csrf
            <table class="w-full border-collapse border border-gray-200">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="border p-3 text-right">المسمى الوظيفي</th>
                        <th class="border p-3 text-right">الاسم المعتمد في الطباعة</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($signatures as $sig)
                    <tr>
                        <td class="border p-3 bg-gray-50 font-bold">{{ $sig->title }}</td>
                        <td class="border p-3">
                            <input type="text" name="names[{{ $sig->id }}]" value="{{ $sig->name }}"
                                   class="w-full border-blue-400 rounded p-2 focus:ring-2 focus:ring-blue-500">
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="mt-6">
                <button type="submit" class="bg-green-600 text-white px-8 py-2 rounded shadow hover:bg-green-700 transition">
                    حفظ التعديلات
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
