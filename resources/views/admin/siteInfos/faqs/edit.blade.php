@extends('admin.layouts.app')

@section('content')
<div class="container mt-4">
    <h3>Edit FAQs</h3>

    <form action="{{ route('faqs.update', $faq->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="mb-3">
            <label for="question" class="form-label">Question</label>
            <input type="text" name="question" id="question" class="form-control" value="{{ $faq->question }}" required>
        </div>

        <div class="mb-3">
            <label for="answer" class="form-label">Answer</label>
            <textarea name="answer" id="answer" class="form-control wysiwyg" rows="3" required>{{ $faq->answer }}</textarea>
        </div>

        <button type="submit" class="btn btn-primary">Update FAQ</button>
    </form>

</div>

@endsection
