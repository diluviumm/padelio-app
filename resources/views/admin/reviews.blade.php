@extends('layouts.admin')
@section('title', 'Review')
@section('heading', 'Kelola Review')

@section('content')
<div class="card" style="overflow:hidden">
    <div class="table-wrap" style="border:none">
        <table class="tbl">
            <thead><tr><th>Lapangan</th><th>Pemain</th><th>Rating</th><th>Ulasan</th><th>Tanggal</th><th style="text-align:right">Aksi</th></tr></thead>
            <tbody>
            @foreach($reviews as $review)
                <tr>
                    <td>{{ $review->court->name }}</td>
                    <td>{{ $review->user->name }}</td>
                    <td style="color:var(--star)">{{ str_repeat('★', $review->rating) }}</td>
                    <td class="small">{{ $review->text }}</td>
                    <td class="small">{{ $review->created_at->translatedFormat('d M Y') }}</td>
                    <td>
                        <form method="POST" action="{{ route('admin.reviews.destroy', $review) }}" data-confirm="Hapus review ini?">@csrf @method('DELETE')<button class="btn btn-sm btn-danger-soft" type="submit">Hapus</button></form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
<div class="pagination">{{ $reviews->links() }}</div>
@endsection
