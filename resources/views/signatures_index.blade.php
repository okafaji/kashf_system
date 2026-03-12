@extends('layouts.app') @section('content')
<div class="container mt-5" dir="rtl">
    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h4 class="mb-0">إعدادات أسماء المسؤولين في الكشوفات</h4>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <form action="{{ route('signatures.update_all') }}" method="POST">
                @csrf
                <table class="table table-bordered text-center">
                    <thead class="bg-light">
                        <tr>
                            <th>المسمى الوظيفي</th>
                            <th>الاسم المعتمد في الطباعة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($signatures as $sig)
                        <tr>
                            <td style="font-family: 'SultanBold'">{{ $sig->title }}</td>
                            <td>
                                <input type="text" name="signatures[{{ $sig->id }}]"
                                       value="{{ $sig->name }}" class="form-control text-center">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                <button type="submit" class="btn btn-success px-5">حفظ التعديلات</button>
            </form>
        </div>
    </div>
</div>
@endsection
